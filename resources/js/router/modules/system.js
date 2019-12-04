/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const systemRoutes = {
  path: '/system',
  component: Layout,
  redirect: '/system/users',
  name: 'System',
  meta: {
    title: 'system',
    icon: 'admin',
    permissions: ['system.manage'],
  },
  children: [
    /** User managements */
    {
      path: 'users/edit/:id(\\d+)',
      component: () => import('@/views/users/Profile'),
      name: 'UserProfile',
      meta: { title: 'userProfile', noCache: true, permissions: ['system.user.edit'] },
      hidden: true,
    },
    {
      path: 'users',
      component: () => import('@/views/users/List'),
      name: 'UserList',
      meta: { title: 'users', icon: 'user', permissions: ['system.user'] },
    },
    /** Role and permission */
    {
      path: 'roles',
      component: () => import('@/views/role-permission/List'),
      name: 'RoleList',
      meta: { title: 'rolePermission', icon: 'role', permissions: ['system.user.permission'] },
    },
  ],
};

export default systemRoutes;
