<template>
  <div class="payment-list">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Pagos</h1>
        <p class="page-description">Historial de pagos recibidos</p>
      </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <div class="stat-label">Total Recaudado</div>
          <div class="stat-value">${{ Number(totalAmount).toFixed(2) }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-content">
          <div class="stat-label">Total Pagos</div>
          <div class="stat-value">{{ payments.length }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💵</div>
        <div class="stat-content">
          <div class="stat-label">Efectivo</div>
          <div class="stat-value">${{ Number(cashAmount).toFixed(2) }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💳</div>
        <div class="stat-content">
          <div class="stat-label">Transferencias/Tarjetas</div>
          <div class="stat-value">${{ Number(electronicAmount).toFixed(2) }}</div>
        </div>
      </div>
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
            placeholder="Nombre, referencia..."
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

        <!-- Método de pago -->
        <div class="filter-group">
          <label class="filter-label">Método de Pago</label>
          <select v-model="filters.payment_method" class="filter-select" @change="applyFilters">
            <option value="">Todos</option>
            <option value="cash">Efectivo</option>
            <option value="transfer">Transferencia</option>
            <option value="card">Tarjeta</option>
            <option value="other">Otro</option>
          </select>
        </div>

        <!-- Rango de fechas -->
        <div class="filter-group">
          <label class="filter-label">Fecha Desde</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="filter-input"
            @change="applyFilters"
          />
        </div>

        <div class="filter-group">
          <label class="filter-label">Fecha Hasta</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="filter-input"
            @change="applyFilters"
          />
        </div>
      </div>

      <!-- Acciones de filtro -->
      <div class="filter-actions">
        <button @click="clearFilters" class="btn btn-secondary">
          <i class="icon">×</i> Limpiar Filtros
        </button>
        <span class="results-count">{{ payments.length }} pagos - Total: ${{ Number(totalAmount).toFixed(2) }}</span>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando pagos...</p>
      </div>

      <div v-else-if="payments.length === 0" class="empty-state">
        <div class="empty-icon">💳</div>
        <h3>No hay pagos registrados</h3>
        <p>Los pagos se registran desde el detalle de cada esterilización</p>
      </div>

      <div v-else class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fecha</th>
              <th>Esterilización</th>
              <th>Propietario</th>
              <th>Mascota</th>
              <th>Monto</th>
              <th>Método</th>
              <th>Referencia</th>
              <th>Recibido por</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in paginatedPayments" :key="payment.id">
              <td>
                <span class="id-badge">#{{ payment.id }}</span>
              </td>
              <td>
                <div class="date-cell">
                  <div class="date-main">{{ formatDate(payment.created_at) }}</div>
                  <div class="date-time">{{ formatTime(payment.created_at) }}</div>
                </div>
              </td>
              <td>
                <router-link
                  v-if="payment.sterilization"
                  :to="{ name: 'admin-sterilizations-detail', params: { id: payment.sterilization.id } }"
                  class="link-text"
                >
                  #{{ payment.sterilization.id }}
                </router-link>
                <span v-else>N/A</span>
              </td>
              <td>
                <div class="owner-cell">
                  {{ payment.sterilization?.owner_full_name || 'N/A' }}
                </div>
              </td>
              <td>
                <div class="pet-cell">
                  <strong>{{ payment.sterilization?.pet_name || 'N/A' }}</strong>
                  <span v-if="payment.sterilization" class="pet-type">
                    ({{ payment.sterilization.pet_type === 'dog' ? 'Perro' : 'Gato' }})
                  </span>
                </div>
              </td>
              <td>
                <div class="amount-cell">
                  ${{ Number(payment.amount).toFixed(2) }}
                </div>
              </td>
              <td>
                <span :class="['method-badge', `method-${payment.payment_method}`]">
                  {{ getPaymentMethodText(payment.payment_method) }}
                </span>
              </td>
              <td>
                <span class="reference-text">{{ payment.reference_number || '-' }}</span>
              </td>
              <td>
                <div class="user-cell">
                  <span v-if="typeof payment.received_by === 'object' && payment.received_by" class="user-badge">
                    👤 {{ (payment.received_by as any).name }}
                  </span>
                  <span v-else class="text-muted">
                    N/A
                  </span>
                </div>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    v-if="payment.sterilization"
                    @click="viewSterilization(payment.sterilization.id)"
                    class="btn-icon"
                    title="Ver esterilización"
                  >
                    👁️
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePaymentStore } from '@/stores/payment'
import { useCampaignStore } from '@/stores/campaign'

const router = useRouter()
const paymentStore = usePaymentStore()
const campaignStore = useCampaignStore()

// Estado
const loading = ref(false)

// Filtros
const filters = ref({
  search: '',
  campaign_id: '',
  payment_method: '',
  date_from: '',
  date_to: ''
})

// Paginación
const currentPage = ref(1)
const itemsPerPage = ref(15)

// Computed
const payments = computed(() => paymentStore.payments)
const campaigns = computed(() => campaignStore.campaigns)

const totalPages = computed(() => 
  Math.ceil(payments.value.length / itemsPerPage.value)
)

const paginatedPayments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return payments.value.slice(start, end)
})

// Estadísticas
const totalAmount = computed(() => 
  payments.value.reduce((sum, p) => sum + Number(p.amount), 0)
)

const cashAmount = computed(() => 
  payments.value
    .filter(p => p.payment_method === 'cash')
    .reduce((sum, p) => sum + Number(p.amount), 0)
)

const electronicAmount = computed(() => 
  payments.value
    .filter(p => ['transfer', 'card'].includes(p.payment_method))
    .reduce((sum, p) => sum + Number(p.amount), 0)
)

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
    await paymentStore.fetchPayments({
      search: filters.value.search || undefined,
      campaign_id: filters.value.campaign_id ? Number(filters.value.campaign_id) : undefined,
      payment_method: filters.value.payment_method || undefined,
      date_from: filters.value.date_from || undefined,
      date_to: filters.value.date_to || undefined
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
    payment_method: '',
    date_from: '',
    date_to: ''
  }
  applyFilters()
}

/**
 * Formatear fecha
 */
const formatDate = (date?: string): string => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

/**
 * Formatear hora
 */
const formatTime = (date?: string): string => {
  if (!date) return ''
  return new Date(date).toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Obtener texto del método de pago
 */
const getPaymentMethodText = (method: string): string => {
  const methodMap: Record<string, string> = {
    cash: 'Efectivo',
    transfer: 'Transferencia',
    card: 'Tarjeta',
    other: 'Otro'
  }
  return methodMap[method] || method
}

/**
 * Ver esterilización
 */
const viewSterilization = (id: number) => {
  router.push({ name: 'admin-sterilizations-detail', params: { id } })
}

/**
 * Cargar datos iniciales
 */
onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      paymentStore.fetchPayments(),
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
.payment-list {
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

/* Estadísticas */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7fafc;
  border-radius: 10px;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
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
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
  font-weight: 500;
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
  white-space: nowrap;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.data-table tbody tr:hover {
  background: #f7fafc;
}

/* Celdas */
.id-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #edf2f7;
  color: #2d3748;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 6px;
  font-family: monospace;
}

.date-cell {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.date-main {
  font-size: 0.875rem;
  color: #1a202c;
  font-weight: 500;
}

.date-time {
  font-size: 0.75rem;
  color: #718096;
}

.link-text {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.link-text:hover {
  text-decoration: underline;
}

.owner-cell,
.user-cell {
  font-size: 0.875rem;
  color: #4a5568;
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

.pet-cell {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.pet-cell strong {
  font-size: 0.875rem;
  color: #1a202c;
}

.pet-type {
  font-size: 0.75rem;
  color: #718096;
}

.amount-cell {
  font-size: 1rem;
  font-weight: 700;
  color: #059669;
}

.method-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
}

.method-cash {
  background: #d1fae5;
  color: #065f46;
}

.method-transfer {
  background: #dbeafe;
  color: #1e40af;
}

.method-card {
  background: #e0e7ff;
  color: #3730a3;
}

.method-other {
  background: #f3f4f6;
  color: #4b5563;
}

.reference-text {
  font-size: 0.875rem;
  color: #718096;
  font-family: monospace;
}

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
}

.btn-icon:hover {
  background: #f7fafc;
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

.icon {
  font-size: 1.25rem;
}

/* Responsive */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
