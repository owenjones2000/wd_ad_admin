<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button v-permission="['advertise.bill.edit']" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <!--<el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">-->
      <!--  {{ $t('table.export') }}-->
      <!--</el-button>-->
    </div>

    <el-table
      v-loading="loading"
      :data="list"
      row-key="id"
      border
      fit
      highlight-current-row
      style="width: 100%"
      :tree-props="{children: 'children', hasChildren: 'hasChildren'}"
    >

      <el-table-column align="left" label="Real Name">
        <template slot-scope="scope">
          <span>{{ scope.row.realname }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Email">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="StartDate">
        <template slot-scope="scope">
          <span>{{ scope.row.start_date }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="EndDate">
        <template slot-scope="scope">
          <span>{{ scope.row.end_date }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Fee Amount">
        <template slot-scope="scope">
          <span>{{ scope.row.fee_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="DueDate">
        <template slot-scope="scope">
          <span>{{ scope.row.due_date }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-link v-permission="['advertise.bill.edit']" :type="scope.row.status ? 'success' : 'info'" size="small" icon="el-icon-s-custom" :underline="false" @click="handleStatus(scope.row)" />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="200">
        <template slot-scope="scope">
          <el-button v-permission="['advertise.bill.edit']" type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row)">
            Edit
          </el-button>
          <!--<el-button v-permission="['advertise.bill.remove']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">-->
          <!--  Delete-->
          <!--</el-button>-->
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Edit bill'" :visible.sync="dialogFormVisible">
      <div v-loading="billCreating" class="form-container">
        <el-form ref="billForm" :rules="rules" :model="currentBill" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('email')" prop="email">
            <el-input v-model="currentBill.email" />
          </el-form-item>
          <el-form-item :label="$t('bill.realname')" prop="realname">
            <el-input v-model="currentBill.realname" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="currentBill.password" show-password />
          </el-form-item>
          <el-form-item :label="$t('user.confirmPassword')" prop="confirmPassword">
            <el-input v-model="currentBill.confirmPassword" show-password />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="saveBill()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import BillResource from '@/api/bill';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Waves directive
import checkPermission from '@/utils/permission'; // Permission checking

const billResource = new BillResource();

export default {
  name: 'BillList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: [],
      total: 0,
      loading: true,
      downloading: false,
      billCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [new Date(), new Date()],
      },
      newBill: {},
      dialogFormVisible: false,
      passwordRequired: true,
      currentBillId: 0,
      currentBill: {
        email: '',
      },

    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    checkPermission,

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await billResource.list(this.query);
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
    handleEdit(bill) {
      this.currentBill = bill;
      this.passwordRequired = false;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['billForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete bill ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        billResource.destroy(id).then(response => {
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
    handleStatus(bill) {
      var displayName = bill.realname + '(' + bill.email + ')';
      this.$confirm('This will ' + (bill.status ? 'disable' : 'enable') + ' bill ' + displayName + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        if (bill.status) {
          billResource.disable(bill.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Bill ' + displayName + ' disabled',
            });
            bill.status = false;
          }).catch(error => {
            console.log(error);
          });
        } else {
          billResource.enable(bill.id).then(response => {
            this.$message({
              type: 'success',
              message: 'Bill ' + displayName + ' enabled',
            });
            bill.status = true;
          }).catch(error => {
            console.log(error);
          });
        }
      }).catch(error => {
        console.log(error);
      });
    },
    saveBill() {
      this.$refs['billForm'].validate((valid) => {
        if (valid) {
          this.billCreating = true;
          billResource
            .save(this.currentBill)
            .then(response => {
              this.$message({
                message: 'Bill ' + this.currentBill.email + ' has been saved successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewBill();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.billCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'bill_id', 'name'];
        const filterVal = ['index', 'id', 'name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'bill-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
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
