import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import type { Campaign, PaginatedResponse } from '@/types'

/**
 * Store de Campañas
 * Gestiona el estado de las campañas de esterilización
 */
export const useCampaignStore = defineStore('campaign', () => {
  // Estado
  const campaigns = ref<Campaign[]>([])
  const currentCampaign = ref<Campaign | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  /**
   * Obtener lista de campañas
   */
  const fetchCampaigns = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<Campaign>>('/campaigns', { params })
      campaigns.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar campañas'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una campaña por ID
   */
  const fetchCampaign = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<{ campaign: Campaign; stats: Record<string, number> }>(`/campaigns/${id}`)
      currentCampaign.value = response.data.campaign
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar la campaña'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear una nueva campaña
   */
  const createCampaign = async (data: Partial<Campaign>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ message: string; campaign: Campaign }>('/campaigns', data)
      campaigns.value.unshift(response.data.campaign)
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al crear la campaña'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar una campaña
   */
  const updateCampaign = async (id: number, data: Partial<Campaign>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ message: string; campaign: Campaign }>(`/campaigns/${id}`, data)
      const index = campaigns.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        campaigns.value[index] = response.data.campaign
      }
      if (currentCampaign.value?.id === id) {
        currentCampaign.value = response.data.campaign
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar la campaña'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar una campaña
   */
  const deleteCampaign = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete<{ message: string }>(`/campaigns/${id}`)
      campaigns.value = campaigns.value.filter((c) => c.id !== id)
      if (currentCampaign.value?.id === id) {
        currentCampaign.value = null
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al eliminar la campaña'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener campañas públicas (sin autenticación)
   */
  const fetchPublicCampaigns = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<Campaign>>('/public/campaigns', { params })
      campaigns.value = response.data.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar campañas'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    // Estado
    campaigns,
    currentCampaign,
    loading,
    error,
    pagination,

    // Actions
    fetchCampaigns,
    fetchCampaign,
    createCampaign,
    updateCampaign,
    deleteCampaign,
    fetchPublicCampaigns,
  }
})
