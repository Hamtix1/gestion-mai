<template>
  <div class="sterilization-form">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ isEdit ? 'Editar' : 'Nueva' }} Esterilización</h1>
        <p class="page-description">
          {{ isEdit ? 'Actualiza los datos de la esterilización' : 'Registra una nueva esterilización' }}
        </p>
      </div>
      <router-link :to="{ name: 'admin-sterilizations' }" class="btn btn-secondary">
        ← Volver al listado
      </router-link>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="handleSubmit" class="form-container">
      <!-- Errores generales -->
      <div v-if="generalError" class="alert alert-error">
        {{ generalError }}
      </div>

      <!-- Sección: Selección de Campaña -->
      <div class="form-section">
        <h2 class="section-title">Campaña</h2>
        <div class="form-grid">
          <div class="form-group full-width">
            <label class="form-label required">Campaña</label>
            <select
              v-model="form.campaign_id"
              class="form-input"
              :class="{ 'input-error': errors.campaign_id }"
              required
              @change="updatePrices"
            >
              <option value="">Selecciona una campaña</option>
              <option
                v-for="campaign in availableCampaigns"
                :key="campaign.id"
                :value="campaign.id"
              >
                {{ campaign.name }} - {{ campaign.location }}
              </option>
            </select>
            <span v-if="errors.campaign_id" class="error-message">{{ errors.campaign_id }}</span>
          </div>
        </div>
      </div>

      <!-- Sección: Datos del Propietario -->
      <div class="form-section">
        <h2 class="section-title">Datos del Propietario</h2>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label required">Nombre Completo</label>
            <input
              v-model="form.owner_full_name"
              type="text"
              class="form-input"
              :class="{ 'input-error': errors.owner_full_name }"
              placeholder="Ej: Juan Pérez García"
              required
            />
            <span v-if="errors.owner_full_name" class="error-message">{{ errors.owner_full_name }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Documento de Identidad</label>
            <input
              v-model="form.owner_id_number"
              type="text"
              class="form-input"
              :class="{ 'input-error': errors.owner_id_number }"
              placeholder="Ej: 12345678A"
              required
            />
            <span v-if="errors.owner_id_number" class="error-message">{{ errors.owner_id_number }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Teléfono</label>
            <input
              v-model="form.owner_phone"
              type="tel"
              class="form-input"
              :class="{ 'input-error': errors.owner_phone }"
              placeholder="Ej: 555-1234"
            />
            <span v-if="errors.owner_phone" class="error-message">{{ errors.owner_phone }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input
              v-model="form.owner_email"
              type="email"
              class="form-input"
              :class="{ 'input-error': errors.owner_email }"
              placeholder="correo@ejemplo.com"
            />
            <span v-if="errors.owner_email" class="error-message">{{ errors.owner_email }}</span>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Dirección</label>
            <input
              v-model="form.owner_address"
              type="text"
              class="form-input"
              :class="{ 'input-error': errors.owner_address }"
              placeholder="Calle, número, colonia"
            />
            <span v-if="errors.owner_address" class="error-message">{{ errors.owner_address }}</span>
          </div>
        </div>
      </div>

      <!-- Sección: Datos de la Mascota -->
      <div class="form-section">
        <h2 class="section-title">Datos de la Mascota</h2>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label required">Nombre de la Mascota</label>
            <input
              v-model="form.pet_name"
              type="text"
              class="form-input"
              :class="{ 'input-error': errors.pet_name }"
              placeholder="Ej: Max"
              required
            />
            <span v-if="errors.pet_name" class="error-message">{{ errors.pet_name }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Especie</label>
            <select
              v-model="form.pet_type"
              class="form-input"
              :class="{ 'input-error': errors.pet_type }"
              required
              @change="updatePrices"
            >
              <option value="">Selecciona</option>
              <option value="dog">Perro</option>
              <option value="cat">Gato</option>
            </select>
            <span v-if="errors.pet_type" class="error-message">{{ errors.pet_type }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Sexo</label>
            <select
              v-model="form.pet_gender"
              class="form-input"
              :class="{ 'input-error': errors.pet_gender }"
              required
            >
              <option value="">Selecciona</option>
              <option value="male">Macho</option>
              <option value="female">Hembra</option>
            </select>
            <span v-if="errors.pet_gender" class="error-message">{{ errors.pet_gender }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Raza</label>
            <input
              v-model="form.pet_breed"
              type="text"
              class="form-input"
              :class="{ 'input-error': errors.pet_breed }"
              placeholder="Ej: Labrador, Mestizo"
            />
            <span v-if="errors.pet_breed" class="error-message">{{ errors.pet_breed }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Edad (meses)</label>
            <input
              v-model.number="form.pet_age_months"
              type="number"
              class="form-input"
              :class="{ 'input-error': errors.pet_age_months }"
              placeholder="Ej: 12"
              min="0"
            />
            <span v-if="errors.pet_age_months" class="error-message">{{ errors.pet_age_months }}</span>
            <span v-if="form.pet_age_months" class="helper-text">
              Aproximadamente {{ Math.floor(form.pet_age_months / 12) }} años
            </span>
          </div>

          <div class="form-group">
            <label class="form-label">Peso (kg)</label>
            <input
              v-model.number="form.pet_weight"
              type="number"
              step="0.1"
              class="form-input"
              :class="{ 'input-error': errors.pet_weight }"
              placeholder="Ej: 15.5"
              min="0"
            />
            <span v-if="errors.pet_weight" class="error-message">{{ errors.pet_weight }}</span>
          </div>
        </div>
      </div>

      <!-- Sección: Programación y Precio -->
      <div class="form-section">
        <h2 class="section-title">Programación y Precio</h2>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Fecha Programada</label>
            <input
              v-model="form.scheduled_date"
              type="date"
              class="form-input"
              :class="{ 'input-error': errors.scheduled_date }"
            />
            <span v-if="errors.scheduled_date" class="error-message">{{ errors.scheduled_date }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Hora Programada</label>
            <input
              v-model="form.scheduled_time"
              type="time"
              class="form-input"
              :class="{ 'input-error': errors.scheduled_time }"
            />
            <span v-if="errors.scheduled_time" class="error-message">{{ errors.scheduled_time }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Estado</label>
            <select
              v-model="form.status"
              class="form-input"
              :class="{ 'input-error': errors.status }"
              required
            >
              <option value="scheduled">Programada</option>
              <option value="in_progress">En Progreso</option>
              <option value="completed">Completada</option>
              <option value="cancelled">Cancelada</option>
            </select>
            <span v-if="errors.status" class="error-message">{{ errors.status }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Precio Total</label>
            <div class="input-with-addon">
              <span class="input-addon">$</span>
              <input
                v-model.number="form.total_price"
                type="number"
                step="0.01"
                class="form-input"
                :class="{ 'input-error': errors.total_price }"
                placeholder="0.00"
                required
                min="0"
              />
            </div>
            <span v-if="errors.total_price" class="error-message">{{ errors.total_price }}</span>
            <span v-if="suggestedPrice !== null" class="helper-text">
              Precio sugerido: ${{ Number(suggestedPrice).toFixed(2) }}
            </span>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Notas / Observaciones</label>
            <textarea
              v-model="form.notes"
              class="form-textarea"
              :class="{ 'input-error': errors.notes }"
              placeholder="Información adicional sobre la esterilización..."
              rows="3"
            ></textarea>
            <span v-if="errors.notes" class="error-message">{{ errors.notes }}</span>
          </div>
        </div>
      </div>

      <!-- Pago Inicial (Solo en creación) -->
      <div v-if="!isEdit" class="form-section">
        <h3 class="section-title">
          💰 Pago Inicial (Opcional)
        </h3>
        
        <div class="payment-toggle">
          <label class="toggle-container">
            <input type="checkbox" v-model="includePayment" />
            <span class="toggle-label">¿Desea registrar un pago inicial?</span>
          </label>
        </div>

        <div v-if="includePayment" class="form-grid">
          <div class="form-group">
            <label class="form-label required">Monto del Pago</label>
            <div class="input-with-addon">
              <span class="input-addon">$</span>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                step="0.01"
                class="form-input"
                :class="{ 'input-error': paymentErrors.amount }"
                placeholder="0.00"
                min="0"
                :max="form.total_price"
              />
            </div>
            <span v-if="paymentErrors.amount" class="error-message">{{ paymentErrors.amount }}</span>
            <span class="helper-text">Total a pagar: ${{ Number(form.total_price).toFixed(2) }}</span>
          </div>

          <div class="form-group">
            <label class="form-label required">Método de Pago</label>
            <select
              v-model="paymentForm.payment_method"
              class="form-input"
              :class="{ 'input-error': paymentErrors.payment_method }"
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
            <span class="helper-text">Opcional. Se generará automáticamente si se deja vacío.</span>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Notas del Pago</label>
            <textarea
              v-model="paymentForm.notes"
              class="form-textarea"
              placeholder="Información adicional sobre el pago..."
              rows="2"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Información de pago calculada (Solo en edición) -->
      <div v-if="isEdit && sterilization" class="info-card">
        <h3 class="info-title">Información de Pagos</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Total a Pagar:</span>
            <span class="info-value">${{ Number(form.total_price).toFixed(2) }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Total Pagado:</span>
            <span class="info-value success">${{ Number(sterilization.total_paid).toFixed(2) }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Saldo Pendiente:</span>
            <span class="info-value" :class="Number(sterilization.balance) > 0 ? 'warning' : 'success'">
              ${{ Number(sterilization.balance).toFixed(2) }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Estado de Pago:</span>
            <span :class="['status-badge', `status-${sterilization.payment_status}`]">
              {{ getPaymentStatusText(sterilization.payment_status) }}
            </span>
          </div>
        </div>
        <p class="info-note">
          <strong>Nota:</strong> Los pagos se gestionan desde el detalle de la esterilización.
        </p>
      </div>

      <!-- Acciones del formulario -->
      <div class="form-actions">
        <router-link :to="{ name: 'admin-sterilizations' }" class="btn btn-secondary">
          Cancelar
        </router-link>
        <button type="submit" class="btn btn-primary" :disabled="submitting">
          <span v-if="submitting" class="spinner-small"></span>
          {{ submitting ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Registrar') }} Esterilización
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSterilizationStore } from '@/stores/sterilization'
import { useCampaignStore } from '@/stores/campaign'
import { usePaymentStore } from '@/stores/payment'
import { useAuthStore } from '@/stores/auth'
import type { Sterilization } from '@/types'

const route = useRoute()
const router = useRouter()
const sterilizationStore = useSterilizationStore()
const campaignStore = useCampaignStore()
const paymentStore = usePaymentStore()
const authStore = useAuthStore()

// Estado
const submitting = ref(false)
const generalError = ref('')
const sterilization = ref<Sterilization | null>(null)

// Estado de pago inicial
const includePayment = ref(false)
const paymentForm = ref({
  amount: 0,
  payment_method: '',
  reference_number: '',
  notes: ''
})
const paymentErrors = ref<Record<string, string>>({})

// Determinar si es edición
const isEdit = computed(() => !!route.params.id)

// Formulario
const form = ref({
  campaign_id: '',
  owner_full_name: '',
  owner_id_number: '',
  owner_phone: '',
  owner_email: '',
  owner_address: '',
  pet_name: '',
  pet_type: '' as 'dog' | 'cat' | '',
  pet_gender: '' as 'male' | 'female' | '',
  pet_breed: '',
  pet_age_months: null as number | null,
  pet_weight: null as number | null,
  scheduled_date: '',
  scheduled_time: '',
  status: 'scheduled' as 'scheduled' | 'in_progress' | 'completed' | 'cancelled',
  total_price: 0,
  notes: ''
})

// Errores
const errors = ref<Record<string, string>>({})

// Campañas disponibles (activas y planificadas)
const availableCampaigns = computed(() => 
  campaignStore.campaigns.filter(c => ['active', 'planned'].includes(c.status))
)

// Campaña seleccionada
const selectedCampaign = computed(() => 
  campaignStore.campaigns.find(c => c.id === Number(form.value.campaign_id))
)

// Precio sugerido según especie
const suggestedPrice = computed(() => {
  if (!selectedCampaign.value || !form.value.pet_type) return null
  return form.value.pet_type === 'dog' 
    ? selectedCampaign.value.dog_price 
    : selectedCampaign.value.cat_price
})

/**
 * Actualizar precios cuando cambia campaña o especie
 */
const updatePrices = () => {
  if (suggestedPrice.value !== null && !isEdit.value) {
    form.value.total_price = suggestedPrice.value
  }
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
 * Validar formulario
 */
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.campaign_id) {
    errors.value.campaign_id = 'Selecciona una campaña'
  }

  if (!form.value.owner_full_name.trim()) {
    errors.value.owner_full_name = 'El nombre del propietario es requerido'
  }

  if (!form.value.owner_id_number.trim()) {
    errors.value.owner_id_number = 'El documento de identidad es requerido'
  }

  if (!form.value.pet_name.trim()) {
    errors.value.pet_name = 'El nombre de la mascota es requerido'
  }

  if (!form.value.pet_type) {
    errors.value.pet_type = 'Selecciona la especie'
  }

  if (!form.value.pet_gender) {
    errors.value.pet_gender = 'Selecciona el sexo'
  }

  if (form.value.total_price <= 0) {
    errors.value.total_price = 'El precio debe ser mayor a 0'
  }

  if (form.value.owner_email && !isValidEmail(form.value.owner_email)) {
    errors.value.owner_email = 'Email inválido'
  }

  return Object.keys(errors.value).length === 0
}

/**
 * Validar email
 */
const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

/**
 * Validar formulario de pago
 */
const validatePayment = (): boolean => {
  paymentErrors.value = {}

  if (!paymentForm.value.amount || paymentForm.value.amount <= 0) {
    paymentErrors.value.amount = 'El monto debe ser mayor a 0'
  }

  if (paymentForm.value.amount > form.value.total_price) {
    paymentErrors.value.amount = 'El monto no puede ser mayor al total a pagar'
  }

  if (!paymentForm.value.payment_method) {
    paymentErrors.value.payment_method = 'Selecciona un método de pago'
  }

  return Object.keys(paymentErrors.value).length === 0
}

/**
 * Manejar envío del formulario
 */
const handleSubmit = async () => {
  if (!validateForm()) {
    generalError.value = 'Por favor, corrige los errores en el formulario'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  submitting.value = true
  generalError.value = ''

  try {
    const data: Partial<Sterilization> = {
      campaign_id: Number(form.value.campaign_id),
      owner_full_name: form.value.owner_full_name,
      owner_id_number: form.value.owner_id_number,
      owner_phone: form.value.owner_phone || undefined,
      owner_email: form.value.owner_email || undefined,
      owner_address: form.value.owner_address || undefined,
      pet_name: form.value.pet_name,
      pet_type: form.value.pet_type as 'dog' | 'cat',
      pet_gender: form.value.pet_gender as 'male' | 'female',
      pet_breed: form.value.pet_breed || undefined,
      pet_age_months: form.value.pet_age_months || undefined,
      pet_weight: form.value.pet_weight || undefined,
      scheduled_date: form.value.scheduled_date || undefined,
      scheduled_time: form.value.scheduled_time || undefined,
      status: form.value.status as 'scheduled' | 'in_progress' | 'completed' | 'cancelled',
      total_price: form.value.total_price,
      notes: form.value.notes || undefined,
      registered_by: authStore.user?.id || 1
    }

    let createdSterilizationId: number | null = null

    if (isEdit.value) {
      await sterilizationStore.updateSterilization(Number(route.params.id), data)
      // Redirigir al detalle de la esterilización al editar
      router.push({ name: 'admin-sterilizations-detail', params: { id: route.params.id } })
    } else {
      const response = await sterilizationStore.createSterilization(data)
      createdSterilizationId = response.sterilization.id

      // Si se marcó incluir pago, registrarlo
      if (includePayment.value && validatePayment()) {
        try {
          await paymentStore.createPayment({
            sterilization_id: createdSterilizationId,
            amount: paymentForm.value.amount,
            payment_method: paymentForm.value.payment_method as 'cash' | 'transfer' | 'card' | 'other',
            reference_number: paymentForm.value.reference_number || undefined,
            notes: paymentForm.value.notes || undefined
          })
        } catch (paymentError) {
          console.error('Error al registrar el pago:', paymentError)
          // No bloqueamos el flujo si falla el pago, pero mostramos un mensaje
          generalError.value = 'Esterilización creada, pero hubo un error al registrar el pago'
        }
      }

      // Redirigir al detalle de la campaña
      router.push({ name: 'admin-campaigns-detail', params: { id: data.campaign_id } })
    }
  } catch (error) {
    const err = error as { response?: { data?: { errors?: Record<string, string>; message?: string } } }
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
    generalError.value = err.response?.data?.message || 'Error al guardar la esterilización'
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    submitting.value = false
  }
}

/**
 * Formatear fecha ISO a formato YYYY-MM-DD
 */
const formatDateForInput = (isoDate?: string): string => {
  if (!isoDate) return ''
  // Extraer solo la fecha (YYYY-MM-DD) del formato ISO
  return isoDate.split('T')[0] || ''
}

/**
 * Formatear fecha/hora ISO a formato HH:mm
 */
const formatTimeForInput = (isoDateTime?: string): string => {
  if (!isoDateTime) return ''
  // Extraer la hora y minutos del formato ISO
  const timePart = isoDateTime.split('T')[1]
  if (!timePart) return ''
  const [hours, minutes] = timePart.split(':')
  return `${hours}:${minutes}`
}

/**
 * Cargar datos para edición
 */
const loadSterilization = async () => {
  const id = Number(route.params.id)
  if (!id) return

  try {
    sterilization.value = await sterilizationStore.fetchSterilization(id)
    
    // Llenar formulario con datos existentes
    form.value = {
      campaign_id: String(sterilization.value.campaign_id),
      owner_full_name: sterilization.value.owner_full_name,
      owner_id_number: sterilization.value.owner_id_number,
      owner_phone: sterilization.value.owner_phone || '',
      owner_email: sterilization.value.owner_email || '',
      owner_address: sterilization.value.owner_address || '',
      pet_name: sterilization.value.pet_name,
      pet_type: sterilization.value.pet_type,
      pet_gender: sterilization.value.pet_gender,
      pet_breed: sterilization.value.pet_breed || '',
      pet_age_months: sterilization.value.pet_age_months || null,
      pet_weight: sterilization.value.pet_weight || null,
      scheduled_date: formatDateForInput(sterilization.value.scheduled_date),
      scheduled_time: formatTimeForInput(sterilization.value.scheduled_time),
      status: sterilization.value.status,
      total_price: Number(sterilization.value.total_price),
      notes: sterilization.value.notes || ''
    }
  } catch (error) {
    console.error('Error al cargar esterilización:', error)
    generalError.value = 'Error al cargar los datos de la esterilización'
  }
}

/**
 * Inicialización
 */
onMounted(async () => {
  // Cargar campañas
  await campaignStore.fetchCampaigns({ per_page: 100 })
  
  // Si es edición, cargar datos
  if (isEdit.value) {
    await loadSterilization()
  }
})
</script>

<style scoped>
.sterilization-form {
  padding: 2rem;
  max-width: 1200px;
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
}

/* Formulario */
.form-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a202c;
  margin: 0 0 1.5rem 0;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
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

/* Info Card */
.info-card {
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
}

.info-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1a202c;
  margin: 0 0 1rem 0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
}

.info-label {
  font-size: 0.875rem;
  color: #718096;
}

.info-value {
  font-size: 1rem;
  font-weight: 600;
  color: #1a202c;
}

.info-value.success {
  color: #059669;
}

.info-value.warning {
  color: #f59e0b;
}

.info-note {
  font-size: 0.875rem;
  color: #718096;
  margin: 0;
  padding-top: 0.75rem;
  border-top: 1px solid #e2e8f0;
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

.status-completed {
  background: #d1fae5;
  color: #065f46;
}

/* Alertas */
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

/* Acciones */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1rem;
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

.btn-secondary:hover {
  background: #edf2f7;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Payment Toggle */
.payment-toggle {
  margin-bottom: 1.5rem;
}

.toggle-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
  transition: all 0.2s;
}

.toggle-container:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.toggle-container input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #667eea;
}

.toggle-label {
  font-size: 1rem;
  font-weight: 600;
  color: #475569;
  user-select: none;
}

.input-with-addon {
  display: flex;
  align-items: stretch;
}

.input-addon {
  display: flex;
  align-items: center;
  padding: 0 0.75rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-right: none;
  border-radius: 8px 0 0 8px;
  color: #64748b;
  font-weight: 600;
}

.input-with-addon .form-input {
  border-radius: 0 8px 8px 0;
  flex: 1;
}

/* Responsive */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
