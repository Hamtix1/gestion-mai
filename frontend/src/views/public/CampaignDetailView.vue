<template>
  <div class="campaign-detail-view">
    <div class="container">
      <!-- Loading -->
      <div v-if="campaignStore.loading" class="loading-container">
        <div class="spinner"></div>
        <p>Cargando campaña...</p>
      </div>

      <!-- Error -->
      <div v-else-if="campaignStore.error" class="alert alert-error">
        {{ campaignStore.error }}
        <router-link to="/campaigns" class="btn btn-secondary mt-3">Volver a Campañas</router-link>
      </div>

      <!-- Campaign Detail -->
      <div v-else-if="campaign" class="campaign-detail">
        <!-- Header -->
        <div class="campaign-header">
          <router-link to="/campaigns" class="back-link">← Volver a campañas</router-link>
          <div class="header-content">
            <h1>{{ campaign.name }}</h1>
            <span :class="['badge', 'badge-large', getStatusClass(campaign.status)]">
              {{ getStatusLabel(campaign.status) }}
            </span>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
          <div class="info-card">
            <h3>📍 Ubicación</h3>
            <p>{{ campaign.location }}</p>
          </div>

          <div class="info-card">
            <h3>📅 Fechas</h3>
            <p>Inicio: {{ formatDate(campaign.start_date) }}</p>
            <p>Fin: {{ formatDate(campaign.end_date) }}</p>
          </div>

          <div class="info-card">
            <h3>🐕 Precio Perros</h3>
            <p class="price">${{ formatCurrency(campaign.dog_price) }}</p>
          </div>

          <div class="info-card">
            <h3>🐱 Precio Gatos</h3>
            <p class="price">${{ formatCurrency(campaign.cat_price) }}</p>
          </div>

          <div class="info-card">
            <h3>📊 Disponibilidad</h3>
            <p>{{ campaign.available_slots - campaign.used_slots }} de {{ campaign.available_slots }} cupos</p>
            <div class="progress-bar">
              <div 
                class="progress-fill" 
                :style="{ width: `${(campaign.used_slots / campaign.available_slots) * 100}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="campaign-section">
          <h2>Descripción</h2>
          <p class="description">{{ campaign.description || 'No hay descripción disponible.' }}</p>
        </div>

        <!-- Important Info -->
        <div class="campaign-section">
          <h2>Información Importante</h2>
          <div class="info-list">
            <div class="info-list-item">
              <span class="icon">✅</span>
              <span>Puedes pagar en varios aportes o en un único pago completo</span>
            </div>
            <div class="info-list-item">
              <span class="icon">✅</span>
              <span>El procedimiento es realizado por veterinarios certificados</span>
            </div>
            <div class="info-list-item">
              <span class="icon">✅</span>
              <span>Incluye medicamentos post-operatorios básicos</span>
            </div>
            <div class="info-list-item">
              <span class="icon">✅</span>
              <span>Puedes consultar el estado de tu mascota en cualquier momento</span>
            </div>
          </div>
        </div>

        <!-- CTA -->
        <div v-if="campaign.status === 'active' && campaign.available_slots > campaign.used_slots" class="cta-section">
          <h2>¿Listo para registrar a tu mascota?</h2>
          <p>Contacta con nosotros para apartar tu cupo en esta campaña.</p>
          <div class="cta-buttons">
            <a href="tel:+573001234567" class="btn btn-primary">📱 Llamar Ahora</a>
            <a href="https://wa.me/573001234567" target="_blank" class="btn btn-success">💬 WhatsApp</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'
import type { Campaign } from '@/types'

const route = useRoute()
const campaignStore = useCampaignStore()

const campaign = computed(() => campaignStore.currentCampaign)

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

onMounted(async () => {
  const id = Number(route.params.id)
  if (id) {
    try {
      await campaignStore.fetchCampaign(id)
    } catch (error) {
      console.error('Error al cargar campaña:', error)
    }
  }
})
</script>

<style scoped>
.campaign-detail-view {
  padding: 3rem 0;
}

.loading-container {
  text-align: center;
  padding: 4rem 0;
}

.loading-container .spinner {
  margin: 0 auto 1rem;
}

/* Header */
.campaign-header {
  margin-bottom: 2rem;
}

.back-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 1rem;
  display: inline-block;
  transition: color 0.2s;
}

.back-link:hover {
  color: #764ba2;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

.badge-large {
  padding: 0.5rem 1rem;
  font-size: 1rem;
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #555;
  margin-bottom: 0.75rem;
}

.info-card p {
  color: #333;
  font-size: 1rem;
  margin: 0.25rem 0;
}

.price {
  font-size: 1.75rem;
  font-weight: 700;
  color: #667eea;
}

.progress-bar {
  height: 8px;
  background-color: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s;
}

/* Sections */
.campaign-section {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.campaign-section h2 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 1.5rem;
}

.description {
  color: #555;
  line-height: 1.8;
  font-size: 1.05rem;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-list-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  color: #555;
  font-size: 1.05rem;
}

.info-list-item .icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

/* CTA Section */
.cta-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 3rem;
  border-radius: 12px;
  text-align: center;
}

.cta-section h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: white;
}

.cta-section p {
  font-size: 1.1rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
  .header-content h1 {
    font-size: 2rem;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .campaign-section {
    padding: 1.5rem;
  }

  .cta-section {
    padding: 2rem 1.5rem;
  }

  .cta-buttons {
    flex-direction: column;
  }
}
</style>
