<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        :placeholder="$t('table.keyword')"
        style="width: 150px"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <!-- <el-select
        v-model="query.is_admin_disable"
        clearable
        placeholder="Review Status"
        style="width: 150px;"
        class="filter-item"
      >
        <el-option
          v-for="item in reviews"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
      </el-select> -->
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
      @sort-change="handleSort"
    >
      <el-table-column prop="id" align="center" label="ID" />
      <el-table-column prop="name" align="center" label="Name" />
      <!-- <el-table-column prop="bundle_id" align="center" label="Package" /> -->
      <el-table-column prop="platform" align="center" label="Platform" />

      <!-- <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-icon
            :style="{color: scope.row.status ? '#67C23A' : '#F56C6C'}"
            size="medium"
            :name="scope.row.status ? 'video-play' : 'video-pause'"
          />
        </template>
      </el-table-column> -->
      <el-table-column align="center" label="IsTag">
        <template slot-scope="scope">
          <i
            :style="{
              color: scope.row.tags.length > 0 ? '#67C23A' : '#F56C6C',
            }"
            :class="
              scope.row.tags.length > 0 ? 'el-icon-check' : 'el-icon-close'
            "
          />
        </template>
      </el-table-column>
      <el-table-column
        prop="created_at"
        :formatter="dateFormat"
        label="Created"
        align="center"
        width="100"
      />
      <!-- <el-table-column align="center" label="Audience">
        <template slot-scope="scope">
          <i :style="{color: scope.row.is_audience ? '#67C23A' : '#F56C6C'}" :class="scope.row.is_audience ? 'el-icon-check' : 'el-icon-close'" />
          <el-link v-permission="['advertise.app.edit']" :type="scope.row.is_audience ? 'danger' : 'info'" size="small" icon="el-icon-remove" :underline="false" @click="handleAudience(scope.row)" />
        </template>
      </el-table-column>-->
      <el-table-column align="center" label="Actions" width="310" fixed="right">
        <template slot-scope="scope">
          <el-button
            v-permission="['advertise.ad.tag']"
            type="primary"
            size="small"
            icon="el-icon-edit"
            @click="handleTag(scope.row)"
          >Edit Tag</el-button>

          <!-- <router-link class="link-type" :to="'/acquisition/app/'+scope.row.id+'/ios/info'">
            <el-button
              v-if="scope.row.platform=='ios'"
              size="small"
              type="primary"
              style="margin:0 10px"
              icon="el-icon-view"
            >Ios</el-button>
          </router-link> -->

          <el-link
            class="link-type"
            :href="
              'https://play.google.com/store/apps/details?id=' +
                scope.row.bundle_id
            "
            target="_blank"
          >
            <el-button
              v-if="scope.row.platform == 'android'"
              size="small"
              type="primary"
              style="margin: 0 10px"
              icon="el-icon-view"
            >android</el-button>
          </el-link>
          <!--<el-button v-permission="['advertise.auth.token']" type="normal" size="small" icon="el-icon-key " @click="handleToken(scope.row)" />-->

          <!--<el-button v-permission="['advertise.app.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);" />-->
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />
    <el-dialog :title="'Tag'" :visible.sync="dialogFormVisible" width="50%">
      <el-tree
        ref="tree"
        v-model="treedata"
        :data="dataoption"
        show-checkbox
        default-expand-all
        node-key="id"
        check-strictly
        highlight-current
        :props="defaultProps"
      />
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="settags(2)">{{
          $t('table.confirm')
        }}</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import AppResource from '@/api/app';
import ChannelResource from '@/api/channel';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import defaultDatePickerOptions from '@/utils/datepicker';

const appResource = new AppResource();
const channelResource = new ChannelResource();

export default {
  name: 'AppList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      rowLoading: false,
      downloading: false,
      appCreating: false,
      options: [
        { value: 'ios', label: 'ios' },
        { value: 'android', label: 'android' },
      ],
      reviews: [
        { value: '0', label: 'Yes' },
        { value: '1', label: 'No' },
      ],
      tagalldata: [],
      selecttagalldata: [],
      query: {
        page: 1,
        limit: 15,
        is_admin_disable: 0,
        keyword: '',
        country: '',
        os: '',
        type: '',
        daterange: [new Date(), new Date()],
      },
      newApp: {},
      defaultProps: {
        children: 'children',
        label: 'label',
      },
      dialogFormVisible: false,
      currentAppId: 0,
      currentApp: {
        name: '',
        tokens: [],
      },
      countrys: [],
      dataoption: [],
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
        os: [
          { required: true, message: 'Platform is required', trigger: 'blur' },
        ],
      },
      dialogTokenFormVisible: false,
      dialogTokenFormName: 'Api token',
      newToken: {
        expired_at: null,
      },
      treedata: [],
      pickerOptions: defaultDatePickerOptions,
    };
  },
  computed: {},
  created() {
    this.getList();
    this.getLists();
    this.gettagall();
  },
  methods: {
    getKeys() {
      console.log();
      var aKey = this.getCheckedKeys(
        this.dataoption,
        this.$refs.tree.getCheckedKeys(),
        'id'
      );
      console.log(aKey);
    },
    getCheckedKeys(data, keys, key) {
      var res = [];
      recursion(data, false);
      return res;

      // arr -&gt; ???????????????
      // keys -&gt; getCheckedKeys??????????????????key???
      // isChild -&gt; ??????????????????????????????
      function recursion(arr, isChild) {
        var aCheck = [];
        for (var i = 0; i < arr.length; i++) {
          var obj = arr[i];
          aCheck[i] = false;

          if (obj.children) {
            aCheck[i] = recursion(obj.children, true) ? true : aCheck[i];
            if (aCheck[i]) {
              res.push(obj[key]);
            }
          }

          for (var j = 0; j < keys.length; j++) {
            if (obj[key] === keys[j]) {
              aCheck[i] = true;
              if (res.indexOf(obj[key]) === -1) {
                res.push(obj[key]);
              }
              break;
            }
          }
        }
        if (isChild) {
          return aCheck.indexOf(true) !== -1;
        }
      }
    },
    async getLists() {
      this.loading = true;
      const { data } = await appResource.tagList(this.query);
      this.dataoption = data;
      for (const y of this.dataoption) {
        y.value = y.id;
        y.label = y.name;
        y.disabled = true;
      }

      this.findfunction(this.dataoption);
      this.groups = [];
      this.dataoption.forEach((element, index) => {
        if (element.group === 0) {
          this.groups.push(element);
        }
      });
      this.loading = false;
      console.log(this.dataoption);
    },
    findfunction(arr) {
      arr.forEach((item) => {
        // ??????foreach????????????
        if (item.children.length === 0) {
          // ????????????????????????
          delete item.children;
          return false;
        } else if (item.children.length > 0) {
          // ??????chlidren???????????????
          for (const y of item.children) {
            y.value = y.id;
            y.label = y.name;
          }
          this.findfunction(item.children); // ????????????
        }
      });
    },
    checkPermission,
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
    async gettagall() {
      const { data } = await appResource.tagAll();
      this.tagalldata = data;
      console.log(this.tagalldata);
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await channelResource.appTagList(this.query);
      data.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
        element['loading'] = false;
      });
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    async countryList() {
      const data = await channelResource.countryList();
      this.countrys = data;
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
    settags(type) {
      this.getKeys();
      const obj = {
        apps: [],
        tags: [],
      };
      obj.apps.push(this.currentApp.id);
      const datas = this.$refs.tree.getCheckedNodes();
      for (const t of datas) {
        obj.tags.push(t.id);
      }

      channelResource.addtgss(obj).then((res) => {
        console.log(res);
        if (res.code === 0) {
          this.$message({
            message: 'Set successfully',
            type: 'success',
          });
          this.dialogFormVisible = false;
          this.getList();
        } else {
          this.$message({
            message: 'Setup failed',
            type: 'warning',
          });
        }
      });
    },
    dateFormat(row, column, cellValue, index) {
      var date = row[column.property];
      return date.substr(0, 10);
    },
    handleTag(app) {
      const tagsarr = [];
      for (const i of app.tags) {
        tagsarr.push(i.id);
      }

      setTimeout(() => {
        this.$refs.tree.setCheckedKeys(tagsarr);
      }, 500);
      this.currentApp = app;
      this.selecttagalldata = app.tags;
      this.dialogFormVisible = true;
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const tHeader = ['id', 'app_id', 'name'];
        const filterVal = ['index', 'id', 'name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'app-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]));
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
