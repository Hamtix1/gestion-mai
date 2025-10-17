<template>
  <div class="financial-dashboard">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard Financiero</h1>
        <p class="page-description">Resumen de ingresos, gastos y balance general</p>
      </div>
      <div class="header-actions">
        <router-link :to="{ name: 'admin-incomes' }" class="btn btn-primary">
          💰 Ingresos
        </router-link>
        <router-link :to="{ name: 'admin-expenses' }" class="btn btn-secondary">
          📊 Gastos
        </router-link>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando datos financieros...</p>
    </div>

    <!-- Contenido -->
    <div v-else>
      <!-- Resumen Principal -->
      <div class="summary-grid">
        <div class="summary-card income">
          <div class="summary-icon">💰</div>
          <div class="summary-content">
            <div class="summary-label">Total Ingresos</div>
            <div class="summary-value">${{ Number(totalIncome).toFixed(2) }}</div>
            <div class="summary-subtitle">{{ incomeCount }} registros</div>
          </div>
        </div>

        <div class="summary-card expense">
          <div class="summary-icon">💸</div>
          <div class="summary-content">
            <div class="summary-label">Total Gastos</div>
            <div class="summary-value">${{ Number(totalExpense).toFixed(2) }}</div>
            <div class="summary-subtitle">{{ expenseCount }} registros</div>
          </div>
        </div>

        <div class="summary-card balance" :class="{ negative: balance < 0 }">
          <div class="summary-icon">{{ balance >= 0 ? '✅' : '⚠️' }}</div>
          <div class="summary-content">
            <div class="summary-label">Balance</div>
            <div class="summary-value">${{ Number(balance).toFixed(2) }}</div>
            <div class="summary-subtitle">
              {{ balance >= 0 ? 'Superávit' : 'Déficit' }}
            </div>
          </div>
        </div>

        <div class="summary-card payments">
          <div class="summary-icon">💳</div>
          <div class="summary-content">
            <div class="summary-label">Pagos Recibidos</div>
            <div class="summary-value">${{ Number(totalPayments).toFixed(2) }}</div>
            <div class="summary-subtitle">{{ paymentCount }} pagos</div>
          </div>
        </div>
      </div>

      <!-- Desglose de Ingresos por Fuente -->
      <div class="section-grid">
        <div class="info-card">
          <h2 class="card-title">Ingresos por Fuente</h2>
          <div class="breakdown-list">
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon sterilization">🐾</span>
                Esterilizaciones
              </div>
              <div class="breakdown-value">${{ Number(incomeBySource.sterilization).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon donation">❤️</span>
                Donaciones
              </div>
              <div class="breakdown-value">${{ Number(incomeBySource.donation).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon fundraising">🎯</span>
                Recaudación de fondos
              </div>
              <div class="breakdown-value">${{ Number(incomeBySource.fundraising).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon other">💼</span>
                Otros
              </div>
              <div class="breakdown-value">${{ Number(incomeBySource.other).toFixed(2) }}</div>
            </div>
          </div>
        </div>

        <!-- Desglose de Gastos por Categoría -->
        <div class="info-card">
          <h2 class="card-title">Gastos por Categoría</h2>
          <div class="breakdown-list">
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon medical">💊</span>
                Médicos
              </div>
              <div class="breakdown-value">${{ Number(expenseByCategory.medical).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon transportation">🚗</span>
                Transporte
              </div>
              <div class="breakdown-value">${{ Number(expenseByCategory.transportation).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon supplies">📦</span>
                Suministros
              </div>
              <div class="breakdown-value">${{ Number(expenseByCategory.supplies).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon marketing">📢</span>
                Marketing
              </div>
              <div class="breakdown-value">${{ Number(expenseByCategory.marketing).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon administrative">🏢</span>
                Administrativos
              </div>
              <div class="breakdown-value">${{ Number(expenseByCategory.administrative).toFixed(2) }}</div>
            </div>
            <div class="breakdown-item">
              <div class="breakdown-label">
                <span class="breakdown-icon other">💼</span>
                Otros
              </div>
              <div class="breakdown-value">${{ Number(expenseByCategory.other).toFixed(2) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Últimos Movimientos -->
      <div class="movements-section">
        <h2 class="section-title">Últimos Movimientos</h2>
        
        <div class="movements-grid">
          <!-- Últimos Ingresos -->
          <div class="movement-card">
            <div class="movement-header">
              <h3>Últimos Ingresos</h3>
              <router-link :to="{ name: 'admin-incomes' }" class="view-all-link">
                Ver todos →
              </router-link>
            </div>
            <div v-if="recentIncomes.length === 0" class="empty-message">
              No hay ingresos registrados
            </div>
            <div v-else class="movement-list">
              <div v-for="income in recentIncomes" :key="income.id" class="movement-item">
                <div class="movement-info">
                  <div class="movement-concept">{{ income.concept }}</div>
                  <div class="movement-date">{{ formatDate(income.income_date) }}</div>
                </div>
                <div class="movement-amount income">+${{ Number(income.amount).toFixed(2) }}</div>
              </div>
            </div>
          </div>

          <!-- Últimos Gastos -->
          <div class="movement-card">
            <div class="movement-header">
              <h3>Últimos Gastos</h3>
              <router-link :to="{ name: 'admin-expenses' }" class="view-all-link">
                Ver todos →
              </router-link>
            </div>
            <div v-if="recentExpenses.length === 0" class="empty-message">
              No hay gastos registrados
            </div>
            <div v-else class="movement-list">
              <div v-for="expense in recentExpenses" :key="expense.id" class="movement-item">
                <div class="movement-info">
                  <div class="movement-concept">{{ expense.concept }}</div>
                  <div class="movement-date">{{ formatDate(expense.expense_date) }}</div>
                </div>
                <div class="movement-amount expense">-${{ Number(expense.amount).toFixed(2) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Acciones Rápidas -->
      <div class="quick-actions">
        <h2 class="section-title">Acciones Rápidas</h2>
        <div class="actions-grid">
          <router-link :to="{ name: 'admin-incomes' }" class="action-card">
            <div class="action-icon">➕</div>
            <div class="action-title">Registrar Ingreso</div>
          </router-link>
          <router-link :to="{ name: 'admin-expenses' }" class="action-card">
            <div class="action-icon">➖</div>
            <div class="action-title">Registrar Gasto</div>
          </router-link>
          <router-link :to="{ name: 'admin-payments' }" class="action-card">
            <div class="action-icon">💳</div>
            <div class="action-title">Ver Pagos</div>
          </router-link>
          <router-link :to="{ name: 'admin-campaigns' }" class="action-card">
            <div class="action-icon">🎯</div>
            <div class="action-title">Ver Campañas</div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFinancialStore } from '@/stores/financial'
import { usePaymentStore } from '@/stores/payment'


const financialStore = useFinancialStore()
const paymentStore = usePaymentStore()

// Estado
const loading = ref(false)

// Computed
const incomes = computed(() => financialStore.incomes)
const expenses = computed(() => financialStore.expenses)
const payments = computed(() => paymentStore.payments)

// Totales
const totalIncome = computed(() => 
  incomes.value.reduce((sum, i) => sum + Number(i.amount), 0)
)

const totalExpense = computed(() => 
  expenses.value.reduce((sum, e) => sum + Number(e.amount), 0)
)

const totalPayments = computed(() => 
  payments.value.reduce((sum, p) => sum + Number(p.amount), 0)
)

const balance = computed(() => totalIncome.value - totalExpense.value)

// Contadores
const incomeCount = computed(() => incomes.value.length)
const expenseCount = computed(() => expenses.value.length)
const paymentCount = computed(() => payments.value.length)

// Desglose por fuente de ingresos
const incomeBySource = computed(() => {
  const sources = {
    sterilization: 0,
    donation: 0,
    fundraising: 0,
    other: 0
  }
  
  incomes.value.forEach(income => {
    if (income.source in sources) {
      sources[income.source as keyof typeof sources] += Number(income.amount)
    }
  })
  
  return sources
})

// Desglose por categoría de gastos
const expenseByCategory = computed(() => {
  const categories = {
    medical: 0,
    transportation: 0,
    supplies: 0,
    marketing: 0,
    administrative: 0,
    other: 0
  }
  
  expenses.value.forEach(expense => {
    if (expense.category in categories) {
      categories[expense.category as keyof typeof categories] += Number(expense.amount)
    }
  })
  
  return categories
})

// Últimos movimientos
const recentIncomes = computed(() => 
  [...incomes.value]
    .sort((a, b) => new Date(b.income_date).getTime() - new Date(a.income_date).getTime())
    .slice(0, 5)
)

const recentExpenses = computed(() => 
  [...expenses.value]
    .sort((a, b) => new Date(b.expense_date).getTime() - new Date(a.expense_date).getTime())
    .slice(0, 5)
)

/**
 * Formatear fecha
 */
const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

/**
 * Cargar datos
 */
onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      financialStore.fetchIncomes(),
      financialStore.fetchExpenses(),
      paymentStore.fetchPayments()
    ])
  } catch (error) {
    console.error('Error al cargar datos:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.financial-dashboard {
  padding: 2rem;
  max-width: 1600px;
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

/* Summary Grid */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.summary-card.income {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.summary-card.expense {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.summary-card.balance {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.summary-card.balance.negative {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.summary-card.payments {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
}

.summary-icon {
  font-size: 3rem;
  width: 70px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
}

.summary-content {
  flex: 1;
}

.summary-label {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  opacity: 0.9;
}

.summary-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.summary-subtitle {
  font-size: 0.875rem;
  opacity: 0.8;
}

/* Section Grid */
.section-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

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

.breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 8px;
  transition: all 0.2s;
}

.breakdown-item:hover {
  background: #edf2f7;
}

.breakdown-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9375rem;
  color: #4a5568;
}

.breakdown-icon {
  font-size: 1.5rem;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.breakdown-icon.sterilization,
.breakdown-icon.donation,
.breakdown-icon.medical {
  background: #d1fae5;
}

.breakdown-icon.fundraising,
.breakdown-icon.transportation {
  background: #dbeafe;
}

.breakdown-icon.supplies,
.breakdown-icon.marketing {
  background: #e0e7ff;
}

.breakdown-icon.administrative,
.breakdown-icon.other {
  background: #f3f4f6;
}

.breakdown-value {
  font-size: 1rem;
  font-weight: 700;
  color: #1a202c;
}

/* Movements Section */
.movements-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0 0 1.5rem 0;
}

.movements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.movement-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.movement-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.movement-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1a202c;
  margin: 0;
}

.view-all-link {
  font-size: 0.875rem;
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.view-all-link:hover {
  text-decoration: underline;
}

.empty-message {
  text-align: center;
  padding: 2rem;
  color: #718096;
  font-size: 0.875rem;
}

.movement-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.movement-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 8px;
  transition: all 0.2s;
}

.movement-item:hover {
  background: #edf2f7;
}

.movement-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.movement-concept {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1a202c;
}

.movement-date {
  font-size: 0.8125rem;
  color: #718096;
}

.movement-amount {
  font-size: 1rem;
  font-weight: 700;
}

.movement-amount.income {
  color: #059669;
}

.movement-amount.expense {
  color: #ef4444;
}

/* Quick Actions */
.quick-actions {
  margin-bottom: 2rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  transition: all 0.2s;
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.action-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7fafc;
  border-radius: 12px;
}

.action-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #1a202c;
  text-align: center;
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

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #f7fafc;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #edf2f7;
}

/* Responsive */
@media (max-width: 1024px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .section-grid,
  .movements-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .header-actions {
    width: 100%;
  }
  
  .header-actions .btn {
    flex: 1;
    justify-content: center;
  }
  
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
