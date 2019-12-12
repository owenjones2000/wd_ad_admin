import request from '@/utils/request';
import Resource from '@/api/resource';

class AppResource extends Resource {
  constructor() {
    super('app');
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
}

export { AppResource as default };
