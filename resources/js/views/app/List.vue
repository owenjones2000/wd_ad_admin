<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-date-picker
        v-model="query.daterange"
        type="daterange"
        class="filter-item"
        align="right"
        unlink-panels
        range-separator=" ~ "
        start-placeholder="start date"
        end-placeholder="end date"
        value-format="yyyy-MM-dd"
        :picker-options="pickerOptions"
      />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <!--<el-button v-permission="['advertise.app.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">-->
      <!--  {{ $t('table.add') }}-->
      <!--</el-button>-->
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%" @expand-change="handleExpandChange">
      <el-table-column align="center" label="" width="80" type="expand">
        <template slot-scope="scope">
          <el-table v-loading="scope.row.loading" :data="scope.row.children" border fit highlight-current-row style="width: 100%">
            <el-table-column align="center" label="Date" width="100">
              <template slot-scope="children">
                <span>{{ children.row.date }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Requests">
              <template slot-scope="children">
                <span>{{ children.row.requests ? children.row.requests : 0 }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Impressions">
              <template slot-scope="children">
                <span>{{ children.row.impressions ? children.row.impressions : 0 }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Clicks">
              <template slot-scope="children">
                <span>{{ children.row.clicks ? children.row.clicks : 0 }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Installs">
              <template slot-scope="children">
                <span>{{ children.row.installs ? children.row.installs : 0 }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="CTR">
              <template slot-scope="children">
                <span>{{ children.row.ctr ? children.row.ctr : '0.00' }}%</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="CVR">
              <template slot-scope="children">
                <span>{{ children.row.cvr ? children.row.cvr : '0.00' }}%</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="IR">
              <template slot-scope="children">
                <span>{{ children.row.ir ? children.row.ir : '0.00' }}%</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Spend">
              <template slot-scope="children">
                <span>${{ children.row.spend ? children.row.spend : '0.00' }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="eCpi">
              <template slot-scope="children">
                <span>${{ children.row.ecpi ? children.row.ecpi : '0.00' }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="eCpm">
              <template slot-scope="children">
                <span>${{ children.row.ecpm ? children.row.ecpm : '0.00' }}</span>
              </template>
            </el-table-column>
          </el-table>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Package">
        <template slot-scope="scope">
          <span>{{ scope.row.bundle_id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Requests">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.requests ? scope.row.kpi.requests : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Impressions">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.impressions ? scope.row.kpi.impressions : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Clicks">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.clicks ? scope.row.kpi.clicks : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Installs">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.installs ? scope.row.kpi.installs : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CTR">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.ctr ? scope.row.kpi.ctr : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CVR">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.cvr ? scope.row.kpi.cvr : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="IR">
        <template slot-scope="scope">
          <span>{{ scope.row.kpi&&scope.row.kpi.ir ? scope.row.kpi.ir : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Spend">
        <template slot-scope="scope">
          <span>${{ scope.row.kpi&&scope.row.kpi.spend ? scope.row.kpi.spend : '0.00' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="eCpi">
        <template slot-scope="scope">
          <span>${{ scope.row.kpi&&scope.row.kpi.ecpi ? scope.row.kpi.ecpi : '0.00' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="eCpm">
        <template slot-scope="scope">
          <span>${{ scope.row.kpi&&scope.row.kpi.ecpm ? scope.row.kpi.ecpm : '0.00' }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-icon :style="{color: scope.row.status ? '#67C23A' : '#F56C6C'}" size="small" :name="scope.row.status ? 'video-play' : 'video-pause'" />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="200">
        <template slot-scope="scope">
          <!--<el-button v-permission="['advertise.app.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)" />-->
          <!--<el-button v-permission="['advertise.auth.token']" type="normal" size="small" icon="el-icon-key " @click="handleToken(scope.row)" />-->
          <el-button v-permission="['advertise.app.edit']" :type="scope.row.is_admin_disable ? 'danger' : 'info'" size="small" icon="el-icon-remove" @click="handleStatus(scope.row)" />
          <!--<el-button v-permission="['advertise.app.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);" />-->
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Create new app'" :visible.sync="dialogFormVisible">
      <div v-loading="appCreating" class="form-container">
        <el-form ref="appForm" :rules="rules" :model="currentApp" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('name')" prop="name">
            <el-input v-model="currentApp.name" />
          </el-form-item>
          <el-form-item :label="$t('app.bundle_id')" prop="bundle_id">
            <el-input v-model="currentApp.bundle_id" />
          </el-form-item>
          <el-form-item :label="$t('platform.name')" prop="platform">
            <el-select v-model="currentApp.os" placeholder="please select platform">
              <el-option label="iOS" value="ios" />
              <el-option label="Android" value="android" />
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="saveApp()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="dialogTokenFormName" :visible.sync="dialogTokenFormVisible">
      <div v-loading="appCreating" class="form-container">
        <el-form ref="tokenForm" v-permission="['basic.auth.token.make']" :model="newToken" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('token.expired_at')" prop="expired_at">
            <el-date-picker v-model="newToken.expired_at" type="date" value-format="yyyy-MM-dd" placeholder="no limit" />
            <el-button type="primary" @click="makeToken()">
              {{ $t('token.make') }}
            </el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-divider />
      <el-table v-loading="loading" :data="currentAppTokens" border fit highlight-current-row style="width: 100%">
        <el-table-column align="center" label="Access Token" width="200" :show-overflow-tooltip="true">
          <template slot-scope="scope">
            <el-link v-clipboard:copy="scope.row.access_token" v-clipboard:success="clipboardSuccess" type="primary" icon="el-icon-document" />

            <span>{{ scope.row.access_token }}</span>
          </template>
        </el-table-column>

        <el-table-column align="center" label="Expired Date">
          <template slot-scope="scope">
            <span>{{ scope.row.expired_at }}</span>
          </template>
        </el-table-column>

        <el-table-column align="center" label="Actions" width="100">
          <template slot-scope="scope">
            <el-link v-permission="['basic.auth.token.destroy']" type="danger" icon="el-icon-delete" @click="handleTokenDelete(scope.row);" />
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>

  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AppResource from '@/api/app';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import defaultDatePickerOptions from '@/utils/datepicker';

const appResource = new AppResource();

export default {
  name: 'AppList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      rowLoading: false,
      downloading: false,
      appCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      newApp: {},
      dialogFormVisible: false,
      currentAppId: 0,
      currentApp: {
        name: '',
        tokens: [],
      },
      currentAppTokens: [],
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        bundle_id: [{ required: true, message: 'Package name is required', trigger: 'blur' }],
        os: [{ required: true, message: 'Platform is required', trigger: 'blur' }],
      },
      dialogTokenFormVisible: false,
      dialogTokenFormName: 'Api token',
      newToken: {
        expired_at: null,
      },
      pickerOptions: defaultDatePickerOptions,
    };
  },
  computed: {
  },
  created() {
    this.resetNewApp();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await appResource.list(this.query);
      data.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
        element['loading'] = false;
      });
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    async handleExpandChange(row) {
      if (!row.hasOwnProperty('children')) {
        const { limit, page } = this.query;
        row.loading = true;
        const query = { ...this.query };
        query['id'] = row.id;
        query['grouping'] = 'date';
        const { data } = await appResource.data(query);
        row['children'] = data;
        row['children'].forEach((element, index) => {
          element['index'] = (page - 1) * limit + index + 1;
        });
      }
      row.loading = false;
    },
    handleCreate() {
      this.resetNewApp();
      this.currentApp = this.newApp;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
    },
    handleEdit(app) {
      this.currentApp = app;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete app ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        appResource.destroy(id).then(response => {
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
    handleStatus(app) {
      this.$confirm('This will ' + (app.is_admin_disable ? 'release control for' : 'disable') + ' app ' + app.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (app.is_admin_disable) {
          appResource.enable(app.id).then(response => {
            this.$message({
              type: 'success',
              message: 'App ' + app.name + ' released',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        } else {
          appResource.disable(app.id).then(response => {
            this.$message({
              type: 'success',
              message: 'App ' + app.name + ' disabled',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        }
      }).catch(error => {
        console.log(error);
      });
    },
    saveApp() {
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.appCreating = true;
          appResource
            .save(this.currentApp)
            .then(response => {
              this.$message({
                message: 'App ' + this.currentApp.name + ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewApp();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.appCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewApp() {
      this.newApp = {
        name: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'app_id', 'name'];
        const filterVal = ['index', 'id', 'name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'app-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
    clipboardSuccess() {
      this.$message({
        message: 'Copy token successfully',
        type: 'success',
        duration: 1500,
      });
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
