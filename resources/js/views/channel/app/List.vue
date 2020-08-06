<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-select
        v-model="query.type"
        clearable
        placeholder="Type"
        style="width: 150px;"
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
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <!--<el-table-column align="center" label="ID" width="80">-->
      <!--  <template slot-scope="scope">-->
      <!--    <span>{{ scope.row.id }}</span>-->
      <!--  </template>-->
      <!--</el-table-column>-->

      <el-table-column label="App" width="300px">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
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
      <el-table-column align="center" label="Blacklist">
        <template slot-scope="scope">
          <i :style="{color: scope.row.is_black ? '#67C23A' : '#F56C6C'}" :class="scope.row.is_black ? 'el-icon-check' : 'el-icon-close'" />
          <el-link v-permission="['advertise.channel.edit']" :type="scope.row.is_black ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleBlack(scope.row)" />
        </template>
      </el-table-column>
      <!-- <el-table-column align="center" label="Status">
        <template slot-scope="scope">
        <el-icon :style="{color: scope.row.status ? '#67C23A' : '#F56C6C'}" size="small" :name="scope.row.status ? 'video-play' : 'video-pause'" />
        </template>
      </el-table-column> -->

      <!-- <el-table-column align="center" label="Actions" width="100">
        <template slot-scope="scope">
        <el-link v-permission="['advertise.channel.ad.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)" />
        <el-link v-permission="['advertise.channel.ad.edit']" :type="scope.row.is_admin_disable ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleStatus(scope.row)" />
        <el-link v-permission="['advertise.channel.ad.destroy']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);" />
        </template>
      </el-table-column> -->
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Create new channel'" :visible.sync="dialogFormVisible">
      <div v-loading="channelCreating" class="form-container">
        <el-form ref="channelForm" :rules="rules" :model="currentChannel" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('name')" prop="name">
            <el-input v-model="currentChannel.name" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="saveChannel()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import ChannelResource from '@/api/channel';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const channelResource = new ChannelResource();

export default {
  name: 'ChannelAppList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      channelCreating: false,
      channel_id: this.$route.params && this.$route.params.channel_id,
      types: [
        { value: '1', label: 'Reward' },
        { value: '2', label: 'Interstitial' },
      ],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        type: '',
        daterange: [new Date(), new Date()],
      },
      newChannel: {},
      dialogFormVisible: false,
      currentChannelId: 0,
      currentChannel: {
        name: '',
        tokens: [],
      },
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        bundle_id: [{ required: true, message: 'Package name is required', trigger: 'blur' }],
        platform: [{ required: true, message: 'Platform is required', trigger: 'blur' }],
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
    this.resetNewChannel();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;

      const { data, meta } = await channelResource.appList(this.channel_id, this.query);
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
    handleCreate() {
      this.resetNewChannel();
      this.currentChannel = this.newChannel;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['channelForm'].clearValidate();
      });
    },
    handleBlack(app) {
      this.$confirm('This will ' + (app.is_black ? 'remove blacklist' : 'join in blacklist') + ' app ' + app.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (app.is_black) {
          channelResource.removeBlack(this.channel_id, app.id).then(response => {
            this.$message({
              type: 'success',
              message: 'app ' + app.name + ' remove blacklist',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        } else {
          channelResource.joinBlack(this.channel_id, app.id).then(response => {
            this.$message({
              type: 'success',
              message: 'app ' + app.name + ' join blacklist',
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
    handleEdit(channel) {
      this.currentChannel = channel;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['channelForm'].clearValidate();
      });
    },
    saveChannel() {
      this.$refs['channelForm'].validate((valid) => {
        if (valid) {
          this.channelCreating = true;
          channelResource
            .save(this.currentChannel)
            .then(response => {
              this.$message({
                message: 'Channel ' + this.currentChannel.name + ' has been saved successfully.',
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
    dateFormat(row, column, cellValue, index){
      var date = row[column.property];
      return date.substr(0, 10);
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
