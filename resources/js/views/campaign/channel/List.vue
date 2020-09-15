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
      <el-button v-permission="['basic.campaign.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%" @sort-change="handleSort">
      <!--<el-table-column align="center" label="ID" width="80">-->
      <!--  <template slot-scope="scope">-->
      <!--    <span>{{ scope.row.id }}</span>-->
      <!--  </template>-->
      <!--</el-table-column>-->

      <el-table-column label="Channel" width="300px" fixed>
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column prop="kpi.requests" :formatter="numberFormat" align="center" label="Requests" sortable="custom" />
      <el-table-column prop="kpi.impressions" :formatter="numberFormat" align="center" label="Impressions" sortable="custom" />
      <el-table-column prop="kpi.clicks" :formatter="numberFormat" align="center" label="Clicks" sortable="custom" />
      <el-table-column prop="kpi.installs" :formatter="numberFormat" align="center" label="Installs" sortable="custom" />
      <el-table-column prop="kpi.ctr" :formatter="percentageFormat" align="center" label="CTR" sortable="custom" />
      <el-table-column prop="kpi.cvr" :formatter="percentageFormat" align="center" label="CVR" sortable="custom" />
      <el-table-column prop="kpi.ir" :formatter="percentageFormat" align="center" label="IR" sortable="custom" />
      <el-table-column prop="kpi.spend" :formatter="moneyFormat" align="center" label="Spend" sortable="custom" />
      <el-table-column prop="kpi.ecpi" :formatter="moneyFormat" align="center" label="eCpi" sortable="custom" />
      <el-table-column prop="kpi.ecpm" :formatter="moneyFormat" align="center" label="eCpm" sortable="custom" />
      <el-table-column align="center" label="Blacklist">
        <template slot-scope="scope">
          <i :style="{color: scope.row.is_black ? '#67C23A' : '#F56C6C'}" :class="scope.row.is_black ? 'el-icon-check' : 'el-icon-close'" />
          <el-link v-permission="['advertise.campaign.edit']" :type="scope.row.is_black ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleBlack(scope.row)" />
        </template>
      </el-table-column>
      <el-table-column align="center" label="Whitelist">
        <template slot-scope="scope">
          <i :style="{color: scope.row.is_white ? '#67C23A' : '#F56C6C'}" :class="scope.row.is_white ? 'el-icon-check' : 'el-icon-close'" />
          <el-link v-permission="['advertise.campaign.edit']" :type="scope.row.is_white ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleWhite(scope.row)" />
        </template>
      </el-table-column>
      <!--<el-table-column align="center" label="Status">-->
      <!--<template slot-scope="scope">-->
      <!--<el-icon :style="{color: scope.row.status ? '#67C23A' : '#F56C6C'}" size="small" :name="scope.row.status ? 'video-play' : 'video-pause'" />-->
      <!--</template>-->
      <!--</el-table-column>-->

      <!--  <el-table-column align="center" label="Actions" width="100" fixed="right"> -->
      <!--<template slot-scope="scope">-->
      <!--<el-link v-permission="['advertise.campaign.ad.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)" />-->
      <!--<el-link v-permission="['advertise.campaign.ad.edit']" :type="scope.row.is_admin_disable ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleStatus(scope.row)" />-->
      <!--<el-link v-permission="['advertise.campaign.ad.destroy']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);" />-->
      <!--</template>-->
      <!--  </el-table-column> -->
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Create new campaign'" :visible.sync="dialogFormVisible">
      <div v-loading="campaignCreating" class="form-container">
        <el-form ref="campaignForm" :rules="rules" :model="currentCampaign" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('name')" prop="name">
            <el-input v-model="currentCampaign.name" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="saveCampaign()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import CampaignResource from '@/api/campaign';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const campaignResource = new CampaignResource();

export default {
  name: 'CampaignChannelList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      campaignCreating: false,
      campaign_id: this.$route.params && this.$route.params.campaign_id,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      newCampaign: {},
      dialogFormVisible: false,
      currentCampaignId: 0,
      currentCampaign: {
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
    this.resetNewCampaign();
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;

      const { data, meta } = await campaignResource.channelList(this.campaign_id, this.query);
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
    handleCreate() {
      this.resetNewCampaign();
      this.currentCampaign = this.newCampaign;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['campaignForm'].clearValidate();
      });
    },
    handleEdit(campaign) {
      this.currentCampaign = campaign;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['campaignForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete campaign ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        campaignResource.destroy(id).then(response => {
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
    handleStatus(ad) {
      this.$confirm('This will ' + (ad.is_admin_disable ? 'release control for' : 'disable') + ' ad ' + ad.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (ad.is_admin_disable) {
          campaignResource.enableAd(ad.campaign_id, ad.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Ad ' + ad.name + ' released',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        } else {
          campaignResource.disableAd(ad.campaign_id, ad.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Ad ' + ad.name + ' disabled',
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
    handleBlack(channel) {
      this.$confirm('This will ' + (channel.is_black ? 'remove blacklist' : 'join in blacklist') + ' channel ' + channel.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (channel.is_black) {
          campaignResource.removeBlack(this.campaign_id, channel.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Channel ' + channel.name + ' remove blacklist',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        } else {
          campaignResource.joinBlack(this.campaign_id, channel.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Channel ' + channel.name + ' join blacklist',
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
    handleWhite(channel) {
      this.$confirm('This will ' + (channel.is_white ? 'remove whitelist' : 'join in whitelist') + ' channel ' + channel.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (channel.is_white) {
          campaignResource.removewhite(this.campaign_id, channel.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Channel ' + channel.name + ' remove whitelist',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        } else {
          campaignResource.joinwhite(this.campaign_id, channel.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Channel ' + channel.name + ' join whitelist',
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
    saveCampaign() {
      this.$refs['campaignForm'].validate((valid) => {
        if (valid) {
          this.campaignCreating = true;
          campaignResource
            .save(this.currentCampaign)
            .then(response => {
              this.$message({
                message: 'Campaign ' + this.currentCampaign.name + ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewCampaign();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.campaignCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewCampaign() {
      this.newCampaign = {
        name: '',
      };
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
    dateFormat(row, column, cellValue, index){
      var date = row[column.property];
      return date.substr(0, 10);
    },
    numberFormat(row, column, cellValue, index){
      return (cellValue === undefined || cellValue === null) ? '-' : cellValue;
    },
    moneyFormat(row, column, cellValue, index){
      return (cellValue === undefined || cellValue === null) ? '-' : '$' + cellValue;
    },
    percentageFormat(row, column, cellValue, index){
      return (cellValue === undefined || cellValue === null) ? '-' : cellValue + '%';
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
