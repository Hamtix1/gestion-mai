# 🐾 Gestión MAI - Sistema de Gestión de Esterilizaciones

Sistema web para la gestión de campañas de esterilización de animales a bajo costo, desarrollado para movimientos animalistas.

## 📋 Características

### Para Administradores
- ✅ Gestión completa de usuarios y roles
- ✅ Creación y gestión de campañas de esterilización
- ✅ Registro de esterilizaciones con datos del propietario y mascota
- ✅ Sistema de pagos parciales y múltiples abonos
- ✅ Gestión de ingresos y egresos
- ✅ Reportes financieros detallados
- ✅ Panel de control con métricas

### Para Vendedores
- ✅ Registro de ventas de esterilizaciones
- ✅ Gestión de pagos y abonos
- ✅ Consulta de campañas activas

### Para Visitantes (Público)
- ✅ Visualización de campañas activas
- ✅ Agendamiento de citas para mascotas
- ✅ Consulta de estado de esterilizaciones

## 🛠️ Tecnologías

### Backend
- **Framework:** Laravel 11
- **Base de Datos:** MySQL / SQLite
- **Autenticación:** Laravel Sanctum
- **PHP:** >= 8.2

### Frontend
- **Framework:** Vue.js 3
- **Build Tool:** Vite
- **TypeScript:** Soporte completo
- **Estado:** Pinia

## 📦 Instalación

### Prerequisitos
- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL (o SQLite para desarrollo)

### Backend

```bash
cd backend

# Instalar dependencias de PHP
composer install

# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos en .env
# Edita DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (crea roles y usuario admin)
php artisan db:seed

# Iniciar servidor de desarrollo
php artisan serve
```

**Usuario Admin por defecto:**
- Email: `admin@gestionmai.com`
- Password: `admin123`
- ⚠️ **Importante:** Cambiar contraseña en producción

### Frontend

```bash
cd frontend

# Instalar dependencias
npm install

# Iniciar servidor de desarrollo
npm run dev
```

El frontend estará disponible en: `http://localhost:5173`
El backend estará disponible en: `http://localhost:8000`

## 📊 Estructura de la Base de Datos

### Tablas Principales

#### `roles`
- Gestiona los roles del sistema (admin, seller, user)

#### `users`
- Usuarios del sistema con su rol asignado
- Campos: name, email, password, role_id, is_active, phone

#### `campaigns`
- Campañas de esterilización
- Campos: name, description, start_date, end_date, location, available_slots, used_slots, dog_price, cat_price, status, is_visible_to_public

#### `sterilizations`
- Registro de esterilizaciones
- **Datos del propietario:** owner_full_name, owner_id_number, owner_phone, owner_email, owner_address
- **Datos de la mascota:** pet_name, pet_type, pet_gender, pet_breed, pet_age_months, pet_weight
- **Datos financieros:** total_price, total_paid, balance, payment_status
- **Datos de agendamiento:** scheduled_date, scheduled_time, status

#### `payments`
- Pagos y abonos de esterilizaciones
- Campos: sterilization_id, amount, payment_method, reference_number, received_by

#### `incomes`
- Ingresos del movimiento
- Campos: campaign_id, concept, description, amount, source, income_date

#### `expenses`
- Egresos del movimiento
- Campos: campaign_id, concept, description, amount, category, expense_date, supplier

## 🔐 Sistema de Roles

### Admin
- Acceso completo al sistema
- Gestión de usuarios, campañas, reportes financieros

### Seller (Vendedor)
- Registro de esterilizaciones
- Gestión de pagos
- Consulta de campañas

### User (Usuario Público)
- Visualización de campañas públicas
- Agendamiento de citas

## 🚀 Comandos Útiles

### Backend

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Refrescar base de datos (⚠️ borra todos los datos)
php artisan migrate:fresh --seed

# Crear nueva migración
php artisan make:migration nombre_migracion

# Crear nuevo modelo
php artisan make:model NombreModelo

# Crear nuevo controlador
php artisan make:controller NombreController

# Ejecutar tests
php artisan test
```

### Frontend

```bash
# Compilar para producción
npm run build

# Preview de compilación
npm run preview

# Linter
npm run lint
```

## 📝 Flujo de Trabajo

### 1. Crear una Campaña
1. Login como admin
2. Ir a "Campañas" > "Nueva Campaña"
3. Llenar datos: nombre, fechas, ubicación, cupos, precios
4. Guardar

### 2. Registrar una Esterilización
1. Login como vendedor o admin
2. Seleccionar campaña activa
3. Llenar datos del propietario y mascota
4. Definir precio y registrar abono inicial
5. Guardar

### 3. Registrar Pagos Adicionales
1. Buscar esterilización por cédula o nombre
2. Agregar nuevo pago/abono
3. El sistema actualiza automáticamente el estado de pago

### 4. Gestión Financiera
1. Login como admin
2. Ir a "Finanzas"
3. Ver ingresos y egresos por campaña
4. Generar reportes

## 🎨 Patrones de Diseño Utilizados

- **Repository Pattern:** Para abstracción de datos
- **Service Layer:** Lógica de negocio separada de controladores
- **Observer Pattern:** Actualización automática de estados de pago
- **Factory Pattern:** Creación de instancias de modelos
- **Middleware Pattern:** Autenticación y autorización

## 🔒 Seguridad

- Autenticación mediante tokens (Sanctum)
- Validación de datos en backend y frontend
- Protección CSRF
- Sanitización de entradas
- Roles y permisos granulares

## 📄 Licencia

Este proyecto es de código abierto para uso de movimientos animalistas.

## 👥 Contribución

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para soporte y consultas, por favor abre un issue en el repositorio.

---

**Desarrollado con ❤️ para ayudar a los animalitos** 🐶🐱
