/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const basicRoutes = {
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
    /** Campaign managements */
    {
      path: 'campaign',
      component: () => import('@/views/campaign/List'),
      name: 'Campaign',
      meta: { title: 'Campaign', icon: 'user', permissions: ['advertise.campaign'] },
    },
    /** Ad managements */
    {
      path: 'campaign/:campaign_id(\\d+)/ad',
      component: () => import('@/views/campaign/ad/List'),
      name: 'Ad',
      meta: { title: 'Ad', icon: 'user', permissions: ['advertise.campaign.ad'] },
      hidden: true,
    },
  ],
};

export default basicRoutes;
