import request from '@/utils/request';
import Resource from '@/api/resource';

class AppResource extends Resource {
  constructor() {
    super('app');
  }

  data(query) {
    return request({
      url: '/' + this.uri + '/data',
      method: 'get',
      params: query,
    });
  }
  appList(query){
    return request({
      url: '/' + this.uri + '/applist',
      method: 'get',
      params: query,
    });
  }
  channelList(app_id, query) {
    return request({
      url: '/' + this.uri + '/' + app_id + '/channel',
      method: 'get',
      params: query,
    });
  }
  campaignList(app_id, query) {
    return request({
      url: '/' + this.uri + '/' + app_id + '/campaign',
      method: 'get',
      params: query,
    });
  }

  enable(app_id){
    return request({
      url: '/' + this.uri + '/' + app_id + '/enable',
      method: 'post',
    });
  }

  disable(app_id){
    return request({
      url: '/' + this.uri + '/' + app_id + '/disable',
      method: 'post',
    });
  }

  enableAudi(app_id) {
    return request({
      url: '/' + this.uri + '/' + app_id + '/enableaudi',
      method: 'post',
    });
  }

  disableAudi(app_id) {
    return request({
      url: '/' + this.uri + '/' + app_id + '/disableaudi',
      method: 'post',
    });
  }
}

export { AppResource as default };
