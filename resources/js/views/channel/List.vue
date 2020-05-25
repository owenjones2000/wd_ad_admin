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
        placeholder="select"
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
        v-permission="['advertise.channel.edit']"
        class="filter-item"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-plus"
        @click="handleCreate"
      >{{ $t('table.add') }}</el-button>
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
            <el-table-column prop="spend" :formatter="moneyFormat" align="center" label="Revenue" />
            <el-table-column prop="ecpi" :formatter="moneyFormat" align="center" label="eCpi" />
            <el-table-column prop="ecpm" :formatter="moneyFormat" align="center" label="eCpm" />
          </el-table>
        </template>
      </el-table-column>
      <el-table-column
        prop="kpi.spend"
        :formatter="moneyFormat"
        align="center"
        label="Revenue"
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
      <el-table-column prop="put_mode" :formatter="putModeFormat" align="center" label="Put Mode" />
      <el-table-column prop="rate" :formatter="percentageFormat" align="center" label="Rate" />
      <el-table-column align="center" label="Actions" width="300" fixed="right">
        <template slot-scope="scope">
          <el-button type="normal" size="small" icon="el-icon-menu">
            <router-link :to="'/acquisition/channel/'+scope.row.id+'/app'">Sources</router-link>
          </el-button>
          <el-button
            v-permission="['advertise.channel.edit']"
            type="primary"
            size="small"
            icon="el-icon-edit"
            @click="handleEdit(scope.row)"
          >Edit</el-button>
          <el-button
            v-permission="['basic.auth.token']"
            type="normal"
            size="small"
            icon="el-icon-key "
            @click="handleToken(scope.row)"
          >Token</el-button>
          <!--<el-button v-permission="['advertise.channel.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">-->
          <!--  Delete-->
          <!--</el-button>-->
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />

    <el-dialog :title="'Edit channel'" :visible.sync="dialogFormVisible">
      <div v-loading="channelCreating" class="form-container">
        <el-form
          ref="channelForm"
          :rules="rules"
          :model="currentChannel"
          label-position="left"
          label-width="150px"
          style="max-width: 500px;"
        >
          <el-form-item :label="$t('channel.name')" prop="name">
            <el-input v-model="currentChannel.name" />
          </el-form-item>
          <el-form-item :label="$t('app.bundle_id')" prop="bundle_id">
            <el-input v-model="currentChannel.bundle_id" />
          </el-form-item>
          <el-form-item :label="$t('platform.name')" prop="platform">
            <el-select v-model="currentChannel.platform" placeholder="please select platform">
              <el-option label="iOS" value="ios" />
              <el-option label="Android" value="android" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('channel.put_mode')" prop="put_mode">
            <el-select v-model="currentChannel.put_mode" placeholder="please select put mode">
              <el-option label="Normal" :value="1" />
              <el-option label="Backup" :value="2" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('channel.rate') + '(%)'" prop="put_mode">
            <el-input-number
              v-model="currentChannel.rate"
              :precision="2"
              :step="10"
              :max="100"
              :min="0"
            />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">{{ $t('table.cancel') }}</el-button>
          <el-button type="primary" @click="saveChannel()">{{ $t('table.confirm') }}</el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="dialogTokenFormName" :visible.sync="dialogTokenFormVisible">
      <div v-loading="channelCreating" class="form-container">
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
        :data="currentChannelTokens"
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

        <el-table-column align="center" label="Actions" width="100" fixed="right">
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
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        os: '',
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
      const { data, meta } = await channelResource.list(this.query);
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
        const { data } = await channelResource.data(query);
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
    makeToken() {
      this.$confirm(
        'This will make a new token for channel ' +
          this.currentChannel.name +
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
            .makeToken(this.currentChannel.bundle_id, this.newToken.expired_at)
            .then(response => {
              this.$message({
                type: 'success',
                message: 'The new token : ' + response.api_token,
              });
              this.getTokenList();
            })
            .catch(error => {
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
            .destroy(token.id)
            .then(response => {
              this.$message({
                type: 'success',
                message: 'Delete token completed',
              });
              this.getTokenList();
            })
            .catch(error => {
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
