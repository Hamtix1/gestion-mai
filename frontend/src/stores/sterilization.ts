import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import type { Sterilization, PaginatedResponse } from '@/types'

/**
 * Store de Esterilizaciones
 * Gestiona el estado de las esterilizaciones
 */
export const useSterilizationStore = defineStore('sterilization', () => {
  // Estado
  const sterilizations = ref<Sterilization[]>([])
  const currentSterilization = ref<Sterilization | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  /**
   * Obtener lista de esterilizaciones
   */
  const fetchSterilizations = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<Sterilization>>('/sterilizations', { params })
      sterilizations.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar esterilizaciones'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una esterilización por ID
   */
  const fetchSterilization = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<Sterilization>(`/sterilizations/${id}`)
      currentSterilization.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar la esterilización'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear una nueva esterilización
   */
  const createSterilization = async (data: Partial<Sterilization>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ message: string; sterilization: Sterilization }>('/sterilizations', data)
      sterilizations.value.unshift(response.data.sterilization)
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al crear la esterilización'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar una esterilización
   */
  const updateSterilization = async (id: number, data: Partial<Sterilization>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ message: string; sterilization: Sterilization }>(`/sterilizations/${id}`, data)
      const index = sterilizations.value.findIndex((s) => s.id === id)
      if (index !== -1) {
        sterilizations.value[index] = response.data.sterilization
      }
      if (currentSterilization.value?.id === id) {
        currentSterilization.value = response.data.sterilization
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar la esterilización'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar una esterilización
   */
  const deleteSterilization = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete<{ message: string }>(`/sterilizations/${id}`)
      sterilizations.value = sterilizations.value.filter((s) => s.id !== id)
      if (currentSterilization.value?.id === id) {
        currentSterilization.value = null
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al eliminar la esterilización'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Consultar estado de esterilización por cédula (público)
   */
  const checkStatus = async (idNumber: string) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ message: string; sterilizations: Sterilization[] }>('/public/check-sterilization', {
        owner_id_number: idNumber,
      })
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al consultar esterilizaciones'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    // Estado
    sterilizations,
    currentSterilization,
    loading,
    error,
    pagination,

    // Actions
    fetchSterilizations,
    fetchSterilization,
    createSterilization,
    updateSterilization,
    deleteSterilization,
    checkStatus,
  }
})
