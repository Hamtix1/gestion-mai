<template>
  <div class="layout-auth">
    <div class="auth-container">
      <div class="auth-header">
        <router-link to="/" class="logo-link">
          <h1>{{ appName }}</h1>
        </router-link>
      </div>

      <div class="auth-content">
        <router-view />
      </div>

      <div class="auth-footer">
        <p>&copy; {{ currentYear }} {{ appName }}. Todos los derechos reservados.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const appName = import.meta.env.VITE_APP_NAME || 'Gestión MAI'
const currentYear = new Date().getFullYear()
</script>

<style scoped>
.layout-auth {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.auth-container {
  width: 100%;
  max-width: 450px;
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-link {
  text-decoration: none;
}

.auth-header h1 {
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.auth-content {
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.auth-footer {
  text-align: center;
  margin-top: 2rem;
  color: white;
  font-size: 0.9rem;
}

.auth-footer p {
  margin: 0;
  opacity: 0.9;
}

/* Responsive */
@media (max-width: 480px) {
  .auth-header h1 {
    font-size: 2rem;
  }
}
</style>
