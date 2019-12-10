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
    /** Channel managements */
    {
      path: 'channel',
      component: () => import('@/views/channel/List'),
      name: 'Channel',
      meta: { title: 'channel', icon: 'user', permissions: ['basic.channel'] },
    },
    /** Country managements */
    // {
    //   path: 'channel',
    //   component: () => import('@/views/channel/List'),
    //   name: 'Country',
    //   meta: { title: 'country', icon: 'user', permissions: ['basic.channel.list'] },
    // },
  ],
};

export default basicRoutes;
