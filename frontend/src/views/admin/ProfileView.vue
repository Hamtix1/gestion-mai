<template>
  <div class="profile-view">
    <div class="page-header">
      <h1>Mi Perfil</h1>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="authStore.user" class="profile-content">
          <div class="profile-info">
            <div class="form-group">
              <label class="form-label">Nombre</label>
              <p class="form-value">{{ authStore.user.name }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Correo Electrónico</label>
              <p class="form-value">{{ authStore.user.email }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Teléfono</label>
              <p class="form-value">{{ authStore.user.phone || 'No especificado' }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Rol</label>
              <p class="form-value">
                <span class="badge badge-info">{{ authStore.user.role.display_name }}</span>
              </p>
            </div>
          </div>

          <div class="profile-actions">
            <p class="text-muted">La edición de perfil estará disponible próximamente</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<style scoped>
.profile-view {
  padding: 2rem 0;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
}

.profile-content {
  max-width: 600px;
}

.profile-info {
  margin-bottom: 2rem;
}

.form-value {
  font-size: 1.05rem;
  color: #333;
  margin: 0;
  padding: 0.75rem 0;
}

.profile-actions {
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
}
</style>
