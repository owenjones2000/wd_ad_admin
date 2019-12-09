import request from '@/utils/request';
import Resource from '@/api/resource';

class TokenResource extends Resource {
  constructor() {
    super('auth/token');
  }

  list(bundle_id){
    return request({
      url: '/' + this.uri,
      method: 'get',
      params: {
        'bundle_id': bundle_id,
      },
    });
  }

  makeToken(bundle_id, expired_at) {
    return request({
      url: '/' + this.uri,
      method: 'post',
      data: {
        'bundle_id': bundle_id,
        'expired_at': expired_at,
      },
    });
  }
}

export { TokenResource as default };
