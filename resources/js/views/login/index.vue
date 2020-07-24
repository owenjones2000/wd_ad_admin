<template>
  <div class="login-container">
    <el-form ref="loginForm" :model="loginForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">
      <h3 class="title">
        {{ $t('login.title') }}
      </h3>
      <lang-select class="set-language" />
      <el-form-item prop="email">
        <span class="svg-container">
          <svg-icon icon-class="user" />
        </span>
        <el-input
          v-model="loginForm.phone"
          name="phone"
          type="text"
          auto-complete="on"
          placeholder="phone"
        />
        <!-- <el-button slot="append" icon="el-icon-search" /> Send Code -->
      </el-form-item>
      <el-form-item prop="password">
        <span class="svg-container">
          <svg-icon icon-class="password" />
        </span>
        <el-input
          v-model="loginForm.code"
          type="text"
          name="code"
          auto-complete="on"
          placeholder="code"
          @keyup.enter.native="handleLogin"
        />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" style="width:100%;" :disabled="disabled" @click="handleSend">
          {{ btnTitle }}
        </el-button>
      </el-form-item>
      <el-form-item>
        <el-button :loading="loading" type="primary" style="width:100%;" :disabled="isClick" @click.native.prevent="handleLogin">
          Sign in
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import LangSelect from '@/components/LangSelect';
import { validPhone } from '@/utils/validate';
import request from '@/utils/request';

export default {
  name: 'Login',
  components: { LangSelect },
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!validPhone(value)) {
        callback(new Error('Please enter the correct phone'));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (value.length < 4) {
        callback(new Error('Password cannot be less than 4 digits'));
      } else {
        callback();
      }
    };

    return {
      disabled: false,
      loginForm: {
        phone: '',
        code: '',
      },
      btnTitle: 'Send Code',
      loginRules: {
        phone: [{ required: true, trigger: 'blur', validator: validatePhone }],
        code: [{ required: true, trigger: 'blur', validator: validatePass }],
      },
      loading: false,
      redirect: undefined,
    };
  },
  computed: {
    // 手机号和验证码都不能为空
    isClick(){
      if (!this.loginForm.phone || !this.loginForm.code) {
        return true;
      } else {
        return false;
      }
    },
  },
  watch: {
    $route: {
      handler: function(route) {
        this.redirect = route.query && route.query.redirect;
      },
      immediate: true,
    },
  },
  methods: {
    handleLogin() {
      console.log(this.loginForm);
      // this.$refs.loginForm.validate(valid => {
      //   if (valid) {
      //     this.loading = true;
      //     this.$store.dispatch('user/login', this.loginForm)
      //       .then(() => {
      //         this.$router.push({ path: this.redirect || '/' });
      //         this.loading = false;
      //       })
      //       .catch(() => {
      //         this.loading = false;
      //       });
      //   } else {
      //     this.$message.error('Please enter the correct');
      //     return false;
      //   }
      // });
      this.loading = true;
      request
        .post('auth/login', this.loginForm)
        .then(response => {
          console.log(response);
          this.$router.push({ path: this.redirect || '/' });
          this.loading = false;
          // this.formData = new FormData();
        })
        .catch(error => {
          console.log(error);
          this.loading = false;
          // this.formData = new FormData();
        });
      console.log(this.btnTitle);
    },
    handleSend(){
      console.log(this.loginForm);
      if (this.validatePhone()) {
        // 发送网络请求
        request
          .post('auth/sendcode', this.loginForm)
          .then(response => {
            console.log(response);
            if (response.status){
              this.validateBtn();
              this.$message({
                message: 'success',
                type: 'success',
              });
            } else {
              this.$message.error(response.error);
            }
            // this.formData = new FormData();
          })
          .catch(error => {
            console.log(error);
            // this.formData = new FormData();
          });
      }
    },
    validatePhone(){
      if (!this.loginForm.phone) {
        this.$message.error('Please enter the correct phone');
      } else if (!/^1[3456789]\d{9}$/.test(this.loginForm.phone)) {
        this.$message.error('Please enter the correct phone');
      } else {
        return true;
      }
    },
    validateBtn(){
      // 倒计时
      let time = 60;
      const timer = setInterval(() => {
        if (time === 0) {
          clearInterval(timer);
          this.disabled = false;
          this.btnTitle = 'Send code';
        } else {
          this.btnTitle = time + '  Seconds Retry';
          this.disabled = true;
          time--;
        }
      }, 1000);
    },
  },
};
</script>

 <style rel="stylesheet/scss" lang="scss">
$bg:#2d3a4b;
$light_gray:#eee;

/* reset element-ui css */
.login-container {
  .el-input {
    display: inline-block;
    height: 47px;
    width: 85%;
    input {
      background: transparent;
      border: 0px;
      -webkit-appearance: none;
      border-radius: 0px;
      padding: 12px 5px 12px 15px;
      color: $light_gray;
      height: 47px;
      &:-webkit-autofill {
        -webkit-box-shadow: 0 0 0px 1000px $bg inset !important;
        -webkit-text-fill-color: #fff !important;
      }
    }
  }
  .el-form-item {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    color: #454545;
  }
}

</style>

<style rel="stylesheet/scss" lang="scss" scoped>
$bg:#2d3a4b;
$dark_gray:#889aa4;
$light_gray:#eee;
.login-container {
  position: fixed;
  height: 100%;
  width: 100%;
  background-color: $bg;
  .login-form {
    position: absolute;
    left: 0;
    right: 0;
    width: 520px;
    max-width: 100%;
    padding: 35px 35px 15px 35px;
    margin: 120px auto;
  }
  .tips {
    font-size: 14px;
    color: #fff;
    margin-bottom: 10px;
    span {
      &:first-of-type {
        margin-right: 16px;
      }
    }
  }
  .svg-container {
    padding: 6px 5px 6px 15px;
    color: $dark_gray;
    vertical-align: middle;
    width: 30px;
    display: inline-block;
  }
  .title {
    font-size: 26px;
    font-weight: 400;
    color: $light_gray;
    margin: 0px auto 40px auto;
    text-align: center;
    font-weight: bold;
  }
  .show-pwd {
    position: absolute;
    right: 10px;
    top: 7px;
    font-size: 16px;
    color: $dark_gray;
    cursor: pointer;
    user-select: none;
  }
  .set-language {
    color: #fff;
    position: absolute;
    top: 40px;
    right: 35px;
  }
}
</style>
