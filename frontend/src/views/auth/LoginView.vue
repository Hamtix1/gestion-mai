<template>
  <div class="login-view">
    <div class="login-card">
      <div class="login-header">
        <h2>Iniciar Sesión</h2>
        <p>Accede al panel de administración</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <!-- Email -->
        <div class="form-group">
          <label for="email" class="form-label">Correo Electrónico</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control"
            :class="{ error: errors.email }"
            placeholder="usuario@ejemplo.com"
            required
          />
          <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password" class="form-label">Contraseña</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            :class="{ error: errors.password }"
            placeholder="••••••••"
            required
          />
          <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
        </div>

        <!-- Error general -->
        <div v-if="authStore.error" class="alert alert-error">
          {{ authStore.error }}
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block" :disabled="authStore.loading">
          <span v-if="!authStore.loading">Iniciar Sesión</span>
          <span v-else>Iniciando...</span>
        </button>
      </form>

      <div class="login-footer">
        <p>¿Necesitas acceso? Contacta al administrador</p>
        <router-link to="/" class="back-link">← Volver al inicio</router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const errors = ref<{ email?: string; password?: string }>({})

/**
 * Manejar login
 */
const handleLogin = async () => {
  errors.value = {}

  // Validaciones básicas
  if (!form.value.email) {
    errors.value.email = 'El correo electrónico es requerido'
    return
  }

  if (!form.value.password) {
    errors.value.password = 'La contraseña es requerida'
    return
  }

  try {
    await authStore.login(form.value.email, form.value.password)
    
    // Redirigir a la ruta solicitada o al dashboard
    const redirect = route.query.redirect as string || '/dashboard'
    router.push(redirect)
  } catch (error) {
    console.error('Error al iniciar sesión:', error)
    // El error ya está manejado en el store
  }
}
</script>

<style scoped>
.login-view {
  padding: 2rem 1rem;
}

.login-card {
  max-width: 450px;
  margin: 0 auto;
}

.login-header {
  text-align: center;
  padding: 2rem 2rem 1.5rem;
  background: white;
  border-radius: 16px 16px 0 0;
}

.login-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 0.5rem;
}

.login-header p {
  color: #666;
  font-size: 1rem;
  margin: 0;
}

.login-form {
  background: white;
  padding: 0 2rem 2rem;
}

.btn-block {
  width: 100%;
  padding: 1rem;
  font-size: 1.05rem;
  font-weight: 600;
  margin-top: 0.5rem;
}

.login-footer {
  background: white;
  padding: 1.5rem 2rem 2rem;
  text-align: center;
  border-radius: 0 0 16px 16px;
}

.login-footer p {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.back-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.back-link:hover {
  color: #764ba2;
}

/* Responsive */
@media (max-width: 480px) {
  .login-header,
  .login-form,
  .login-footer {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .login-header h2 {
    font-size: 1.75rem;
  }
}
</style>
