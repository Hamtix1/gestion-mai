# Frontend - Gestión MAI

## 📋 Descripción

Aplicación web frontend desarrollada con Vue.js 3 + TypeScript para la gestión de campañas de esterilización de mascotas.

## 🛠️ Tecnologías

- **Vue.js 3** - Framework progresivo de JavaScript
- **TypeScript** - Tipado estático
- **Pinia** - Gestión de estado
- **Vue Router** - Enrutamiento
- **Axios** - Cliente HTTP
- **Vite** - Build tool

## 📁 Estructura del Proyecto

```
frontend/
├── src/
│   ├── api/
│   │   └── axios.ts                 # Configuración de Axios con interceptores
│   ├── layouts/
│   │   ├── DefaultLayout.vue        # Layout para área autenticada
│   │   ├── PublicLayout.vue         # Layout para visitantes
│   │   └── AuthLayout.vue           # Layout para login
│   ├── router/
│   │   └── index.ts                 # Configuración de rutas + guards
│   ├── stores/
│   │   ├── auth.ts                  # Store de autenticación
│   │   ├── campaign.ts              # Store de campañas
│   │   ├── sterilization.ts         # Store de esterilizaciones
│   │   ├── payment.ts               # Store de pagos
│   │   ├── financial.ts             # Store financiero
│   │   └── user.ts                  # Store de usuarios
│   ├── types/
│   │   └── index.ts                 # Definiciones TypeScript
│   ├── views/
│   │   ├── public/                  # Vistas públicas
│   │   │   ├── HomeView.vue
│   │   │   ├── CampaignsView.vue
│   │   │   ├── CampaignDetailView.vue
│   │   │   └── CheckStatusView.vue
│   │   ├── auth/                    # Vistas de autenticación
│   │   │   └── LoginView.vue
│   │   ├── admin/                   # Vistas administrativas
│   │   │   ├── DashboardView.vue
│   │   │   ├── ProfileView.vue
│   │   │   ├── campaigns/           # Gestión de campañas
│   │   │   ├── sterilizations/      # Gestión de esterilizaciones
│   │   │   ├── payments/            # Gestión de pagos
│   │   │   ├── financial/           # Gestión financiera
│   │   │   └── users/               # Gestión de usuarios
│   │   └── NotFoundView.vue         # Página 404
│   ├── App.vue                      # Componente raíz con layouts dinámicos
│   └── main.ts                      # Punto de entrada
├── .env                             # Variables de entorno
└── package.json                     # Dependencias y scripts
```

## 🚀 Instalación y Configuración

### Prerrequisitos

- Node.js >= 18
- npm o yarn

### Pasos de Instalación

1. **Navegar al directorio del frontend:**
   ```bash
   cd frontend
   ```

2. **Instalar dependencias:**
   ```bash
   npm install
   ```

3. **Configurar variables de entorno:**
   
   El archivo `.env` ya está configurado con:
   ```env
   VITE_API_URL=http://localhost:8000/api
   VITE_APP_NAME=Gestión MAI
   ```

4. **Iniciar servidor de desarrollo:**
   ```bash
   npm run dev
   ```

5. **Compilar para producción:**
   ```bash
   npm run build
   ```

## 📦 Stores de Pinia Disponibles

### 🔐 Auth Store (`useAuthStore`)
Gestiona la autenticación y perfil del usuario.

**Estado:**
- `user` - Usuario autenticado
- `token` - Token de autenticación
- `loading` - Estado de carga
- `error` - Mensajes de error

**Actions:**
- `login(email, password)` - Iniciar sesión
- `logout()` - Cerrar sesión
- `fetchUser()` - Obtener datos del usuario
- `updateProfile(data)` - Actualizar perfil

**Getters:**
- `isAdmin` - Verificar si es admin
- `isSeller` - Verificar si es vendedor
- `isAuthenticated` - Verificar si está autenticado

### 📅 Campaign Store (`useCampaignStore`)
Gestiona las campañas de esterilización.

**Actions:**
- `fetchCampaigns(params)` - Listar campañas (admin)
- `fetchPublicCampaigns(params)` - Listar campañas públicas
- `fetchCampaign(id)` - Obtener campaña por ID
- `createCampaign(data)` - Crear nueva campaña
- `updateCampaign(id, data)` - Actualizar campaña
- `deleteCampaign(id)` - Eliminar campaña

### 🐾 Sterilization Store (`useSterilizationStore`)
Gestiona las esterilizaciones.

**Actions:**
- `fetchSterilizations(params)` - Listar esterilizaciones
- `fetchSterilization(id)` - Obtener esterilización
- `createSterilization(data)` - Registrar esterilización
- `updateSterilization(id, data)` - Actualizar esterilización
- `deleteSterilization(id)` - Eliminar esterilización
- `checkStatus(idNumber)` - Consultar estado público

### 💰 Payment Store (`usePaymentStore`)
Gestiona los pagos.

**Actions:**
- `fetchPayments(params)` - Listar pagos
- `fetchSterilizationPayments(sterilizationId)` - Pagos de una esterilización
- `createPayment(data)` - Registrar pago
- `updatePayment(id, data)` - Actualizar pago
- `deletePayment(id)` - Eliminar pago
- `calculateTotals(payments)` - Calcular totales

### 📊 Financial Store (`useFinancialStore`)
Gestiona ingresos, gastos y reportes.

**Actions de Ingresos:**
- `fetchIncomes(params)` - Listar ingresos
- `createIncome(data)` - Registrar ingreso
- `updateIncome(id, data)` - Actualizar ingreso
- `deleteIncome(id)` - Eliminar ingreso

**Actions de Gastos:**
- `fetchExpenses(params)` - Listar gastos
- `createExpense(data)` - Registrar gasto
- `updateExpense(id, data)` - Actualizar gasto
- `deleteExpense(id)` - Eliminar gasto

**Actions de Reportes:**
- `fetchFinancialReport(params)` - Obtener reporte financiero

### 👥 User Store (`useUserStore`)
Gestiona usuarios del sistema (solo admin).

**Actions:**
- `fetchUsers(params)` - Listar usuarios
- `createUser(data)` - Crear usuario
- `updateUser(id, data)` - Actualizar usuario
- `deleteUser(id)` - Eliminar usuario
- `changeUserRole(id, roleId)` - Cambiar rol

## 🗺️ Rutas Configuradas

### Rutas Públicas
- `/` - Página de inicio
- `/campaigns` - Lista de campañas
- `/campaigns/:id` - Detalle de campaña
- `/check-status` - Consultar estado de esterilización

### Rutas de Autenticación
- `/login` - Inicio de sesión

### Rutas Protegidas (Requieren Autenticación)
- `/dashboard` - Panel principal
- `/profile` - Perfil de usuario

### Rutas Administrativas
- `/admin/campaigns` - Gestión de campañas
- `/admin/sterilizations` - Gestión de esterilizaciones (seller/admin)
- `/admin/payments` - Gestión de pagos (seller/admin)
- `/admin/financial` - Gestión financiera (admin)
- `/admin/users` - Gestión de usuarios (admin)

## 🔒 Protección de Rutas

El router implementa guards de navegación que:
- Verifican autenticación antes de acceder a rutas protegidas
- Verifican roles (admin/seller) para rutas específicas
- Redirigen al login si no hay autenticación
- Redirigen al dashboard si ya está autenticado (para login)

## 🎨 Componentes y Layouts

### Layouts Disponibles

**DefaultLayout** - Para área administrativa
- Header con navegación completa
- Menú de usuario con dropdown
- Footer informativo

**PublicLayout** - Para visitantes
- Header simple con navegación pública
- Menú móvil responsive
- Footer con información de contacto

**AuthLayout** - Para login
- Diseño centrado y minimalista
- Fondo con gradiente
- Card de login

## 📝 Variables de Entorno

```env
VITE_API_URL=http://localhost:8000/api    # URL base del API backend
VITE_APP_NAME=Gestión MAI                  # Nombre de la aplicación
```

## 🔧 Scripts Disponibles

```bash
npm run dev           # Servidor de desarrollo
npm run build         # Compilar para producción
npm run preview       # Previsualizar build de producción
npm run type-check    # Verificar tipos TypeScript
npm run lint          # Ejecutar linter
```

## 📱 Características Implementadas

✅ **Autenticación completa** con token bearer
✅ **6 Stores de Pinia** con TypeScript
✅ **24 rutas** con protección por rol
✅ **3 layouts** dinámicos
✅ **Interceptores Axios** para manejo de tokens
✅ **Vistas públicas** funcionales (Home, Campañas, Consulta)
✅ **Dashboard administrativo** con acciones rápidas
✅ **Diseño responsive** y moderno
✅ **Manejo de errores** centralizado

## 🚧 Próximos Pasos

Las siguientes vistas tienen placeholders y requieren desarrollo completo:

1. **Gestión de Campañas (CRUD completo)**
   - Listado con filtros
   - Formulario de creación/edición
   - Vista de detalle con estadísticas

2. **Gestión de Esterilizaciones**
   - Formulario de registro
   - Listado con búsqueda
   - Detalle con historial de pagos

3. **Gestión de Pagos**
   - Registrar pagos parciales
   - Historial de pagos
   - Comprobantes

4. **Reportes Financieros**
   - Dashboard con gráficas
   - Filtros por fecha y campaña
   - Exportación a PDF/Excel

5. **Gestión de Usuarios**
   - CRUD completo
   - Asignación de roles
   - Activación/desactivación

## 🤝 Contribución

Para contribuir al desarrollo:

1. Revisar la estructura de stores existentes como ejemplo
2. Seguir el patrón de diseño establecido en las vistas
3. Utilizar TypeScript correctamente con las interfaces en `types/index.ts`
4. Implementar manejo de errores consistente
5. Mantener diseño responsive

## 📄 Licencia

© 2025 Gestión MAI. Todos los derechos reservados.
