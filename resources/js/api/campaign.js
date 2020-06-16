import request from '@/utils/request';
import Resource from '@/api/resource';

class CampaignResource extends Resource {
  constructor() {
    super('campaign');
  }

  enable(campaign_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/enable',
      method: 'post',
    });
  }

  disable(campaign_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/disable',
      method: 'post',
    });
  }

  restart(campaign_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/restart',
      method: 'post',
    });
  }

  adList(campaign_id, query) {
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/ad',
      method: 'get',
      params: query,
    });
  }

  channelList(campaign_id, query) {
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/channel',
      method: 'get',
      params: query,
    });
  }

  enableAd(campaign_id, ad_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/ad/' + ad_id + '/enable',
      method: 'post',
    });
  }

  restartAd(campaign_id, ad_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/ad/' + ad_id + '/restart',
      method: 'post',
    });
  }

  disableAd(campaign_id, ad_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/ad/' + ad_id + '/disable',
      method: 'post',
    });
  }

  passAd(campaign_id, ad_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/ad/' + ad_id + '/pass',
      method: 'post',
    });
  }
}

export { CampaignResource as default };
