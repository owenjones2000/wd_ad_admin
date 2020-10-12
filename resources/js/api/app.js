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
  appTagList(query){
    return request({
      url: '/' + this.uri + '/apptaglist',
      method: 'get',
      params: query,
    });
  }
  tagAll(query) {
    return request({
      url: '/' + this.uri + '/tagall',
      method: 'get',
      params: query,
    });
  }
  tagList(query){
    return request({
      url: '/' + this.uri + '/taglist',
      method: 'get',
      params: query,
    });
  }
  saveTag(resource) {
    var url = '/' + this.uri + '/tag';
    if (resource.id) {
      url = '/' + this.uri + '/tag/' + resource.id;
    }
    return request({
      url: url,
      method: 'post',
      data: resource,
    });
  }
  channelList(app_id, query) {
    return request({
      url: '/' + this.uri + '/' + app_id + '/channel',
      method: 'get',
      params: query,
    });
  }
  iosInfo(app_id){
    return request({
      url: '/' + this.uri + '/' + app_id + '/iosinfo',
      method: 'get',
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
