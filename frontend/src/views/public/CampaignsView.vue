<template>
  <div class="campaigns-view">
    <div class="container">
      <!-- Header -->
      <div class="page-header">
        <h1>Campañas de Esterilización</h1>
        <p>Consulta nuestras campañas activas y próximas. Encuentra la más cercana a ti.</p>
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

      <!-- Campaigns Grid -->
      <div v-else-if="campaigns.length > 0" class="campaigns-grid">
        <div v-for="campaign in campaigns" :key="campaign.id" class="campaign-card">
          <div class="campaign-status">
            <span :class="['badge', getStatusClass(campaign.status)]">
              {{ getStatusLabel(campaign.status) }}
            </span>
          </div>

          <h2>{{ campaign.name }}</h2>
          
          <div class="campaign-info">
            <div class="info-item">
              <span class="icon">📍</span>
              <span>{{ campaign.location }}</span>
            </div>
            <div class="info-item">
              <span class="icon">📅</span>
              <span>{{ formatDate(campaign.start_date) }}</span>
            </div>
            <div class="info-item">
              <span class="icon">💰</span>
              <span>Desde ${{ formatCurrency(getMinPrice(campaign)) }}</span>
            </div>
          </div>

          <p class="campaign-description">{{ campaign.description }}</p>

          <div class="campaign-footer">
            <router-link :to="`/campaigns/${campaign.id}`" class="btn btn-primary">
              Ver Detalles
            </router-link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">📅</div>
        <h3>No hay campañas disponibles</h3>
        <p>Por el momento no tenemos campañas activas. Vuelve pronto para ver nuevas oportunidades.</p>
      </div>

      <!-- Pagination -->
      <div v-if="campaignStore.pagination.last_page > 1" class="pagination">
        <button
          class="btn btn-secondary"
          :disabled="campaignStore.pagination.current_page === 1"
          @click="loadCampaigns(campaignStore.pagination.current_page - 1)"
        >
          Anterior
        </button>
        <span class="pagination-info">
          Página {{ campaignStore.pagination.current_page }} de {{ campaignStore.pagination.last_page }}
        </span>
        <button
          class="btn btn-secondary"
          :disabled="campaignStore.pagination.current_page === campaignStore.pagination.last_page"
          @click="loadCampaigns(campaignStore.pagination.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useCampaignStore } from '@/stores/campaign'
import type { Campaign } from '@/types'

const campaignStore = useCampaignStore()

const campaigns = computed(() => campaignStore.campaigns)

/**
 * Cargar campañas públicas
 */
const loadCampaigns = async (page = 1) => {
  try {
    await campaignStore.fetchPublicCampaigns({ page, per_page: 9 })
  } catch (error) {
    console.error('Error al cargar campañas:', error)
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
    planned: 'Próximamente',
    active: 'Activa',
    completed: 'Finalizada',
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
 * Formatear moneda
 */
const formatCurrency = (amount: number | string) => {
  return Number(amount).toLocaleString('es-CO')
}

/**
 * Obtener precio mínimo
 */
const getMinPrice = (campaign: Campaign) => {
  return Math.min(campaign.dog_price, campaign.cat_price)
}

onMounted(() => {
  loadCampaigns()
})
</script>

<style scoped>
.campaigns-view {
  padding: 3rem 0;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.page-header p {
  font-size: 1.1rem;
  color: #666;
  max-width: 600px;
  margin: 0 auto;
}

/* Loading */
.loading-container {
  text-align: center;
  padding: 4rem 0;
}

.loading-container .spinner {
  margin: 0 auto 1rem;
}

/* Campaigns Grid */
.campaigns-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.campaign-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  display: flex;
  flex-direction: column;
}

.campaign-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.campaign-status {
  margin-bottom: 1rem;
}

.campaign-card h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 1.5rem;
}

.campaign-info {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #555;
  font-size: 0.95rem;
}

.info-item .icon {
  font-size: 1.1rem;
}

.campaign-description {
  color: #666;
  line-height: 1.6;
  flex: 1;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.campaign-footer {
  margin-top: auto;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
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
  margin-bottom: 1rem;
}

.empty-state p {
  color: #666;
  font-size: 1.1rem;
  max-width: 500px;
  margin: 0 auto;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 3rem;
}

.pagination-info {
  color: #555;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .campaigns-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .pagination {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
