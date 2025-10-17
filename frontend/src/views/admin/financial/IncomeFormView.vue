<template>
  <div class="income-form-view">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div>
          <h1 class="page-title">{{ isEditMode ? 'Editar Ingreso' : 'Nuevo Ingreso' }}</h1>
          <p class="page-description">{{ isEditMode ? 'Modificar información del ingreso' : 'Registrar un nuevo ingreso' }}</p>
        </div>
        <div class="header-actions">
          <router-link :to="{ name: 'admin-incomes' }" class="btn btn-secondary">
            ← Volver al listado
          </router-link>
        </div>
      </div>
    </div>

    <!-- Formulario -->
    <div class="form-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">Información del Ingreso</h3>
          
          <div class="form-grid">
            <!-- Concepto -->
            <div class="form-group full-width">
              <label class="required">Concepto</label>
              <input
                v-model="form.concept"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.concept }"
                placeholder="Ej: Donación de la empresa X"
                maxlength="255"
              />
              <span v-if="errors.concept" class="error-message">{{ errors.concept }}</span>
            </div>

            <!-- Descripción -->
            <div class="form-group full-width">
              <label>Descripción</label>
              <textarea
                v-model="form.description"
                class="form-control"
                :class="{ 'is-invalid': errors.description }"
                placeholder="Detalles adicionales del ingreso..."
                rows="3"
              ></textarea>
              <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
            </div>

            <!-- Monto -->
            <div class="form-group">
              <label class="required">Monto</label>
              <div class="input-with-addon">
                <span class="addon">$</span>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control"
                  :class="{ 'is-invalid': errors.amount }"
                  placeholder="0.00"
                />
              </div>
              <span v-if="errors.amount" class="error-message">{{ errors.amount }}</span>
            </div>

            <!-- Fuente -->
            <div class="form-group">
              <label class="required">Fuente de Ingreso</label>
              <select
                v-model="form.source"
                class="form-control"
                :class="{ 'is-invalid': errors.source }"
              >
                <option value="">Selecciona una fuente</option>
                <option value="sterilization">Esterilización</option>
                <option value="donation">Donación</option>
                <option value="fundraising">Recaudación de fondos</option>
                <option value="other">Otros</option>
              </select>
              <span v-if="errors.source" class="error-message">{{ errors.source }}</span>
            </div>

            <!-- Fecha -->
            <div class="form-group">
              <label class="required">Fecha del Ingreso</label>
              <input
                v-model="form.income_date"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.income_date }"
              />
              <span v-if="errors.income_date" class="error-message">{{ errors.income_date }}</span>
            </div>

            <!-- Campaña (opcional) -->
            <div class="form-group">
              <label>Campaña</label>
              <select
                v-model="form.campaign_id"
                class="form-control"
                :class="{ 'is-invalid': errors.campaign_id }"
              >
                <option value="">Sin campaña específica</option>
                <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                  {{ campaign.name }}
                </option>
              </select>
              <span v-if="errors.campaign_id" class="error-message">{{ errors.campaign_id }}</span>
            </div>

            <!-- Número de Referencia -->
            <div class="form-group full-width">
              <label>Número de Referencia</label>
              <input
                v-model="form.reference_number"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.reference_number }"
                placeholder="Ej: REF-2025-001"
                maxlength="255"
              />
              <span v-if="errors.reference_number" class="error-message">{{ errors.reference_number }}</span>
              <small class="form-hint">Opcional: Número de transacción, factura o referencia interna</small>
            </div>
          </div>
        </div>

        <!-- Mensajes de error -->
        <div v-if="submitError" class="alert alert-danger">
          <strong>Error:</strong> {{ submitError }}
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
          <router-link :to="{ name: 'admin-incomes' }" class="btn btn-secondary">
            Cancelar
          </router-link>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="btn-spinner"></span>
            {{ submitting ? 'Guardando...' : (isEditMode ? 'Actualizar Ingreso' : 'Registrar Ingreso') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFinancialStore } from '@/stores/financial'
import { useCampaignStore } from '@/stores/campaign'
import { formatDateForInput } from '@/utils/dates'

const router = useRouter()
const route = useRoute()
const financialStore = useFinancialStore()
const campaignStore = useCampaignStore()

// Estado
const submitting = ref(false)
const submitError = ref('')
const loading = ref(false)

// Formulario
const form = ref({
  concept: '',
  description: '',
  amount: '',
  source: '' as 'sterilization' | 'donation' | 'fundraising' | 'other' | '',
  income_date: new Date().toISOString().split('T')[0],
  campaign_id: '',
  reference_number: ''
})

// Errores de validación
const errors = ref<Record<string, string>>({})

// Computed
const campaigns = computed(() => campaignStore.campaigns)
const isEditMode = computed(() => !!route.params.id)

/**
 * Validar formulario
 */
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.concept.trim()) {
    errors.value.concept = 'El concepto es obligatorio'
  }

  if (!form.value.amount || Number(form.value.amount) <= 0) {
    errors.value.amount = 'El monto debe ser mayor a 0'
  }

  if (!form.value.source) {
    errors.value.source = 'La fuente es obligatoria'
  }

  if (!form.value.income_date) {
    errors.value.income_date = 'La fecha es obligatoria'
  }

  return Object.keys(errors.value).length === 0
}

/**
 * Enviar formulario
 */
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  submitting.value = true
  submitError.value = ''

  try {
    const data = {
      concept: form.value.concept.trim(),
      description: form.value.description.trim() || undefined,
      amount: Number(form.value.amount),
      source: form.value.source as 'sterilization' | 'donation' | 'fundraising' | 'other',
      income_date: form.value.income_date,
      campaign_id: form.value.campaign_id ? Number(form.value.campaign_id) : undefined,
      reference_number: form.value.reference_number.trim() || undefined
    }

    if (isEditMode.value) {
      await financialStore.updateIncome(Number(route.params.id), data)
    } else {
      await financialStore.createIncome(data)
    }

    router.push({ name: 'admin-incomes' })
  } catch (error) {
    console.error('Error al guardar ingreso:', error)
    const err = error as { response?: { data?: { message?: string; errors?: Record<string, string> } } }
    submitError.value = err.response?.data?.message || 'Error al guardar el ingreso'
    
    // Mostrar errores de validación del servidor
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    submitting.value = false
  }
}

/**
 * Cargar ingreso para editar
 */
const loadIncome = async () => {
  if (!isEditMode.value) return

  loading.value = true
  try {
    const income = await financialStore.fetchIncome(Number(route.params.id))
    
    form.value = {
      concept: income.concept,
      description: income.description || '',
      amount: income.amount.toString(),
      source: income.source,
      income_date: formatDateForInput(income.income_date),
      campaign_id: income.campaign_id?.toString() || '',
      reference_number: income.reference_number || ''
    }
  } catch (error) {
    console.error('Error al cargar ingreso:', error)
    submitError.value = 'Error al cargar el ingreso'
  } finally {
    loading.value = false
  }
}

/**
 * Cargar datos iniciales
 */
onMounted(async () => {
  await campaignStore.fetchCampaigns()
  if (isEditMode.value) {
    await loadIncome()
  }
})
</script>

<style scoped>
.income-form-view {
  padding: 2rem;
  max-width: 1200px;
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

/* Formulario */
.form-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a202c;
  margin: 0 0 1.5rem 0;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e5e7eb;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-group label.required::after {
  content: ' *';
  color: #ef4444;
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

.form-control.is-invalid {
  border-color: #ef4444;
}

.form-control.is-invalid:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
}

.input-with-addon {
  display: flex;
  align-items: center;
}

.input-with-addon .addon {
  padding: 0.625rem 0.875rem;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-right: none;
  border-radius: 6px 0 0 6px;
  font-weight: 600;
  color: #6b7280;
}

.input-with-addon .form-control {
  border-radius: 0 6px 6px 0;
}

.form-hint {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.error-message {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

/* Alertas */
.alert {
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.alert-danger {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* Acciones */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
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

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .income-form-view {
    padding: 1rem;
  }

  .header-content {
    flex-direction: column;
    gap: 1rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
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
