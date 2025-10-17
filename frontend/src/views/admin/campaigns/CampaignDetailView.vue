<template>
  <div class="campaign-detail-view">
    <!-- Loading -->
    <div v-if="campaignStore.loading" class="loading-container">
      <div class="spinner"></div>
      <p>Cargando campaña...</p>
    </div>

    <!-- Error -->
    <div v-else-if="campaignStore.error" class="alert alert-error">
      {{ campaignStore.error }}
      <router-link to="/admin/campaigns" class="btn btn-secondary mt-3">Volver</router-link>
    </div>

    <!-- Content -->
    <div v-else-if="campaign" class="campaign-content">
      <!-- Header -->
      <div class="page-header">
        <div>
          <router-link to="/admin/campaigns" class="back-link">← Volver</router-link>
          <div class="header-title">
            <h1>{{ campaign.name }}</h1>
            <span :class="['badge', 'badge-large', getStatusClass(campaign.status)]">
              {{ getStatusLabel(campaign.status) }}
            </span>
          </div>
        </div>
        <div class="header-actions" v-if="authStore.isAdmin">
          <router-link :to="`/admin/campaigns/${campaign.id}/edit`" class="btn btn-primary">
            ✏️ Editar
          </router-link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">🐾</div>
          <div class="stat-content">
            <h3>Esterilizaciones</h3>
            <p class="stat-number">{{ stats.total_sterilizations || 0 }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <h3>Recaudado</h3>
            <p class="stat-number">${{ formatCurrency(stats.total_collected || 0) }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <h3>Pagos Completos</h3>
            <p class="stat-number">{{ stats.completed_payments || 0 }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">⏳</div>
          <div class="stat-content">
            <h3>Pendientes</h3>
            <p class="stat-number">{{ stats.pending_payments || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Campaign Info -->
      <div class="info-section">
        <div class="card">
          <div class="card-header">
            <h2>Información de la Campaña</h2>
          </div>
          <div class="card-body">
            <div class="info-grid">
              <div class="info-item">
                <label>📍 Ubicación</label>
                <p>{{ campaign.location }}</p>
              </div>

              <div class="info-item">
                <label>📅 Fecha de Inicio</label>
                <p>{{ formatDate(campaign.start_date) }}</p>
              </div>

              <div class="info-item">
                <label>📅 Fecha de Fin</label>
                <p>{{ formatDate(campaign.end_date) }}</p>
              </div>

              <div class="info-item">
                <label>🐕 Precio Perros</label>
                <p class="price">${{ formatCurrency(campaign.dog_price) }}</p>
              </div>

              <div class="info-item">
                <label>🐱 Precio Gatos</label>
                <p class="price">${{ formatCurrency(campaign.cat_price) }}</p>
              </div>

              <div class="info-item">
                <label>👁️ Visibilidad</label>
                <p>
                  <span :class="['badge', campaign.is_visible_to_public ? 'badge-success' : 'badge-danger']">
                    {{ campaign.is_visible_to_public ? 'Pública' : 'Oculta' }}
                  </span>
                </p>
              </div>

              <div class="info-item">
                <label>📊 Cupos Disponibles</label>
                <p>{{ campaign.available_slots - campaign.used_slots }} de {{ campaign.available_slots }}</p>
              </div>

              <div class="info-item">
                <label>📈 Ocupación</label>
                <div>
                  <div class="progress-bar">
                    <div
                      class="progress-fill"
                      :style="{ width: `${(campaign.used_slots / campaign.available_slots) * 100}%` }"
                    ></div>
                  </div>
                  <small class="progress-text">
                    {{ Math.round((campaign.used_slots / campaign.available_slots) * 100) }}%
                  </small>
                </div>
              </div>
            </div>

            <div v-if="campaign.description" class="description-section">
              <label>📝 Descripción</label>
              <p>{{ campaign.description }}</p>
            </div>

            <div class="meta-info">
              <small>Creado por: {{ campaign.creator?.name || 'Desconocido' }}</small>
              <small v-if="campaign.created_at">Fecha de creación: {{ formatDateTime(campaign.created_at) }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Sterilizations List -->
      <div class="sterilizations-section">
        <div class="card">
          <div class="card-header">
            <h2>Esterilizaciones de esta Campaña</h2>
            <span class="count-badge">{{ filteredSterilizations.length }} registro(s)</span>
          </div>
          <div class="card-body">
            <!-- Search Bar and Filters -->
            <div class="search-section">
              <div class="search-box">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Buscar por mascota, propietario, cédula..."
                  class="search-input"
                />
                <span class="search-icon">🔍</span>
              </div>
              
              <!-- Filters -->
              <div class="filters-row">
                <div class="filter-group">
                  <label>Tipo de Mascota:</label>
                  <select v-model="filterPetType" class="filter-select">
                    <option value="">Todos</option>
                    <option value="dog">🐕 Perros</option>
                    <option value="cat">🐱 Gatos</option>
                  </select>
                </div>

                <div class="filter-group">
                  <label>Estado de Pago:</label>
                  <select v-model="filterPaymentStatus" class="filter-select">
                    <option value="">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="partial">Parcial</option>
                    <option value="completed">Pagado</option>
                  </select>
                </div>

                <div class="filter-group">
                  <label>Estado:</label>
                  <select v-model="filterStatus" class="filter-select">
                    <option value="">Todos</option>
                    <option value="scheduled">Programada</option>
                    <option value="in_progress">En Progreso</option>
                    <option value="completed">Completada</option>
                    <option value="cancelled">Cancelada</option>
                  </select>
                </div>

                <button 
                  @click="clearFilters" 
                  class="btn-clear-filters"
                  v-if="hasActiveFilters"
                  title="Limpiar filtros"
                >
                  ✖️ Limpiar
                </button>
              </div>
            </div>

            <!-- Loading -->
            <div v-if="loadingSterilizations" class="loading-state">
              <div class="spinner-small"></div>
              <p>Cargando esterilizaciones...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="sterilizations.length === 0" class="empty-state">
              <div class="empty-icon">🐾</div>
              <h3>No hay esterilizaciones registradas</h3>
              <p>Esta campaña aún no tiene esterilizaciones asociadas</p>
              <router-link
                to="/admin/sterilizations/create"
                class="btn btn-primary"
                v-if="authStore.isSeller || authStore.isAdmin"
              >
                Registrar Primera Esterilización
              </router-link>
            </div>

            <!-- No Results -->
            <div v-else-if="filteredSterilizations.length === 0" class="empty-state">
              <div class="empty-icon">🔍</div>
              <h3>No se encontraron resultados</h3>
              <p>Intenta con otros términos de búsqueda</p>
            </div>

            <!-- Sterilizations Table -->
            <div v-else class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Mascota</th>
                    <th>Propietario</th>
                    <th>Cédula</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Vendedor</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="sterilization in filteredSterilizations" :key="sterilization.id">
                    <td>
                      <span class="id-badge">#{{ sterilization.id }}</span>
                    </td>
                    <td>
                      <div class="pet-info">
                        <span class="pet-name">{{ sterilization.pet_name }}</span>
                        <span class="pet-breed">{{ sterilization.pet_breed || 'N/A' }}</span>
                      </div>
                    </td>
                    <td>{{ sterilization.owner_full_name }}</td>
                    <td>{{ sterilization.owner_id_number }}</td>
                    <td>
                      <span class="pet-type-badge" :class="`type-${sterilization.pet_type}`">
                        {{ sterilization.pet_type === 'dog' ? '🐕 Perro' : '🐱 Gato' }}
                      </span>
                    </td>
                    <td>
                      <span :class="['badge', `badge-${sterilization.status}`]">
                        {{ getStatusText(sterilization.status) }}
                      </span>
                    </td>
                    <td>
                      <span v-if="typeof sterilization.registered_by === 'object' && sterilization.registered_by" class="user-badge">
                        👤 {{ (sterilization.registered_by as any).name }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td>
                      <span :class="['badge', `badge-${sterilization.payment_status}`]">
                        {{ getPaymentStatusText(sterilization.payment_status) }}
                      </span>
                    </td>
                    <td>
                      <div class="amount-info">
                        <span class="amount">${{ Number(sterilization.total_price).toFixed(2) }}</span>
                        <span v-if="sterilization.payment_status === 'partial'" class="paid-amount">
                          Pagado: ${{ Number(sterilization.total_paid).toFixed(2) }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <router-link
                          :to="{ name: 'admin-sterilizations-detail', params: { id: sterilization.id } }"
                          class="btn-icon"
                          title="Ver detalles"
                        >
                          👁️
                        </router-link>
                        <router-link
                          :to="{ name: 'admin-sterilizations-edit', params: { id: sterilization.id } }"
                          class="btn-icon"
                          title="Editar"
                          v-if="authStore.isSeller || authStore.isAdmin"
                        >
                          ✏️
                        </router-link>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="actions-section">
        <div class="card">
          <div class="card-header">
            <h2>Acciones Rápidas</h2>
          </div>
          <div class="card-body">
            <div class="actions-grid">
              <router-link
                to="/admin/sterilizations/create"
                class="action-card"
                v-if="authStore.isSeller || authStore.isAdmin"
              >
                <span class="action-icon">🐾</span>
                <h3>Nueva Esterilización</h3>
                <p>Registrar una esterilización en esta campaña</p>
              </router-link>

              <router-link to="/admin/sterilizations" class="action-card">
                <span class="action-icon">📋</span>
                <h3>Ver Esterilizaciones</h3>
                <p>Lista de todas las esterilizaciones</p>
              </router-link>

              <router-link to="/admin/payments" class="action-card" v-if="authStore.isSeller || authStore.isAdmin">
                <span class="action-icon">💰</span>
                <h3>Ver Pagos</h3>
                <p>Historial de pagos de esta campaña</p>
              </router-link>

              <router-link :to="`/campaigns/${campaign.id}`" class="action-card" target="_blank">
                <span class="action-icon">🌐</span>
                <h3>Vista Pública</h3>
                <p>Ver cómo se ve para los visitantes</p>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'
import { useSterilizationStore } from '@/stores/sterilization'
import { useAuthStore } from '@/stores/auth'
import type { Campaign, Sterilization } from '@/types'

const route = useRoute()
const campaignStore = useCampaignStore()
const sterilizationStore = useSterilizationStore()
const authStore = useAuthStore()

const campaign = computed(() => campaignStore.currentCampaign)
const stats = ref<Record<string, number>>({})

// Estado para esterilizaciones
const sterilizations = ref<Sterilization[]>([])
const loadingSterilizations = ref(false)
const searchQuery = ref('')

// Filtros
const filterPetType = ref('')
const filterPaymentStatus = ref('')
const filterStatus = ref('')

// Verificar si hay filtros activos
const hasActiveFilters = computed(() => {
  return filterPetType.value !== '' || 
         filterPaymentStatus.value !== '' || 
         filterStatus.value !== ''
})

// Limpiar filtros
const clearFilters = () => {
  filterPetType.value = ''
  filterPaymentStatus.value = ''
  filterStatus.value = ''
  searchQuery.value = ''
}

// Filtrar esterilizaciones por búsqueda y filtros
const filteredSterilizations = computed(() => {
  let filtered = sterilizations.value

  // Filtrar por búsqueda de texto
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter((s) =>
      s.pet_name.toLowerCase().includes(query) ||
      s.owner_full_name.toLowerCase().includes(query) ||
      s.owner_id_number.toLowerCase().includes(query) ||
      (s.pet_breed && s.pet_breed.toLowerCase().includes(query))
    )
  }

  // Filtrar por tipo de mascota
  if (filterPetType.value) {
    filtered = filtered.filter((s) => s.pet_type === filterPetType.value)
  }

  // Filtrar por estado de pago
  if (filterPaymentStatus.value) {
    filtered = filtered.filter((s) => s.payment_status === filterPaymentStatus.value)
  }

  // Filtrar por estado
  if (filterStatus.value) {
    filtered = filtered.filter((s) => s.status === filterStatus.value)
  }

  return filtered
})

/**
 * Cargar campaña
 */
const loadCampaign = async () => {
  const id = Number(route.params.id)
  if (id) {
    try {
      const response = await campaignStore.fetchCampaign(id)
      stats.value = response.stats || {}
    } catch (error) {
      console.error('Error al cargar campaña:', error)
    }
  }
}

/**
 * Cargar esterilizaciones de la campaña
 */
const loadSterilizations = async () => {
  const id = Number(route.params.id)
  if (!id) return

  loadingSterilizations.value = true
  
  try {
    const response = await sterilizationStore.fetchSterilizations({
      campaign_id: id,
      per_page: 1000 // Traer todas las esterilizaciones de la campaña
    })
    sterilizations.value = response.data
  } catch (error) {
    console.error('Error al cargar esterilizaciones:', error)
  } finally {
    loadingSterilizations.value = false
  }
}

/**
 * Obtener clase CSS según estado
 */
const getStatusClass = (status: Campaign['status']) => {
  const classes = {
    planned: 'badge-info',
    active: 'badge-success',
    completed: 'badge-warning',
    cancelled: 'badge-danger',
  }
  return classes[status] || 'badge-info'
}

/**
 * Obtener etiqueta de estado
 */
const getStatusLabel = (status: Campaign['status']) => {
  const labels = {
    planned: 'Planeada',
    active: 'Activa',
    completed: 'Completada',
    cancelled: 'Cancelada',
  }
  return labels[status] || status
}

/**
 * Formatear fecha
 */
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-CO', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

/**
 * Formatear fecha y hora
 */
const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Obtener texto del estado de esterilización
 */
const getStatusText = (status: string): string => {
  const statusMap: Record<string, string> = {
    scheduled: 'Programada',
    in_progress: 'En Progreso',
    completed: 'Completada',
    cancelled: 'Cancelada'
  }
  return statusMap[status] || status
}

/**
 * Obtener texto del estado de pago
 */
const getPaymentStatusText = (status: string): string => {
  const statusMap: Record<string, string> = {
    pending: 'Pendiente',
    partial: 'Parcial',
    completed: 'Pagado'
  }
  return statusMap[status] || status
}

/**
 * Formatear moneda
 */
const formatCurrency = (amount: number | string) => {
  return Number(amount).toLocaleString('es-CO')
}

onMounted(() => {
  loadCampaign()
  loadSterilizations()
})
</script>

<style scoped>
.campaign-detail-view {
  padding: 2rem 0;
}

.loading-container {
  text-align: center;
  padding: 4rem 0;
}

.loading-container .spinner {
  margin: 0 auto 1rem;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.back-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: inline-block;
  transition: color 0.2s;
}

.back-link:hover {
  color: #764ba2;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.header-title h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

.badge-large {
  padding: 0.5rem 1rem;
  font-size: 1rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1.5rem;
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

/* Info Section */
.info-section {
  margin-bottom: 2rem;
}

.card-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.info-item label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: #666;
  margin-bottom: 0.5rem;
}

.info-item p {
  font-size: 1.05rem;
  color: #333;
  margin: 0;
}

.price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #667eea;
}

.progress-bar {
  height: 10px;
  background-color: #e0e0e0;
  border-radius: 5px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s;
}

.progress-text {
  color: #666;
  font-size: 0.9rem;
}

.description-section {
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
  margin-top: 2rem;
}

.description-section label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #666;
  margin-bottom: 0.75rem;
}

.description-section p {
  color: #555;
  line-height: 1.8;
  margin: 0;
}

.meta-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
  margin-top: 2rem;
}

.meta-info small {
  color: #999;
  font-size: 0.85rem;
}

/* Actions */
.actions-section {
  margin-bottom: 2rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.action-card {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 2rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s;
  text-align: center;
}

.action-card:hover {
  border-color: #667eea;
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
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

/* Sterilizations Section */
.sterilizations-section {
  margin-top: 2rem;
}

.count-badge {
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-section {
  margin-bottom: 1.5rem;
}

.search-box {
  position: relative;
  max-width: 500px;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

/* Estilos para filtros */
.filters-row {
  display: flex;
  gap: 1rem;
  align-items: end;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 180px;
}

.filter-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.filter-select {
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:hover {
  border-color: #9ca3af;
}

.filter-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-clear-filters {
  padding: 0.625rem 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  align-self: end;
}

.btn-clear-filters:hover {
  background: #dc2626;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
}

.btn-clear-filters:active {
  transform: translateY(0);
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #f8fafc;
  padding: 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #64748b;
  font-size: 0.875rem;
  border-bottom: 2px solid #e2e8f0;
}

.data-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  color: #475569;
}

.data-table tbody tr:hover {
  background: #f8fafc;
}

.id-badge {
  background: #f1f5f9;
  color: #475569;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  font-family: monospace;
}

.pet-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.pet-name {
  font-weight: 600;
  color: #1e293b;
}

.pet-breed {
  font-size: 0.875rem;
  color: #64748b;
}

.pet-type-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
}

.pet-type-badge.type-dog {
  background: #dbeafe;
  color: #1e40af;
}

.pet-type-badge.type-cat {
  background: #fce7f3;
  color: #9f1239;
}

.user-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #f3f4f6;
  color: #374151;
  font-size: 0.875rem;
  border-radius: 6px;
  font-weight: 500;
}

.text-muted {
  color: #9ca3af;
  font-size: 0.875rem;
}

.amount-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.amount {
  font-weight: 600;
  color: #1e293b;
}

.paid-amount {
  font-size: 0.875rem;
  color: #059669;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: #f1f5f9;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-icon:hover {
  background: #e2e8f0;
  transform: scale(1.05);
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 2rem;
  color: #64748b;
}

.spinner-small {
  width: 24px;
  height: 24px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.25rem;
  color: #475569;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

/* Badge Variants */
.badge-scheduled {
  background: #dbeafe;
  color: #1e40af;
}

.badge-in_progress {
  background: #fef3c7;
  color: #92400e;
}

.badge-completed {
  background: #d1fae5;
  color: #065f46;
}

.badge-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.badge-pending {
  background: #fee2e2;
  color: #991b1b;
}

.badge-partial {
  background: #fef3c7;
  color: #92400e;
}

/* Responsive */
@media (max-width: 768px) {
  .header-title h1 {
    font-size: 2rem;
  }

  .stats-grid,
  .info-grid,
  .actions-grid {
    grid-template-columns: 1fr;
  }

  .page-header {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .btn {
    flex: 1;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .search-box {
    max-width: 100%;
  }

  .data-table {
    font-size: 0.875rem;
  }

  .data-table th,
  .data-table td {
    padding: 0.5rem;
  }
}
</style>
