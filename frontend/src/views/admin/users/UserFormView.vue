<template>
  <div class="user-form-view">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div>
          <h1 class="page-title">{{ isEditMode ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
          <p class="page-description">{{ isEditMode ? 'Modificar información del usuario' : 'Registrar un nuevo usuario en el sistema' }}</p>
        </div>
        <div class="header-actions">
          <router-link :to="{ name: 'admin-users' }" class="btn btn-secondary">
            ← Volver al listado
          </router-link>
        </div>
      </div>
    </div>

    <!-- Formulario -->
    <div class="form-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">Información Personal</h3>
          
          <div class="form-grid">
            <!-- Nombre -->
            <div class="form-group full-width">
              <label class="required">Nombre Completo</label>
              <input
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Ej: Juan Pérez"
                maxlength="255"
              />
              <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label class="required">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': errors.email }"
                placeholder="usuario@ejemplo.com"
                maxlength="255"
              />
              <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
            </div>

            <!-- Teléfono -->
            <div class="form-group">
              <label>Teléfono</label>
              <input
                v-model="form.phone"
                type="tel"
                class="form-control"
                :class="{ 'is-invalid': errors.phone }"
                placeholder="Ej: +57 300 123 4567"
                maxlength="20"
              />
              <span v-if="errors.phone" class="error-message">{{ errors.phone }}</span>
              <small class="form-hint">Opcional: Número de contacto</small>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">Credenciales</h3>
          
          <div class="form-grid">
            <!-- Contraseña -->
            <div class="form-group">
              <label :class="{ required: !isEditMode }">Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': errors.password }"
                :placeholder="isEditMode ? 'Dejar en blanco para mantener la actual' : 'Mínimo 8 caracteres'"
                minlength="8"
              />
              <span v-if="errors.password" class="error-message">{{ errors.password }}</span>
              <small v-if="isEditMode" class="form-hint">Dejar en blanco si no deseas cambiar la contraseña</small>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="form-group" v-if="!isEditMode || form.password">
              <label :class="{ required: !isEditMode }">Confirmar Contraseña</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': errors.password_confirmation }"
                placeholder="Repetir contraseña"
                minlength="8"
              />
              <span v-if="errors.password_confirmation" class="error-message">{{ errors.password_confirmation }}</span>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">Rol y Permisos</h3>
          
          <div class="form-grid">
            <!-- Rol -->
            <div class="form-group">
              <label class="required">Rol</label>
              <select
                v-model="form.role_id"
                class="form-control"
                :class="{ 'is-invalid': errors.role_id }"
              >
                <option value="">Selecciona un rol</option>
                <option value="1">Administrador</option>
                <option value="2">Vendedor</option>
                <option value="3">Usuario</option>
              </select>
              <span v-if="errors.role_id" class="error-message">{{ errors.role_id }}</span>
              <small class="form-hint">
                <strong>Admin:</strong> Acceso completo | 
                <strong>Vendedor:</strong> Registra esterilizaciones y pagos | 
                <strong>Usuario:</strong> Acceso limitado
              </small>
            </div>

            <!-- Estado Activo -->
            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="form-checkbox"
                />
                <span class="checkbox-text">
                  Usuario Activo
                  <small>El usuario podrá iniciar sesión en el sistema</small>
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- Mensajes de error -->
        <div v-if="submitError" class="alert alert-danger">
          <strong>Error:</strong> {{ submitError }}
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
          <router-link :to="{ name: 'admin-users' }" class="btn btn-secondary">
            Cancelar
          </router-link>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="btn-spinner"></span>
            {{ submitting ? 'Guardando...' : (isEditMode ? 'Actualizar Usuario' : 'Crear Usuario') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

// Estado
const submitting = ref(false)
const submitError = ref('')
const loading = ref(false)

// Formulario
const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role_id: '',
  is_active: true
})

// Errores de validación
const errors = ref<Record<string, string>>({})

// Computed
const isEditMode = computed(() => !!route.params.id)

/**
 * Validar formulario
 */
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.name.trim()) {
    errors.value.name = 'El nombre es obligatorio'
  }

  if (!form.value.email.trim()) {
    errors.value.email = 'El email es obligatorio'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'El email no es válido'
  }

  if (!isEditMode.value && !form.value.password) {
    errors.value.password = 'La contraseña es obligatoria'
  }

  if (form.value.password && form.value.password.length < 8) {
    errors.value.password = 'La contraseña debe tener al menos 8 caracteres'
  }

  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Las contraseñas no coinciden'
  }

  if (!form.value.role_id) {
    errors.value.role_id = 'El rol es obligatorio'
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
    const data: {
      name: string
      email: string
      phone?: string
      role_id: number
      is_active: boolean
      password?: string
    } = {
      name: form.value.name.trim(),
      email: form.value.email.trim(),
      phone: form.value.phone.trim() || undefined,
      role_id: Number(form.value.role_id),
      is_active: form.value.is_active
    }

    // Solo enviar password si se ingresó
    if (form.value.password) {
      data.password = form.value.password
    }

    if (isEditMode.value) {
      await userStore.updateUser(Number(route.params.id), data)
    } else {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      await userStore.createUser(data as any)
    }

    router.push({ name: 'admin-users' })
  } catch (error) {
    console.error('Error al guardar usuario:', error)
    const err = error as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    submitError.value = err.response?.data?.message || 'Error al guardar el usuario'
    
    // Mostrar errores de validación del servidor
    if (err.response?.data?.errors) {
      const serverErrors: Record<string, string> = {}
      Object.entries(err.response.data.errors).forEach(([key, messages]) => {
        serverErrors[key] = Array.isArray(messages) ? (messages[0] || '') : (messages || '')
      })
      errors.value = serverErrors
    }
  } finally {
    submitting.value = false
  }
}

/**
 * Cargar usuario para editar
 */
const loadUser = async () => {
  if (!isEditMode.value) return

  loading.value = true
  try {
    const user = await userStore.fetchUser(Number(route.params.id))
    
    form.value = {
      name: user.name,
      email: user.email,
      phone: user.phone || '',
      password: '',
      password_confirmation: '',
      role_id: user.role.id.toString(),
      is_active: user.is_active
    }
  } catch (error) {
    console.error('Error al cargar usuario:', error)
    submitError.value = 'Error al cargar el usuario'
  } finally {
    loading.value = false
  }
}

/**
 * Cargar datos iniciales
 */
onMounted(async () => {
  if (isEditMode.value) {
    await loadUser()
  }
})
</script>

<style scoped>
.user-form-view {
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

.form-hint {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
  display: block;
}

.error-message {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

/* Checkbox */
.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
  margin: 0;
}

.form-checkbox {
  width: 20px;
  height: 20px;
  cursor: pointer;
  margin-top: 2px;
}

.checkbox-text {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.checkbox-text small {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 400;
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
  .user-form-view {
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
