<template>
  <div class="app-container">
    <div class="filter-container">
      <!--<el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />-->
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

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="Country">
        <template slot-scope="scope">
          <span>{{ scope.row.country ? scope.row.country : 'Unknown' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="uq_idfa Devices">
        <template slot-scope="scope">
          <span>{{ scope.row.uq_idfa ? scope.row.uq_idfa : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="uq_no_idfa Devices">
        <template slot-scope="scope">
          <span>{{ scope.row.uq_no_idfa ? scope.row.uq_no_idfa : 0 }}</span>
        </template>
      </el-table-column>
      <!-- <el-table-column align="center" label="Request Avg">
        <template slot-scope="scope">
          <span>{{ scope.row.request_avg ? scope.row.request_avg : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Impression Avg">
        <template slot-scope="scope">
          <span>{{ scope.row.impression_avg ? scope.row.impression_avg : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Click Avg">
        <template slot-scope="scope">
          <span>{{ scope.row.click_avg ? scope.row.click_avg : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Install Avg">
        <template slot-scope="scope">
          <span>{{ scope.row.install_avg ? scope.row.install_avg : 0 }}</span>
        </template>
      </el-table-column> -->
    </el-table>

  </div>
</template>

<script>
import Statis from '@/api/statis';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import defaultDatePickerOptions from '@/utils/datepicker';

const statis = new Statis();

export default {
  name: 'DeviceStatisByApp',
  components: { },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      appCreating: false,
      query: {
        page: 1,
        limit: 100,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      pickerOptions: defaultDatePickerOptions,
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await statis.deviceByCountry(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta ? meta.total : 1;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
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
