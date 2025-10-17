import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import type { Income, Expense, PaginatedResponse } from '@/types'

/**
 * Interface para reporte financiero
 */
interface FinancialReport {
  total_incomes: number
  total_expenses: number
  balance: number
  incomes_by_type: Record<string, number>
  expenses_by_category: Record<string, number>
}

/**
 * Store Financiero
 * Gestiona ingresos, gastos y reportes financieros
 */
export const useFinancialStore = defineStore('financial', () => {
  // Estado - Ingresos
  const incomes = ref<Income[]>([])
  const currentIncome = ref<Income | null>(null)
  const incomesLoading = ref(false)
  const incomesError = ref<string | null>(null)
  const incomesPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  // Estado - Gastos
  const expenses = ref<Expense[]>([])
  const currentExpense = ref<Expense | null>(null)
  const expensesLoading = ref(false)
  const expensesError = ref<string | null>(null)
  const expensesPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  // Estado - Reportes
  const report = ref<FinancialReport | null>(null)
  const reportLoading = ref(false)
  const reportError = ref<string | null>(null)

  /**
   * INGRESOS - Obtener lista
   */
  const fetchIncomes = async (params = {}) => {
    incomesLoading.value = true
    incomesError.value = null

    try {
      const response = await api.get<PaginatedResponse<Income>>('/financial/incomes', { params })
      incomes.value = response.data.data
      incomesPagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar ingresos'
      incomesError.value = errorMessage
      throw err
    } finally {
      incomesLoading.value = false
    }
  }

  /**
   * INGRESOS - Obtener uno por ID
   */
  const fetchIncome = async (id: number) => {
    incomesLoading.value = true
    incomesError.value = null

    try {
      const response = await api.get<Income>(`/financial/incomes/${id}`)
      currentIncome.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar el ingreso'
      incomesError.value = errorMessage
      throw err
    } finally {
      incomesLoading.value = false
    }
  }

  /**
   * INGRESOS - Crear nuevo
   */
  const createIncome = async (data: Partial<Income>) => {
    incomesLoading.value = true
    incomesError.value = null

    try {
      const response = await api.post<{ message: string; income: Income }>('/financial/incomes', data)
      incomes.value.unshift(response.data.income)
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al crear el ingreso'
      incomesError.value = errorMessage
      throw err
    } finally {
      incomesLoading.value = false
    }
  }

  /**
   * INGRESOS - Actualizar
   */
  const updateIncome = async (id: number, data: Partial<Income>) => {
    incomesLoading.value = true
    incomesError.value = null

    try {
      const response = await api.put<{ message: string; income: Income }>(`/financial/incomes/${id}`, data)
      const index = incomes.value.findIndex((i) => i.id === id)
      if (index !== -1) {
        incomes.value[index] = response.data.income
      }
      if (currentIncome.value?.id === id) {
        currentIncome.value = response.data.income
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar el ingreso'
      incomesError.value = errorMessage
      throw err
    } finally {
      incomesLoading.value = false
    }
  }

  /**
   * INGRESOS - Eliminar
   */
  const deleteIncome = async (id: number) => {
    incomesLoading.value = true
    incomesError.value = null

    try {
      const response = await api.delete<{ message: string }>(`/financial/incomes/${id}`)
      incomes.value = incomes.value.filter((i) => i.id !== id)
      if (currentIncome.value?.id === id) {
        currentIncome.value = null
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al eliminar el ingreso'
      incomesError.value = errorMessage
      throw err
    } finally {
      incomesLoading.value = false
    }
  }

  /**
   * GASTOS - Obtener lista
   */
  const fetchExpenses = async (params = {}) => {
    expensesLoading.value = true
    expensesError.value = null

    try {
      const response = await api.get<PaginatedResponse<Expense>>('/financial/expenses', { params })
      expenses.value = response.data.data
      expensesPagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar gastos'
      expensesError.value = errorMessage
      throw err
    } finally {
      expensesLoading.value = false
    }
  }

  /**
   * GASTOS - Obtener uno por ID
   */
  const fetchExpense = async (id: number) => {
    expensesLoading.value = true
    expensesError.value = null

    try {
      const response = await api.get<Expense>(`/financial/expenses/${id}`)
      currentExpense.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar el gasto'
      expensesError.value = errorMessage
      throw err
    } finally {
      expensesLoading.value = false
    }
  }

  /**
   * GASTOS - Crear nuevo
   */
  const createExpense = async (data: Partial<Expense>) => {
    expensesLoading.value = true
    expensesError.value = null

    try {
      const response = await api.post<{ message: string; expense: Expense }>('/financial/expenses', data)
      expenses.value.unshift(response.data.expense)
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al crear el gasto'
      expensesError.value = errorMessage
      throw err
    } finally {
      expensesLoading.value = false
    }
  }

  /**
   * GASTOS - Actualizar
   */
  const updateExpense = async (id: number, data: Partial<Expense>) => {
    expensesLoading.value = true
    expensesError.value = null

    try {
      const response = await api.put<{ message: string; expense: Expense }>(`/financial/expenses/${id}`, data)
      const index = expenses.value.findIndex((e) => e.id === id)
      if (index !== -1) {
        expenses.value[index] = response.data.expense
      }
      if (currentExpense.value?.id === id) {
        currentExpense.value = response.data.expense
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar el gasto'
      expensesError.value = errorMessage
      throw err
    } finally {
      expensesLoading.value = false
    }
  }

  /**
   * GASTOS - Eliminar
   */
  const deleteExpense = async (id: number) => {
    expensesLoading.value = true
    expensesError.value = null

    try {
      const response = await api.delete<{ message: string }>(`/financial/expenses/${id}`)
      expenses.value = expenses.value.filter((e) => e.id !== id)
      if (currentExpense.value?.id === id) {
        currentExpense.value = null
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al eliminar el gasto'
      expensesError.value = errorMessage
      throw err
    } finally {
      expensesLoading.value = false
    }
  }

  /**
   * REPORTES - Obtener reporte financiero
   */
  const fetchFinancialReport = async (params: { campaign_id?: number; start_date?: string; end_date?: string } = {}) => {
    reportLoading.value = true
    reportError.value = null

    try {
      const response = await api.get<FinancialReport>('/financial/report', { params })
      report.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar el reporte financiero'
      reportError.value = errorMessage
      throw err
    } finally {
      reportLoading.value = false
    }
  }

  return {
    // Estado - Ingresos
    incomes,
    currentIncome,
    incomesLoading,
    incomesError,
    incomesPagination,

    // Estado - Gastos
    expenses,
    currentExpense,
    expensesLoading,
    expensesError,
    expensesPagination,

    // Estado - Reportes
    report,
    reportLoading,
    reportError,

    // Actions - Ingresos
    fetchIncomes,
    fetchIncome,
    createIncome,
    updateIncome,
    deleteIncome,

    // Actions - Gastos
    fetchExpenses,
    fetchExpense,
    createExpense,
    updateExpense,
    deleteExpense,

    // Actions - Reportes
    fetchFinancialReport,
  }
})
