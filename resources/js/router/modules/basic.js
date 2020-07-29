/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const basicRoutes = {
  path: '/basic',
  component: Layout,
  redirect: '/basic/channel',
  name: 'Basic',
  meta: {
    title: 'basic',
    icon: 'admin',
    permissions: ['basic.manage'],
  },
  children: [

    {
      path: 'advertiser',
      component: () => import('@/views/advertiser/List'),
      name: 'AdvertiserList',
      meta: { title: 'Advertiser', icon: 'peoples', permissions: ['advertise.manage'] },
    },
    {
      path: 'log',
      component: () => import('@/views/users/Operation'),
      name: 'LogList',
      meta: { title: 'Log', icon: 'component', permissions: ['basic.manage'] },
    },
  ],
};

export default basicRoutes;
