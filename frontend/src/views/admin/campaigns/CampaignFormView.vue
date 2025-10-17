<template>
  <div class="campaign-form-view">
    <!-- Header -->
    <div class="page-header">
      <div>
        <router-link to="/admin/campaigns" class="back-link">← Volver</router-link>
        <h1>{{ isEdit ? 'Editar' : 'Nueva' }} Campaña</h1>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading && isEdit" class="loading-container">
      <div class="spinner"></div>
      <p>Cargando campaña...</p>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="campaign-form">
      <div class="card">
        <div class="card-header">
          <h2>Información Básica</h2>
        </div>
        <div class="card-body">
          <div class="form-grid">
            <!-- Name -->
            <div class="form-group col-span-2">
              <label for="name" class="form-label">Nombre de la Campaña *</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ error: errors.name }"
                placeholder="Ej: Campaña de Esterilización - Febrero 2025"
                required
              />
              <span v-if="errors.name" class="form-error">{{ errors.name }}</span>
            </div>

            <!-- Location -->
            <div class="form-group col-span-2">
              <label for="location" class="form-label">Ubicación *</label>
              <input
                id="location"
                v-model="form.location"
                type="text"
                class="form-control"
                :class="{ error: errors.location }"
                placeholder="Ej: Parque Central, Calle 123 #45-67"
                required
              />
              <span v-if="errors.location" class="form-error">{{ errors.location }}</span>
            </div>

            <!-- Description -->
            <div class="form-group col-span-2">
              <label for="description" class="form-label">Descripción</label>
              <textarea
                id="description"
                v-model="form.description"
                class="form-control"
                rows="4"
                placeholder="Describe los detalles de la campaña..."
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Fechas y Cupos</h2>
        </div>
        <div class="card-body">
          <div class="form-grid">
            <!-- Start Date -->
            <div class="form-group">
              <label for="start_date" class="form-label">Fecha de Inicio *</label>
              <input
                id="start_date"
                v-model="form.start_date"
                type="date"
                class="form-control"
                :class="{ error: errors.start_date }"
                required
              />
              <span v-if="errors.start_date" class="form-error">{{ errors.start_date }}</span>
            </div>

            <!-- End Date -->
            <div class="form-group">
              <label for="end_date" class="form-label">Fecha de Fin *</label>
              <input
                id="end_date"
                v-model="form.end_date"
                type="date"
                class="form-control"
                :class="{ error: errors.end_date }"
                required
              />
              <span v-if="errors.end_date" class="form-error">{{ errors.end_date }}</span>
            </div>

            <!-- Available Slots -->
            <div class="form-group">
              <label for="available_slots" class="form-label">Cupos Disponibles *</label>
              <input
                id="available_slots"
                v-model.number="form.available_slots"
                type="number"
                min="1"
                class="form-control"
                :class="{ error: errors.available_slots }"
                placeholder="50"
                required
              />
              <span v-if="errors.available_slots" class="form-error">{{ errors.available_slots }}</span>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label for="status" class="form-label">Estado *</label>
              <select
                id="status"
                v-model="form.status"
                class="form-control"
                :class="{ error: errors.status }"
                required
              >
                <option value="planned">Planeada</option>
                <option value="active">Activa</option>
                <option value="completed">Completada</option>
                <option value="cancelled">Cancelada</option>
              </select>
              <span v-if="errors.status" class="form-error">{{ errors.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Precios</h2>
        </div>
        <div class="card-body">
          <div class="form-grid">
            <!-- Dog Price -->
            <div class="form-group">
              <label for="dog_price" class="form-label">🐕 Precio Perros *</label>
              <div class="input-group">
                <span class="input-prefix">$</span>
                <input
                  id="dog_price"
                  v-model.number="form.dog_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="form-control"
                  :class="{ error: errors.dog_price }"
                  placeholder="50000"
                  required
                />
              </div>
              <span v-if="errors.dog_price" class="form-error">{{ errors.dog_price }}</span>
            </div>

            <!-- Cat Price -->
            <div class="form-group">
              <label for="cat_price" class="form-label">🐱 Precio Gatos *</label>
              <div class="input-group">
                <span class="input-prefix">$</span>
                <input
                  id="cat_price"
                  v-model.number="form.cat_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="form-control"
                  :class="{ error: errors.cat_price }"
                  placeholder="40000"
                  required
                />
              </div>
              <span v-if="errors.cat_price" class="form-error">{{ errors.cat_price }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Configuración</h2>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="checkbox-label">
              <input
                v-model="form.is_visible_to_public"
                type="checkbox"
                class="checkbox"
              />
              <span>👁️ Visible al público</span>
            </label>
            <small class="form-help">Si está marcado, los visitantes podrán ver esta campaña en la página pública</small>
          </div>
        </div>
      </div>

      <!-- Error general -->
      <div v-if="campaignStore.error" class="alert alert-error">
        {{ campaignStore.error }}
      </div>

      <!-- Actions -->
      <div class="form-actions">
        <router-link to="/admin/campaigns" class="btn btn-secondary">
          Cancelar
        </router-link>
        <button type="submit" class="btn btn-primary" :disabled="campaignStore.loading">
          {{ campaignStore.loading ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Crear') }} Campaña
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'
import { useAuthStore } from '@/stores/auth'
import { formatDateForInput } from '@/utils/dates'
import type { Campaign } from '@/types'

const route = useRoute()
const router = useRouter()
const campaignStore = useCampaignStore()
const authStore = useAuthStore()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)

const form = ref({
  name: '',
  description: '',
  location: '',
  start_date: '',
  end_date: '',
  available_slots: 50,
  dog_price: 0,
  cat_price: 0,
  status: 'planned' as Campaign['status'],
  is_visible_to_public: true,
})

const errors = ref<Record<string, string>>({})

/**
 * Cargar campaña si es edición
 */
const loadCampaign = async () => {
  if (!isEdit.value) return

  loading.value = true
  try {
    const id = Number(route.params.id)
    const response = await campaignStore.fetchCampaign(id)
    const campaign = response.campaign
    
    // Llenar formulario
    form.value = {
      name: campaign.name,
      description: campaign.description || '',
      location: campaign.location,
      start_date: formatDateForInput(campaign.start_date),
      end_date: formatDateForInput(campaign.end_date),
      available_slots: campaign.available_slots,
      dog_price: campaign.dog_price,
      cat_price: campaign.cat_price,
      status: campaign.status,
      is_visible_to_public: campaign.is_visible_to_public,
    }
  } catch (error) {
    console.error('Error al cargar campaña:', error)
    router.push('/admin/campaigns')
  } finally {
    loading.value = false
  }
}

/**
 * Validar formulario
 */
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.name.trim()) {
    errors.value.name = 'El nombre es requerido'
  }

  if (!form.value.location.trim()) {
    errors.value.location = 'La ubicación es requerida'
  }

  if (!form.value.start_date) {
    errors.value.start_date = 'La fecha de inicio es requerida'
  }

  if (!form.value.end_date) {
    errors.value.end_date = 'La fecha de fin es requerida'
  }

  if (form.value.start_date && form.value.end_date && form.value.start_date > form.value.end_date) {
    errors.value.end_date = 'La fecha de fin debe ser posterior a la fecha de inicio'
  }

  if (form.value.available_slots < 1) {
    errors.value.available_slots = 'Debe haber al menos 1 cupo disponible'
  }

  if (form.value.dog_price < 0) {
    errors.value.dog_price = 'El precio no puede ser negativo'
  }

  if (form.value.cat_price < 0) {
    errors.value.cat_price = 'El precio no puede ser negativo'
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

  try {
    if (isEdit.value) {
      const id = Number(route.params.id)
      await campaignStore.updateCampaign(id, form.value)
    } else {
      await campaignStore.createCampaign(form.value)
    }

    router.push('/admin/campaigns')
  } catch (error) {
    console.error('Error al guardar campaña:', error)
  }
}

onMounted(() => {
  if (!authStore.isAdmin) {
    router.push('/dashboard')
    return
  }

  loadCampaign()
})
</script>

<style scoped>
.campaign-form-view {
  padding: 2rem 0;
}

.page-header {
  margin-bottom: 2rem;
}

.back-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: inline-block;
  transition: color 0.2s;
}

.back-link:hover {
  color: #764ba2;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

/* Loading */
.loading-container {
  text-align: center;
  padding: 4rem 0;
}

.loading-container .spinner {
  margin: 0 auto 1rem;
}

/* Form */
.campaign-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.card-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.col-span-2 {
  grid-column: span 2;
}

.input-group {
  display: flex;
  align-items: center;
}

.input-prefix {
  background-color: #f0f0f0;
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-right: none;
  border-radius: 8px 0 0 8px;
  color: #666;
  font-weight: 500;
}

.input-group .form-control {
  border-radius: 0 8px 8px 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-size: 1.05rem;
  font-weight: 500;
}

.checkbox {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.form-help {
  display: block;
  color: #666;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

/* Actions */
.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .col-span-2 {
    grid-column: span 1;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
