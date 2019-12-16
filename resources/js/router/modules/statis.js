/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const statisRoutes = {
  path: '/statis',
  component: Layout,
  redirect: '/statis/total',
  name: 'Statis',
  meta: {
    title: 'Statis',
    icon: 'admin',
    permissions: ['advertise.statis'],
  },
  children: [
    /** Campaign managements */
    {
      path: 'total',
      component: () => import('@/views/statis/List'),
      name: 'Total',
      meta: { title: 'Total', icon: 'user', permissions: ['advertise.statis'] },
    },
  ],
};

export default statisRoutes;
