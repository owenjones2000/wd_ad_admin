
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
        class="filter-item"
        type="primary"
        style=" float: right;margin-right:30px"
        icon="el-icon-edit"
        @click="addtagsbtn"
      >{{ $t('table.tags') }}</el-button>
    </div>

    <el-table
      ref="multipleTable"
      v-loading="loading"
      :data="list"
      border
      tooltip-effect="dark"
      :formatter="dateFormat"
      fit
      highlight-current-row
      style="width: 100%"
      @selection-change="handleSelectionChange"
      @sort-change="handleSort"
    >

      <el-table-column prop="id" label="ID" fixed type="selection" />
      <el-table-column prop="id" label="ID" fixed />
      <el-table-column prop="name" label="Ad" fixed />
      <el-table-column label="Campaign" fixed>
        <template slot-scope="scope">
          <router-link class="link-type" :to="'/acquisition/campaign?keyword='+scope.row.campaign.id">
            {{ scope.row.campaign.name }}
          </router-link>
        </template>
      </el-table-column>
      <el-table-column label="App" fixed>
        <template slot-scope="scope">
          <router-link class="link-type" :to="'/acquisition/app?keyword='+scope.row.campaign.app.id">
            {{ scope.row.campaign.app.name }}
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="campaign.advertiser.realname" align="center" label="Advertiser" fixed />
      <el-table-column align="center" label="Preview">
        <template slot-scope="scope">
          <el-link
            v-permission="['advertise.campaign.ad']"
            :type="scope.row.is_upload_completed ? 'success' : 'warning'"
            size="medium"
            icon="el-icon-view"
            :underline="false"
            @click="handlePreview(scope.row);"
          />
        </template>
      </el-table-column>
      <!--  <el-table-column align="center" label="tags">
        <template slot-scope="scope">
          <el-tag v-for="( item,key ) of scope.row.tags" :key="key" style="margin:5px">{{ item.name }}</el-tag>
        </template>
      </el-table-column>-->
      <el-table-column prop="created_at" label="Created" align="center" width="100" />

      <el-table-column align="center" prop="kpi.impressions" label="Impression" />
      <el-table-column align="center" prop="kpi.installs" label="Install" />
      <el-table-column align="center" prop="kpi.spend" label="Spend" />

      <el-table-column align="center" label="Actions" width="150px" fixed="right">
        <template slot-scope="scope">
          <el-tag v-if="scope.row.tags.length>0" type="success">Identified</el-tag>
          <el-tag v-else type="danger">Unidentified</el-tag>
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

    <!--  ?????????????????? -->
    <el-dialog title="Addtags" :visible.sync="addtagsvisible" width="30%">
      <h3>ALL</h3>
      <div>
        <el-tag
          v-for="(item,key) of tagalldata"
          :key="key"
          style="margin:5px;cursor:pointer"
          size="medium"
          @click="selecttags(item)"
        >{{ item.name }}</el-tag>
      </div>
      <h3>chosen</h3>
      <div>
        <el-tag
          v-for="(item,key) of selecttagalldata"
          :key="key"
          style="margin:5px;cursor:pointer"
          type="success"
          closable
          size="medium"
          @close="handleClose(item.name)"
        >{{ item.name }}</el-tag>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="addtagsvisible = false">{{ $t('table.cancel') }}</el-button>
        <el-button type="primary" @click="settags(1)">{{ $t('table.confirm') }}</el-button>
      </span>
    </el-dialog>

    <el-dialog :title="'Preview'" :visible.sync="previewDialogFormVisible" width="50%">
      <div v-for="(asset) in currentAd.assets" :key="asset.id">
        <div v-if="asset.type.mime_type == 'video'" style="display:flex">
          <video
            :src="asset.url"
            :width="((asset.spec && asset.spec.width) ? ((asset.spec.width > 500) ? '500px' : asset.spec.width) : '500') + 'px'"
            poster
            controls
            controlslist="nodownload"
          />
          <div style="margin-left:30px">
            <h3>ALL</h3>
            <div style="display:flex;flex-wrap:wrap">
              <div v-for="(item,key) of tagalldata" :key="key">
                <el-tag
                  v-if="item.group === 1"
                  style="margin:5px;cursor:pointer"
                  size="medium"
                  @click="selecttags(item)"
                >{{ item.name }}</el-tag>
              </div>
            </div>
            <div style="display:flex;margin-top:15px;flex-wrap:wrap">
              <div v-for="(item,key) of tagalldata" :key="key">
                <el-tag
                  v-if="item.group === 2"
                  style="margin:5px;cursor:pointer"
                  size="medium"
                  @click="selecttags(item)"
                >{{ item.name }}</el-tag>
              </div>
            </div>
            <div style="display:flex;margin-top:15px;flex-wrap:wrap">
              <div v-for="(item,key) of tagalldata" :key="key">
                <el-tag
                  v-if="item.group === 3"
                  style="margin:5px;cursor:pointer"
                  size="medium"
                  @click="selecttags(item)"
                >{{ item.name }}</el-tag>
              </div>
            </div>
            <div style="display:flex;margin-top:15px;flex-wrap:wrap">
              <div v-for="(item,key) of tagalldata" :key="key">
                <el-tag
                  v-if="item.group === 4"
                  style="margin:5px;cursor:pointer"
                  size="medium"
                  @click="selecttags(item)"
                >{{ item.name }}</el-tag>
              </div>
            </div>

            <h3>chosen</h3>
            <div>
              <el-tag
                v-for="(item,key) of selecttagalldata"
                :key="key"
                style="margin:5px;cursor:pointer"
                type="success"
                closable
                size="medium"
                @close="handleClose(item.name)"
              >{{ item.name }}</el-tag>
            </div>
            <span slot="footer" class="dialog-footer">
              <el-button type="primary" @click="settags(2)">{{ $t('table.confirm') }}</el-button>
            </span>
          </div>
        </div>
        <el-link
          v-else-if="asset.type.mime_type == 'html'"
          :href="asset.url"
          target="_blank"
          type="primary"
        >Click to preview</el-link>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import CampaignResource from '@/api/campaign';
import waves from '@/directive/waves'; // Waves direc
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const campaignResource = new CampaignResource();

export default {
  name: 'CampaignAdList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      multipleSelection: [],
      addtagsvisible: false,
      list: null,
      tagalldata: [],
      selecttagalldata: [],
      total: 0,
      loading: true,
      downloading: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      previewDialogFormVisible: false,
      currentAd: {
        name: '',
      },
      defaultPickerValue: [new Date(), new Date()],
      pickerOptions: {
        shortcuts: [
          {
            text: 'Today',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              picker.$emit('pick', [start, end]);
            },
          },
          {
            text: 'Yesterday',
            onClick(picker) {
              const end = new Date(
                new Date().setDate(new Date().getDate() - 1)
              );
              const start = new Date(
                new Date().setDate(new Date().getDate() - 1)
              );
              picker.$emit('pick', [start, end]);
            },
          },
          {
            text: 'Last 7 days',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit('pick', [start, end]);
            },
          },
          {
            text: 'Month to date',
            onClick(picker) {
              const end = new Date(
                new Date(
                  new Date().setMonth(new Date().getMonth() + 1)
                ).setDate(0)
              );
              const start = new Date(new Date().setDate(1));
              picker.$emit('pick', [start, end]);
            },
          },
          {
            text: 'The previous Month',
            onClick(picker) {
              const end = new Date(new Date().setDate(0));
              const start = new Date(
                new Date(
                  new Date().setMonth(new Date().getMonth() - 1)
                ).setDate(1)
              );
              picker.$emit('pick', [start, end]);
            },
          },
          {
            text: 'Year to date',
            onClick(picker) {
              const end = new Date(
                new Date(new Date().setMonth(12)).setDate(0)
              );
              const start = new Date(
                new Date(new Date().setMonth(0)).setDate(1)
              );
              picker.$emit('pick', [start, end]);
            },
          },
        ],
      },
    };
  },
  computed: {},
  created() {
    this.resetNewCampaign();
    this.getList();
    this.gettagall();
  },
  methods: {
    checkPermission,
    // ????????????
    selecttags(val) {
      this.selecttagalldata.push(val);
      // console.log(this.se
      var result = [];
      var obj = {};
      for (var i = 0; i < this.selecttagalldata.length; i++) {
        if (!obj[this.selecttagalldata[i].id]) {
          result.push(this.selecttagalldata[i]);
          obj[this.selecttagalldata[i].id] = true;
        }
      }
      this.selecttagalldata = result;
    },
    handleClose(tag) {
      this.selecttagalldata.splice(this.selecttagalldata.indexOf(tag), 1);
    },
    // 1???????????????  2???????????????
    settags(type) {
      const obj = {
        ads: [],
        tags: [],
      };
      console.log(type, this.currentAd.id);
      if (type === 2) {
        obj.ads.push(this.currentAd.id);
      } else if (type === 1) {
        for (const y of this.multipleSelection) {
          obj.ads.push(y.id);
        }
      }
      for (const t of this.selecttagalldata) {
        obj.tags.push(t.id);
      }
      if (obj.ads.length === 0 || obj.tags.length === 0) {
        return false;
      }
      campaignResource.addtgss(obj).then((res) => {
        console.log(res);
        if (res.code === 0) {
          this.$message({
            message: 'Set successfully',
            type: 'success',
          });
          this.addtagsvisible = false;
          this.getList();
        } else {
          this.$message({
            message: 'Setup failed',
            type: 'warning',
          });
        }
      });
    },
    handleSelectionChange(val) {
      this.multipleSelection = val;
    },
    addtagsbtn(val) {
      if (this.multipleSelection.length === 0) {
        this.$message({
          message: 'Please select at least one piece of data',
          type: 'warning',
        });
      } else {
        this.addtagsvisible = true;
      }
    },
    async gettagall() {
      const { data } = await campaignResource.tagAll();
      this.tagalldata = data;
      console.log(this.tagalldata);
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;

      const { data, meta } = await campaignResource.adtagaList(this.query);
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
    handlePreview(ad) {
      this.currentAd = ad;
      this.selecttagalldata = ad.tags;
      this.previewDialogFormVisible = true;
    },
    handleReview(ad) {
      this.$confirm(
        'This will pass the ad ' + ad.name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          if (ad.need_review) {
            campaignResource
              .passAd(ad.campaign_id, ad.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Ad ' + ad.name + ' passed',
                });
                this.getList();
              })
              .catch((error) => {
                console.log(error);
              });
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handleStatus(ad) {
      this.$confirm(
        'This will ' +
          (ad.is_admin_disable ? 'release control for' : 'disable') +
          ' ad ' +
          ad.name +
          '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          if (ad.is_admin_disable) {
            campaignResource
              .enableAd(ad.campaign_id, ad.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Ad ' + ad.name + ' released',
                });
                this.getList();
              })
              .catch((error) => {
                console.log(error);
              });
          } else {
            campaignResource
              .disableAd(ad.campaign_id, ad.id)
              .then((response) => {
                this.$message({
                  type: 'success',
                  message: 'Ad ' + ad.name + ' disabled',
                });
                this.getList();
              })
              .catch((error) => {
                console.log(error);
              });
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handleRestart(ad) {
      this.$confirm(
        'This will resart ad ' + ad.name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          campaignResource
            .restartAd(ad.campaign_id, ad.id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Ad ' + ad.name + ' resart',
              });
              this.getList();
            })
            .catch((error) => {
              console.log(error);
            });
        })
        .catch((error) => {
          console.log(error);
        });
    },
    saveCampaign() {
      this.$refs['campaignForm'].validate((valid) => {
        if (valid) {
          this.campaignCreating = true;
          campaignResource
            .save(this.currentCampaign)
            .then((response) => {
              this.$message({
                message:
                  'Campaign ' +
                  this.currentCampaign.name +
                  ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewCampaign();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch((error) => {
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
      import('@/vendor/Export2Excel').then((excel) => {
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
      return jsonData.map((v) => filterVal.map((j) => v[j]));
    },
    dateFormat(row, column, cellValue, index) {
      var date = row[column.property];
      return date.substr(0, 10);
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
