<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        :placeholder="$t('table.keyword')"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />

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
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >{{ $t('table.search') }}</el-button>
      <el-button
        v-waves
        :loading="downloading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >
        {{ $t('table.export') }}
      </el-button>
    </div>

    <el-table
      v-loading="loading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%"
      @expand-change="handleExpandChange"
      @sort-change="handleSort"
    >
      <!-- <el-table-column align="center" label width="80" type="expand">
        <template slot-scope="scope">
          <el-table
            v-loading="scope.row.loading"
            :data="scope.row.children"
            border
            fit
            highlight-current-row
            style="width: 100%"
          >
            <el-table-column align="center" label="Date" width="100">
              <template slot-scope="children">
                <span>{{ children.row.date }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="spend" :formatter="moneyFormat" align="center" label="Spend" />
            <el-table-column prop="ecpi" :formatter="moneyFormat" align="center" label="eCpi" />
            <el-table-column prop="ecpm" :formatter="moneyFormat" align="center" label="eCpm" />
            <el-table-column
              prop="requests"
              :formatter="numberFormat"
              align="center"
              label="Requests"
            />
            <el-table-column
              prop="impressions"
              :formatter="numberFormat"
              align="center"
              label="Impressions"
            />
            <el-table-column prop="clicks" :formatter="numberFormat" align="center" label="Clicks" />
            <el-table-column
              prop="installs"
              :formatter="numberFormat"
              align="center"
              label="Installs"
            />
            <el-table-column prop="ctr" :formatter="percentageFormat" align="center" label="CTR" />
            <el-table-column prop="cvr" :formatter="percentageFormat" align="center" label="CVR" />
            <el-table-column prop="ir" :formatter="percentageFormat" align="center" label="IR" />
          </el-table>
        </template>
      </el-table-column> -->

      <el-table-column prop="id" align="center" label="ID" />
      <el-table-column prop="realname" align="center" label="Advertiser" />
      <!-- <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-icon
            :style="{color: scope.row.status ? '#67C23A' : '#F56C6C'}"
            size="small"
            :name="scope.row.status ? 'video-play' : 'video-pause'"
          />
        </template>
      </el-table-column> -->

      <el-table-column
        prop="kpi.spend"
        :formatter="moneyFormat"
        align="center"
        label="Spend"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.ecpi"
        :formatter="moneyFormat"
        align="center"
        label="eCpi"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.ecpm"
        :formatter="moneyFormat"
        align="center"
        label="eCpm"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.requests"
        :formatter="numberFormat"
        align="center"
        label="Requests"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.impressions"
        :formatter="numberFormat"
        align="center"
        label="Impressions"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.clicks"
        :formatter="numberFormat"
        align="center"
        label="Clicks"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.installs"
        :formatter="numberFormat"
        align="center"
        label="Installs"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.ctr"
        :formatter="percentageFormat"
        align="center"
        label="CTR"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.cvr"
        :formatter="percentageFormat"
        align="center"
        label="CVR"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.ir"
        :formatter="percentageFormat"
        align="center"
        label="IR"
        sortable="custom"
      />

      <!-- <el-table-column align="center" label="Actions" fixed="right">
        <template slot-scope="scope">
          <el-button
            v-permission="['advertise.app']"
            type="primary"
            size="small"
            icon="el-icon-info"
            @click="handleEdit(scope.row)"
          />
          <el-button
            v-permission="['advertise.app.edit']"
            :type="scope.row.is_admin_disable ? 'danger' : 'info'"
            size="small"
            icon="el-icon-remove"
            @click="handleStatus(scope.row)"
          />
          <el-button type="normal" size="small" icon="el-icon-menu">
            <router-link :to="'/acquisition/app/'+scope.row.id+'/channel'">Channels</router-link>
          </el-button>
        </template>
      </el-table-column> -->
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />

    <el-dialog :title="'App Info'" :visible.sync="dialogFormVisible">
      <div v-loading="appCreating" class="form-container">
        <el-form
          ref="appForm"
          :rules="rules"
          :model="currentApp"
          disabled
          label-position="left"
          label-width="150px"
          style="max-width: 500px;"
        >
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
          <el-form-item :label="$t('app.icon')" prop="icon_url">
            <el-avatar shape="square" :size="50" :src="currentApp.icon_url" />
          </el-form-item>
          <el-form-item :label="$t('app.description')" prop="description">
            <el-input v-model="currentApp.description" />
          </el-form-item>
          <el-form-item :label="$t('app.track_platform')" prop="track_platform_id">
            <el-select
              v-model="currentApp.track_platform_id"
              placeholder="please select track platform"
            >
              <el-option label="AppsFlyer" :value="1" />
              <el-option label="Adjust" :value="2" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('app.track_code')" prop="track_code">
            <el-input v-model="currentApp.track_code" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">{{ $t('table.cancel') }}</el-button>
          <!--<el-button type="primary" @click="saveApp()">-->
          <!--{{ $t('table.confirm') }}-->
          <!--</el-button>-->
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="dialogTokenFormName" :visible.sync="dialogTokenFormVisible">
      <div v-loading="appCreating" class="form-container">
        <el-form
          ref="tokenForm"
          v-permission="['basic.auth.token.make']"
          :model="newToken"
          label-position="left"
          label-width="150px"
          style="max-width: 500px;"
        >
          <el-form-item :label="$t('token.expired_at')" prop="expired_at">
            <el-date-picker
              v-model="newToken.expired_at"
              type="date"
              value-format="yyyy-MM-dd"
              placeholder="no limit"
            />
            <el-button type="primary" @click="makeToken()">{{ $t('token.make') }}</el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-divider />
      <el-table
        v-loading="loading"
        :data="currentAppTokens"
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

        <el-table-column align="center" label="Actions" width="100">
          <template slot-scope="scope">
            <el-link
              v-permission="['basic.auth.token.destroy']"
              type="danger"
              icon="el-icon-delete"
              @click="handleTokenDelete(scope.row);"
            />
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AccountResource from '@/api/account';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import defaultDatePickerOptions from '@/utils/datepicker';

const accountResource = new AccountResource();

export default {
  name: 'AdvertiserList',
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
        name: [
          { required: true, message: 'Name is required', trigger: 'blur' },
        ],
        bundle_id: [
          {
            required: true,
            message: 'Package name is required',
            trigger: 'blur',
          },
        ],
        os: [
          { required: true, message: 'Platform is required', trigger: 'blur' },
        ],
      },
      dialogTokenFormVisible: false,
      dialogTokenFormName: 'Api token',
      newToken: {
        expired_at: null,
      },
      pickerOptions: defaultDatePickerOptions,
    };
  },
  computed: {},
  created() {
    this.resetNewApp();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await accountResource.adverlist(this.query);
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
    handleSort(column) {
      switch (column.order) {
        case 'ascending':
          this.query.field = column.prop;
          this.query.order = 'asc';
          break;
        case 'descending':
          this.query.field = column.prop;
          this.query.order = 'desc';
          break;
        default:
          delete this.query.field;
          delete this.query.order;
      }
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
        const { data } = await accountResource.data(query);
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
    resetNewApp() {
      this.newApp = {
        name: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const tHeader = [
          'Advertiser',
          'Impressions',
          'Clicks',
          'Installs',
          'CTR',
          'CVR',
          'IR',
          'Spend',
          'eCpi',
          'eCpm',
        ];
        const filterVal = [
          'realname',
          'impressions',
          'clicks',
          'installs',
          'ctr',
          'cvr',
          'ir',
          'spend',
          'ecpi',
          'ecpm',
        ];
        const data = this.formatJson(filterVal, this.list);
        // const data = this.list;
        console.log(data);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'advertiser-list',
          bookType: 'csv',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        // console.log(v);
        // console.log(j);
        Object.assign(v, v['kpi']);
        if (j === 'ctr' || j === 'cvr' || j === 'ir') {
          return v[j] + '%';
        } else {
          return v[j];
        }
        // return v[j];
      }));
    },
    clipboardSuccess() {
      this.$message({
        message: 'Copy token successfully',
        type: 'success',
        duration: 1500,
      });
    },
    numberFormat(row, column, cellValue, index) {
      return cellValue === undefined || cellValue === null ? '-' : cellValue;
    },
    moneyFormat(row, column, cellValue, index) {
      return cellValue === undefined || cellValue === null
        ? '-'
        : '$' + cellValue;
    },
    percentageFormat(row, column, cellValue, index) {
      return cellValue === undefined || cellValue === null
        ? '-'
        : cellValue + '%';
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
