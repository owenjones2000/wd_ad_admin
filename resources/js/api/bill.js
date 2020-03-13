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
}

export { BillResource as default };
