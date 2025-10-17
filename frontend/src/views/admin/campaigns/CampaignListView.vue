<template>
  <div class="campaign-list-view">
    <!-- Header -->
    <div class="page-header">
      <h1>Gestión de Campañas</h1>
      <router-link to="/admin/campaigns/create" class="btn btn-primary" v-if="authStore.isAdmin">
        ➕ Nueva Campaña
      </router-link>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="form-group">
          <label class="form-label">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Nombre o ubicación..."
            @input="debouncedSearch"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Estado</label>
          <select v-model="filters.status" class="form-control" @change="loadCampaigns()">
            <option value="">Todos</option>
            <option value="planned">Planeada</option>
            <option value="active">Activa</option>
            <option value="completed">Completada</option>
            <option value="cancelled">Cancelada</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Visibilidad</label>
          <select v-model="filters.is_visible" class="form-control" @change="loadCampaigns()">
            <option value="">Todas</option>
            <option value="1">Públicas</option>
            <option value="0">Ocultas</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">&nbsp;</label>
          <button class="btn btn-secondary" @click="clearFilters">
            🔄 Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="campaignStore.loading" class="loading-container">
      <div class="spinner"></div>
      <p>Cargando campañas...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="campaignStore.error" class="alert alert-error">
      {{ campaignStore.error }}
    </div>

    <!-- Table -->
    <div v-else-if="campaigns.length > 0" class="card">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Ubicación</th>
              <th>Fechas</th>
              <th>Cupos</th>
              <th>Precios</th>
              <th>Estado</th>
              <th>Visible</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="campaign in campaigns" :key="campaign.id">
              <td>
                <strong>{{ campaign.name }}</strong>
              </td>
              <td>
                <span class="location">📍 {{ campaign.location }}</span>
              </td>
              <td>
                <div class="dates">
                  <small>{{ formatDate(campaign.start_date) }}</small>
                  <small>{{ formatDate(campaign.end_date) }}</small>
                </div>
              </td>
              <td>
                <div class="slots">
                  <span>{{ campaign.used_slots }}/{{ campaign.available_slots }}</span>
                  <div class="progress-mini">
                    <div
                      class="progress-fill-mini"
                      :style="{ width: `${(campaign.used_slots / campaign.available_slots) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </td>
              <td>
                <div class="prices">
                  <small>🐕 ${{ formatCurrency(campaign.dog_price) }}</small>
                  <small>🐱 ${{ formatCurrency(campaign.cat_price) }}</small>
                </div>
              </td>
              <td>
                <span :class="['badge', getStatusClass(campaign.status)]">
                  {{ getStatusLabel(campaign.status) }}
                </span>
              </td>
              <td>
                <span :class="['badge', campaign.is_visible_to_public ? 'badge-success' : 'badge-danger']">
                  {{ campaign.is_visible_to_public ? '👁️ Visible' : '🔒 Oculta' }}
                </span>
              </td>
              <td>
                <div class="actions">
                  <router-link
                    :to="`/admin/campaigns/${campaign.id}`"
                    class="btn-icon"
                    title="Ver detalle"
                  >
                    👁️
                  </router-link>
                  <router-link
                    v-if="authStore.isAdmin"
                    :to="`/admin/campaigns/${campaign.id}/edit`"
                    class="btn-icon"
                    title="Editar"
                  >
                    ✏️
                  </router-link>
                  <button
                    v-if="authStore.isAdmin"
                    class="btn-icon btn-danger"
                    title="Eliminar"
                    @click="confirmDelete(campaign)"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <button
          class="btn btn-secondary"
          :disabled="campaignStore.pagination.current_page === 1"
          @click="loadCampaigns(campaignStore.pagination.current_page - 1)"
        >
          ← Anterior
        </button>
        <span class="pagination-info">
          Página {{ campaignStore.pagination.current_page }} de {{ campaignStore.pagination.last_page }}
          ({{ campaignStore.pagination.total }} campañas)
        </span>
        <button
          class="btn btn-secondary"
          :disabled="campaignStore.pagination.current_page === campaignStore.pagination.last_page"
          @click="loadCampaigns(campaignStore.pagination.current_page + 1)"
        >
          Siguiente →
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <div class="empty-icon">📅</div>
      <h3>No hay campañas</h3>
      <p>Comienza creando tu primera campaña de esterilización</p>
      <router-link to="/admin/campaigns/create" class="btn btn-primary" v-if="authStore.isAdmin">
        ➕ Crear Primera Campaña
      </router-link>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="showDeleteModal = false">
      <div class="modal-content" @click.stop>
        <h3>¿Eliminar campaña?</h3>
        <p>¿Estás seguro de que deseas eliminar la campaña <strong>{{ campaignToDelete?.name }}</strong>?</p>
        <p class="text-danger">Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Cancelar</button>
          <button class="btn btn-danger" @click="deleteCampaign" :disabled="deleting">
            {{ deleting ? 'Eliminando...' : 'Eliminar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useCampaignStore } from '@/stores/campaign'
import type { Campaign } from '@/types'

const authStore = useAuthStore()
const campaignStore = useCampaignStore()

const filters = ref({
  search: '',
  status: '',
  is_visible: '',
})

const showDeleteModal = ref(false)
const campaignToDelete = ref<Campaign | null>(null)
const deleting = ref(false)

const campaigns = computed(() => campaignStore.campaigns)

let searchTimeout: ReturnType<typeof setTimeout>

/**
 * Cargar campañas con filtros
 */
const loadCampaigns = async (page = 1) => {
  try {
    const params: Record<string, string | number> = { page, per_page: 15 }
    
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.is_visible) params.is_visible_to_public = filters.value.is_visible

    await campaignStore.fetchCampaigns(params)
  } catch (error) {
    console.error('Error al cargar campañas:', error)
  }
}

/**
 * Búsqueda con debounce
 */
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadCampaigns()
  }, 500)
}

/**
 * Limpiar filtros
 */
const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    is_visible: '',
  }
  loadCampaigns()
}

/**
 * Confirmar eliminación
 */
const confirmDelete = (campaign: Campaign) => {
  campaignToDelete.value = campaign
  showDeleteModal.value = true
}

/**
 * Eliminar campaña
 */
const deleteCampaign = async () => {
  if (!campaignToDelete.value) return

  deleting.value = true
  try {
    await campaignStore.deleteCampaign(campaignToDelete.value.id)
    showDeleteModal.value = false
    campaignToDelete.value = null
    loadCampaigns(campaignStore.pagination.current_page)
  } catch (error) {
    console.error('Error al eliminar campaña:', error)
  } finally {
    deleting.value = false
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
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

/**
 * Formatear moneda
 */
const formatCurrency = (amount: number | string) => {
  return Number(amount).toLocaleString('es-CO')
}

onMounted(() => {
  loadCampaigns()
})
</script>

<style scoped>
.campaign-list-view {
  padding: 2rem 0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

/* Filters */
.filters-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

/* Loading */
.loading-container {
  text-align: center;
  padding: 4rem 0;
}

.loading-container .spinner {
  margin: 0 auto 1rem;
}

/* Table */
.table-responsive {
  overflow-x: auto;
}

.table th {
  white-space: nowrap;
}

.table td {
  vertical-align: middle;
}

.location {
  display: inline-block;
  white-space: nowrap;
}

.dates {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.dates small {
  color: #666;
  font-size: 0.85rem;
}

.slots {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.progress-mini {
  height: 4px;
  background-color: #e0e0e0;
  border-radius: 2px;
  overflow: hidden;
  width: 80px;
}

.progress-fill-mini {
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s;
}

.prices {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.prices small {
  font-size: 0.85rem;
  color: #555;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
  text-decoration: none;
}

.btn-icon:hover {
  background-color: #f0f0f0;
}

.btn-icon.btn-danger:hover {
  background-color: #ffe0e0;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-top: 1px solid #e0e0e0;
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: #666;
  font-size: 0.95rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.75rem;
}

.empty-state p {
  color: #666;
  font-size: 1.05rem;
  margin-bottom: 2rem;
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
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-content h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 1rem;
}

.modal-content p {
  color: #666;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}

.text-danger {
  color: #dc3545;
  font-weight: 500;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .pagination {
    flex-direction: column;
  }

  .table {
    font-size: 0.9rem;
  }

  .actions {
    flex-direction: column;
  }
}
</style>
