<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
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
          <span>${{ scope.row.fee_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="DueDate">
        <template slot-scope="scope">
          <span>{{ scope.row.due_date }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Status">
        <template slot-scope="scope">
          <el-link v-permission="['advertise.bill']" :type="(scope.row.fee_amount == 0 || scope.row.paid_at) ? 'success' : 'danger'" size="small" icon="el-icon-money" :underline="false" />
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="300">
        <template slot-scope="scope">
          <el-button v-if="scope.row.paid_at==null" v-permission="['advertise.bill.pay']" type="danger" size="small" icon="el-icon-finished" @click="handlePay(scope.row)">
            Confirm Paid
          </el-button>
          <el-button v-if="scope.row.fee_amount>0" v-permission="['advertise.bill']" type="success" size="small" icon="fa fa-eye" @click="handleInvoice(scope.row)">
            Invoice
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Invoice'" :visible.sync="invoiceDialogVisible">
      <div class="form-container">
        <div id="invoice" v-html="invoice" />
        <div slot="footer" class="el-footer" style="text-align: right">
          <el-button type="primary" @click="handleSendInvoice()">
            {{ $t('Send To') }} : {{ currentBill.email }}
          </el-button>
          <el-button type="primary" @click="handleInvoicePdf()">
            {{ $t('Download') }}
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
      invoiceDialogVisible: false,
      passwordRequired: true,
      currentBillId: 0,
      currentBill: {
        email: '',
      },
      invoice: '',
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
    async handleInvoice(bill) {
      const data = await billResource.invoice(bill.id);
      this.invoice = data;
      this.currentBill = bill;
      this.invoiceDialogVisible = true;
    },
    async handleInvoicePdf() {
      const data = await billResource.invoicePdf(this.currentBill.id);
      const url = window.URL.createObjectURL(data);
      const link = document.createElement('a');
      link.style.display = 'none';
      link.href = url;
      link.setAttribute('download', 'invoice.pdf');
      document.body.appendChild(link);
      link.click();
    },
    handleSendInvoice() {
      this.$confirm('Send pdf of invoice to ' + this.currentBill.email + ' ?', 'Warning', {
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        billResource.sendInvoice(this.currentBill.id).then(response => {
          this.$message({
            type: 'success',
            message: 'Send successfully',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'canceled',
        });
      });
    },
    handlePay(bill) {
      this.$confirm('Confirm that the bill has been paid ?', 'Warning', {
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        billResource.pay(bill.id).then(response => {
          this.$message({
            type: 'success',
            message: 'Pay completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'canceled',
        });
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
table .odd-row {
  background: #f0f0f0;
}
</style>
