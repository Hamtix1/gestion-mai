<template>
  <div class="expense-list-view">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div>
          <h1 class="page-title">Gastos</h1>
          <p class="page-description">Gestión de gastos y egresos</p>
        </div>
        <div class="header-actions">
          <router-link :to="{ name: 'admin-expenses-create' }" class="btn btn-primary">
            <span class="btn-icon">➕</span>
            Registrar Gasto
          </router-link>
        </div>
      </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">💸</div>
        <div class="stat-content">
          <div class="stat-label">Total Gastos</div>
          <div class="stat-value">${{ Number(totalAmount).toFixed(2) }}</div>
          <div class="stat-subtitle">{{ expenses.length }} registros</div>
        </div>
      </div>

      <div class="stat-card medical">
        <div class="stat-icon">💊</div>
        <div class="stat-content">
          <div class="stat-label">Médicos</div>
          <div class="stat-value">${{ Number(medicalAmount).toFixed(2) }}</div>
          <div class="stat-subtitle">{{ medicalCount }} gastos</div>
        </div>
      </div>

      <div class="stat-card supplies">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
          <div class="stat-label">Suministros</div>
          <div class="stat-value">${{ Number(suppliesAmount).toFixed(2) }}</div>
          <div class="stat-subtitle">{{ suppliesCount }} gastos</div>
        </div>
      </div>

      <div class="stat-card other">
        <div class="stat-icon">💼</div>
        <div class="stat-content">
          <div class="stat-label">Otros Gastos</div>
          <div class="stat-value">${{ Number(otherAmount).toFixed(2) }}</div>
          <div class="stat-subtitle">{{ otherCount }} gastos</div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <div class="filters-header">
        <h3>Filtros</h3>
        <button @click="clearFilters" class="btn-link">Limpiar filtros</button>
      </div>
      <div class="filters-grid">
        <div class="form-group">
          <label>Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Concepto, descripción, referencia..."
            @input="debouncedSearch"
          />
        </div>

        <div class="form-group">
          <label>Campaña</label>
          <select v-model="filters.campaign_id" @change="applyFilters" class="form-control">
            <option value="">Todas las campañas</option>
            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
              {{ campaign.name }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Categoría</label>
          <select v-model="filters.category" @change="applyFilters" class="form-control">
            <option value="">Todas las categorías</option>
            <option value="medical">Médicos</option>
            <option value="transportation">Transporte</option>
            <option value="supplies">Suministros</option>
            <option value="marketing">Marketing</option>
            <option value="administrative">Administrativos</option>
            <option value="other">Otros</option>
          </select>
        </div>

        <div class="form-group">
          <label>Fecha Desde</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="form-control"
            @change="applyFilters"
          />
        </div>

        <div class="form-group">
          <label>Fecha Hasta</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="form-control"
            @change="applyFilters"
          />
        </div>
      </div>
    </div>

    <!-- Tabla de Gastos -->
    <div class="table-card">
      <div class="table-header">
        <h3>Listado de Gastos</h3>
        <span class="results-count">{{ expenses.length }} gastos - Total: ${{ Number(totalAmount).toFixed(2) }}</span>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando gastos...</p>
      </div>

      <div v-else-if="expenses.length === 0" class="empty-state">
        <div class="empty-icon">📊</div>
        <h3>No hay gastos registrados</h3>
        <p>Comienza registrando el primer gasto</p>
        <router-link :to="{ name: 'admin-expenses-create' }" class="btn btn-primary">
          Registrar Gasto
        </router-link>
      </div>

      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Categoría</th>
              <th>Campaña</th>
              <th>Monto</th>
              <th>Referencia</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="expense in paginatedExpenses" :key="expense.id">
              <td class="id-cell">#{{ expense.id }}</td>
              <td>{{ formatDate(expense.expense_date) }}</td>
              <td>
                <div class="concept-cell">
                  <div class="concept-text">{{ expense.concept }}</div>
                  <div v-if="expense.description" class="description-text">
                    {{ expense.description }}
                  </div>
                </div>
              </td>
              <td>
                <span :class="['badge', `badge-${expense.category}`]">
                  {{ getCategoryLabel(expense.category) }}
                </span>
              </td>
              <td>
                <span v-if="expense.campaign" class="campaign-name">
                  {{ expense.campaign.name }}
                </span>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="amount-cell">
                ${{ Number(expense.amount).toFixed(2) }}
              </td>
              <td>
                <code v-if="expense.invoice_number" class="reference-code">
                  {{ expense.invoice_number }}
                </code>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="actions-cell">
                <router-link
                  :to="{ name: 'admin-expenses-edit', params: { id: expense.id } }"
                  class="btn-icon-action"
                  title="Editar"
                >
                  ✏️
                </router-link>
                <button
                  @click="confirmDelete(expense)"
                  class="btn-icon-action delete"
                  title="Eliminar"
                >
                  🗑️
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="expenses.length > itemsPerPage" class="pagination">
        <button
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="btn-pagination"
        >
          ← Anterior
        </button>
        <span class="pagination-info">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
        <button
          @click="currentPage++"
          :disabled="currentPage === totalPages"
          class="btn-pagination"
        >
          Siguiente →
        </button>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="showDeleteModal = false">
      <div class="modal-content" @click.stop>
        <h3>¿Eliminar gasto?</h3>
        <p>
          ¿Estás seguro de que deseas eliminar el gasto
          <strong>{{ expenseToDelete?.concept }}</strong>?
        </p>
        <p class="text-muted">Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
          <button @click="showDeleteModal = false" class="btn btn-secondary">
            Cancelar
          </button>
          <button @click="deleteExpense" class="btn btn-danger">
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFinancialStore } from '@/stores/financial'
import { useCampaignStore } from '@/stores/campaign'
import type { Expense } from '@/types'

const financialStore = useFinancialStore()
const campaignStore = useCampaignStore()

// Estado
const loading = ref(false)
const showDeleteModal = ref(false)
const expenseToDelete = ref<Expense | null>(null)

// Filtros
const filters = ref({
  search: '',
  campaign_id: '',
  category: '',
  date_from: '',
  date_to: ''
})

// Paginación
const currentPage = ref(1)
const itemsPerPage = ref(15)

// Computed
const expenses = computed(() => financialStore.expenses)
const campaigns = computed(() => campaignStore.campaigns)

const totalPages = computed(() => 
  Math.ceil(expenses.value.length / itemsPerPage.value)
)

const paginatedExpenses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return expenses.value.slice(start, end)
})

// Estadísticas
const totalAmount = computed(() => 
  expenses.value.reduce((sum, e) => sum + Number(e.amount), 0)
)

const medicalAmount = computed(() => 
  expenses.value
    .filter(e => e.category === 'medical')
    .reduce((sum, e) => sum + Number(e.amount), 0)
)

const medicalCount = computed(() => 
  expenses.value.filter(e => e.category === 'medical').length
)

const suppliesAmount = computed(() => 
  expenses.value
    .filter(e => e.category === 'supplies')
    .reduce((sum, e) => sum + Number(e.amount), 0)
)

const suppliesCount = computed(() => 
  expenses.value.filter(e => e.category === 'supplies').length
)

const otherAmount = computed(() => 
  expenses.value
    .filter(e => ['transportation', 'marketing', 'administrative', 'other'].includes(e.category))
    .reduce((sum, e) => sum + Number(e.amount), 0)
)

const otherCount = computed(() => 
  expenses.value.filter(e => ['transportation', 'marketing', 'administrative', 'other'].includes(e.category)).length
)

/**
 * Obtener etiqueta de categoría
 */
const getCategoryLabel = (category: string): string => {
  const labels: Record<string, string> = {
    medical: 'Médicos',
    transportation: 'Transporte',
    supplies: 'Suministros',
    marketing: 'Marketing',
    administrative: 'Administrativos',
    other: 'Otros'
  }
  return labels[category] || category
}

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
    await financialStore.fetchExpenses({
      search: filters.value.search || undefined,
      campaign_id: filters.value.campaign_id ? Number(filters.value.campaign_id) : undefined,
      category: filters.value.category || undefined,
      start_date: filters.value.date_from || undefined,
      end_date: filters.value.date_to || undefined
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
    category: '',
    date_from: '',
    date_to: ''
  }
  applyFilters()
}

/**
 * Confirmar eliminación
 */
const confirmDelete = (expense: Expense) => {
  expenseToDelete.value = expense
  showDeleteModal.value = true
}

/**
 * Eliminar gasto
 */
const deleteExpense = async () => {
  if (!expenseToDelete.value) return

  try {
    await financialStore.deleteExpense(expenseToDelete.value.id)
    showDeleteModal.value = false
    expenseToDelete.value = null
  } catch (error) {
    console.error('Error al eliminar gasto:', error)
    alert('Error al eliminar el gasto')
  }
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
 * Cargar datos iniciales
 */
onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      financialStore.fetchExpenses(),
      campaignStore.fetchCampaigns()
    ])
  } catch (error) {
    console.error('Error al cargar datos:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.expense-list-view {
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
}

/* Header */
.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
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

.header-actions {
  display: flex;
  gap: 0.75rem;
}

/* Estadísticas */
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
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-card.total {
  border-left: 4px solid #ef4444;
}

.stat-card.medical {
  border-left: 4px solid #8b5cf6;
}

.stat-card.supplies {
  border-left: 4px solid #f59e0b;
}

.stat-card.other {
  border-left: 4px solid #6b7280;
}

.stat-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.25rem;
}

.stat-subtitle {
  font-size: 0.875rem;
  color: #9ca3af;
}

/* Filtros */
.filters-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filters-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1a202c;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

/* Tabla */
.table-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.table-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a202c;
}

.results-count {
  font-size: 0.875rem;
  color: #6b7280;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.data-table th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background-color 0.2s;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.data-table td {
  padding: 1rem;
  font-size: 0.875rem;
  color: #1f2937;
}

.id-cell {
  font-weight: 600;
  color: #6b7280;
}

.concept-cell {
  max-width: 300px;
}

.concept-text {
  font-weight: 500;
  color: #1a202c;
  margin-bottom: 0.25rem;
}

.description-text {
  font-size: 0.8125rem;
  color: #6b7280;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.amount-cell {
  font-weight: 600;
  color: #ef4444;
  font-size: 1rem;
}

.campaign-name {
  font-size: 0.875rem;
  color: #3b82f6;
}

.reference-code {
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  color: #6b7280;
}

/* Badges */
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-medical {
  background: #ede9fe;
  color: #5b21b6;
}

.badge-transportation {
  background: #dbeafe;
  color: #1e40af;
}

.badge-supplies {
  background: #fef3c7;
  color: #92400e;
}

.badge-marketing {
  background: #fce7f3;
  color: #9f1239;
}

.badge-administrative {
  background: #e0e7ff;
  color: #3730a3;
}

.badge-other {
  background: #f3f4f6;
  color: #374151;
}

/* Acciones */
.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.btn-icon-action {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  font-size: 1.25rem;
  transition: transform 0.2s;
  text-decoration: none;
}

.btn-icon-action:hover {
  transform: scale(1.1);
}

.btn-icon-action.delete:hover {
  filter: brightness(1.2);
}

/* Paginación */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.btn-pagination {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-pagination:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Estados */
.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a202c;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
}

.modal-content h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  color: #1a202c;
}

.modal-content p {
  margin: 0 0 1rem 0;
  color: #4b5563;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

/* Botones */
.btn {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-link {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 0.875rem;
  cursor: pointer;
  padding: 0;
}

.btn-link:hover {
  text-decoration: underline;
}

.btn-icon {
  font-size: 1rem;
}

/* Formularios */
.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-control {
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1f2937;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.text-muted {
  color: #9ca3af;
}
</style>
