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
    permissions: ['acquisition.manage'],
  },
  children: [
    /** Campaign managements */
    {
      path: 'campaign',
      component: () => import('@/views/campaign/List'),
      name: 'Campaign',
      meta: { title: 'campaign', icon: 'user', permissions: ['basic.campaign'] },
    },
    /** Country managements */
    {
      path: 'campaign',
      component: () => import('@/views/campaign/List'),
      name: 'Country',
      meta: { title: 'country', icon: 'user', permissions: ['basic.campaign.list'] },
    },
  ],
};

export default basicRoutes;
