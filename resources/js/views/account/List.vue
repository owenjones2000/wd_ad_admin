<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button v-permission="['advertise.account.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
    </div>

    <el-table
      v-loading="loading"
      :data="list"
      row-key="rowKey"
      border
      fit
      highlight-current-row
      style="width: 100%"
      :tree-props="{children: 'children', hasChildren: 'hasChildren'}"
    >
      <el-table-column prop="email" align="left" label="Email" />
      <el-table-column prop="realname" align="center" label="Real Name" />
      <el-table-column prop="phone" align="center" label="Phone" />

      <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-link :type="scope.row.status ? 'success' : 'info'" size="small" icon="el-icon-s-custom" :underline="false" @click="handleStatus(scope.row)" />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Advertising Status">
        <template slot-scope="scope">
          <el-link :type="scope.row.isAdvertiseEnabled ? 'success' : 'info'" size="small" icon="el-icon-upload" :underline="false" @click="handleAdvertisingStatus(scope.row)" />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="200">
        <template slot-scope="scope">
          <el-button v-permission="['advertise.account.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)">
            Edit
          </el-button>
          <el-button v-permission="['advertise.bill']" type="primary" size="small" icon="el-icon-edit" @click="handleBillSet(scope.row)">
            BillSet
          </el-button>
          <!--<el-button v-permission="['advertise.account.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">-->
          <!--  Delete-->
          <!--</el-button>-->
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="isNewAccount?'Add Main Account':'Edit Account'" :visible.sync="dialogFormVisible">
      <div v-loading="accountCreating" class="form-container">
        <el-form ref="accountForm" :rules="rules" :model="currentAccount" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('email')" prop="email">
            <el-input v-model="currentAccount.email" />
          </el-form-item>
          <el-form-item :label="$t('account.realname')" prop="realname">
            <el-input v-model="currentAccount.realname" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="currentAccount.password" show-password />
          </el-form-item>
          <el-form-item :label="$t('user.confirmPassword')" prop="confirmPassword">
            <el-input v-model="currentAccount.confirmPassword" show-password />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="saveAccount()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Bill Set'" :visible.sync="billDialogFormVisible">
      <div v-loading="billSetting" class="form-container">
        <el-form ref="billSetForm" :rules="billSetRules" :model="currentBillSet" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('bill.address')" prop="address">
            <el-input v-model="currentBillSet.address" type="textarea" />
          </el-form-item>
          <el-form-item :label="$t('bill.phone')" prop="phone">
            <el-input v-model="currentBillSet.phone" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="billDialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="saveBillSet()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AccountResource from '@/api/account';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const accountResource = new AccountResource();

export default {
  name: 'AccountList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: [],
      total: 0,
      loading: true,
      downloading: false,
      accountCreating: false,
      billSetting: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      newAccount: {},
      isNewAccount: false,
      dialogFormVisible: false,
      passwordRequired: true,
      currentAccountId: 0,
      currentAccount: {
        email: '',
      },
      currentBillSet: {
        address: '',
        phone: '',
      },
      billDialogFormVisible: false,
    };
  },
  computed: {
    rules() {
      var validateConfirmPassword = (rule, value, callback) => {
        if (value !== this.currentAccount.password) {
          callback(new Error('Password is mismatched!'));
        } else {
          callback();
        }
      };
      return {
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        realname: [{ required: true, message: 'Real name is required', trigger: 'blur' }],
        password: [{ required: this.passwordRequired, message: 'Password is required', trigger: 'blur' }],
        confirmPassword: [{ validator: validateConfirmPassword, trigger: 'blur' }],
      };
    },
    billSetRules() {
      return {
        address: [
          { required: true, message: 'Address is required', trigger: 'blur' },
          { max: 255 },
        ],
        phone: [{ max: 13 }],
      };
    },
  },
  created() {
    this.resetNewAccount();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await accountResource.list(this.query);
      this.list = data;
      var rowKey = 1;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
        element['rowKey'] = rowKey++;
        element.children.forEach((child) => {
          child['rowKey'] = rowKey++;
        });
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewAccount();
      this.isNewAccount = true;
      this.currentAccount = this.newAccount;
      this.passwordRequired = true;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['accountForm'].clearValidate();
      });
    },
    handleEdit(account) {
      this.isNewAccount = false;
      this.currentAccount = account;
      this.passwordRequired = false;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['accountForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete account ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        accountResource.destroy(id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    handleStatus(account) {
      if (!checkPermission(['advertise.account.edit'])) {
        this.$message({
          type: 'warning',
          message: 'No permission',
        });
        return;
      }
      var displayName = account.realname + '(' + account.email + ')';
      this.$confirm('This will ' + (account.status ? 'disable' : 'enable') + ' account ' + displayName + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (account.status) {
          accountResource.disable(account.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Account ' + displayName + ' disabled',
            });
            account.status = false;
          }).catch(error => {
            console.log(error);
          });
        } else {
          accountResource.enable(account.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Account ' + displayName + ' enabled',
            });
            account.status = true;
          }).catch(error => {
            console.log(error);
          });
        }
      }).catch(error => {
        console.log(error);
      });
    },
    handleAdvertisingStatus(account) {
      if (!checkPermission(['advertise.account.edit'])) {
        this.$message({
          type: 'warning',
          message: 'No permission',
        });
        return;
      }
      var displayName = account.realname + '(' + account.email + ')';
      this.$confirm('This will ' + (account.isAdvertiseEnabled ? 'disable' : 'enable') + ' advertising for account ' + displayName + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (account.isAdvertiseEnabled) {
          accountResource.disableAdvertising(account.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Advertising of Account ' + displayName + ' disabled',
            });
            account.isAdvertiseEnabled = false;
          }).catch(error => {
            console.log(error);
          });
        } else {
          accountResource.enableAdvertising(account.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Advertising of Account ' + displayName + ' enabled',
            });
            account.isAdvertiseEnabled = true;
          }).catch(error => {
            console.log(error);
          });
        }
      }).catch(error => {
        console.log(error);
      });
    },
    saveAccount() {
      this.$refs['accountForm'].validate((valid) => {
        if (valid) {
          this.accountCreating = true;
          accountResource
            .save(this.currentAccount)
            .then(response => {
              this.$message({
                message: 'Account ' + this.currentAccount.email + ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewAccount();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.accountCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewAccount() {
      this.newAccount = {
        email: '',
        realname: '',
        password: '',
        confirmPassword: '',
      };
    },
    handleBillSet(account) {
      this.currentAccount = account;
      this.currentBillSet = account.bill ? account.bill : { address: '', phone: '' };
      this.currentBillSet.id = account.id;
      this.billDialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['billSetForm'].clearValidate();
      });
    },
    saveBillSet() {
      this.$refs['billSetForm'].validate((valid) => {
        if (valid) {
          this.billSetting = true;
          accountResource
            .saveBillSet(this.currentBillSet)
            .then(response => {
              this.$message({
                message: 'The bill set of Account ' + this.currentAccount.email + ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewAccount();
              this.billDialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.billSetting = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'account_id', 'name'];
        const filterVal = ['index', 'id', 'name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'account-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}
.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;
  .block {
    float: left;
    min-width: 250px;
  }
  .clear-left {
    clear: left;
  }
}
</style>
