import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import type { Payment, PaginatedResponse } from '@/types'

/**
 * Store de Pagos
 * Gestiona el estado de los pagos
 */
export const usePaymentStore = defineStore('payment', () => {
  // Estado
  const payments = ref<Payment[]>([])
  const currentPayment = ref<Payment | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  /**
   * Obtener lista de pagos
   */
  const fetchPayments = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<Payment>>('/payments', { params })
      payments.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar pagos'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener pagos de una esterilización específica
   */
  const fetchSterilizationPayments = async (sterilizationId: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<Payment[]>(`/sterilizations/${sterilizationId}/payments`)
      payments.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar pagos de la esterilización'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un pago por ID
   */
  const fetchPayment = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<Payment>(`/payments/${id}`)
      currentPayment.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar el pago'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Registrar un nuevo pago
   */
  const createPayment = async (data: Partial<Payment>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ message: string; payment: Payment }>('/payments', data)
      payments.value.unshift(response.data.payment)
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al registrar el pago'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar un pago
   */
  const updatePayment = async (id: number, data: Partial<Payment>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ message: string; payment: Payment }>(`/payments/${id}`, data)
      const index = payments.value.findIndex((p) => p.id === id)
      if (index !== -1) {
        payments.value[index] = response.data.payment
      }
      if (currentPayment.value?.id === id) {
        currentPayment.value = response.data.payment
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar el pago'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar un pago
   */
  const deletePayment = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete<{ message: string }>(`/payments/${id}`)
      payments.value = payments.value.filter((p) => p.id !== id)
      if (currentPayment.value?.id === id) {
        currentPayment.value = null
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al eliminar el pago'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Calcular totales de pagos
   */
  const calculateTotals = (paymentList: Payment[]) => {
    return paymentList.reduce((total, payment) => total + Number(payment.amount), 0)
  }

  return {
    // Estado
    payments,
    currentPayment,
    loading,
    error,
    pagination,

    // Actions
    fetchPayments,
    fetchSterilizationPayments,
    fetchPayment,
    createPayment,
    updatePayment,
    deletePayment,
    calculateTotals,
  }
})
