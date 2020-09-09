<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button v-permission="['advertise.app.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <!-- <el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">
        Export
      </el-button> -->
    </div>

    <el-table
      v-loading="loading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%"
    >
      <el-table-column prop="id" align="center" label="ID" />
      <el-table-column prop="name" align="center" label="Name" />
      <!-- <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-link :type="scope.row.status ? 'success' : 'info'" size="small" icon="el-icon-s-custom" :underline="false" @click="handleStatus(scope.row)" />
        </template>
      </el-table-column> -->
      <!-- <el-table-column align="center" label="Actions" width="360">
        <template slot-scope="scope">
          <el-button v-permission="['advertise.app.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)">
            Edit
          </el-button>
        </template>
      </el-table-column> -->
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="isNewAccount?'Add  Tag':'Edit Tag'" :visible.sync="dialogFormVisible">
      <div v-loading="accountCreating" class="form-container">
        <el-form ref="accountForm" :rules="rules" :model="currentAccount" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item label="Tag Name" prop="name">
            <el-input v-model="currentAccount.name" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="save()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AppResource from '@/api/app';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import clipboard from '@/directive/clipboard/index.js'; // use clipboard by v-directive

const appResource = new AppResource();

export default {
  name: 'AccountList',
  components: { Pagination },
  directives: { waves, permission, clipboard },
  data() {
    return {
      list: [],
      record: {
        amount: null,
        date: null,
      },
      total: 0,
      loading: true,
      downloading: false,
      accountCreating: false,
      addCrediting: false,
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
      dialogCreditVisible: false,
      dialogCashVisible: false,
      passwordRequired: true,
      currentAccountId: 0,
      currentAccount: {
        name: '',
      },
      currentBillSet: {
        address: '',
        phone: '',
      },
      billDialogFormVisible: false,
      dialogAssign: {
        title: 'Assign',
        loading: false,
        visible: false,
        account: null,
        form: {
          email: '',
        },
      },
      dialogTokenFormVisible: false,
      dialogTokenFormName: 'User token',
      newToken: {
        expired_at: null,
      },
      currentUserTokens: [],
      dialogPermission: {
        title: 'Permissions',
        loading: false,
        visible: false,
        allPermission: null,
      },
    };
  },
  computed: {
    rules() {
      return {
        name: [{ required: true, message: 'name is required', trigger: 'blur' }],
      };
    },
    assignRules() {
      return {
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
      };
    },
  },
  created() {
    this.resetNewTag();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await appResource.tagList(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    clipboardSuccess() {
      this.$message({
        message: 'Copy token successfully',
        type: 'success',
        duration: 1500,
      });
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleTreeCheck(node, checked) {
      var tree_node = this.$refs.permissionTree.getNode(node);
      this.recursionSelectTreeNode(tree_node, checked);
    },
    recursionSelectTreeNode(node, checked, direction = ''){
      node.checked = checked;
      if (direction !== 'down' && checked && node.parent && node.parent.id > 0) {
        this.recursionSelectTreeNode(node.parent, checked, 'up');
      }
      if (direction !== 'up' && !checked && node.childNodes && node.childNodes.length > 0){
        node.childNodes.forEach(function(item){
          this.recursionSelectTreeNode(item, checked, 'down');
        }, this);
      }
    },
    handleCreate() {
      this.resetNewTag();
      this.isNewAccount = true;
      this.currentAccount = this.newAccount;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['accountForm'].clearValidate();
      });
    },
    handleEdit(account) {
      this.isNewAccount = false;
      this.currentAccount = account;
      console.log(account);
      console.log(this.currentAccount);
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['accountForm'].clearValidate();
      });
    },
    handleStatus(account) {
      if (!checkPermission(['advertise.app.edit'])) {
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
          appResource.disable(account.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Account ' + displayName + ' disabled',
            });
            account.status = false;
          }).catch(error => {
            console.log(error);
          });
        } else {
          appResource.enable(account.id).then(response => {
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
    save() {
      this.$refs['accountForm'].validate((valid) => {
        if (valid) {
          this.accountCreating = true;
          appResource
            .saveTag(this.currentAccount)
            .then(response => {
              this.$message({
                message: 'Tag ' + this.currentAccount.name + ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewTag();
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
    resetNewTag() {
      this.newTag = {
        name: '',
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
