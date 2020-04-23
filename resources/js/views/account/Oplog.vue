<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-select v-model="query.method" placeholder="Method" class="filter-item">
        <el-option label="ALL" value="" />
        <el-option label="POST" value="post" />
        <el-option label="GET" value="get" />
      </el-select>
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
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%" @sort-change="handleSort">
      <el-table-column prop="main_account.realname" align="center" label="Main Account" fixed />
      <el-table-column prop="account.realname" align="center" label="Account" fixed />
      <el-table-column prop="account.email" align="center" label="Email" fixed />
      <el-table-column prop="method" align="center" label="Method" />
      <el-table-column prop="uri" align="center" label="Uri" />
      <el-table-column prop="query" align="center" label="Params" />
      <el-table-column prop="ip" align="center" label="IP" />
      <el-table-column prop="created_at" label="Created" align="center" width="100" />
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
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
  name: 'OpLog',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      defaultPickerValue: [
        new Date(),
        new Date(),
      ],
      pickerOptions: {
        shortcuts: [{
          text: 'Today',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Yesterday',
          onClick(picker) {
            const end = new Date(new Date().setDate(new Date().getDate() - 1));
            const start = new Date(new Date().setDate(new Date().getDate() - 1));
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last 7 days',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Month to date',
          onClick(picker) {
            const end = new Date(new Date(new Date().setMonth(new Date().getMonth() + 1)).setDate(0));
            const start = new Date(new Date().setDate(1));
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'The previous Month',
          onClick(picker) {
            const end = new Date(new Date().setDate(0));
            const start = new Date(new Date(new Date().setMonth(new Date().getMonth() - 1)).setDate(1));
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Year to date',
          onClick(picker) {
            const end = new Date(new Date(new Date().setMonth(12)).setDate(0));
            const start = new Date(new Date(new Date().setMonth(0)).setDate(1));
            picker.$emit('pick', [start, end]);
          },
        },
        ],
      },
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
      const { data, meta } = await accountResource.opLog(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleSort(column){
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
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'campaign_id', 'name'];
        const filterVal = ['index', 'id', 'name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'campaign-list',
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
