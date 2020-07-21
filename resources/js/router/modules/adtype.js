/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const adtypeRoutes = {
  path: '/placement',
  component: Layout,
  redirect: '/placement/channel',
  name: 'placement',
  meta: {
    title: 'Placement',
    icon: 'admin',
    permissions: ['placement.manage'],
  },
  children: [
    {
      path: 'channel',
      component: () => import('@/views/adtype/List'),
      name: 'ChannelList',
      meta: { title: 'Channel', icon: 'tree', permissions: ['placement.manage'] },
    },
    {
      path: 'app',
      component: () => import('@/views/adtype/AppList'),
      name: 'AppList',
      meta: { title: 'App', icon: 'component', permissions: ['placement.manage'] },
    },
  ],
};

export default adtypeRoutes;
