<template>
  <div class="dashboard-view">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p>Bienvenido, {{ authStore.user?.name }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <h3>Campañas Activas</h3>
          <p class="stat-number">{{ stats.activeCampaigns }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🐾</div>
        <div class="stat-content">
          <h3>Esterilizaciones</h3>
          <p class="stat-number">{{ stats.totalSterilizations }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <h3>Pagos Pendientes</h3>
          <p class="stat-number">{{ stats.pendingPayments }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <h3>Usuarios</h3>
          <p class="stat-number">{{ stats.totalUsers }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="section">
      <h2>Acciones Rápidas</h2>
      <div class="actions-grid">
        <router-link to="/admin/campaigns/create" class="action-card" v-if="authStore.isAdmin">
          <span class="action-icon">➕</span>
          <h3>Nueva Campaña</h3>
          <p>Crear una nueva campaña de esterilización</p>
        </router-link>

        <router-link to="/admin/sterilizations/create" class="action-card" v-if="authStore.isSeller || authStore.isAdmin">
          <span class="action-icon">🐾</span>
          <h3>Registrar Esterilización</h3>
          <p>Registrar una nueva esterilización</p>
        </router-link>

        <router-link to="/admin/payments" class="action-card" v-if="authStore.isSeller || authStore.isAdmin">
          <span class="action-icon">💵</span>
          <h3>Registrar Pago</h3>
          <p>Registrar un nuevo pago</p>
        </router-link>

        <router-link to="/check-status" class="action-card">
          <span class="action-icon">🔍</span>
          <h3>Consultar Estado</h3>
          <p>Ver estado de esterilizaciones</p>
        </router-link>
      </div>
    </div>

    <!-- Recent Activity (placeholder) -->
    <div class="section">
      <h2>Actividad Reciente</h2>
      <div class="card">
        <div class="card-body">
          <p class="text-muted">Próximamente: Listado de actividad reciente del sistema</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const stats = ref({
  activeCampaigns: 0,
  totalSterilizations: 0,
  pendingPayments: 0,
  totalUsers: 0,
})

/**
 * Cargar estadísticas del dashboard
 */
const loadStats = async () => {
  // TODO: Implementar llamada a API para obtener estadísticas
  // Por ahora, datos de ejemplo
  stats.value = {
    activeCampaigns: 3,
    totalSterilizations: 125,
    pendingPayments: 12,
    totalUsers: 8,
  }
}

onMounted(() => {
  loadStats()
})
</script>

<style scoped>
.dashboard-view {
  padding: 2rem 0;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 0.5rem;
}

.page-header p {
  font-size: 1.1rem;
  color: #666;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1.5rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 3rem;
}

.stat-content h3 {
  font-size: 0.95rem;
  font-weight: 500;
  color: #666;
  margin-bottom: 0.5rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #667eea;
  margin: 0;
}

/* Sections */
.section {
  margin-bottom: 3rem;
}

.section h2 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 1.5rem;
}

/* Actions Grid */
.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.action-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  color: inherit;
  transition: all 0.2s;
  text-align: center;
}

.action-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.action-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.action-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.action-card p {
  color: #666;
  font-size: 0.95rem;
  margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .stats-grid,
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
