<template>
  <div class="home-view">
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <h1>Esterilización Responsable y Accesible</h1>
          <p class="hero-subtitle">
            Somos un movimiento animalista dedicado a esterilizar perritos y gaticos a bajo costo.
            Juntos podemos controlar la sobrepoblación y darles una mejor calidad de vida.
          </p>
          <div class="hero-buttons">
            <router-link to="/campaigns" class="btn btn-primary btn-large">
              Ver Campañas Activas
            </router-link>
            <router-link to="/check-status" class="btn btn-outline btn-large">
              Consultar Estado
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">🐾</div>
            <h3>{{ stats.totalSterilizations || 0 }}</h3>
            <p>Esterilizaciones Realizadas</p>
          </div>
          <div class="stat-card">
            <div class="stat-icon">📅</div>
            <h3>{{ stats.activeCampaigns || 0 }}</h3>
            <p>Campañas Activas</p>
          </div>
          <div class="stat-card">
            <div class="stat-icon">💚</div>
            <h3>{{ stats.happyPets || 0 }}</h3>
            <p>Mascotas Felices</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <h2 class="section-title">¿Por qué elegirnos?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">💰</div>
            <h3>Precios Accesibles</h3>
            <p>Ofrecemos esterilizaciones a bajo costo con la opción de pagar en varios aportes.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">👨‍⚕️</div>
            <h3>Personal Calificado</h3>
            <p>Contamos con veterinarios profesionales y con amplia experiencia.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Seguimiento Online</h3>
            <p>Consulta el estado de tu mascota en cualquier momento desde nuestra plataforma.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">🗓️</div>
            <h3>Campañas Frecuentes</h3>
            <p>Realizamos campañas en diferentes ubicaciones para estar más cerca de ti.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>¿Listo para esterilizar a tu mascota?</h2>
          <p>Consulta nuestras campañas activas y agenda tu cita hoy mismo.</p>
          <router-link to="/campaigns" class="btn btn-primary btn-large">
            Ver Campañas Disponibles
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const stats = ref({
  totalSterilizations: 0,
  activeCampaigns: 0,
  happyPets: 0,
})

/**
 * Cargar estadísticas (opcional, si hay endpoint público)
 */
onMounted(() => {
  // Valores de ejemplo - puedes conectar con un endpoint si existe
  stats.value = {
    totalSterilizations: 1250,
    activeCampaigns: 3,
    happyPets: 1180,
  }
})
</script>

<style scoped>
/* Hero Section */
.hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 6rem 0;
  text-align: center;
}

.hero-content h1 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.25rem;
  max-width: 700px;
  margin: 0 auto 2.5rem;
  opacity: 0.95;
  line-height: 1.6;
}

.hero-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-large {
  padding: 1rem 2rem;
  font-size: 1.1rem;
}

.btn-outline {
  background-color: transparent;
  border: 2px solid white;
  color: white;
}

.btn-outline:hover {
  background-color: white;
  color: #667eea;
}

/* Stats Section */
.stats-section {
  padding: 4rem 0;
  background-color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.stat-card {
  text-align: center;
  padding: 2rem;
  border-radius: 12px;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
}

.stat-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.stat-card h3 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #667eea;
  margin-bottom: 0.5rem;
}

.stat-card p {
  color: #555;
  font-size: 1.1rem;
}

/* Features Section */
.features-section {
  padding: 4rem 0;
  background-color: #f5f7fa;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 3rem;
  color: #333;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #333;
}

.feature-card p {
  color: #666;
  line-height: 1.6;
}

/* CTA Section */
.cta-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 5rem 0;
  text-align: center;
}

.cta-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1.1rem;
  }

  .section-title {
    font-size: 2rem;
  }

  .cta-content h2 {
    font-size: 2rem;
  }

  .stat-card h3 {
    font-size: 2rem;
  }
}
</style>
