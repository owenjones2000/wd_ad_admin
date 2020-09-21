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
  addtgss(resource) {
    return request({
      url: '/' + this.uri + '/ad/bindtag',
      method: 'post',
      data: resource,
    });
  }
  tagList(query) {
    return request({
      url: '/' + this.uri + '/ad/taglist',
      method: 'get',
      params: query,
    });
  }
  tagAll(query) {
    return request({
      url: '/' + this.uri + '/ad/tagall',
      method: 'get',
      params: query,
    });
  }
  saveTag(resource) {
    var url = '/' + this.uri + '/ad/tag';
    if (resource.id) {
      url = '/' + this.uri + '/ad/tag/' + resource.id;
    }
    return request({
      url: url,
      method: 'post',
      data: resource,
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
  adReviewList(query) {
    return request({
      url: '/' + this.uri + '/adreview',
      method: 'get',
      params: query,
    });
  }

  adtagaList(query) {
    return request({
      url: '/' + this.uri + '/ad/list',
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

  removeBlack(campaign_id, channel_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/channel/' + channel_id + '/removeblack',
      method: 'post',
    });
  }
  joinBlack(campaign_id, channel_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/channel/' + channel_id + '/joinblack',
      method: 'post',
    });
  }
  removewhite(campaign_id, channel_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/channel/' + channel_id + '/removewhite',
      method: 'post',
    });
  }
  joinwhite(campaign_id, channel_id){
    return request({
      url: '/' + this.uri + '/' + campaign_id + '/channel/' + channel_id + '/joinwhite',
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
