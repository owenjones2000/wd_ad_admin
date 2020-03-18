import request from '@/utils/request';
import Resource from '@/api/resource';

class BillResource extends Resource {
  constructor() {
    super('bill');
  }

  pay(id){
    return request({
      url: '/' + this.uri + '/' + id + '/pay',
      method: 'post',
    });
  }
  invoice(id){
    return request({
      url: '/' + this.uri + '/' + id + '/invoice',
      method: 'get',
    });
  }
  invoicePdf(id){
    return request({
      url: '/' + this.uri + '/' + id + '/invoice/pdf',
      method: 'get',
      responseType: 'blob',
    });
  }
  sendInvoice(id){
    return request({
      url: '/' + this.uri + '/' + id + '/invoice/send',
      method: 'post',
    });
  }
}

export { BillResource as default };
