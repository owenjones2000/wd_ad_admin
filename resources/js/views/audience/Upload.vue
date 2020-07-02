<template>
  <div class="app-container">
    <el-upload
      class="upload-demo"
      drag
      action=""
      accept="csv"
      :on-change="handleFileChange"
      :http-request="uploadFile"
    >
      <i class="el-icon-upload" />
      <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
      <div slot="tip" class="el-upload__tip">只能上传csv文件</div>
    </el-upload>
  </div>
</template>

<script>
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import request from '@/utils/request';

export default {
  name: 'AudienceUpload',
  directives: { waves, permission },
  data() {
    return {
      form: {
        idfa_file: '',
      },
      formData: null,
    };
  },
  computed: {
  },
  created() {
    this.formData = new FormData();
  },
  methods: {
    checkPermission,
    handleFileChange(file){
      this.form.idfa_file = file.raw;
      this.formData.append('idfa_file', file.raw);
      console.log(this.formData);
    },
    uploadFile(){
      request.post('/audience/upload', this.formData).then(response => {
        this.$message({
          type: 'success',
        });
        this.formData = new FormData();
      }).catch(error => {
        console.log(error);
        this.formData = new FormData();
      });
    //   audienceResource.upload(this.formData).then(response => {
    //     this.$message({
    //       type: 'success',
    //     });
    //   }).catch(error => {
    //     console.log(error);
    //   });
    },
  },
};
</script>
