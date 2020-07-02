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
}

export { AudienceResource as default };
