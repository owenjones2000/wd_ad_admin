import request from '@/utils/request';

class Statis {
  total(query){
    return request({
      url: '/statis/total',
      method: 'get',
      params: query,
    });
  }
}

export { Statis as default };
