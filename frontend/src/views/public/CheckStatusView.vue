<template>
  <div class="check-status-view">
    <div class="container">
      <div class="check-status-card">
        <!-- Header -->
        <div class="card-header">
          <h1>Consultar Estado de Esterilización</h1>
          <p>Ingresa tu número de cédula para consultar el estado de las esterilizaciones de tus mascotas.</p>
        </div>

        <!-- Form -->
        <div class="card-body">
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label for="idNumber" class="form-label">Número de Cédula</label>
              <input
                id="idNumber"
                v-model="idNumber"
                type="text"
                class="form-control"
                :class="{ error: errors.idNumber }"
                placeholder="Ej: 1234567890"
                required
              />
              <span v-if="errors.idNumber" class="form-error">{{ errors.idNumber }}</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block" :disabled="sterilizationStore.loading">
              <span v-if="!sterilizationStore.loading">🔍 Consultar</span>
              <span v-else>Consultando...</span>
            </button>
          </form>
        </div>

        <!-- Results -->
        <div v-if="results.length > 0" class="results-section">
          <h2>Resultados de la Búsqueda</h2>
          <p class="results-count">Se encontraron {{ results.length }} esterilización(es)</p>

          <div class="sterilizations-list">
            <div v-for="sterilization in results" :key="sterilization.id" class="sterilization-card">
              <div class="sterilization-header">
                <h3>{{ sterilization.pet_name }}</h3>
                <span :class="['badge', getStatusClass(sterilization.payment_status)]">
                  {{ getStatusLabel(sterilization.payment_status) }}
                </span>
              </div>

              <div class="sterilization-info">
                <div class="info-row">
                  <span class="label">Tipo:</span>
                  <span class="value">{{ sterilization.pet_type === 'dog' ? '🐕 Perro' : '🐱 Gato' }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Campaña:</span>
                  <span class="value">{{ sterilization.campaign?.name || 'N/A' }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Fecha:</span>
                  <span class="value">{{ formatDate(sterilization.campaign?.start_date || '') }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Total a Pagar:</span>
                  <span class="value">${{ formatCurrency(sterilization.total_price) }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Pagado:</span>
                  <span class="value">${{ formatCurrency(sterilization.total_paid) }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Saldo:</span>
                  <span class="value balance">${{ formatCurrency(sterilization.balance) }}</span>
                </div>
              </div>

              <div v-if="sterilization.notes" class="notes">
                <p><strong>Notas:</strong> {{ sterilization.notes }}</p>
              </div>

              <div class="progress-section">
                <p class="progress-label">Progreso de Pago</p>
                <div class="progress-bar">
                  <div 
                    class="progress-fill" 
                    :style="{ width: `${(Number(sterilization.total_paid) / Number(sterilization.total_price)) * 100}%` }"
                  ></div>
                </div>
                <p class="progress-text">
                  {{ Math.round((Number(sterilization.total_paid) / Number(sterilization.total_price)) * 100) }}% completado
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="searched && !sterilizationStore.loading" class="empty-state">
          <div class="empty-icon">🔍</div>
          <h3>No se encontraron registros</h3>
          <p>No hay esterilizaciones registradas con el número de cédula ingresado.</p>
        </div>

        <!-- Error -->
        <div v-if="sterilizationStore.error" class="alert alert-error">
          {{ sterilizationStore.error }}
        </div>
      </div>

      <!-- Help Section -->
      <div class="help-section">
        <h3>¿Necesitas ayuda?</h3>
        <p>Si tienes alguna pregunta o no encuentras tu registro, no dudes en contactarnos:</p>
        <div class="contact-buttons">
          <a href="tel:+573001234567" class="btn btn-secondary">📱 Llamar</a>
          <a href="https://wa.me/573001234567" target="_blank" class="btn btn-success">💬 WhatsApp</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useSterilizationStore } from '@/stores/sterilization'
import type { Sterilization } from '@/types'

const sterilizationStore = useSterilizationStore()

const idNumber = ref('')
const errors = ref<{ idNumber?: string }>({})
const results = ref<Sterilization[]>([])
const searched = ref(false)

/**
 * Manejar envío del formulario
 */
const handleSubmit = async () => {
  errors.value = {}
  
  // Validación
  if (!idNumber.value || idNumber.value.trim() === '') {
    errors.value.idNumber = 'El número de cédula es requerido'
    return
  }

  if (!/^\d+$/.test(idNumber.value)) {
    errors.value.idNumber = 'El número de cédula solo debe contener números'
    return
  }

  try {
    searched.value = true
    const response = await sterilizationStore.checkStatus(idNumber.value)
    results.value = response.sterilizations || []
  } catch (error) {
    console.error('Error al consultar:', error)
    results.value = []
  }
}

/**
 * Obtener clase CSS según estado de pago
 */
const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'badge-warning',
    partial: 'badge-info',
    completed: 'badge-success',
  }
  return classes[status] || 'badge-warning'
}

/**
 * Obtener etiqueta de estado de pago
 */
const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pendiente',
    partial: 'Pago Parcial',
    completed: 'Pagado',
  }
  return labels[status] || status
}

/**
 * Formatear fecha
 */
const formatDate = (date: string) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-CO', {
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
</script>

<style scoped>
.check-status-view {
  padding: 3rem 0;
  min-height: calc(100vh - 400px);
}

.check-status-card {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2.5rem;
  text-align: center;
}

.card-header h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}

.card-header p {
  font-size: 1.05rem;
  opacity: 0.95;
  margin: 0;
}

.card-body {
  padding: 2.5rem;
}

.btn-block {
  width: 100%;
  padding: 1rem;
  font-size: 1.1rem;
}

/* Results */
.results-section {
  padding: 0 2.5rem 2.5rem;
}

.results-section h2 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.results-count {
  color: #666;
  margin-bottom: 2rem;
}

.sterilizations-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.sterilization-card {
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 1.5rem;
  background: #f9f9f9;
}

.sterilization-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.sterilization-header h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.sterilization-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.info-row {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-row .label {
  font-size: 0.9rem;
  color: #777;
  font-weight: 500;
}

.info-row .value {
  font-size: 1.05rem;
  color: #333;
  font-weight: 600;
}

.info-row .balance {
  color: #667eea;
  font-size: 1.25rem;
}

.notes {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  border-left: 4px solid #667eea;
}

.notes p {
  margin: 0;
  color: #555;
  line-height: 1.6;
}

.progress-section {
  margin-top: 1.5rem;
}

.progress-label {
  font-weight: 500;
  color: #555;
  margin-bottom: 0.5rem;
}

.progress-bar {
  height: 12px;
  background-color: #e0e0e0;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s;
}

.progress-text {
  text-align: right;
  color: #666;
  font-size: 0.9rem;
  margin: 0;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem 2.5rem;
}

.empty-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.75rem;
}

.empty-state p {
  color: #666;
  font-size: 1.05rem;
}

/* Help Section */
.help-section {
  max-width: 800px;
  margin: 3rem auto 0;
  text-align: center;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.help-section h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.75rem;
}

.help-section p {
  color: #666;
  margin-bottom: 1.5rem;
}

.contact-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
  .card-header,
  .card-body,
  .results-section {
    padding: 1.5rem;
  }

  .card-header h1 {
    font-size: 1.5rem;
  }

  .sterilization-info {
    grid-template-columns: 1fr;
  }

  .contact-buttons {
    flex-direction: column;
  }
}
</style>
