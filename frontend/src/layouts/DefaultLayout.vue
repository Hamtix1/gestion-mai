<template>
  <div class="layout-default">
    <!-- Header / Navbar -->
    <header class="header">
      <div class="container">
        <div class="header-content">
          <div class="logo">
            <router-link to="/dashboard">
              <h1>{{ appName }}</h1>
            </router-link>
          </div>

          <nav class="nav-menu">
            <router-link to="/dashboard" class="nav-link">
              <span>📊</span> Dashboard
            </router-link>

            <router-link to="/admin/campaigns" class="nav-link">
              <span>📅</span> Campañas
            </router-link>

            <router-link v-if="authStore.isSeller || authStore.isAdmin" to="/admin/sterilizations" class="nav-link">
              <span>🐾</span> Esterilizaciones
            </router-link>

            <router-link v-if="authStore.isSeller || authStore.isAdmin" to="/admin/payments" class="nav-link">
              <span>💰</span> Pagos
            </router-link>

            <router-link v-if="authStore.isAdmin" to="/admin/financial" class="nav-link">
              <span>📈</span> Finanzas
            </router-link>

            <router-link v-if="authStore.isAdmin" to="/admin/users" class="nav-link">
              <span>👥</span> Usuarios
            </router-link>
          </nav>

          <div class="user-menu">
            <div class="dropdown">
              <button class="user-button" @click="toggleUserMenu">
                <span class="user-avatar">{{ userInitials }}</span>
                <span class="user-name">{{ authStore.user?.name }}</span>
                <span class="dropdown-arrow">▼</span>
              </button>

              <div v-if="showUserMenu" class="dropdown-menu">
                <router-link to="/profile" class="dropdown-item" @click="showUserMenu = false">
                  <span>👤</span> Mi Perfil
                </router-link>
                <div class="dropdown-divider"></div>
                <button class="dropdown-item" @click="handleLogout">
                  <span>🚪</span> Cerrar Sesión
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <div class="container">
        <router-view />
      </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <p>&copy; {{ currentYear }} {{ appName }}. Todos los derechos reservados.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const appName = import.meta.env.VITE_APP_NAME || 'Gestión MAI'
const currentYear = new Date().getFullYear()
const showUserMenu = ref(false)

/**
 * Obtener iniciales del usuario
 */
const userInitials = computed(() => {
  const name = authStore.user?.name || 'U'
  return name
    .split(' ')
    .map((word) => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

/**
 * Toggle del menú de usuario
 */
const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

/**
 * Cerrar menú al hacer click fuera
 */
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.dropdown')) {
    showUserMenu.value = false
  }
}

/**
 * Cerrar sesión
 */
const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push({ name: 'login' })
  } catch (error) {
    console.error('Error al cerrar sesión:', error)
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.layout-default {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 0;
}

.logo h1 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.logo a {
  text-decoration: none;
  color: inherit;
}

/* Navigation */
.nav-menu {
  display: flex;
  gap: 0.5rem;
  flex: 1;
  justify-content: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.nav-link:hover,
.nav-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.2);
}

/* User Menu */
.user-menu {
  position: relative;
}

.dropdown {
  position: relative;
}

.user-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 0.95rem;
}

.user-button:hover {
  background: rgba(255, 255, 255, 0.3);
}

.user-avatar {
  background: rgba(255, 255, 255, 0.3);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.85rem;
}

.dropdown-arrow {
  font-size: 0.7rem;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  overflow: hidden;
  z-index: 1000;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #333;
  text-decoration: none;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 0.95rem;
}

.dropdown-item:hover {
  background-color: #f5f5f5;
}

.dropdown-divider {
  height: 1px;
  background-color: #e0e0e0;
  margin: 0.25rem 0;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 2rem 0;
  background-color: #f5f7fa;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Footer */
.footer {
  background-color: #2d3748;
  color: white;
  padding: 1.5rem 0;
  text-align: center;
}

.footer p {
  margin: 0;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
  }

  .nav-menu {
    flex-wrap: wrap;
    justify-content: center;
  }

  .nav-link {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
  }

  .user-name {
    display: none;
  }
}
</style>
