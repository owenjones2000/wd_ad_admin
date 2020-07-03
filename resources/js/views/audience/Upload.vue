<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="80px">
      <el-form-item label="csv">
        <el-upload
          class="upload-demo"
          drag
          action
          accept="csv"
          :auto-upload="false"
          :on-change="handleFileChange"
          :http-request="uploadFile"
        >
          <i class="el-icon-upload" />
          <div class="el-upload__text">
            <em>点击上传</em>
          </div>
          <div slot="tip" class="el-upload__tip">只能上传csv文件</div>
        </el-upload>
      </el-form-item>
      <el-form-item label="tag name">
        <el-input v-model="form.tag" placeholder="tag" clearable />
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          style="width:100%"
          :loading="authLoading"
          @click="uploadClick"
        >submit</el-button>
      </el-form-item>
    </el-form>

    <el-table
      v-loading="loading"
      border
      fit
      highlight-current-row
      :data="tableData"
      style="width: 100%"
    >
      <el-table-column prop="id" label="Id" />
      <el-table-column prop="batch_no" label="Batch_no" />
      <el-table-column prop="tag" label="Tag" />
      <el-table-column prop="count" label="Count" />
      <el-table-column prop="created_at" label="Time" />
    </el-table>
    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getLog"
    />
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking
import request from '@/utils/request';

export default {
  name: 'AudienceUpload',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      form: {
        idfa_file: '',
        tag: '',
      },
      loading: true,
      authLoading: false,
      formData: null,
      tableData: {},
      query: {
        page: 1,
        limit: 15,
      },
      total: 0,
    };
  },
  computed: {},
  created() {
    this.formData = new FormData();
    this.getLog();
    console.log(this.tableData);
  },
  methods: {
    checkPermission,
    handleFileChange(file) {
      this.form.idfa_file = file.raw;
      this.formData.append('idfa_file', file.raw);
      console.log(this.formData);
    },
    async getLog() {
      this.loading = true;
      const { data, meta } = await request({
        url: '/audience/upload/log',
        method: 'get',
        params: this.query,
      });
      this.tableData = data;
      this.total = meta.total;
      this.loading = false;
    },
    uploadFile() {
      request
        .post('/audience/upload', this.formData)
        .then(response => {
          this.$message({
            type: 'success',
          });
          this.formData = new FormData();
        })
        .catch(error => {
          console.log(error);
          this.formData = new FormData();
        });
      //   audienceResource.upload(this.formData).then(response => {
      //     this.$message({
      //       type: 'success',
      //     });
      //   }).catch(error => {
      //     console.log(error);
      //   });
    },
    uploadClick() {
      this.authLoading = true;
      this.formData.append('tag', this.form.tag);
      request
        .post('/audience/upload', this.formData)
        .then(response => {
          this.$message({
            type: 'success',
          });
          this.authLoading = false;
          this.formData = new FormData();
          this.getLog();
        })
        .catch(error => {
          console.log(error);
          this.authLoading = false;
          this.formData = new FormData();
        });
    },
  },
};
</script>
