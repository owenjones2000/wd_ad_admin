import request from '@/utils/request';
import Resource from '@/api/resource';

class ChannelResource extends Resource {
  constructor() {
    super('channel');
  }

  appList(channel_id, query) {
    return request({
      url: '/' + this.uri + '/' + channel_id + '/app',
      method: 'get',
      params: query,
    });
  }
}

export { ChannelResource as default };
