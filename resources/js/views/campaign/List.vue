/* eslint-disable vue/no-parsing-error */
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

      <el-table-column label="Campaign" width="250px" fixed>
        <template slot-scope="scope">
          <router-link class="link-type" :to="'/acquisition/campaign/'+scope.row.id+'/ad'">
            {{ scope.row.name }}
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="app.name" align="center" label="App" fixed />
      <el-table-column prop="kpi.spend" :formatter="moneyFormat" align="center" label="Spend" sortable="custom" />
      <el-table-column prop="kpi.ecpi" :formatter="moneyFormat" align="center" label="eCpi" sortable="custom" />
      <el-table-column prop="kpi.ecpm" :formatter="moneyFormat" align="center" label="eCpm" sortable="custom" />
      <el-table-column prop="default_budget" align="center" label="Budget" />
      <el-table-column prop="default_bid" align="center" label="Bid" />
      <el-table-column prop="created_at" :formatter="dateFormat" label="Created" align="center" width="100" />
      <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-icon :style="{color: scope.row.status ? '#67C23A' : '#F56C6C'}" size="small" :name="scope.row.status ? 'video-play' : 'video-pause'" />
          <el-link v-permission="['advertise.campaign.edit']" :type="scope.row.is_admin_disable ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleStatus(scope.row)" />
        </template>
      </el-table-column>

      <el-table-column prop="kpi.requests" :formatter="numberFormat" align="center" label="Requests" sortable="custom" />
      <el-table-column prop="kpi.impressions" :formatter="numberFormat" align="center" label="Impressions" sortable="custom" />
      <el-table-column prop="kpi.clicks" :formatter="numberFormat" align="center" label="Clicks" sortable="custom" />
      <el-table-column prop="kpi.installs" :formatter="numberFormat" align="center" label="Installs" sortable="custom" />
      <el-table-column prop="kpi.ctr" :formatter="percentageFormat" align="center" label="CTR" sortable="custom" />
      <el-table-column prop="kpi.cvr" :formatter="percentageFormat" align="center" label="CVR" sortable="custom" />
      <el-table-column prop="kpi.ir" :formatter="percentageFormat" align="center" label="IR" sortable="custom" />
      <el-table-column prop="advertiser.realname" align="center" label="Advertiser" />
      <el-table-column align="center" label="Actions" fixed="right" width="270">
        <template slot-scope="scope">
          <el-button
            v-permission="['advertise.campaign']"
            type="primary"
            size="small"
            icon="el-icon-info"
            @click="handleEdit(scope.row)"
          />
          <router-link class="link-type" :to="'/acquisition/campaign/'+scope.row.id+'/channel'">Sources</router-link>
          <el-link v-permission="['advertise.campaign.restart']" type="danger" size="small" icon="el-icon-refresh-left" :underline="false" @click="handleRestart(scope.row)" />
          <!--<el-link v-permission="['advertise.campaign.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)" />-->
          <!--<el-link v-permission="['advertise.campaign.destroy']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);" />-->
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'campaign info'" :visible.sync="dialogFormVisible">
      <div v-loading="campaignCreating" class="form-container">
        <el-form ref="campaignForm" :rules="rules" :model="currentCampaign" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('name')" prop="name">
            <el-input v-model="currentCampaign.name" />
          </el-form-item>
          <el-form-item label="Gender">
            <el-radio-group v-model="currentCampaign.audience.gender">
              <el-radio :label="0" /> All
              <el-radio :label="1" /> Male
              <el-radio :label="2" /> Female
            </el-radio-group>
          </el-form-item>
          <el-form-item label="Adult">
            <el-radio-group v-model="currentCampaign.audience.adult">
              <el-radio :label="0" />All
              <el-radio :label="1" />Adult
            </el-radio-group>
          </el-form-item>
          <el-form-item label="States">
            <el-select v-model="currentCampaign.audience.states" multiple placeholder="select">
              <el-option
                v-for="(item,i) in currentCampaign.audience.all_states"
                :key="i"
                :label="item"
                :value="i"
              />
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <!-- <el-button type="primary" @click="saveCampaign()">
            {{ $t('table.confirm') }}
          </el-button> -->
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
  name: 'CampaignList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      campaignCreating: false,
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
        audience: {},
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
      const { data, meta } = await campaignResource.list(this.query);
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
      console.log(campaign);
      console.log(this.currentCampaign.audience.gender);
      console.log(this.currentCampaign.audience.adult);
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
    handleStatus(campaign) {
      this.$confirm('This will ' + (campaign.is_admin_disable ? 'release control for' : 'disable') + ' campaign ' + campaign.name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (campaign.is_admin_disable) {
          campaignResource.enable(campaign.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Campaign ' + campaign.name + ' released',
            });
            this.getList();
          }).catch(error => {
            console.log(error);
          });
        } else {
          campaignResource.disable(campaign.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Campaign ' + campaign.name + ' disabled',
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
    handleRestart(campaign) {
      this.$confirm('This will resart campaign ' + campaign.name + ' all ad. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        campaignResource.restart(campaign.id).then(response => {
          this.$message({
            type: 'success',
            message: 'Campaign ' + campaign.name + 'resart',
          });
          this.getList();
        }).catch(error => {
          console.log(error);
        });
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
