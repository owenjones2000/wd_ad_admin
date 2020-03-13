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
      meta: { title: 'Account', icon: 'user', permissions: ['advertise.account'] },
    },
    /** Bill managements */
    {
      path: 'bill',
      component: () => import('@/views/bill/List'),
      name: 'BillList',
      meta: { title: 'Bill', icon: 'money', permissions: ['advertise.bill'] },
    },
    /** App managements */
    {
      path: 'app',
      component: () => import('@/views/app/List'),
      name: 'AppList',
      meta: { title: 'App', icon: 'user', permissions: ['advertise.app'] },
    },
    /** Campaign managements */
    {
      path: 'campaign',
      component: () => import('@/views/campaign/List'),
      name: 'CampaignList',
      meta: { title: 'Campaign', icon: 'user', permissions: ['advertise.campaign'] },
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
      meta: { title: 'Channel', icon: 'user', permissions: ['advertise.channel'] },
    },
    {
      path: 'channel/:channel_id(\\d+)/app',
      component: () => import('@/views/channel/app/List'),
      name: 'ChannelAppList',
      meta: { title: 'App By Channel', icon: 'user', permissions: ['advertise.campaign'] },
      hidden: true,
    },
  ],
};

export default acquisitionRoutes;
