<template>
  <el-row :gutter="40" class="panel-group">
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-people">
          <i class="fa fa-globe card-panel-icon" />
          <!--<svg-icon icon-class="shopping" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Countries
          </div>
          <count-to :start-val="0" :end-val="total.countries" :duration="3200" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-message">
          <i class="fa fa-link card-panel-icon" />
          <!--<svg-icon icon-class="peoples" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Apps to channels
          </div>
          <count-to :start-val="0" :end-val="total.apps" :duration="2600" class="card-panel-num" /><i class="fa fa-angle-double-right fa-2x" /><count-to :start-val="0" :end-val="total.channels" :duration="2600" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fa fa-film card-panel-icon" />
          <!--<svg-icon icon-class="user" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Campaigns / Ads
          </div>
          <count-to :start-val="0" :end-val="total.campaigns" :duration="3000" class="card-panel-num" /> / <count-to :start-val="0" :end-val="total.ads" :duration="3000" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-money">
          <i class="fa fa-users card-panel-icon" />
          <!--<svg-icon icon-class="people" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Requests
          </div>
          <count-to :start-val="0" :end-val="total.requests" :duration="total.requests / 1000" class="card-panel-num" />
        </div>
      </div>
    </el-col>

    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-people">
          <i class="fa fa-eye card-panel-icon" />
          <!--<svg-icon icon-class="peoples" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Impressions
          </div>
          <count-to :start-val="0" :end-val="total.impressions" :duration="2600" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-message">
          <i class="fa fa-hand-pointer-o card-panel-icon" />
          <!--<svg-icon icon-class="user" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Clicks
          </div>
          <count-to :start-val="0" :end-val="total.clicks" :duration="3000" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fa fa-android card-panel-icon" />
          <!--<svg-icon icon-class="people" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Installs
          </div>
          <count-to :start-val="0" :end-val="total.installs" :duration="3600" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-money">
          <i class="fa fa-money card-panel-icon" />
          <!--<svg-icon icon-class="shopping" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            Spend
          </div>
          <count-to :start-val="0" :end-val="total.spend" :duration="total.spend / 100" class="card-panel-num" />
        </div>
      </div>
    </el-col>

    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-people">
          <i class="fa fa-adn card-panel-icon" />
          <!--<svg-icon icon-class="peoples" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            New App
          </div>
          <count-to :start-val="0" :end-val="total.newapp" :duration="60" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-message">
          <i class="fa fa-rocket card-panel-icon" />
          <!--<svg-icon icon-class="user" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            New Channel
          </div>
          <count-to :start-val="0" :end-val="total.newchannel" :duration="30" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fa fa-youtube-play card-panel-icon" />
          <!--<svg-icon icon-class="people" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            New Campaign
          </div>
          <count-to :start-val="0" :end-val="total.newcampaign" :duration="60" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper icon-money">
          <i class="fa fa-audio-description card-panel-icon" />
          <!--<svg-icon icon-class="shopping" class-name="card-panel-icon" />-->
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">
            New Ad
          </div>
          <count-to :start-val="0" :end-val="total.newad" :duration="60" class="card-panel-num" />
        </div>
      </div>
    </el-col>
  </el-row>
</template>

<script>
import Statis from '@/api/statis';
import CountTo from 'vue-count-to';

const statis = new Statis();

export default {
  components: {
    CountTo,
  },
  data() {
    return {
      total: {
        countries: 0,
        apps: 0,
        channels: 0,
        campaigns: 0,
        ads: 0,
        requests: 0,
        impressions: 0,
        clicks: 0,
        installs: 0,
        spend: 0,
        newapp: 0,
        newchannel: 0,
        newcampaign: 0,
        newad: 0,
      },
    };
  },
  created() {
    this.getTotal();
    this.getNewAdd();
    // console.log(this.total);
  },
  methods: {
    async getTotal() {
      const query = {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      };
      const { data } = await statis.total(query);
      console.log(data);
      if (data.length > 0) {
        this.total.countries = Number(data[0].countries);
        this.total.apps = Number(data[0].apps);
        this.total.channels = Number(data[0].channels);
        this.total.campaigns = Number(data[0].campaigns);
        this.total.ads = Number(data[0].ads);
        this.total.requests = Number(data[0].requests);
        this.total.impressions = Number(data[0].impressions);
        this.total.clicks = Number(data[0].clicks);
        this.total.installs = Number(data[0].installs);
        this.total.spend = Number(data[0].spend);
      }
    },
    async getNewAdd(){
      const query = {
        daterange: [new Date(), new Date()],
      };
      const data = await statis.newAdd(query);
      console.log(data);
      this.total.newapp = Number(data.newapp);
      this.total.newchannel = Number(data.newchannel);
      this.total.newad = Number(data.newad);
      this.total.newcampaign = Number(data.newcampaign);
    },
  },
};
</script>

<style lang="scss" scoped>
.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);
    border-color: rgba(0, 0, 0, .05);

    /*&:hover {*/
      /*.card-panel-icon-wrapper {*/
        /*color: #fff;*/
      /*}*/

      /*.icon-people {*/
        /*background: #40c9c6;*/
      /*}*/

      /*.icon-message {*/
        /*background: #36a3f7;*/
      /*}*/

      /*.icon-money {*/
        /*background: #f4516c;*/
      /*}*/

      /*.icon-shopping {*/
        /*background: #34bfa3*/
      /*}*/
    /*}*/

    .icon-people {
      color: #303133;
    }

    .icon-message {
      color: #36a3f7;
    }

    .icon-money {
      color: #f4516c;
    }

    .icon-shopping {
      color: #34bfa3
    }

    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 48px;
    }

    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;

      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 16px;
        margin-bottom: 12px;
      }

      .card-panel-num {
        font-size: 20px;
      }
    }
  }
}

@media (max-width:550px) {
  .card-panel-description {
    display: none;
  }

  .card-panel-icon-wrapper {
    float: none !important;
    width: 100%;
    height: 100%;
    margin: 0 !important;

    .svg-icon {
      display: block;
      margin: 14px auto !important;
      float: none !important;
    }
  }
}
</style>
