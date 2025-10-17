import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

/**
 * Rutas de la aplicación
 */
const routes: RouteRecordRaw[] = [
  // =====================
  // RUTAS PÚBLICAS
  // =====================
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/public/HomeView.vue'),
    meta: { requiresAuth: false, layout: 'public' },
  },
  {
    path: '/campaigns',
    name: 'public-campaigns',
    component: () => import('@/views/public/CampaignsView.vue'),
    meta: { requiresAuth: false, layout: 'public' },
  },
  {
    path: '/campaigns/:id',
    name: 'public-campaign-detail',
    component: () => import('@/views/public/CampaignDetailView.vue'),
    meta: { requiresAuth: false, layout: 'public' },
  },
  {
    path: '/check-status',
    name: 'check-status',
    component: () => import('@/views/public/CheckStatusView.vue'),
    meta: { requiresAuth: false, layout: 'public' },
  },

  // =====================
  // RUTAS DE AUTENTICACIÓN
  // =====================
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { requiresAuth: false, hideForAuth: true, layout: 'auth' },
  },

  // =====================
  // RUTAS PROTEGIDAS (REQUIEREN AUTENTICACIÓN)
  // =====================
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/admin/DashboardView.vue'),
    meta: { requiresAuth: true, layout: 'default' },
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/admin/ProfileView.vue'),
    meta: { requiresAuth: true, layout: 'default' },
  },

  // =====================
  // GESTIÓN DE CAMPAÑAS
  // =====================
  {
    path: '/admin/campaigns',
    name: 'admin-campaigns',
    component: () => import('@/views/admin/campaigns/CampaignListView.vue'),
    meta: { requiresAuth: true, layout: 'default' },
  },
  {
    path: '/admin/campaigns/create',
    name: 'admin-campaigns-create',
    component: () => import('@/views/admin/campaigns/CampaignFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/campaigns/:id/edit',
    name: 'admin-campaigns-edit',
    component: () => import('@/views/admin/campaigns/CampaignFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/campaigns/:id',
    name: 'admin-campaigns-detail',
    component: () => import('@/views/admin/campaigns/CampaignDetailView.vue'),
    meta: { requiresAuth: true, layout: 'default' },
  },

  // =====================
  // GESTIÓN DE ESTERILIZACIONES
  // =====================
  {
    path: '/admin/sterilizations',
    name: 'admin-sterilizations',
    component: () => import('@/views/admin/sterilizations/SterilizationListView.vue'),
    meta: { requiresAuth: true, requiresSeller: true, layout: 'default' },
  },
  {
    path: '/admin/sterilizations/create',
    name: 'admin-sterilizations-create',
    component: () => import('@/views/admin/sterilizations/SterilizationFormView.vue'),
    meta: { requiresAuth: true, requiresSeller: true, layout: 'default' },
  },
  {
    path: '/admin/sterilizations/:id/edit',
    name: 'admin-sterilizations-edit',
    component: () => import('@/views/admin/sterilizations/SterilizationFormView.vue'),
    meta: { requiresAuth: true, requiresSeller: true, layout: 'default' },
  },
  {
    path: '/admin/sterilizations/:id',
    name: 'admin-sterilizations-detail',
    component: () => import('@/views/admin/sterilizations/SterilizationDetailView.vue'),
    meta: { requiresAuth: true, requiresSeller: true, layout: 'default' },
  },

  // =====================
  // GESTIÓN DE PAGOS
  // =====================
  {
    path: '/admin/payments',
    name: 'admin-payments',
    component: () => import('@/views/admin/payments/PaymentListView.vue'),
    meta: { requiresAuth: true, requiresSeller: true, layout: 'default' },
  },

  // =====================
  // GESTIÓN FINANCIERA
  // =====================
  {
    path: '/admin/financial',
    name: 'admin-financial',
    component: () => import('@/views/admin/financial/FinancialView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/financial/incomes',
    name: 'admin-incomes',
    component: () => import('@/views/admin/financial/IncomeListView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/financial/incomes/create',
    name: 'admin-incomes-create',
    component: () => import('@/views/admin/financial/IncomeFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/financial/incomes/:id/edit',
    name: 'admin-incomes-edit',
    component: () => import('@/views/admin/financial/IncomeFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/financial/expenses',
    name: 'admin-expenses',
    component: () => import('@/views/admin/financial/ExpenseListView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/financial/expenses/create',
    name: 'admin-expenses-create',
    component: () => import('@/views/admin/financial/ExpenseFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/financial/expenses/:id/edit',
    name: 'admin-expenses-edit',
    component: () => import('@/views/admin/financial/ExpenseFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },

  // =====================
  // GESTIÓN DE USUARIOS
  // =====================
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('@/views/admin/users/UserListView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/users/create',
    name: 'admin-users-create',
    component: () => import('@/views/admin/users/UserFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },
  {
    path: '/admin/users/:id/edit',
    name: 'admin-users-edit',
    component: () => import('@/views/admin/users/UserFormView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, layout: 'default' },
  },

  // =====================
  // PÁGINA 404
  // =====================
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { requiresAuth: false, layout: 'public' },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

/**
 * Guard de navegación global
 * Maneja autenticación y permisos
 */
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  // Si la ruta requiere autenticación
  if (to.meta.requiresAuth) {
    // Si no hay token, redirigir al login
    if (!authStore.token) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }

    // Si hay token pero no hay usuario, cargar usuario
    if (!authStore.user) {
      try {
        await authStore.fetchUser()
      } catch {
        // Si falla, limpiar token y redirigir al login
        authStore.logout()
        return next({ name: 'login', query: { redirect: to.fullPath } })
      }
    }

    // Verificar permisos de rol
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      return next({ name: 'dashboard' })
    }

    if (to.meta.requiresSeller && !authStore.isSeller && !authStore.isAdmin) {
      return next({ name: 'dashboard' })
    }
  }

  // Si la ruta no debe mostrarse a usuarios autenticados (ej: login)
  if (to.meta.hideForAuth && authStore.token) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router

