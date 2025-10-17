<template>
  <div class="sterilization-list">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Esterilizaciones</h1>
        <p class="page-description">Gestiona los registros de esterilizaciones</p>
      </div>
      <router-link 
        :to="{ name: 'admin-sterilizations-create' }" 
        class="btn btn-primary"
      >
        <i class="icon">+</i> Nueva Esterilización
      </router-link>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <div class="filters-grid">
        <!-- Búsqueda -->
        <div class="filter-group">
          <label class="filter-label">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nombre de mascota, propietario..."
            class="filter-input"
            @input="debouncedSearch"
          />
        </div>

        <!-- Campaña -->
        <div class="filter-group">
          <label class="filter-label">Campaña</label>
          <select v-model="filters.campaign_id" class="filter-select" @change="applyFilters">
            <option value="">Todas las campañas</option>
            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
              {{ campaign.name }}
            </option>
          </select>
        </div>

        <!-- Estado de pago -->
        <div class="filter-group">
          <label class="filter-label">Estado de Pago</label>
          <select v-model="filters.payment_status" class="filter-select" @change="applyFilters">
            <option value="">Todos</option>
            <option value="pending">Pendiente</option>
            <option value="partial">Parcial</option>
            <option value="paid">Pagado</option>
          </select>
        </div>

        <!-- Especies -->
        <div class="filter-group">
          <label class="filter-label">Especie</label>
          <select v-model="filters.species" class="filter-select" @change="applyFilters">
            <option value="">Todas</option>
            <option value="dog">Perro</option>
            <option value="cat">Gato</option>
          </select>
        </div>
      </div>

      <!-- Acciones de filtro -->
      <div class="filter-actions">
        <button @click="clearFilters" class="btn btn-secondary">
          <i class="icon">×</i> Limpiar Filtros
        </button>
        <span class="results-count">{{ sterilizations.length }} resultados</span>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando esterilizaciones...</p>
      </div>

      <div v-else-if="sterilizations.length === 0" class="empty-state">
        <div class="empty-icon">📋</div>
        <h3>No hay esterilizaciones registradas</h3>
        <p>Comienza agregando tu primera esterilización</p>
        <router-link :to="{ name: 'admin-sterilizations-create' }" class="btn btn-primary">
          Nueva Esterilización
        </router-link>
      </div>

      <div v-else class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Mascota</th>
              <th>Propietario</th>
              <th>Campaña</th>
              <th>Especie</th>
              <th>Sexo</th>
              <th>Fecha</th>
              <th>Vendedor</th>
              <th>Estado Pago</th>
              <th>Monto</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sterilization in paginatedSterilizations" :key="sterilization.id">
              <td>
                <span class="code-badge">#{{ sterilization.id }}</span>
              </td>
              <td>
                <div class="pet-info">
                  <strong>{{ sterilization.pet_name }}</strong>
                  <span v-if="sterilization.pet_age_months" class="text-muted">
                    {{ Math.floor(sterilization.pet_age_months / 12) }} años
                  </span>
                </div>
              </td>
              <td>
                <div class="owner-info">
                  <div>{{ sterilization.owner_full_name }}</div>
                  <div v-if="sterilization.owner_phone" class="text-muted small">
                    {{ sterilization.owner_phone }}
                  </div>
                </div>
              </td>
              <td>
                <span class="campaign-badge">
                  {{ getCampaignName(sterilization.campaign_id) }}
                </span>
              </td>
              <td>
                <span :class="['species-badge', `species-${sterilization.pet_type}`]">
                  {{ sterilization.pet_type === 'dog' ? 'Perro' : 'Gato' }}
                </span>
              </td>
              <td>
                <span class="gender-badge">
                  {{ sterilization.pet_gender === 'male' ? '♂ Macho' : '♀ Hembra' }}
                </span>
              </td>
              <td>
                <div class="date-info">
                  {{ sterilization.scheduled_date ? formatDate(sterilization.scheduled_date) : 'Sin programar' }}
                </div>
              </td>
              <td>
                <div class="user-info">
                  <span v-if="typeof sterilization.registered_by === 'object' && sterilization.registered_by" class="user-badge">
                    👤 {{ (sterilization.registered_by as any).name }}
                  </span>
                  <span v-else class="text-muted">
                    Sin asignar
                  </span>
                </div>
              </td>
              <td>
                <span :class="['status-badge', `status-${sterilization.payment_status}`]">
                  {{ getPaymentStatusText(sterilization.payment_status) }}
                </span>
              </td>
              <td>
                <div class="amount-info">
                  <div class="amount-total">${{ Number(sterilization.total_price).toFixed(2) }}</div>
                  <div v-if="sterilization.payment_status === 'partial'" class="amount-paid">
                    Pagado: ${{ Number(sterilization.total_paid).toFixed(2) }}
                  </div>
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
                  >
                    ✏️
                  </router-link>
                  <button
                    @click="confirmDelete(sterilization)"
                    class="btn-icon btn-danger"
                    title="Eliminar"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="totalPages > 1" class="pagination">
      <button
        @click="currentPage--"
        :disabled="currentPage === 1"
        class="btn btn-secondary"
      >
        Anterior
      </button>
      <span class="pagination-info">
        Página {{ currentPage }} de {{ totalPages }}
      </span>
      <button
        @click="currentPage++"
        :disabled="currentPage === totalPages"
        class="btn btn-secondary"
      >
        Siguiente
      </button>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="showDeleteModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Confirmar Eliminación</h3>
          <button @click="showDeleteModal = false" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que deseas eliminar esta esterilización?</p>
          <div v-if="sterilizationToDelete" class="delete-info">
            <p><strong>Mascota:</strong> {{ sterilizationToDelete.pet_name }}</p>
            <p><strong>ID:</strong> #{{ sterilizationToDelete.id }}</p>
            <p class="warning-text">Esta acción no se puede deshacer.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showDeleteModal = false" class="btn btn-secondary">
            Cancelar
          </button>
          <button @click="deleteSterilization" class="btn btn-danger">
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSterilizationStore } from '@/stores/sterilization'
import { useCampaignStore } from '@/stores/campaign'
import type { Sterilization } from '@/types'

const sterilizationStore = useSterilizationStore()
const campaignStore = useCampaignStore()

// Estado
const loading = ref(false)
const showDeleteModal = ref(false)
const sterilizationToDelete = ref<Sterilization | null>(null)

// Filtros
const filters = ref({
  search: '',
  campaign_id: '',
  payment_status: '',
  species: ''
})

// Paginación
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Computed
const sterilizations = computed(() => sterilizationStore.sterilizations)
const campaigns = computed(() => campaignStore.campaigns)

const totalPages = computed(() => 
  Math.ceil(sterilizations.value.length / itemsPerPage.value)
)

const paginatedSterilizations = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return sterilizations.value.slice(start, end)
})

/**
 * Búsqueda con debounce
 */
let searchTimeout: ReturnType<typeof setTimeout>
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

/**
 * Aplicar filtros
 */
const applyFilters = async () => {
  loading.value = true
  currentPage.value = 1
  
  try {
    await sterilizationStore.fetchSterilizations({
      search: filters.value.search || undefined,
      campaign_id: filters.value.campaign_id ? Number(filters.value.campaign_id) : undefined,
      payment_status: filters.value.payment_status || undefined,
      pet_type: filters.value.species || undefined
    })
  } catch (error) {
    console.error('Error al aplicar filtros:', error)
  } finally {
    loading.value = false
  }
}

/**
 * Limpiar filtros
 */
const clearFilters = () => {
  filters.value = {
    search: '',
    campaign_id: '',
    payment_status: '',
    species: ''
  }
  applyFilters()
}

/**
 * Obtener nombre de campaña
 */
const getCampaignName = (campaignId: number): string => {
  const campaign = campaigns.value.find(c => c.id === campaignId)
  return campaign?.name || 'N/A'
}

/**
 * Formatear fecha
 */
const formatDate = (date: string | Date): string => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
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
 * Confirmar eliminación
 */
const confirmDelete = (sterilization: Sterilization) => {
  sterilizationToDelete.value = sterilization
  showDeleteModal.value = true
}

/**
 * Eliminar esterilización
 */
const deleteSterilization = async () => {
  if (!sterilizationToDelete.value) return

  try {
    await sterilizationStore.deleteSterilization(sterilizationToDelete.value.id)
    showDeleteModal.value = false
    sterilizationToDelete.value = null
    await applyFilters()
  } catch (error) {
    console.error('Error al eliminar esterilización:', error)
  }
}

/**
 * Cargar datos iniciales
 */
onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      sterilizationStore.fetchSterilizations(),
      campaignStore.fetchCampaigns({ per_page: 100 })
    ])
  } catch (error) {
    console.error('Error al cargar datos:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.sterilization-list {
  padding: 2rem;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0 0 0.5rem 0;
}

.page-description {
  color: #718096;
  margin: 0;
}

/* Filtros */
.filters-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.filter-input,
.filter-select {
  padding: 0.625rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.results-count {
  font-size: 0.875rem;
  color: #718096;
}

/* Tabla */
.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f7fafc;
  border-bottom: 2px solid #e2e8f0;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.data-table tbody tr:hover {
  background: #f7fafc;
}

/* Badges */
.code-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #edf2f7;
  color: #2d3748;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 6px;
  font-family: monospace;
}

.species-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
}

.species-dog {
  background: #fef3c7;
  color: #92400e;
}

.species-cat {
  background: #dbeafe;
  color: #1e40af;
}

.gender-badge {
  font-size: 0.875rem;
  color: #4a5568;
}

.campaign-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #e0e7ff;
  color: #3730a3;
  font-size: 0.875rem;
  border-radius: 6px;
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

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.status-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-partial {
  background: #dbeafe;
  color: #1e40af;
}

.status-paid {
  background: #d1fae5;
  color: #065f46;
}

/* Info cells */
.pet-info,
.owner-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.text-muted {
  color: #718096;
  font-size: 0.875rem;
}

.small {
  font-size: 0.8125rem;
}

.date-info {
  font-size: 0.875rem;
  color: #4a5568;
}

.amount-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.amount-total {
  font-weight: 600;
  color: #1a202c;
}

.amount-paid {
  font-size: 0.8125rem;
  color: #059669;
}

/* Botones de acción */
.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 1.125rem;
  border-radius: 6px;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-icon:hover {
  background: #f7fafc;
}

.btn-icon.btn-danger:hover {
  background: #fee;
}

/* Estados */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

/* Paginación */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.pagination-info {
  color: #718096;
  font-size: 0.875rem;
}

/* Botones */
.btn {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #f7fafc;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #edf2f7;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.icon {
  font-size: 1.25rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 90%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1a202c;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #718096;
  padding: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}

.modal-close:hover {
  background: #f7fafc;
}

.modal-body {
  padding: 1.5rem;
}

.delete-info {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  padding: 1rem;
  margin-top: 1rem;
  border-radius: 6px;
}

.delete-info p {
  margin: 0.5rem 0;
}

.warning-text {
  color: #dc2626;
  font-weight: 500;
  margin-top: 1rem !important;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}
</style>
