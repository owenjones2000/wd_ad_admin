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
  userTokenList(user_id){
    return request({
      url: '/account/' + this.uri,
      method: 'get',
      params: {
        'user_id': user_id,
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

  makeUserToken(user_id, expired_at) {
    return request({
      url: '/account/' + this.uri,
      method: 'post',
      data: {
        'user_id': user_id,
        'expired_at': expired_at,
      },
    });
  }
  delUserToken(user_id) {
    return request({
      url: '/account/' + this.uri + '/' + user_id,
      method: 'delete',
    });
  }
}

export { TokenResource as default };
