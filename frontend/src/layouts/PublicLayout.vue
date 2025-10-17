<template>
  <div class="layout-public">
    <!-- Header / Navbar -->
    <header class="header">
      <div class="container">
        <div class="header-content">
          <div class="logo">
            <router-link to="/">
              <h1>{{ appName }}</h1>
              <p class="tagline">Esterilización responsable y accesible</p>
            </router-link>
          </div>

          <nav class="nav-menu">
            <router-link to="/" class="nav-link">Inicio</router-link>
            <router-link to="/campaigns" class="nav-link">Campañas</router-link>
            <router-link to="/check-status" class="nav-link">Consultar Estado</router-link>
            <router-link to="/login" class="nav-link nav-link-login">Iniciar Sesión</router-link>
          </nav>

          <!-- Mobile Menu Button -->
          <button class="mobile-menu-button" @click="toggleMobileMenu">
            <span v-if="!showMobileMenu">☰</span>
            <span v-else>✕</span>
          </button>
        </div>

        <!-- Mobile Menu -->
        <nav v-if="showMobileMenu" class="mobile-menu">
          <router-link to="/" class="mobile-link" @click="closeMobileMenu">Inicio</router-link>
          <router-link to="/campaigns" class="mobile-link" @click="closeMobileMenu">Campañas</router-link>
          <router-link to="/check-status" class="mobile-link" @click="closeMobileMenu">Consultar Estado</router-link>
          <router-link to="/login" class="mobile-link mobile-link-login" @click="closeMobileMenu">Iniciar Sesión</router-link>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>{{ appName }}</h3>
            <p>Movimiento animalista dedicado a esterilizaciones a bajo costo para perritos y gaticos.</p>
          </div>

          <div class="footer-section">
            <h4>Enlaces Rápidos</h4>
            <ul>
              <li><router-link to="/">Inicio</router-link></li>
              <li><router-link to="/campaigns">Campañas</router-link></li>
              <li><router-link to="/check-status">Consultar Estado</router-link></li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Contacto</h4>
            <p>📧 info@gestionmai.com</p>
            <p>📱 +57 300 123 4567</p>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; {{ currentYear }} {{ appName }}. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const appName = import.meta.env.VITE_APP_NAME || 'Gestión MAI'
const currentYear = new Date().getFullYear()
const showMobileMenu = ref(false)

const toggleMobileMenu = () => {
  showMobileMenu.value = !showMobileMenu.value
}

const closeMobileMenu = () => {
  showMobileMenu.value = false
}
</script>

<style scoped>
.layout-public {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 0;
}

.logo h1 {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.logo .tagline {
  font-size: 0.9rem;
  margin: 0.25rem 0 0;
  opacity: 0.9;
}

.logo a {
  text-decoration: none;
  color: inherit;
}

/* Navigation */
.nav-menu {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.2s;
  font-weight: 500;
}

.nav-link:hover,
.nav-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.2);
}

.nav-link-login {
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.nav-link-login:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

/* Mobile Menu */
.mobile-menu-button {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.mobile-menu {
  display: none;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  margin-top: 1rem;
}

.mobile-link {
  color: white;
  text-decoration: none;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  transition: background-color 0.2s;
  font-weight: 500;
}

.mobile-link:hover,
.mobile-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.2);
}

.mobile-link-login {
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Main Content */
.main-content {
  flex: 1;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Footer */
.footer {
  background-color: #2d3748;
  color: white;
  padding: 3rem 0 1rem;
  margin-top: 4rem;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-section h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: white;
}

.footer-section h4 {
  font-size: 1.1rem;
  margin-bottom: 1rem;
  color: white;
}

.footer-section p {
  margin: 0.5rem 0;
  opacity: 0.9;
  line-height: 1.6;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li {
  margin: 0.5rem 0;
}

.footer-section a {
  color: white;
  text-decoration: none;
  opacity: 0.9;
  transition: opacity 0.2s;
}

.footer-section a:hover {
  opacity: 1;
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 1.5rem;
  text-align: center;
}

.footer-bottom p {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-menu {
    display: none;
  }

  .mobile-menu-button {
    display: block;
  }

  .mobile-menu {
    display: flex;
  }

  .logo h1 {
    font-size: 1.4rem;
  }

  .logo .tagline {
    font-size: 0.8rem;
  }

  .footer-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}
</style>
