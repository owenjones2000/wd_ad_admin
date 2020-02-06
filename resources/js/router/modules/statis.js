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
    /** Statics */
    {
      path: 'total',
      component: () => import('@/views/statis/List'),
      name: 'AdvertiseStatis',
      meta: { title: 'Total Statis', icon: 'user', permissions: ['advertise.statis'] },
    },
    {
      path: 'device',
      component: () => import('@/views/statis/Device'),
      name: 'DeviceStatis',
      meta: { title: 'Device Statis', icon: 'user', permissions: ['advertise.statis'] },
    },
    {
      path: 'device/channel',
      component: () => import('@/views/statis/DeviceByChannel'),
      name: 'DeviceStatisByChannel',
      meta: { title: 'Device Statis By Channel', icon: 'user', permissions: ['advertise.statis'] },
      hidden: true,
    },
    {
      path: 'device/app',
      component: () => import('@/views/statis/DeviceByApp'),
      name: 'DeviceStatisByApp',
      meta: { title: 'Device Statis By App', icon: 'user', permissions: ['advertise.statis'] },
      hidden: true,
    },
  ],
};

export default statisRoutes;
