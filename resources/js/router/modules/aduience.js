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
    {
      path: 'upload/app',
      component: () => import('@/views/audience/UploadApp'),
      name: 'AudienceUploadApp',
      meta: { title: 'Audience Upload App', icon: 'tab', permissions: ['audience.manage'] },
    },
    {
      path: 'tag',
      component: () => import('@/views/audience/Taglist'),
      name: 'AudienceTag',
      meta: { title: 'Tags', icon: 'user', permissions: ['audience.manage'] },
    },
  ],
};

export default acquisitionRoutes;
