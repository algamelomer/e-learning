import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../components/auth/Login.vue';
import Register from '../components/auth/Register.vue';
import Dashboard from '../components/Dashboard.vue';
import UserManagement from '../components/admin/UserManagement.vue';
import RoleManagement from '../components/admin/RoleManagement.vue';

const routes = [{
        path: '/',
        redirect: '/login'
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/admin/users',
        name: 'user-management',
        component: UserManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/roles',
        name: 'role-management',
        component: RoleManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async(to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;
    const isAdmin = authStore.isAdmin;

    // Routes that require authentication
    if (to.meta.requiresAuth) {
        if (!isAuthenticated) {
            next({ name: 'login' });
            return;
        }

        // Check for admin routes
        if (to.meta.requiresAdmin && !isAdmin) {
            next({ name: 'dashboard' });
            return;
        }
    }

    // Guest routes (accessible only when not authenticated)
    if (to.meta.guest) {
        if (isAuthenticated) {
            next({ name: 'dashboard' });
            return;
        }
    }

    next();
});

export default router;