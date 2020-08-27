<template>
  <div class="app-container">
    <el-carousel :interval="5000" arrow="always" height="700px" type="card">
      <el-carousel-item v-for="shot in appinfo.screenshotUrls" :key="shot">
        <img :src="shot" alt="">
      </el-carousel-item>
    </el-carousel>
    <div v-loading="appCreating" class="form-container">
      <el-form
        ref="appForm"
        :model="appinfo"
        label-position="left"
        label-width="250px"
      >
        <el-form-item :label="$t('bundleId')" prop="bundleId">
          <el-input v-model="appinfo.bundleId" />
        </el-form-item>
        <el-form-item :label="$t('averageUserRating')" prop="averageUserRating">
          <el-input v-model="appinfo.averageUserRating" />
        </el-form-item>
        <el-form-item :label="$t('sellerName')" prop="sellerName">
          <el-input v-model="appinfo.sellerName" />
        </el-form-item>
        <el-form-item :label="$t('trackName')" prop="trackName">
          <el-input v-model="appinfo.trackName" />
        </el-form-item>
        <el-form-item :label="$t('trackContentRating')" prop="trackContentRating">
          <el-input v-model="appinfo.trackContentRating" />
        </el-form-item>
        <el-form-item :label="$t('description')" prop="description">
          <el-input v-model="appinfo.description" type="textarea" />
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script>
// import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AppResource from '@/api/app';
// import ChannelResource from '@/api/channel';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const appResource = new AppResource();
// const channelResource = new ChannelResource();

export default {
  name: 'AppChannelList',
  components: {},
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      app_id: this.$route.params && this.$route.params.app_id,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        type: '',
        country: '',
        daterange: [new Date(), new Date()],
      },
      countrys: [

      ],
      types: [
        { value: '1', label: 'Reward' },
        { value: '2', label: 'Interstitial' },
      ],
      newChannel: {},
      dialogFormVisible: false,
      currentChannelId: 0,
      currentChannel: {
        name: '',
        tokens: [],
      },
      appinfo: {

      },
      screenshortUrls: [

      ],
      appCreating: true,

    };
  },
  computed: {
  },
  created() {
    this.getIosinfo();
  },
  methods: {
    checkPermission,
    async getIosinfo() {
      const data = await appResource.iosInfo(this.app_id);
      this.appinfo = data;
      this.screenshortUrls = data.screenshotUrls;
      this.appCreating = false;
      console.log(this.appinfo);
      console.log(this.appinfo.screenshotUrls);
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
