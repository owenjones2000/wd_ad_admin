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

  tagApps(id) {
    return request({
      url: '/' + this.uri + '/tag/' + id + '/apps',
      method: 'get',
    });
  }

  updateTagApps(id, apps) {
    return request({
      url: '/' + this.uri + '/tag/' + id + '/apps',
      method: 'put',
      data: apps,
    });
  }
}

export { AudienceResource as default };
