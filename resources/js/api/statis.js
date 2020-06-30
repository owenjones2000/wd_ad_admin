import request from '@/utils/request';

class Statis {
  total(query){
    return request({
      url: '/statis/total',
      method: 'get',
      params: query,
    });
  }
  newAdd(query){
    return request({
      url: '/statis/newadd',
      method: 'get',
      params: query,
    });
  }
  group(query){
    return request({
      url: '/statis/group',
      method: 'get',
      params: query,
    });
  }
  groupByChannel(query){
    return request({
      url: '/statis/group/channel',
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
  deviceByChannel(query){
    return request({
      url: '/statis/device/channel',
      method: 'get',
      params: query,
    });
  }
  deviceByApp(query){
    return request({
      url: '/statis/device/app',
      method: 'get',
      params: query,
    });
  }
}

export { Statis as default };
