import request from '@/utils/request';
import Resource from '@/api/resource';

class AudienceResource extends Resource {
  constructor() {
    super('audience');
  }

  upload(data) {
    return request({
      url: '/' + this.uri + '/upload',
      method: 'post',
    }, data);
  }

  taglist(query) {
    return request({
      url: '/' + this.uri + '/taglist',
      method: 'get',
      params: query,
    });
  }
}

export { AudienceResource as default };
