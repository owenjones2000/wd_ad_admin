<template>
  <div class="dashboard-editor-container">

    <panel-group />

    <el-row style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <line-chart :chart-data="lineChartData" />
    </el-row>
  </div>
</template>

<script>
import PanelGroup from './components/PanelGroup';
import LineChart from './components/LineChart';
import Statis from '@/api/statis';

const statis = new Statis();

export default {
  name: 'DashboardAdmin',
  components: {
    PanelGroup,
    LineChart,
  },
  data() {
    return {
      lineChartData: {},
    };
  },
  created() {
    this.getTotalWithGroup();
  },
  methods: {
    async getTotalWithGroup() {
      const query = {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(new Date().setDate(new Date().getDate() - 7)), new Date(new Date().setDate(new Date().getDate() - 1))],
        grouping: 'date',
        order: 'asc',
      };
      const { data } = await statis.total(query);
      const option = this.buildLineChartOptions(data, ['impressions', 'clicks', 'installs'], 'date');
      this.lineChartData = option;
    },
    buildLineChartOptions(data, selects, group, subgroup) {
      const { legend_data, xAxis_data, series } = this.buildBarOrLineData('line', data, selects, group, subgroup);
      return {
        tooltip: {
          trigger: 'axis',
        },
        legend: {
          data: [...legend_data],
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true,
        },
        toolbox: {
          feature: {
            saveAsImage: {},
          },
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: [...xAxis_data],
        },
        yAxis: {
          type: 'value',
        },
        series: series,
      };
    },
    buildBarOrLineData(type, data, selects, group, subgroup) {
      var series = [];
      var xAxis_data = new Set();
      var legend_data = new Set();
      if (data.length > 0) {
        var bar_data = {};
        data.forEach(function(item) {
          selects.forEach(function(select) {
            var value = Number(item[select]);
            if (value > 0) {
              if (!bar_data.hasOwnProperty(select)) {
                bar_data[select] = {};
              }
              var subGroupName = (item[subgroup] instanceof Object) ? item[subgroup]['name'] : item[subgroup] || '未知';
              if (!bar_data[select].hasOwnProperty(subGroupName)) {
                bar_data[select][subGroupName] = {};
              }
              var groupName = (item[group] instanceof Object) ? item[group]['name'] : item[group] || '未知';
              bar_data[select][subGroupName][groupName] = value;
              xAxis_data.add(groupName);
            }
          });
        });
        for (const key in bar_data) {
          for (const sub_key in bar_data[key]) {
            var legend_key = (subgroup !== undefined ? (sub_key + ' ' + key) : key);
            var serie = {
              name: legend_key,
              type: type,
            };
            if (type === 'bar') {
              serie['stack'] = key;
            }
            serie['data'] = [];
            xAxis_data.forEach(function(item) {
              if (bar_data[key][sub_key].hasOwnProperty(item)) {
                serie['data'].push(Number(bar_data[key][sub_key][item]));
                legend_data.add(legend_key);
              } else {
                serie['data'].push(0);
              }
            });
            series.push(serie);
          }
        }
      }

      return { legend_data, xAxis_data, series };
    },
  },
};
</script>

<style lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  background-color: rgb(240, 242, 245);
  position: relative;

  .github-corner {
    position: absolute;
    top: 0px;
    border: 0;
    right: 0;
  }

  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}

@media (max-width:1024px) {
  .chart-wrapper {
    padding: 8px;
  }
}
</style>
