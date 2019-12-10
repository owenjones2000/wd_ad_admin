/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const basicRoutes = {
  path: '/acquisition',
  component: Layout,
  redirect: '/acquisition/campaign',
  name: 'Acquisition',
  meta: {
    title: 'acquisition',
    icon: 'admin',
    permissions: ['advertise.manage'],
  },
  children: [
    /** Campaign managements */
    {
      path: 'campaign',
      component: () => import('@/views/campaign/List'),
      name: 'Campaign',
      meta: { title: 'campaign', icon: 'user', permissions: ['advertise.campaign'] },
    },
    /** Ad managements */
    {
      path: 'campaign/ad',
      component: () => import('@/views/campaign/ad/List'),
      name: 'Ad',
      meta: { title: 'ad', icon: 'user', permissions: ['advertise.campaign.ad.list'] },
    },
  ],
};

export default basicRoutes;
