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
      path: 'app/tag',
      component: () => import('@/views/app/Tag'),
      name: 'AppTag',
      meta: { title: 'App Tag', icon: 'component', permissions: ['basic.manage'] },
    },
    {
      path: 'ad/tag',
      component: () => import('@/views/ad/Tag'),
      name: 'AdTag',
      meta: { title: 'Ad Tag', icon: 'component', permissions: ['basic.manage'] },
    },
    // {
    //   path: 'channel/tag',
    //   component: () => import('@/views/channel/Tag'),
    //   name: 'ChannelTag',
    //   meta: { title: 'Channel Tag', icon: 'component', permissions: ['basic.manage'] },
    // },
    {
      path: 'log',
      component: () => import('@/views/users/Oplog'),
      name: 'LogList',
      meta: { title: 'Admin Log', icon: 'component', permissions: ['system.user.oplog'] },
    },
    {
      path: 'account/oplog',
      component: () => import('@/views/account/Oplog'),
      name: 'OpLog',
      meta: { title: 'Account Log', icon: 'tree-table', permissions: ['advertise.account.oplog'] },
    },
  ],
};

export default basicRoutes;
