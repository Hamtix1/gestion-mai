import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import type { User, PaginatedResponse } from '@/types'

/**
 * Store de Usuarios
 * Gestiona la administración de usuarios (solo admin)
 */
export const useUserStore = defineStore('user', () => {
  // Estado
  const users = ref<User[]>([])
  const currentUser = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  /**
   * Obtener lista de usuarios
   */
  const fetchUsers = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<User>>('/users', { params })
      users.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar usuarios'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un usuario por ID
   */
  const fetchUser = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<User>(`/users/${id}`)
      currentUser.value = response.data
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cargar el usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear un nuevo usuario
   */
  const createUser = async (data: Partial<User> & { password: string }) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ message: string; user: User }>('/users', data)
      users.value.unshift(response.data.user)
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al crear el usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar un usuario
   */
  const updateUser = async (id: number, data: Partial<User>) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ message: string; user: User }>(`/users/${id}`, data)
      const index = users.value.findIndex((u) => u.id === id)
      if (index !== -1) {
        users.value[index] = response.data.user
      }
      if (currentUser.value?.id === id) {
        currentUser.value = response.data.user
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar el usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar un usuario
   */
  const deleteUser = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete<{ message: string }>(`/users/${id}`)
      users.value = users.value.filter((u) => u.id !== id)
      if (currentUser.value?.id === id) {
        currentUser.value = null
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al eliminar el usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Cambiar rol de un usuario
   */
  const changeUserRole = async (id: number, roleId: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ message: string; user: User }>(`/users/${id}`, { role_id: roleId })
      const index = users.value.findIndex((u) => u.id === id)
      if (index !== -1) {
        users.value[index] = response.data.user
      }
      if (currentUser.value?.id === id) {
        currentUser.value = response.data.user
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cambiar el rol del usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Cambiar estado activo/inactivo de un usuario
   */
  const toggleUserStatus = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ message: string; user: User }>(`/users/${id}/toggle-status`)
      const index = users.value.findIndex((u) => u.id === id)
      if (index !== -1) {
        users.value[index] = response.data.user
      }
      if (currentUser.value?.id === id) {
        currentUser.value = response.data.user
      }
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al cambiar el estado del usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    // Estado
    users,
    currentUser,
    loading,
    error,
    pagination,

    // Actions
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    changeUserRole,
    toggleUserStatus,
  }
})
