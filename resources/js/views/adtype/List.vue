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
      <el-select
        v-model="query.os"
        clearable
        placeholder="platform"
        style="width: 200px;"
        class="filter-item"
      >
        <el-option
          v-for="item in options"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
      </el-select>

      <el-select
        v-model="query.type"
        placeholder="type"
        style="width: 200px;"
        class="filter-item"
      >
        <el-option
          v-for="item in types"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
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
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >{{ $t('table.search') }}</el-button>
      <!-- <el-button
        v-permission="['advertise.channel.edit']"
        class="filter-item"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-plus"
        @click="handleCreate"
      >{{ $t('table.add') }}</el-button> -->
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
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
      <!--<el-table-column prop="id" align="center" label="ID" width="80" fixed />-->

      <el-table-column align="center" label width="80" type="expand" fixed>
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
            <el-table-column prop="spend" :formatter="moneyFormat" align="center" label="Cpi_Revenue" />
            <el-table-column prop="cost" :formatter="moneyFormat" align="center" label="Bid_Revenue" />
            <el-table-column prop="cpm" :formatter="moneyFormat" align="center" label="Cpm_Revenue" />
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
      </el-table-column>
      <el-table-column
        prop="kpi.spend"
        :formatter="moneyFormat"
        align="center"
        label="Cpi_Revenue"
        sortable="custom"
      />
      <el-table-column
        prop="kpi.cost"
        :formatter="moneyFormat"
        align="center"
        label="Bid_Revenue"
      />
      <el-table-column
        prop="kpi.cpm"
        :formatter="moneyFormat"
        align="center"
        label="Cpm_Revenue"
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
      <el-table-column prop="name" align="center" label="Name" fixed />
      <el-table-column prop="bundle_id" align="center" label="Package" fixed />
      <el-table-column prop="platform" align="center" label="Platform" fixed />
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
      <el-table-column prop="publisher.realname" align="center" label="Publisher" />

    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import ChannelResource from '@/api/channel';
import TokenResource from '@/api/token';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import clipboard from '@/directive/clipboard/index.js'; // use clipboard by v-directive
import defaultDatePickerOptions from '@/utils/datepicker';

const channelResource = new ChannelResource();
const tokenResource = new TokenResource();

export default {
  name: 'ChannelList',
  components: { Pagination },
  directives: { waves, permission, clipboard },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      channelCreating: false,
      options: [
        { value: 'ios', label: 'ios' },
        { value: 'android', label: 'android' },
      ],
      types: [
        { value: '1', label: 'Reward' },
        { value: '2', label: 'Interstitial' },
      ],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        os: '',
        type: '1',
        daterange: [new Date(), new Date()],
      },
      newChannel: {},
      dialogFormVisible: false,
      currentChannelId: 0,
      currentChannel: {
        name: '',
        tokens: [],
      },
      currentChannelTokens: [],
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
        platform: [
          { required: true, message: 'Platform is required', trigger: 'blur' },
        ],
        put_mode: [
          { required: true, message: 'Put mode is required', trigger: 'blur' },
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
    this.resetNewChannel();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await channelResource.placementList(this.query);
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
        const { data } = await channelResource.placementData(query);
        row['children'] = data;
        row['children'].forEach((element, index) => {
          element['index'] = (page - 1) * limit + index + 1;
        });
      }
      console.log(row);
      row.loading = false;
    },
    handleCreate() {
      this.resetNewChannel();
      this.currentChannel = this.newChannel;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['channelForm'].clearValidate();
      });
    },
    handleEdit(channel) {
      this.currentChannel = channel;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['channelForm'].clearValidate();
      });
    },
    async getTokenList() {
      this.currentChannelTokens = [];
      const { data } = await tokenResource.list(this.currentChannel.bundle_id);
      this.currentChannelTokens = data;
    },
    handleToken(channel) {
      this.currentChannel = channel;
      this.dialogTokenFormName = channel.name;
      this.getTokenList();
      this.dialogTokenFormVisible = true;
    },
    handleDelete(id, name) {
      this.$confirm(
        'This will permanently delete channel ' + name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          channelResource
            .destroy(id)
            .then(response => {
              this.$message({
                type: 'success',
                message: 'Delete completed',
              });
              this.handleFilter();
            })
            .catch(error => {
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
    saveChannel() {
      this.$refs['channelForm'].validate(valid => {
        if (valid) {
          this.channelCreating = true;
          channelResource
            .save(this.currentChannel)
            .then(response => {
              this.$message({
                message:
                  'Channel ' +
                  this.currentChannel.name +
                  ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewChannel();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.channelCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewChannel() {
      this.newChannel = {
        name: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'channel_id', 'name'];
        const filterVal = ['index', 'id', 'name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'channel-list',
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
    putModeFormat(row, column, cellValue, index) {
      switch (cellValue) {
        case 1:
          return 'Normal';
        case 2:
          return 'Backup';
        default:
          return 'Unknown';
      }
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
