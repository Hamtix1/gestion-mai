<template>
  <div class="sterilization-detail">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Detalle de Esterilización</h1>
        <p v-if="sterilization" class="page-description">
          ID: #{{ sterilization.id }} - {{ sterilization.pet_name }}
        </p>
      </div>
      <div class="header-actions">
        <router-link
          v-if="sterilization"
          :to="{ name: 'admin-sterilizations-edit', params: { id: sterilization.id } }"
          class="btn btn-secondary"
        >
          ✏️ Editar
        </router-link>
        <router-link :to="{ name: 'admin-sterilizations' }" class="btn btn-secondary">
          ← Volver
        </router-link>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando información...</p>
    </div>

    <!-- Contenido -->
    <div v-else-if="sterilization" class="content-grid">
      <!-- Información General -->
      <div class="info-card">
        <h2 class="card-title">Información General</h2>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Campaña:</span>
            <span class="info-value">{{ sterilization.campaign?.name }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Estado:</span>
            <span :class="['status-badge', `status-${sterilization.status}`]">
              {{ getStatusText(sterilization.status) }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Fecha Programada:</span>
            <span class="info-value">
              {{ sterilization.scheduled_date ? formatDate(sterilization.scheduled_date) : 'Sin programar' }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Hora:</span>
            <span class="info-value">{{ sterilization.scheduled_time || 'N/A' }}</span>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Registrado por:</span>
            <span class="info-value">{{ sterilization.registeredBy?.name || 'N/A' }}</span>
          </div>
        </div>
      </div>

      <!-- Datos del Propietario -->
      <div class="info-card">
        <h2 class="card-title">Propietario</h2>
        <div class="info-grid">
          <div class="info-item full-width">
            <span class="info-label">Nombre:</span>
            <span class="info-value">{{ sterilization.owner_full_name }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Documento:</span>
            <span class="info-value">{{ sterilization.owner_id_number }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Teléfono:</span>
            <span class="info-value">{{ sterilization.owner_phone || 'N/A' }}</span>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ sterilization.owner_email || 'N/A' }}</span>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Dirección:</span>
            <span class="info-value">{{ sterilization.owner_address || 'N/A' }}</span>
          </div>
        </div>
      </div>

      <!-- Datos de la Mascota -->
      <div class="info-card">
        <h2 class="card-title">Datos de la Mascota</h2>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Nombre:</span>
            <span class="info-value strong">{{ sterilization.pet_name }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Especie:</span>
            <span :class="['species-badge', `species-${sterilization.pet_type}`]">
              {{ sterilization.pet_type === 'dog' ? 'Perro' : 'Gato' }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Sexo:</span>
            <span class="info-value">
              {{ sterilization.pet_gender === 'male' ? '♂ Macho' : '♀ Hembra' }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Raza:</span>
            <span class="info-value">{{ sterilization.pet_breed || 'No especificada' }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Edad:</span>
            <span class="info-value">
              {{ sterilization.pet_age_months 
                ? `${Math.floor(sterilization.pet_age_months / 12)} años (${sterilization.pet_age_months} meses)` 
                : 'No especificada' 
              }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Peso:</span>
            <span class="info-value">
              {{ sterilization.pet_weight ? `${sterilization.pet_weight} kg` : 'No especificado' }}
            </span>
          </div>
          <div v-if="sterilization.notes" class="info-item full-width">
            <span class="info-label">Observaciones:</span>
            <span class="info-value">{{ sterilization.notes }}</span>
          </div>
        </div>
      </div>

      <!-- Resumen de Pagos -->
      <div class="payment-summary">
        <h2 class="card-title">Resumen de Pagos</h2>
        <div class="summary-grid">
          <div class="summary-item total">
            <div class="summary-label">Total a Pagar</div>
            <div class="summary-value">${{ Number(sterilization.total_price).toFixed(2) }}</div>
          </div>
          <div class="summary-item paid">
            <div class="summary-label">Total Pagado</div>
            <div class="summary-value">${{ Number(sterilization.total_paid).toFixed(2) }}</div>
          </div>
          <div class="summary-item balance">
            <div class="summary-label">Saldo Pendiente</div>
            <div class="summary-value" :class="{ 'text-danger': Number(sterilization.balance) > 0 }">
              ${{ Number(sterilization.balance).toFixed(2) }}
            </div>
          </div>
          <div class="summary-item status">
            <div class="summary-label">Estado</div>
            <span :class="['status-badge', `status-${sterilization.payment_status}`]">
              {{ getPaymentStatusText(sterilization.payment_status) }}
            </span>
          </div>
        </div>

        <!-- Botón para registrar pago -->
        <div v-if="sterilization.balance > 0" class="payment-action">
          <button @click="showPaymentModal = true" class="btn btn-primary">
            💰 Registrar Pago
          </button>
        </div>
      </div>

      <!-- Historial de Pagos -->
      <div class="payment-history">
        <h2 class="card-title">Historial de Pagos</h2>
        
        <div v-if="!payments || payments.length === 0" class="empty-state">
          <div class="empty-icon">💳</div>
          <p>No hay pagos registrados para esta esterilización</p>
          <button 
            v-if="sterilization.balance > 0"
            @click="showPaymentModal = true"
            class="btn btn-primary"
          >
            Registrar Primer Pago
          </button>
        </div>

        <div v-else class="payments-list">
          <div v-for="payment in payments" :key="payment.id" class="payment-item">
            <div class="payment-header">
              <div class="payment-amount">${{ Number(payment.amount).toFixed(2) }}</div>
              <div class="payment-date">{{ formatDateTime(payment.created_at) }}</div>
            </div>
            <div class="payment-details">
              <div class="payment-detail">
                <span class="detail-label">Método:</span>
                <span class="detail-value">{{ getPaymentMethodText(payment.payment_method) }}</span>
              </div>
              <div v-if="payment.reference_number" class="payment-detail">
                <span class="detail-label">Referencia:</span>
                <span class="detail-value">{{ payment.reference_number }}</span>
              </div>
              <div class="payment-detail">
                <span class="detail-label">Recibido por:</span>
                <span class="detail-value">{{ payment.receivedBy?.name || 'N/A' }}</span>
              </div>
              <div v-if="payment.notes" class="payment-detail full">
                <span class="detail-label">Notas:</span>
                <span class="detail-value">{{ payment.notes }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Esterilizaciones de la Misma Campaña -->
      <div v-if="campaignSterilizations.length > 0" class="campaign-sterilizations-card">
        <div class="card-header-flex">
          <h2 class="card-title">Esterilizaciones de esta Campaña</h2>
          <span class="sterilization-count">{{ campaignSterilizations.length }} registro(s)</span>
        </div>
        
        <div class="sterilizations-table">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Mascota</th>
                <th>Propietario</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Pago</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="item in campaignSterilizations" 
                :key="item.id"
                :class="{ 'current-row': item.id === sterilization?.id }"
              >
                <td>
                  <span class="id-badge">#{{ item.id }}</span>
                </td>
                <td>
                  <div class="pet-info">
                    <span class="pet-name">{{ item.pet_name }}</span>
                    <span class="pet-breed">{{ item.pet_breed || 'N/A' }}</span>
                  </div>
                </td>
                <td>{{ item.owner_full_name }}</td>
                <td>
                  <span class="pet-type-badge" :class="`type-${item.pet_type}`">
                    {{ item.pet_type === 'dog' ? '🐕 Perro' : '🐱 Gato' }}
                  </span>
                </td>
                <td>
                  <span :class="['status-badge', `status-${item.status}`]">
                    {{ getStatusText(item.status) }}
                  </span>
                </td>
                <td>
                  <span :class="['status-badge', `status-${item.payment_status}`]">
                    {{ getPaymentStatusText(item.payment_status) }}
                  </span>
                </td>
                <td>
                  <router-link
                    v-if="item.id !== sterilization?.id"
                    :to="{ name: 'admin-sterilizations-detail', params: { id: item.id } }"
                    class="btn-icon"
                    title="Ver detalles"
                  >
                    👁️
                  </router-link>
                  <span v-else class="current-label">Actual</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal de Registro de Pago -->
    <div v-if="showPaymentModal" class="modal-overlay" @click="showPaymentModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Registrar Pago</h3>
          <button @click="showPaymentModal = false" class="modal-close">×</button>
        </div>
        <form @submit.prevent="handlePaymentSubmit" class="modal-body">
          <div v-if="paymentError" class="alert alert-error">
            {{ paymentError }}
          </div>

          <div class="form-group">
            <label class="form-label required">Monto</label>
            <div class="input-with-addon">
              <span class="input-addon">$</span>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                step="0.01"
                class="form-input"
                :class="{ 'input-error': paymentErrors.amount }"
                :max="sterilization?.balance"
                placeholder="0.00"
                required
              />
            </div>
            <span v-if="paymentErrors.amount" class="error-message">{{ paymentErrors.amount }}</span>
            <span class="helper-text">Saldo pendiente: ${{ Number(sterilization?.balance).toFixed(2) }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Método de Pago</label>
            <select
              v-model="paymentForm.payment_method"
              class="form-input"
              :class="{ 'input-error': paymentErrors.payment_method }"
              required
            >
              <option value="">Selecciona un método</option>
              <option value="cash">Efectivo</option>
              <option value="transfer">Transferencia</option>
              <option value="card">Tarjeta</option>
              <option value="other">Otro</option>
            </select>
            <span v-if="paymentErrors.payment_method" class="error-message">{{ paymentErrors.payment_method }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Número de Referencia</label>
            <input
              v-model="paymentForm.reference_number"
              type="text"
              class="form-input"
              placeholder="Se generará automáticamente"
            />
            <span class="helper-text">
              Opcional. Si no se proporciona, se generará automáticamente (Formato: PAY-YYYYMMDD-XXXX)
            </span>
          </div>

          <div class="form-group">
            <label class="form-label">Notas</label>
            <textarea
              v-model="paymentForm.notes"
              class="form-textarea"
              placeholder="Información adicional sobre el pago..."
              rows="3"
            ></textarea>
          </div>
        </form>
        <div class="modal-footer">
          <button @click="showPaymentModal = false" class="btn btn-secondary" :disabled="submittingPayment">
            Cancelar
          </button>
          <button @click="handlePaymentSubmit" class="btn btn-primary" :disabled="submittingPayment">
            <span v-if="submittingPayment" class="spinner-small"></span>
            {{ submittingPayment ? 'Registrando...' : 'Registrar Pago' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSterilizationStore } from '@/stores/sterilization'
import { usePaymentStore } from '@/stores/payment'
import { useAuthStore } from '@/stores/auth'
import type { Sterilization, Payment } from '@/types'

const route = useRoute()
const sterilizationStore = useSterilizationStore()
const paymentStore = usePaymentStore()
const authStore = useAuthStore()

// Estado
const loading = ref(false)
const sterilization = ref<Sterilization | null>(null)
const payments = ref<Payment[]>([])
const campaignSterilizations = ref<Sterilization[]>([])
const showPaymentModal = ref(false)
const submittingPayment = ref(false)
const paymentError = ref('')

// Formulario de pago
const paymentForm = ref({
  amount: 0,
  payment_method: '',
  reference_number: '',
  notes: ''
})

const paymentErrors = ref<Record<string, string>>({})

/**
 * Formatear fecha
 */
const formatDate = (date: string | Date): string => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

/**
 * Formatear fecha y hora
 */
const formatDateTime = (datetime?: string): string => {
  if (!datetime) return 'N/A'
  return new Date(datetime).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Obtener texto del estado
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
 * Manejar envío de pago
 */
const handlePaymentSubmit = async () => {
  if (!sterilization.value) return

  paymentErrors.value = {}
  paymentError.value = ''

  // Validación
  if (paymentForm.value.amount <= 0) {
    paymentErrors.value.amount = 'El monto debe ser mayor a 0'
    return
  }

  if (paymentForm.value.amount > sterilization.value.balance) {
    paymentErrors.value.amount = 'El monto no puede ser mayor al saldo pendiente'
    return
  }

  if (!paymentForm.value.payment_method) {
    paymentErrors.value.payment_method = 'Selecciona un método de pago'
    return
  }

  submittingPayment.value = true

  try {
    await paymentStore.createPayment({
      sterilization_id: sterilization.value.id,
      amount: paymentForm.value.amount,
      payment_method: paymentForm.value.payment_method as 'cash' | 'transfer' | 'card' | 'other',
      reference_number: paymentForm.value.reference_number || undefined,
      notes: paymentForm.value.notes || undefined,
      received_by: authStore.user?.id || 1
    })

    // Recargar datos
    await loadData()

    // Cerrar modal y limpiar formulario
    showPaymentModal.value = false
    paymentForm.value = {
      amount: 0,
      payment_method: '',
      reference_number: '',
      notes: ''
    }
  } catch (error) {
    const err = error as { response?: { data?: { message?: string } } }
    paymentError.value = err.response?.data?.message || 'Error al registrar el pago'
  } finally {
    submittingPayment.value = false
  }
}

/**
 * Cargar esterilizaciones de la misma campaña
 */
const loadCampaignSterilizations = async (campaignId: number) => {
  try {
    const response = await sterilizationStore.fetchSterilizations({
      campaign_id: campaignId,
      per_page: 100 // Traer todas las esterilizaciones de la campaña
    })
    
    // Filtrar excluyendo la actual para evitar duplicados en la lista
    campaignSterilizations.value = response.data.filter((s: Sterilization) => s.id !== sterilization.value?.id)
  } catch (error) {
    console.error('Error al cargar esterilizaciones de la campaña:', error)
  }
}

/**
 * Cargar datos
 */
const loadData = async () => {
  const id = Number(route.params.id)
  if (!id) return

  loading.value = true

  try {
    // Cargar esterilización con relaciones
    sterilization.value = await sterilizationStore.fetchSterilization(id)
    
    // Cargar pagos
    payments.value = sterilization.value.payments || []
    
    // Cargar esterilizaciones de la misma campaña
    if (sterilization.value.campaign_id) {
      await loadCampaignSterilizations(sterilization.value.campaign_id)
    }
  } catch (error) {
    console.error('Error al cargar datos:', error)
  } finally {
    loading.value = false
  }
}

/**
 * Inicialización
 */
onMounted(() => {
  loadData()
})
</script>

<style scoped>
.sterilization-detail {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
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
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
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

/* Content Grid */
.content-grid {
  display: grid;
  gap: 1.5rem;
}

/* Info Card */
.info-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a202c;
  margin: 0 0 1.5rem 0;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.info-label {
  font-size: 0.8125rem;
  font-weight: 500;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-value {
  font-size: 0.9375rem;
  color: #1a202c;
}

.info-value.strong {
  font-weight: 600;
  font-size: 1.125rem;
}

/* Badges */
.status-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
  width: fit-content;
}

.status-scheduled {
  background: #dbeafe;
  color: #1e40af;
}

.status-in_progress {
  background: #fef3c7;
  color: #92400e;
}

.status-completed {
  background: #d1fae5;
  color: #065f46;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-partial {
  background: #dbeafe;
  color: #1e40af;
}

.species-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
  width: fit-content;
}

.species-dog {
  background: #fef3c7;
  color: #92400e;
}

.species-cat {
  background: #dbeafe;
  color: #1e40af;
}

/* Payment Summary */
.payment-summary {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.summary-item {
  padding: 1.25rem;
  border-radius: 10px;
  text-align: center;
}

.summary-item.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.summary-item.paid {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.summary-item.balance {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.summary-item.status {
  background: #f7fafc;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.summary-label {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  opacity: 0.9;
}

.summary-value {
  font-size: 1.75rem;
  font-weight: 700;
}

.text-danger {
  color: #ef4444 !important;
}

.payment-action {
  display: flex;
  justify-content: center;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

/* Payment History */
.payment-history {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 2rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.payments-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-item {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1.25rem;
  transition: all 0.2s;
}

.payment-item:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border-color: #cbd5e0;
}

.payment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.payment-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #059669;
}

.payment-date {
  font-size: 0.875rem;
  color: #718096;
}

.payment-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.75rem;
}

.payment-detail {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.payment-detail.full {
  grid-column: 1 / -1;
}

.detail-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-value {
  font-size: 0.9375rem;
  color: #1a202c;
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

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f7fafc;
  color: #4a5568;
}

.btn-secondary:hover:not(:disabled) {
  background: #edf2f7;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
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
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

/* Form Elements */
.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 1rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.form-label.required::after {
  content: ' *';
  color: #ef4444;
}

.form-input,
.form-textarea {
  padding: 0.625rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
  resize: vertical;
  font-family: inherit;
}

.input-error {
  border-color: #ef4444;
}

.error-message {
  color: #ef4444;
  font-size: 0.8125rem;
  margin-top: 0.25rem;
}

.helper-text {
  color: #718096;
  font-size: 0.8125rem;
  margin-top: 0.25rem;
}

.input-with-addon {
  display: flex;
  align-items: stretch;
}

.input-addon {
  display: flex;
  align-items: center;
  padding: 0.625rem;
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-right: none;
  border-radius: 8px 0 0 8px;
  font-weight: 600;
  color: #4a5568;
}

.input-with-addon .form-input {
  border-radius: 0 8px 8px 0;
  flex: 1;
}

.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.alert-error {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Campaign Sterilizations Section */
.campaign-sterilizations-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-top: 1.5rem;
}

.card-header-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.sterilization-count {
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
}

.sterilizations-table {
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

.data-table tbody tr.current-row {
  background: #eff6ff;
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

.current-label {
  color: #059669;
  font-weight: 600;
  font-size: 0.875rem;
  padding: 0.25rem 0.5rem;
  background: #d1fae5;
  border-radius: 6px;
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

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .header-actions {
    width: 100%;
    flex-direction: column;
  }
  
  .header-actions .btn {
    width: 100%;
    justify-content: center;
  }
  
  .summary-grid,
  .info-grid,
  .payment-details {
    grid-template-columns: 1fr;
  }
  
  .sterilizations-table {
    font-size: 0.875rem;
  }
  
  .data-table th,
  .data-table td {
    padding: 0.5rem;
  }
  
  .card-header-flex {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>
