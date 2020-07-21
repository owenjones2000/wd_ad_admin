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
}

export { ChannelResource as default };
