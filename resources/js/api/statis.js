import request from '@/utils/request';

class Statis {
  total(query){
    return request({
      url: '/statis/total',
      method: 'get',
      params: query,
    });
  }
  device(query){
    return request({
      url: '/statis/device',
      method: 'get',
      params: query,
    });
  }
}

export { Statis as default };
