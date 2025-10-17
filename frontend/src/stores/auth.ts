import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'
import type { User, LoginResponse, UpdateProfileData } from '@/types'

/**
 * Store de Autenticación
 * Gestiona el estado de autenticación del usuario
 */
export const useAuthStore = defineStore('auth', () => {
  // Estado
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role?.name === 'admin')
  const isSeller = computed(() => user.value?.role?.name === 'seller' || isAdmin.value)
  const userRole = computed(() => user.value?.role?.name || null)

  /**
   * Inicializar el usuario desde localStorage
   */
  const initUser = () => {
    const storedUser = localStorage.getItem('auth_user')
    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
      } catch (e) {
        console.error('Error parsing user data:', e)
        logout()
      }
    }
  }

  /**
   * Login
   */
  const login = async (email: string, password: string): Promise<LoginResponse> => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<LoginResponse>('/auth/login', { email, password })
      
      token.value = response.data.token
      user.value = response.data.user

      // Guardar en localStorage
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('auth_user', JSON.stringify(response.data.user))

      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al iniciar sesión'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout
   */
  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      console.error('Error during logout:', err)
    } finally {
      // Limpiar estado
      token.value = null
      user.value = null
      
      // Limpiar localStorage
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  /**
   * Obtener datos del usuario autenticado
   */
  const fetchUser = async (): Promise<User> => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<{ user: User }>('/auth/me')
      user.value = response.data.user
      localStorage.setItem('auth_user', JSON.stringify(response.data.user))
      return response.data.user
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al obtener datos del usuario'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar perfil del usuario
   */
  const updateProfile = async (data: UpdateProfileData): Promise<{ message: string; user: User }> => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ message: string; user: User }>('/auth/profile', data)
      user.value = response.data.user
      localStorage.setItem('auth_user', JSON.stringify(response.data.user))
      return response.data
    } catch (err) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Error al actualizar perfil'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  // Inicializar usuario al cargar el store
  initUser()

  return {
    // Estado
    user,
    token,
    loading,
    error,
    
    // Getters
    isAuthenticated,
    isAdmin,
    isSeller,
    userRole,
    
    // Actions
    login,
    logout,
    fetchUser,
    updateProfile,
    initUser,
  }
})
