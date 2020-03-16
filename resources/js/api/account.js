import request from '@/utils/request';
import Resource from '@/api/resource';

class AccountResource extends Resource {
  constructor() {
    super('account');
  }

  enable(id){
    return request({
      url: '/' + this.uri + '/' + id + '/enable',
      method: 'post',
    });
  }

  disable(id){
    return request({
      url: '/' + this.uri + '/' + id + '/disable',
      method: 'post',
    });
  }

  saveBillSet(billSet){
    return request({
      url: '/' + this.uri + '/' + billSet.id + '/bill',
      method: 'post',
      data: billSet,
    });
  }
}

export { AccountResource as default };
