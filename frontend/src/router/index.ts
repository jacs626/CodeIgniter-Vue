import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/productos',
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/RegisterView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/productos',
    name: 'productos',
    component: () => import('../views/ProductosView.vue'),
  },
  {
    path: '/productos/:id',
    name: 'producto-detalle',
    component: () => import('../views/ProductoDetalleView.vue'),
    props: true,
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('../views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated && authStore.token) {
    await authStore.restoreSession()
  }

  const requiresAuth = to.meta.requiresAuth === true

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (!requiresAuth && authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    next('/productos')
  } else {
    next()
  }
})

export default router