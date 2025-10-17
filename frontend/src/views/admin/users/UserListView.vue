<template>
  <div class="user-list-view">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div>
          <h1 class="page-title">Gestión de Usuarios</h1>
          <p class="page-description">Administra los usuarios del sistema</p>
        </div>
        <div class="header-actions">
          <router-link :to="{ name: 'admin-users-create' }" class="btn btn-primary">
            + Nuevo Usuario
          </router-link>
        </div>
      </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Total Usuarios</div>
          <div class="stat-value">{{ totalUsers }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
          <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Administradores</div>
          <div class="stat-value">{{ adminUsers }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
          <i class="fas fa-user-md"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Veterinarios</div>
          <div class="stat-value">{{ vetUsers }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Usuarios Activos</div>
          <div class="stat-value">{{ activeUsers }}</div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Nombre o email..."
          />
        </div>

        <div class="filter-group">
          <label>Rol</label>
          <select v-model="filters.role" class="form-control">
            <option value="">Todos los roles</option>
            <option value="admin">Administrador</option>
            <option value="veterinarian">Veterinario</option>
            <option value="receptionist">Recepcionista</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Estado</label>
          <select v-model="filters.is_active" class="form-control">
            <option value="">Todos</option>
            <option value="true">Activos</option>
            <option value="false">Inactivos</option>
          </select>
        </div>

        <div class="filter-actions">
          <button @click="applyFilters" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
          </button>
          <button @click="clearFilters" class="btn btn-secondary">
            <i class="fas fa-times"></i> Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando usuarios...</p>
      </div>

      <div v-else-if="users.length === 0" class="empty-state">
        <i class="fas fa-users"></i>
        <h3>No hay usuarios</h3>
        <p>No se encontraron usuarios con los filtros seleccionados</p>
        <router-link :to="{ name: 'admin-users-create' }" class="btn btn-primary">
          + Crear Primer Usuario
        </router-link>
      </div>

      <div v-else class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Fecha Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>#{{ user.id }}</td>
              <td>
                <div class="user-info">
                  <div class="user-avatar">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="user-name">{{ user.name }}</span>
                </div>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ user.phone || '-' }}</td>
              <td>
                <span class="badge" :class="`badge-${getRoleBadgeClass(user.role.name)}`">
                  {{ user.role.display_name }}
                </span>
              </td>
              <td>
                <button
                  @click="toggleUserStatus(user)"
                  class="status-toggle"
                  :class="{ active: user.is_active }"
                  :disabled="user.id === currentUser?.id"
                >
                  <i :class="user.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                  {{ user.is_active ? 'Activo' : 'Inactivo' }}
                </button>
              </td>
              <td>{{ formatDate(user.created_at) }}</td>
              <td>
                <div class="action-buttons">
                  <router-link
                    :to="{ name: 'admin-users-edit', params: { id: user.id } }"
                    class="btn-icon btn-edit"
                    title="Editar"
                  >
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button
                    v-if="user.id !== currentUser?.id"
                    @click="confirmDelete(user)"
                    class="btn-icon btn-delete"
                    title="Eliminar"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="pagination-btn"
        >
          <i class="fas fa-chevron-left"></i>
        </button>

        <span class="pagination-info">
          Página {{ pagination.current_page }} de {{ pagination.last_page }}
          ({{ pagination.total }} usuarios)
        </span>

        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="pagination-btn"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="showDeleteModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Confirmar Eliminación</h3>
          <button @click="showDeleteModal = false" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que deseas eliminar al usuario <strong>{{ userToDelete?.name }}</strong>?</p>
          <p class="warning-text">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button @click="showDeleteModal = false" class="btn btn-secondary">
            Cancelar
          </button>
          <button @click="deleteUser" class="btn btn-danger">
            <i class="fas fa-trash"></i> Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { useAuthStore } from '@/stores/auth'
import type { User } from '@/types'

const userStore = useUserStore()
const authStore = useAuthStore()

// Estado
const loading = ref(false)
const showDeleteModal = ref(false)
const userToDelete = ref<User | null>(null)

// Filtros
const filters = ref({
  search: '',
  role: '',
  is_active: ''
})

// Computed
const users = computed(() => userStore.users)
const pagination = computed(() => userStore.pagination)
const currentUser = computed(() => authStore.user)

// Estadísticas
const totalUsers = computed(() => pagination.value.total || users.value.length)
const adminUsers = computed(() => users.value.filter(u => u.role.name === 'admin').length)
const vetUsers = computed(() => users.value.filter(u => u.role.name === 'veterinarian').length)
const activeUsers = computed(() => users.value.filter(u => u.is_active).length)

/**
 * Obtener clase de badge según rol
 */
const getRoleBadgeClass = (role: string): string => {
  const classes: Record<string, string> = {
    admin: 'danger',
    veterinarian: 'primary',
    receptionist: 'info'
  }
  return classes[role] || 'secondary'
}

/**
 * Formatear fecha
 */
const formatDate = (date: string | undefined): string => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

/**
 * Aplicar filtros
 */
const applyFilters = async () => {
  loading.value = true
  try {
    await userStore.fetchUsers({
      search: filters.value.search || undefined,
      role: filters.value.role || undefined,
      is_active: filters.value.is_active || undefined
    })
  } catch (error) {
    console.error('Error al aplicar filtros:', error)
  } finally {
    loading.value = false
  }
}

/**
 * Limpiar filtros
 */
const clearFilters = () => {
  filters.value = {
    search: '',
    role: '',
    is_active: ''
  }
  applyFilters()
}

/**
 * Cambiar página
 */
const changePage = (page: number) => {
  if (page < 1 || page > pagination.value.last_page) return
  
  userStore.fetchUsers({
    page,
    search: filters.value.search || undefined,
    role: filters.value.role || undefined,
    is_active: filters.value.is_active || undefined
  })
}

/**
 * Cambiar estado del usuario
 */
const toggleUserStatus = async (user: User) => {
  if (user.id === currentUser.value?.id) {
    alert('No puedes cambiar tu propio estado')
    return
  }

  try {
    await userStore.toggleUserStatus(user.id)
    await applyFilters()
  } catch (error) {
    console.error('Error al cambiar estado:', error)
    alert('Error al cambiar el estado del usuario')
  }
}

/**
 * Confirmar eliminación
 */
const confirmDelete = (user: User) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

/**
 * Eliminar usuario
 */
const deleteUser = async () => {
  if (!userToDelete.value) return

  try {
    await userStore.deleteUser(userToDelete.value.id)
    showDeleteModal.value = false
    userToDelete.value = null
    await applyFilters()
  } catch (error) {
    console.error('Error al eliminar usuario:', error)
    alert('Error al eliminar el usuario')
  }
}

/**
 * Cargar datos iniciales
 */
onMounted(() => {
  applyFilters()
})
</script>

<style scoped>
.user-list-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

/* Header */
.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
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

/* Estadísticas */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
}

/* Filtros */
.filters-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-control {
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1f2937;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
}

/* Tabla */
.table-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.loading-state, .empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state i {
  font-size: 4rem;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  color: #1a202c;
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  color: #718096;
  margin: 0 0 1.5rem 0;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.data-table th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
  color: #1f2937;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-name {
  font-weight: 500;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.badge-primary {
  background: #dbeafe;
  color: #1e40af;
}

.badge-info {
  background: #ddd6fe;
  color: #5b21b6;
}

.status-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  color: #6b7280;
}

.status-toggle:hover:not(:disabled) {
  border-color: #9ca3af;
}

.status-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.status-toggle.active {
  background: #dcfce7;
  border-color: #22c55e;
  color: #166534;
}

.status-toggle.active i {
  color: #22c55e;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-edit {
  background: #dbeafe;
  color: #1e40af;
}

.btn-edit:hover {
  background: #bfdbfe;
}

.btn-delete {
  background: #fee2e2;
  color: #991b1b;
}

.btn-delete:hover {
  background: #fecaca;
}

/* Paginación */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.pagination-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

/* Botones */
.btn {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1a202c;
}

.btn-close {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f3f4f6;
}

.modal-body {
  padding: 1.5rem;
}

.modal-body p {
  margin: 0 0 1rem 0;
  color: #374151;
}

.warning-text {
  color: #dc2626;
  font-weight: 500;
  font-size: 0.875rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
  .user-list-view {
    padding: 1rem;
  }

  .header-content {
    flex-direction: column;
    gap: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .filter-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .table-container {
    overflow-x: auto;
  }

  .data-table {
    min-width: 800px;
  }
}
</style>
