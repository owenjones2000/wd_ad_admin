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
      <el-button v-permission="['advertise.channel.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">
        {{ $t('table.export') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
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

      <el-table-column align="center" label="Platform">
        <template slot-scope="scope">
          <span>{{ scope.row.platform }}</span>
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

      <el-table-column align="center" label="Actions" width="200">
        <template slot-scope="scope">
          <el-button v-permission="['advertise.channel.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)">
            Edit
          </el-button>
          <el-button v-permission="['basic.auth.token']" type="normal" size="small" icon="el-icon-key " @click="handleToken(scope.row)">
            Token
          </el-button>
          <!--<el-button v-permission="['advertise.channel.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">-->
          <!--  Delete-->
          <!--</el-button>-->
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Create new channel'" :visible.sync="dialogFormVisible">
      <div v-loading="channelCreating" class="form-container">
        <el-form ref="channelForm" :rules="rules" :model="currentChannel" label-position="left" label-width="150px" style="max-width: 500px;">
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

    <el-dialog :title="dialogTokenFormName" :visible.sync="dialogTokenFormVisible">
      <div v-loading="channelCreating" class="form-container">
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
      <el-table v-loading="loading" :data="currentChannelTokens" border fit highlight-current-row style="width: 100%">
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
      query: {
        page: 1,
        limit: 15,
        keyword: '',
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
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        bundle_id: [{ required: true, message: 'Package name is required', trigger: 'blur' }],
        platform: [{ required: true, message: 'Platform is required', trigger: 'blur' }],
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
    this.resetNewChannel();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await channelResource.list(this.query);
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
    handleEdit(channel) {
      this.currentChannel = channel;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['channelForm'].clearValidate();
      });
    },
    async getTokenList(){
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
      this.$confirm('This will make a new token for channel ' + this.currentChannel.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        tokenResource.makeToken(this.currentChannel.bundle_id, this.newToken.expired_at).then(response => {
          this.$message({
            type: 'success',
            message: 'The new token : ' + response.api_token,
          });
          this.getTokenList();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Make token canceled',
        });
      });
    },
    handleTokenDelete(token) {
      this.$confirm('This will permanently delete token ' + token.access_token + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        tokenResource.destroy(token.id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete token completed',
          });
          this.getTokenList();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete token canceled',
        });
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete channel ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        channelResource.destroy(id).then(response => {
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
