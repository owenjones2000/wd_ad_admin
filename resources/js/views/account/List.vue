<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        :placeholder="$t('table.keyword')"
        style="width: 200px"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >
        {{ $t('table.search') }}
      </el-button>
      <el-button
        v-permission="['advertise.account.edit']"
        class="filter-item"
        style="margin-left: 10px"
        type="primary"
        icon="el-icon-plus"
        @click="handleCreate"
      >
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
      <el-table-column align="center" label="" width="80" type="expand">
        <template slot-scope="scope">
          <el-button
            v-permission="['advertise.account.edit']"
            type="primary"
            size="small"
            icon="el-icon-plus"
            @click="handleAssign(scope.row)"
          >
            Assign
          </el-button>
          <el-divider />
          <el-table
            v-loading="scope.row.loading"
            :data="scope.row.subAccounts"
            border
            fit
            highlight-current-row
            style="width: 100%"
          >
            <el-table-column prop="email" align="left" label="Email" />
            <el-table-column prop="realname" align="center" label="Real Name" />
            <el-table-column prop="phone" align="center" label="Phone" />

            <el-table-column align="center" label="Actions" width="270">
              <template slot-scope="subScope">
                <el-button
                  v-permission="['advertise.account.edit']"
                  type="warning"
                  size="small"
                  icon="el-icon-edit"
                  @click="handleEditPermissions(subScope.row, scope.row)"
                >
                  Permissions
                </el-button>
                <el-button
                  v-permission="['advertise.account.edit']"
                  type="danger"
                  size="small"
                  icon="el-icon-delete"
                  @click="handleRemoveSubAccount(subScope.row, scope.row)"
                >
                  Remove
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </template>
      </el-table-column>
      <el-table-column prop="email" align="left" label="Email" />
      <el-table-column prop="realname" align="center" label="Real Name" />
      <el-table-column prop="ava_credit" align="center" label="Credit" />
      <el-table-column
        prop="expend_credit"
        align="center"
        label="Expanp Credit"
      />

      <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-link
            :type="scope.row.status ? 'success' : 'info'"
            size="small"
            icon="el-icon-s-custom"
            :underline="false"
            @click="handleStatus(scope.row)"
          />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Advertising Status">
        <template slot-scope="scope">
          <el-link
            :type="scope.row.isAdvertiseEnabled ? 'success' : 'info'"
            size="small"
            icon="el-icon-upload"
            :underline="false"
            @click="handleAdvertisingStatus(scope.row)"
          />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Publishing Status">
        <template slot-scope="scope">
          <el-link
            :type="scope.row.isPublishEnabled ? 'success' : 'info'"
            size="small"
            icon="el-icon-upload"
            :underline="false"
            @click="handlePublishingStatus(scope.row)"
          />
        </template>
      </el-table-column>
      <el-table-column align="center" label="Is Agency">
        <template slot-scope="scope">
          <el-link
            :type="scope.row.is_agency == 1 ? 'success' : 'info'"
            size="small"
            icon="el-icon-s-custom"
            :underline="false"
          />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="360">
        <template slot-scope="scope">
          <el-button
            v-permission="['advertise.account.edit']"
            type="primary"
            size="small"
            icon="el-icon-edit"
            @click="handleEdit(scope.row)"
          >
            Edit
          </el-button>
          <el-button
            v-permission="['advertise.bill']"
            type="primary"
            size="small"
            icon="el-icon-date"
            @click="handleBillSet(scope.row)"
          >
            BillSet
          </el-button>
          <el-button
            v-permission="['advertise.account.edit']"
            type="warning"
            size="small"
            icon="el-icon-key "
            @click="handleToken(scope.row)"
          >Token</el-button>

          <el-button
            v-permission="['advertise.account.edit']"
            style="margin-top: 10px"
            type="primary"
            size="small"
            icon="el-icon-plus"
            @click="handleCredit(scope.row)"
          >
            Add Credit
          </el-button>
          <el-button
            v-permission="['advertise.account.edit']"
            type="warning"
            size="small"
            icon="el-icon-edit"
            @click="handleEditPermissions(scope.row, scope.row)"
          >
            Permissions
          </el-button>
          <el-button
            v-permission="['advertise.account.cash']"
            type="normal"
            size="small"
            icon="el-icon-plus"
            @click="handleCash(scope.row)"
          >
            Add Cash
          </el-button>
          <!--<el-button v-permission="['advertise.account.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">-->
          <!--  Delete-->
          <!--</el-button>-->
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />

    <el-dialog
      :title="isNewAccount ? 'Add Main Account' : 'Edit Account'"
      :visible.sync="dialogFormVisible"
    >
      <div v-loading="accountCreating" class="form-container">
        <el-form
          ref="accountForm"
          :rules="rules"
          :model="currentAccount"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item :label="$t('email')" prop="email">
            <el-input v-model="currentAccount.email" />
          </el-form-item>
          <el-form-item :label="$t('account.realname')" prop="realname">
            <el-input v-model="currentAccount.realname" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="currentAccount.password" show-password />
          </el-form-item>
          <el-form-item
            :label="$t('user.confirmPassword')"
            prop="confirmPassword"
          >
            <el-input v-model="currentAccount.confirmPassword" show-password />
          </el-form-item>
          <el-form-item label="Agency Name" prop="Agency Name">
            <el-input v-model="currentAccount.agency_name" />
          </el-form-item>
          <el-form-item label="Is Agency" prop="Is Agency">
            <el-radio-group v-model="currentAccount.is_agency">
              <el-radio :label="1">YES</el-radio>
              <el-radio :label="0">NO</el-radio>
            </el-radio-group>
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

    <el-dialog title="Add Credit" :visible.sync="dialogCreditVisible">
      <div v-loading="addCrediting" class="form-container">
        <el-form
          ref="creditForm"
          :rules="creditrules"
          :model="currentAccount"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item label="Credit" prop="addcredit">
            <el-input v-model="currentAccount.addcredit" type="number" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogCreditVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="addCredit()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog title="Add Cash" :visible.sync="dialogCashVisible">
      <div v-loading="addCrediting" class="form-container">
        <el-form
          ref="cashForm"
          :rules="cashrules"
          :model="record"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item label="Amount" prop="amount">
            <el-input v-model="record.amount" type="number" />
          </el-form-item>
          <el-form-item label="Date" prop="date">
            <el-date-picker
              v-model="record.date"
              type="date"
              value-format="yyyy-MM-dd"
              placeholder="date"
            />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogCashVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="addCash()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog title="Assign" :visible.sync="dialogAssign.visible">
      <div v-loading="dialogAssign.loading" class="form-container">
        <el-form
          ref="assignForm"
          :rules="assignRules"
          :model="dialogAssign.form"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item :label="$t('email')" prop="email">
            <el-input v-model="dialogAssign.form.email" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogAssign.visible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="assignAccount()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Bill Set'" :visible.sync="billDialogFormVisible">
      <div v-loading="billSetting" class="form-container">
        <el-form
          ref="billSetForm"
          :rules="billSetRules"
          :model="currentBillSet"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item :label="$t('bill.address')" prop="address">
            <el-input v-model="currentBillSet.address" type="textarea" />
          </el-form-item>
          <el-form-item :label="$t('bill.phone')" prop="phone">
            <el-input v-model="currentBillSet.phone" />
          </el-form-item>
          <el-form-item label="Company Name" prop="company">
            <el-input v-model="currentBillSet.company" />
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
    <el-dialog
      :title="dialogTokenFormName"
      :visible.sync="dialogTokenFormVisible"
    >
      <div v-loading="loading" class="form-container">
        <el-form
          ref="tokenForm"
          v-permission="['advertise.account.edit']"
          :model="newToken"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item :label="$t('token.expired_at')" prop="expired_at">
            <el-date-picker
              v-model="newToken.expired_at"
              type="date"
              value-format="yyyy-MM-dd"
              placeholder="no limit"
            />
            <el-button type="primary" @click="makeToken()">{{
              $t('token.make')
            }}</el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-divider />
      <el-table
        v-loading="loading"
        :data="currentUserTokens"
        border
        fit
        highlight-current-row
        style="width: 100%"
      >
        <el-table-column
          align="center"
          label="Access Token"
          width="200"
          :show-overflow-tooltip="true"
        >
          <template slot-scope="scope">
            <el-link
              v-clipboard:copy="scope.row.access_token"
              v-clipboard:success="clipboardSuccess"
              type="primary"
              icon="el-icon-document"
            />

            <span>{{ scope.row.access_token }}</span>
          </template>
        </el-table-column>

        <el-table-column align="center" label="Expired Date">
          <template slot-scope="scope">
            <span>{{ scope.row.expired_at }}</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="Actions"
          width="100"
          fixed="right"
        >
          <template slot-scope="scope">
            <el-link
              v-permission="['advertise.account.edit']"
              type="danger"
              icon="el-icon-delete"
              @click="handleTokenDelete(scope.row)"
            />
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
    <el-dialog
      :title="dialogPermission.title"
      :visible.sync="dialogPermission.visible"
    >
      <div v-loading="dialogPermission.Loading" class="form-container">
        <el-tree
          ref="permissionTree"
          :data="dialogPermission.allPermission"
          show-checkbox
          check-strictly
          node-key="name"
          default-expand-all
          :props="{ children: 'children', label: 'display_name' }"
          @check-change="handleTreeCheck"
        />
      </div>
      <div style="text-align: right">
        <el-button type="danger" @click="dialogPermission.visible = false">
          {{ $t('permission.cancel') }}
        </el-button>
        <el-button type="primary" @click="confirmPermission">
          {{ $t('permission.confirm') }}
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AccountResource from '@/api/account';
import TokenResource from '@/api/token';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import clipboard from '@/directive/clipboard/index.js'; // use clipboard by v-directive

const accountResource = new AccountResource();
const tokenResource = new TokenResource();

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
        email: '',
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
          {
            type: 'email',
            message: 'Please input correct email address',
            trigger: ['blur', 'change'],
          },
        ],
        realname: [
          { required: true, message: 'Real name is required', trigger: 'blur' },
        ],
        password: [
          {
            required: this.passwordRequired,
            message: 'Password is required',
            trigger: 'blur',
          },
        ],
        confirmPassword: [
          { validator: validateConfirmPassword, trigger: 'blur' },
        ],
      };
    },
    creditrules() {
      return {
        addcredit: [
          { required: true, message: 'credit is required', trigger: 'blur' },
        ],
      };
    },
    cashrules() {
      return {
        amount: [
          { required: true, message: 'cash is required', trigger: 'blur' },
        ],
        date: [
          { required: true, message: 'date is required', trigger: 'blur' },
        ],
      };
    },
    billSetRules() {
      return {
        address: [
          { required: true, message: 'Address is required', trigger: 'blur' },
          { max: 255 },
        ],
        company: [
          { required: true, message: 'company is required', trigger: 'blur' },
          { max: 255 },
        ],
        phone: [{ max: 13 }],
      };
    },
    assignRules() {
      return {
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          {
            type: 'email',
            message: 'Please input correct email address',
            trigger: ['blur', 'change'],
          },
        ],
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
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleToken(account) {
      this.currentAccount = account;
      console.log(account);
      console.log(this.currentAccount);
      this.dialogTokenFormName = account.realname;
      this.getTokenList();
      this.dialogTokenFormVisible = true;
    },
    async getTokenList() {
      this.currentUserTokens = [];
      const { data } = await tokenResource.userTokenList(
        this.currentAccount.id
      );
      this.currentUserTokens = data;
    },
    makeToken() {
      this.$confirm(
        'This will make a new token for' +
          this.currentAccount.realname +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          console.log(this.currentAccount);
          tokenResource
            .makeUserToken(this.currentAccount.id, this.newToken.expired_at)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'The new token : ' + response.api_token,
              });
              this.getTokenList();
            })
            .catch((error) => {
              console.log(error);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Make token canceled',
          });
        });
    },
    clipboardSuccess() {
      this.$message({
        message: 'Copy token successfully',
        type: 'success',
        duration: 1500,
      });
    },
    handleTokenDelete(token) {
      this.$confirm(
        'This will permanently delete token ' +
          token.access_token +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          tokenResource
            .delUserToken(token.id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Delete token completed',
              });
              this.getTokenList();
            })
            .catch((error) => {
              console.log(error);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete token canceled',
          });
        });
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    async handleEditPermissions(account, main_account) {
      if (!this.dialogPermission.allPermission) {
        const { data } = await accountResource.allPermission();
        this.dialogPermission.allPermission = data;
      }
      if (account === main_account) {
        this.dialogPermission.title =
          'Grant ???' + account.realname + '??? permissions';
      } else {
        this.dialogPermission.title =
          'Grant ???' +
          account.realname +
          '??? permissions to serve ???' +
          main_account.realname +
          '???';
      }
      this.dialogPermission.account = account;
      this.dialogPermission.mainAccount = main_account;
      this.dialogPermission.loading = true;
      this.dialogPermission.visible = true;
      const { data } = await accountResource.permissions(
        account.id,
        main_account.id
      );
      this.$refs.permissionTree.setCheckedKeys([]);
      data.forEach((item, index, array) => {
        this.$refs.permissionTree.setChecked(item, true);
      });
      this.dialogPermission.loading = false;
    },
    confirmPermission() {
      const checkedPermissions = this.$refs.permissionTree
        .getCheckedKeys()
        .concat(this.$refs.permissionTree.getHalfCheckedKeys());
      this.dialogPermission.loading = true;
      accountResource
        .updatePermission(
          this.dialogPermission.account.id,
          this.dialogPermission.mainAccount.id,
          { permissions: checkedPermissions }
        )
        .then((response) => {
          this.$message({
            message: 'Permissions has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          });
          this.dialogPermission.loading = false;
          this.dialogPermission.visible = false;
        });
    },
    handleTreeCheck(node, checked) {
      var tree_node = this.$refs.permissionTree.getNode(node);
      this.recursionSelectTreeNode(tree_node, checked);
    },
    recursionSelectTreeNode(node, checked, direction = '') {
      node.checked = checked;
      if (
        direction !== 'down' &&
        checked &&
        node.parent &&
        node.parent.id > 0
      ) {
        this.recursionSelectTreeNode(node.parent, checked, 'up');
      }
      if (
        direction !== 'up' &&
        !checked &&
        node.childNodes &&
        node.childNodes.length > 0
      ) {
        node.childNodes.forEach(function(item) {
          this.recursionSelectTreeNode(item, checked, 'down');
        }, this);
      }
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
      console.log(account);
      console.log(this.currentAccount);
      this.passwordRequired = false;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['accountForm'].clearValidate();
      });
    },
    handleCredit(account) {
      this.currentAccount = account;
      console.log(account);
      console.log(this.currentAccount);
      this.dialogCreditVisible = true;
      this.$nextTick(() => {
        this.$refs['creditForm'].clearValidate();
      });
    },
    handleCash(account) {
      this.currentAccount = account;
      this.dialogCashVisible = true;
      this.$nextTick(() => {
        this.$refs['cashForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm(
        'This will permanently delete account ' + name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          accountResource
            .destroy(id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Delete completed',
              });
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
          });
        });
    },
    handleAssign(account) {
      this.dialogAssign.account = account;
      this.dialogAssign.visible = true;
      this.$nextTick(() => {
        this.$refs['assignForm'].clearValidate();
      });
    },
    assignAccount() {
      this.dialogAssign.loading = true;
      accountResource
        .assign(this.dialogAssign.account.id, this.dialogAssign.form)
        .then((response) => {
          this.$message({
            message:
              this.dialogAssign.form.email +
              ' assigned to ' +
              this.dialogAssign.account.realname,
            type: 'success',
            duration: 5 * 1000,
          });
          this.handleFilter();
          this.dialogAssign.loading = false;
          this.dialogAssign.visible = false;
        })
        .catch((error) => {
          this.dialogAssign.loading = false;
          console.log(error);
        });
    },
    handleRemoveSubAccount(account, main_account) {
      this.$confirm(
        'This will remove account ' +
          account.realname +
          ' from ' +
          main_account.realname +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          accountResource
            .detach(main_account.id, account.id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Detach completed',
              });
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Detach canceled',
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
      this.$confirm(
        'This will ' +
          (account.status ? 'disable' : 'enable') +
          ' account ' +
          displayName +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          if (account.status) {
            accountResource
              .disable(account.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Account ' + displayName + ' disabled',
                });
                account.status = false;
              })
              .catch((error) => {
                console.log(error);
              });
          } else {
            accountResource
              .enable(account.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Account ' + displayName + ' enabled',
                });
                account.status = true;
              })
              .catch((error) => {
                console.log(error);
              });
          }
        })
        .catch((error) => {
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
      this.$confirm(
        'This will ' +
          (account.isAdvertiseEnabled ? 'disable' : 'enable') +
          ' advertising for account ' +
          displayName +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          if (account.isAdvertiseEnabled) {
            accountResource
              .disableAdvertising(account.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message:
                    'Advertising of Account ' + displayName + ' disabled',
                });
                account.isAdvertiseEnabled = false;
              })
              .catch((error) => {
                console.log(error);
              });
          } else {
            accountResource
              .enableAdvertising(account.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Advertising of Account ' + displayName + ' enabled',
                });
                account.isAdvertiseEnabled = true;
              })
              .catch((error) => {
                console.log(error);
              });
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handlePublishingStatus(account) {
      if (!checkPermission(['advertise.account.edit'])) {
        this.$message({
          type: 'warning',
          message: 'No permission',
        });
        return;
      }
      var displayName = account.realname + '(' + account.email + ')';
      this.$confirm(
        'This will ' +
          (account.isPublishEnabled ? 'disable' : 'enable') +
          ' publishing for account ' +
          displayName +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          if (account.isPublishEnabled) {
            accountResource
              .disablePublishing(account.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Publishing of Account ' + displayName + ' disabled',
                });
                account.isPublishEnabled = false;
              })
              .catch((error) => {
                console.log(error);
              });
          } else {
            accountResource
              .enablePublishing(account.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Publishing of Account ' + displayName + ' enabled',
                });
                account.isPublishEnabled = true;
              })
              .catch((error) => {
                console.log(error);
              });
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    saveAccount() {
      this.$refs['accountForm'].validate((valid) => {
        if (valid) {
          this.accountCreating = true;
          accountResource
            .save(this.currentAccount)
            .then((response) => {
              console.log(response);
              if (response.code !== 0) {
                this.$message({
                  message: response.message,
                  type: 'error',
                  duration: 5 * 1000,
                });
              } else {
                this.$message({
                  message:
                    'Account ' +
                    this.currentAccount.email +
                    ' has been saved successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
                this.dialogFormVisible = false;
              }

              this.resetNewAccount();

              this.handleFilter();
            })
            .catch((error) => {
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
    addCredit() {
      this.$refs['creditForm'].validate((valid) => {
        if (valid) {
          accountResource
            .addCredit(this.currentAccount)
            .then((response) => {
              this.$message({
                message:
                  'Account ' +
                  this.currentAccount.email +
                  ' has been add credit successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.dialogCreditVisible = false;
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            })
            .finally(() => {
              this.addCrediting = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    addCash() {
      this.$refs['cashForm'].validate((valid) => {
        if (valid) {
          console.log(this.record);
          accountResource
            .addCash(this.currentAccount.id, this.record)
            .then((response) => {
              this.$message({
                message:
                  'Account ' +
                  this.currentAccount.email +
                  ' has been add cash successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.dialogCashVisible = false;
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            })
            .finally(() => {
              this.addCrediting = false;
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
      this.currentBillSet = account.bill
        ? account.bill
        : { address: '', phone: '' };
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
            .then((response) => {
              if (response.code !== 0) {
                this.$message({
                  message: response.message,
                  type: 'error',
                  duration: 5 * 1000,
                });
              } else {
                this.$message({
                  message:
                    'The bill set of Account ' +
                    this.currentAccount.email +
                    ' has been saved successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
                this.billDialogFormVisible = false;
              }

              this.resetNewAccount();

              this.handleFilter();
            })
            .catch((error) => {
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
      import('@/vendor/Export2Excel').then((excel) => {
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
      return jsonData.map((v) => filterVal.map((j) => v[j]));
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
