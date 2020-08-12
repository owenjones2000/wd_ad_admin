<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        :placeholder="$t('table.keyword')"
        style="width: 150px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select
        v-model="query.os"
        clearable
        placeholder="platform"
        style="width: 150px;"
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
      <el-select
        v-model="query.country"
        clearable
        placeholder="ALL Country"
        style="width: 150px;"
        class="filter-item"
      >
        <el-option
          v-for="item in countrys"
          :key="item.code"
          :label="item.name"
          :value="item.code"
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
      <!--<el-button v-permission="['advertise.app.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">-->
      <!--  {{ $t('table.add') }}-->
      <!--</el-button>-->
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
      style="width: 100%; margin-bottom: 20px;"
    >
      <el-table-column align="center" label="Apps" prop="apps" />
      <el-table-column align="center" label="Campaigns" prop="campaigns" />
      <el-table-column align="center" label="Ads" prop="ads" />
      <el-table-column align="center" label="Channels" prop="channels" />
      <el-table-column align="center" label="Countries" prop="countries" />
      <el-table-column align="center" label="Requests">
        <template slot-scope="scope">
          <span>{{ scope.row.requests ? scope.row.requests : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Impressions">
        <template slot-scope="scope">
          <span>{{ scope.row.impressions ? scope.row.impressions : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Clicks">
        <template slot-scope="scope">
          <span>{{ scope.row.clicks ? scope.row.clicks : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Installs">
        <template slot-scope="scope">
          <span>{{ scope.row.installs ? scope.row.installs : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CTR">
        <template slot-scope="scope">
          <span>{{ scope.row.ctr ? scope.row.ctr : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CVR">
        <template slot-scope="scope">
          <span>{{ scope.row.cvr ? scope.row.cvr : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="IR">
        <template slot-scope="scope">
          <span>{{ scope.row.ir ? scope.row.ir : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Spend">
        <template slot-scope="scope">
          <span>${{ scope.row.spend ? scope.row.spend : '0.00' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="eCpi">
        <template slot-scope="scope">
          <span>${{ scope.row.ecpi ? scope.row.ecpi : '0.00' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="eCpm">
        <template slot-scope="scope">
          <span>${{ scope.row.ecpm ? scope.row.ecpm : '0.00' }}</span>
        </template>
      </el-table-column>
    </el-table>
    <el-divider />
    <el-table
      v-loading="loading"
      :data="listByGroup"
      border
      fit
      highlight-current-row
      style="width: 100%"
    >
      <el-table-column align="center" label="Date" prop="date" />
      <el-table-column align="center" label="Apps" prop="apps" />
      <el-table-column align="center" label="Campaigns" prop="campaigns" />
      <el-table-column align="center" label="Ads" prop="ads" />
      <el-table-column align="center" label="Channels" prop="channels" />
      <el-table-column align="center" label="Countries" prop="countries" />
      <el-table-column align="center" label="Requests">
        <template slot-scope="scope">
          <span>{{ scope.row.requests ? scope.row.requests : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Impressions">
        <template slot-scope="scope">
          <span>{{ scope.row.impressions ? scope.row.impressions : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Clicks">
        <template slot-scope="scope">
          <span>{{ scope.row.clicks ? scope.row.clicks : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Installs">
        <template slot-scope="scope">
          <span>{{ scope.row.installs ? scope.row.installs : 0 }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CTR">
        <template slot-scope="scope">
          <span>{{ scope.row.ctr ? scope.row.ctr : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CVR">
        <template slot-scope="scope">
          <span>{{ scope.row.cvr ? scope.row.cvr : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="IR">
        <template slot-scope="scope">
          <span>{{ scope.row.ir ? scope.row.ir : '0.00' }}%</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Spend">
        <template slot-scope="scope">
          <span>${{ scope.row.spend ? scope.row.spend : '0.00' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="eCpi">
        <template slot-scope="scope">
          <span>${{ scope.row.ecpi ? scope.row.ecpi : '0.00' }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="eCpm">
        <template slot-scope="scope">
          <span>${{ scope.row.ecpm ? scope.row.ecpm : '0.00' }}</span>
        </template>
      </el-table-column>
    </el-table>
    <!-- <pagination v-show="totalListByGroup>0" :total="totalListByGroup" :page.sync="query.page" :limit.sync="query.limit" @pagination="getListByGroup" /> -->
  </div>
</template>

<script>
import Statis from '@/api/statis';
// import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import defaultDatePickerOptions from '@/utils/datepicker';
import ChannelResource from '@/api/channel';

const statis = new Statis();
const channelResource = new ChannelResource();

export default {
  name: 'AdvertiseStatis',
  // components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      listByGroup: null,
      totalListByGroup: 0,
      loading: true,
      options: [
        { value: 'ios', label: 'ios' },
        { value: 'android', label: 'android' },
      ],
      downloading: false,
      appCreating: false,
      countrys: [

      ],
      types: [
        { value: '1', label: 'Reward' },
        { value: '2', label: 'Interstitial' },
      ],
      query: {
        page: 1,
        limit: 15,
        os: '',
        keyword: '',
        type: '',
        country: '',
        daterange: [
          new Date(new Date().setDate(new Date().getDate() - 7)),
          new Date(),
        ],
      },
      pickerOptions: defaultDatePickerOptions,
    };
  },
  computed: {},
  created() {
    this.getList();
    this.getListByGroup();
    this.countryList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      // const { data, meta } = await statis.total(this.query);
      const { data } = await statis.total(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      // this.total = meta.total;
      this.loading = false;
    },
    async countryList(){
      const data = await channelResource.countryList();
      this.countrys = data;
    },
    async getListByGroup() {
      const { limit, page } = this.query;
      const group_query = { ...this.query };
      group_query['grouping'] = 'date';
      this.loading = true;
      // const { data, meta } = await statis.total(group_query);
      const { data } = await statis.total(group_query);
      this.listByGroup = data;
      this.listByGroup.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      // this.totalListByGroup = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
      this.getListByGroup();
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const tHeader = [
          'Date',
          'Apps',
          'Campaigns',
          'Ads',
          'Channels',
          'Countries',
          'Requests',
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
          'date',
          'apps',
          'campaigns',
          'ads',
          'channels',
          'countries',
          'requests',
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
        const data = this.formatJson(filterVal, this.listByGroup);
        // const data = this.list;
        console.log(data);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'statis-list',
          bookType: 'csv',
        });
        this.downloading = false;
      });
    },
    // formatJson(filterVal, jsonData) {
    //   return jsonData.map((v) => filterVal.map((j) => v[j]));
    // },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'ctr' || j === 'cvr' || j === 'ir') {
          return v[j] + '%';
        } else {
          return v[j];
        }
      }));
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

.el-container {
  margin-bottom: 20px;
  &:last-child {
    margin-bottom: 0;
  }
}
</style>
