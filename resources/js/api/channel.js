import request from '@/utils/request';
import Resource from '@/api/resource';

class ChannelResource extends Resource {
  constructor() {
    super('channel');
  }

  data(query) {
    return request({
      url: '/' + this.uri + '/data',
      method: 'get',
      params: query,
    });
  }
  countryList() {
    return request({
      url: '/' + this.uri + '/country-list',
      method: 'get',
    });
  }
  placementData(query) {
    return request({
      url: '/' + this.uri + '/placement-data',
      method: 'get',
      params: query,
    });
  }
  restart(channel_id) {
    return request({
      url: '/' + this.uri + '/' + channel_id + '/restart',
      method: 'post',
    });
  }
  removeBlack(channel_id, app_id) {
    return request({
      url: '/' + this.uri + '/' + channel_id + '/app/' + app_id + '/removeblack',
      method: 'post',
    });
  }
  joinBlack(channel_id, app_id) {
    return request({
      url: '/' + this.uri + '/' + channel_id + '/app/' + app_id + '/joinblack',
      method: 'post',
    });
  }
  appList(channel_id, query) {
    return request({
      url: '/' + this.uri + '/' + channel_id + '/app',
      method: 'get',
      params: query,
    });
  }
  placementList(query) {
    return request({
      url: '/' + this.uri + '/placement',
      method: 'get',
      params: query,
    });
  }

  tagList(query) {
    return request({
      url: '/' + this.uri + '/taglist',
      method: 'get',
      params: query,
    });
  }
  saveTag(resource) {
    var url = '/' + this.uri + '/tag/save';
    if (resource.id) {
      url = '/' + this.uri + '/tag/save/' + resource.id;
    }
    return request({
      url: url,
      method: 'post',
      data: resource,
    });
  }
  appTagList(query) {
    return request({
      url: '/' + this.uri + '/apptaglist',
      method: 'get',
      params: query,
    });
  }

  addtgss(resource) {
    return request({
      url: '/' + this.uri + '/bind/tag',
      method: 'post',
      data: resource,
    });
  }
}

export { ChannelResource as default };
