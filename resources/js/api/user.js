import request from '@/utils/request';
import Resource from '@/api/resource';

class UserResource extends Resource {
  constructor() {
    super('users');
  }

  save(resource) {
    if (resource.id) {
      return this.update(resource.id, resource);
    } else {
      return this.store(resource);
    }
  }

  permissions(id) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
        method: 'get',
    });
  }

  updatePermission(id, permissions) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'put',
      data: permissions,
    });
  }
}

export { UserResource as default };
