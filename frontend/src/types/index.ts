/**
 * Tipos de datos para la aplicación
 */

export interface Role {
  id: number
  name: string
  display_name: string
  description?: string
}

export interface User {
  id: number
  name: string
  email: string
  phone?: string
  role: Role
  is_active: boolean
  created_at?: string
  updated_at?: string
}

export interface Campaign {
  id: number
  name: string
  description?: string
  start_date: string
  end_date: string
  location: string
  available_slots: number
  used_slots: number
  dog_price: number
  cat_price: number
  status: 'planned' | 'active' | 'completed' | 'cancelled'
  is_visible_to_public: boolean
  created_by: number
  creator?: User
  created_at?: string
  updated_at?: string
}

export interface Sterilization {
  id: number
  campaign_id: number
  campaign?: Campaign
  owner_full_name: string
  owner_id_number: string
  owner_phone?: string
  owner_email?: string
  owner_address?: string
  pet_name: string
  pet_type: 'dog' | 'cat'
  pet_gender: 'male' | 'female'
  pet_breed?: string
  pet_age_months?: number
  pet_weight?: number
  total_price: number
  total_paid: number
  balance: number
  payment_status: 'pending' | 'partial' | 'completed'
  scheduled_date?: string
  scheduled_time?: string
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled'
  notes?: string
  registered_by: number | User // Puede ser el ID o el objeto User cuando viene con relación
  registeredBy?: User // Alias para compatibilidad
  payments?: Payment[]
  created_at?: string
  updated_at?: string
}

export interface Payment {
  id: number
  sterilization_id: number
  sterilization?: Sterilization
  amount: number
  payment_method: 'cash' | 'transfer' | 'card' | 'other'
  reference_number?: string
  notes?: string
  received_by: number | User // Puede ser el ID o el objeto User cuando viene con relación
  receivedBy?: User // Alias para compatibilidad
  created_at?: string
  updated_at?: string
}

export interface Income {
  id: number
  campaign_id?: number
  campaign?: Campaign
  concept: string
  description?: string
  amount: number
  source: 'sterilization' | 'donation' | 'fundraising' | 'other'
  income_date: string
  reference_number?: string
  registered_by: number
  registeredBy?: User
  created_at?: string
  updated_at?: string
}

export interface Expense {
  id: number
  campaign_id?: number
  campaign?: Campaign
  concept: string
  description?: string
  amount: number
  category: 'medical' | 'transportation' | 'supplies' | 'marketing' | 'administrative' | 'other'
  expense_date: string
  invoice_number?: string
  supplier?: string
  registered_by: number
  registeredBy?: User
  created_at?: string
  updated_at?: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface LoginResponse {
  message: string
  user: User
  token: string
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface UpdateProfileData {
  name?: string
  email?: string
  phone?: string
  current_password?: string
  password?: string
  password_confirmation?: string
}
