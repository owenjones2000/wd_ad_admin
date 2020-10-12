/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const acquisitionRoutes = {
  path: '/acquisition',
  component: Layout,
  redirect: '/acquisition/campaign',
  name: 'Acquisition',
  meta: {
    title: 'Acquisition',
    icon: 'admin',
    permissions: ['advertise.manage'],
  },
  children: [
    /** Account managements */
    {
      path: 'account',
      component: () => import('@/views/account/List'),
      name: 'AccountList',
      meta: { title: 'Account', icon: 'peoples', permissions: ['advertise.account'] },
    },
    /** Bill managements */
    {
      path: 'bill',
      component: () => import('@/views/bill/List'),
      name: 'BillList',
      meta: { title: 'Billing', icon: 'dollar', permissions: ['advertise.bill'] },
    },
    /** Advertiser managements */
    {
      path: 'advertiser',
      component: () => import('@/views/advertiser/List'),
      name: 'AdvertiserList',
      meta: { title: 'Advertiser', icon: 'peoples', permissions: ['advertise.manage'] },
    },
    /** App managements */
    {
      path: 'app',
      component: () => import('@/views/app/List'),
      name: 'AppList',
      meta: { title: 'App', icon: 'component', permissions: ['advertise.app'] },
    },
    {
      path: 'app/review',
      component: () => import('@/views/app/Review'),
      name: 'AppReviewList',
      meta: { title: 'App Review', icon: 'component', permissions: ['advertise.app.edit'] },
    },
    {
      path: 'app/tag',
      component: () => import('@/views/app/AppTag'),
      name: 'AppTagList',
      meta: { title: 'App Tag', icon: 'component', permissions: ['advertise.ad.tag'] },
    },
    {
      path: 'app/:app_id(\\d+)/ios/info',
      component: () => import('@/views/app/Ios'),
      name: 'AppIosInfo',
      meta: { title: 'App IOS Review', icon: 'component', permissions: ['advertise.app.edit'] },
      hidden: true,
    },
    {
      path: 'app/:app_id(\\d+)/channel',
      component: () => import('@/views/app/channel/List'),
      name: 'AppChannelList',
      meta: { title: 'Channel By App', icon: 'user', permissions: ['advertise.app'] },
      hidden: true,
    },
    {
      path: 'app/:app_id(\\d+)/campaign',
      component: () => import('@/views/app/campaignList'),
      name: 'AppCampaignList',
      meta: { title: 'Campaign By App', icon: 'user', permissions: ['advertise.app'] },
      hidden: true,
    },
    /** Campaign managements */
    {
      path: 'campaign',
      component: () => import('@/views/campaign/List'),
      name: 'CampaignList',
      meta: { title: 'Campaign', icon: 'tree-table', permissions: ['advertise.campaign'] },
    },
    {
      path: 'ad/review',
      component: () => import('@/views/campaign/ad/Review'),
      // component: () => import('@/views/campaign/List'),
      name: 'AdList',
      meta: { title: 'Ad Review', icon: 'user', permissions: ['advertise.campaign.ad.edit'] },
    },
    {
      path: 'ad/adtag',
      component: () => import('@/views/ad/Adtag'),
      name: 'AdTag',
      meta: { title: 'Ad Tag', icon: 'component', permissions: ['advertise.ad.tag'] },
    },
    {
      path: 'campaign/:campaign_id(\\d+)/channel',
      component: () => import('@/views/campaign/channel/List'),
      name: 'CampaignChannelList',
      meta: { title: 'Channel By Campaign', icon: 'user', permissions: ['advertise.campaign'] },
      hidden: true,
    },
    {
      path: 'campaign/:campaign_id(\\d+)/ad',
      component: () => import('@/views/campaign/ad/List'),
      name: 'CampaignAdList',
      meta: { title: 'Ad', icon: 'user', permissions: ['advertise.campaign.ad'] },
      hidden: true,
    },

    /** Channel managements */
    {
      path: 'channel',
      component: () => import('@/views/channel/List'),
      name: 'ChannelList',
      meta: { title: 'Channel', icon: 'tree', permissions: ['advertise.channel'] },
    },
    {
      path: 'channel/:channel_id(\\d+)/app',
      component: () => import('@/views/channel/app/List'),
      name: 'ChannelAppList',
      meta: { title: 'App By Channel', icon: 'user', permissions: ['advertise.channel'] },
      hidden: true,
    },
  ],
};

export default acquisitionRoutes;
