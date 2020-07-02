/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const acquisitionRoutes = {
  path: '/audience',
  component: Layout,
  redirect: '/audience/upload',
  name: 'audience',
  meta: {
    title: 'audience',
    icon: 'admin',
    permissions: ['audience.manage'],
  },
  children: [
    {
      path: 'upload',
      component: () => import('@/views/audience/Upload'),
      name: 'AudienceUpload',
      meta: { title: 'Audience Upload', icon: 'tab', permissions: ['audience.manage'] },
    },
    /** App managements */
    {
      path: 'app',
      component: () => import('@/views/app/List'),
      name: 'AppList',
      meta: { title: 'App', icon: 'component', permissions: ['advertise.app'] },
    },
    {
      path: 'app/:app_id(\\d+)/channel',
      component: () => import('@/views/app/channel/List'),
      name: 'AppChannelList',
      meta: { title: 'Channel By App', icon: 'user', permissions: ['advertise.app'] },
      hidden: true,
    },
  ],
};

export default acquisitionRoutes;
