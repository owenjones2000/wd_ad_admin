import request from '@/utils/request';
import Resource from '@/api/resource';

class AccountResource extends Resource {
  constructor() {
    super('account');
  }

  allPermission(){
    return request({
      url: '/' + this.uri + '/permissions',
      method: 'get',
    });
  }

  permissions(account_id, main_account_id){
    return request({
      url: '/' + this.uri + '/' + account_id + '/permission/to/' + main_account_id,
      method: 'get',
    });
  }

  updatePermission(account_id, main_account_id, permissions) {
    return request({
      url: '/' + this.uri + '/' + account_id + '/permission/to/' + main_account_id,
      method: 'post',
      data: permissions,
    });
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

  enableAdvertising(id){
    return request({
      url: '/' + this.uri + '/' + id + '/advertising/enable',
      method: 'post',
    });
  }

  disableAdvertising(id){
    return request({
      url: '/' + this.uri + '/' + id + '/advertising/disable',
      method: 'post',
    });
  }

  enablePublishing(id){
    return request({
      url: '/' + this.uri + '/' + id + '/publishing/enable',
      method: 'post',
    });
  }

  disablePublishing(id){
    return request({
      url: '/' + this.uri + '/' + id + '/publishing/disable',
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
