# 📚 Documentación de la API - Gestión MAI

## 🔗 Base URL
```
http://localhost:8000/api
```

## 🔐 Autenticación

La API utiliza **Laravel Sanctum** para autenticación mediante tokens Bearer.

### Headers requeridos para rutas protegidas:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 📋 Endpoints

### 🌐 RUTAS PÚBLICAS (sin autenticación)

#### **Listar campañas públicas**
```http
GET /api/public/campaigns
```
**Query Parameters:**
- `per_page` (opcional): número de resultados por página (default: 10)

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Campaña Octubre 2025",
      "description": "...",
      "start_date": "2025-10-20",
      "end_date": "2025-10-25",
      "location": "Centro Veterinario",
      "available_slots": 50,
      "used_slots": 20,
      "remaining_slots": 30,
      "dog_price": "50000.00",
      "cat_price": "40000.00"
    }
  ]
}
```

#### **Detalles de una campaña**
```http
GET /api/public/campaigns/{id}
```

#### **Consultar estado de esterilización**
```http
POST /api/public/check-sterilization
```
**Body:**
```json
{
  "owner_id_number": "1234567890"
}
```

#### **Cupos disponibles por fecha**
```http
GET /api/public/campaigns/{id}/available-slots?date=2025-10-20
```

#### **Estadísticas públicas**
```http
GET /api/public/statistics
```

---

### 🔑 AUTENTICACIÓN

#### **Login**
```http
POST /api/auth/login
```
**Body:**
```json
{
  "email": "admin@gestionmai.com",
  "password": "admin123"
}
```

**Respuesta:**
```json
{
  "message": "Inicio de sesión exitoso",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@gestionmai.com",
    "role": {
      "name": "admin",
      "display_name": "Administrador"
    }
  },
  "token": "1|xxxxxxxxxxxxxxx"
}
```

#### **Logout**
```http
POST /api/auth/logout
```
**Headers:** `Authorization: Bearer {token}`

#### **Obtener usuario autenticado**
```http
GET /api/auth/me
```
**Headers:** `Authorization: Bearer {token}`

#### **Actualizar perfil**
```http
PUT /api/auth/profile
```
**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "name": "Nuevo Nombre",
  "phone": "3001234567",
  "current_password": "password_actual",
  "password": "nuevo_password",
  "password_confirmation": "nuevo_password"
}
```

---

### 👥 USUARIOS (solo Admin)

#### **Listar usuarios**
```http
GET /api/users
```
**Query Parameters:**
- `role`: filtrar por rol (admin, seller, user)
- `is_active`: filtrar por estado (true/false)
- `search`: buscar por nombre o email
- `per_page`: resultados por página

#### **Crear usuario**
```http
POST /api/users
```
**Body:**
```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "role_id": 2,
  "phone": "3001234567",
  "is_active": true
}
```

#### **Actualizar usuario**
```http
PUT /api/users/{id}
```

#### **Eliminar usuario**
```http
DELETE /api/users/{id}
```

#### **Activar/Desactivar usuario**
```http
POST /api/users/{id}/toggle-status
```

---

### 🎪 CAMPAÑAS (Admin y Vendedor)

#### **Listar campañas**
```http
GET /api/campaigns
```
**Query Parameters:**
- `status`: planned, active, completed, cancelled
- `is_visible_to_public`: true/false
- `with_slots`: true (solo con cupos disponibles)
- `start_date` y `end_date`: rango de fechas

#### **Crear campaña**
```http
POST /api/campaigns
```
**Body:**
```json
{
  "name": "Campaña Noviembre 2025",
  "description": "Descripción de la campaña",
  "start_date": "2025-11-01",
  "end_date": "2025-11-05",
  "location": "Centro Veterinario Sur",
  "available_slots": 100,
  "dog_price": 50000,
  "cat_price": 40000,
  "status": "planned",
  "is_visible_to_public": true
}
```

#### **Ver detalles de campaña**
```http
GET /api/campaigns/{id}
```

#### **Actualizar campaña**
```http
PUT /api/campaigns/{id}
```

#### **Eliminar campaña**
```http
DELETE /api/campaigns/{id}
```

#### **Estadísticas de campañas**
```http
GET /api/campaigns-statistics
```

---

### 🐾 ESTERILIZACIONES (Admin y Vendedor)

#### **Listar esterilizaciones**
```http
GET /api/sterilizations
```
**Query Parameters:**
- `campaign_id`: filtrar por campaña
- `payment_status`: pending, partial, completed
- `status`: scheduled, in_progress, completed, cancelled
- `owner_id_number`: cédula del propietario
- `owner_name`: nombre del propietario
- `pet_type`: dog, cat
- `scheduled_date`: fecha agendada

#### **Registrar esterilización**
```http
POST /api/sterilizations
```
**Body:**
```json
{
  "campaign_id": 1,
  "owner_full_name": "María García",
  "owner_id_number": "1234567890",
  "owner_phone": "3001234567",
  "owner_email": "maria@example.com",
  "owner_address": "Calle 123 #45-67",
  "pet_name": "Firulais",
  "pet_type": "dog",
  "pet_gender": "male",
  "pet_breed": "Criollo",
  "pet_age_months": 24,
  "pet_weight": 15.5,
  "scheduled_date": "2025-10-25",
  "scheduled_time": "10:00",
  "notes": "Observaciones adicionales"
}
```

#### **Ver detalles de esterilización**
```http
GET /api/sterilizations/{id}
```

#### **Actualizar esterilización**
```http
PUT /api/sterilizations/{id}
```

#### **Eliminar esterilización**
```http
DELETE /api/sterilizations/{id}
```

#### **Estadísticas de esterilizaciones**
```http
GET /api/sterilizations-statistics?campaign_id=1
```

---

### 💰 PAGOS (Admin y Vendedor)

#### **Listar pagos**
```http
GET /api/payments
```
**Query Parameters:**
- `sterilization_id`: filtrar por esterilización
- `payment_method`: cash, transfer, card, other
- `start_date` y `end_date`: rango de fechas
- `received_by`: usuario que recibió el pago

#### **Registrar pago**
```http
POST /api/payments
```
**Body:**
```json
{
  "sterilization_id": 1,
  "amount": 25000,
  "payment_method": "cash",
  "reference_number": "REF123",
  "notes": "Primer abono"
}
```

#### **Ver historial de pagos de una esterilización**
```http
GET /api/payments/sterilization/{sterilization_id}
```

#### **Actualizar pago**
```http
PUT /api/payments/{id}
```

#### **Eliminar pago**
```http
DELETE /api/payments/{id}
```

#### **Estadísticas de pagos**
```http
GET /api/payments-statistics?start_date=2025-10-01&end_date=2025-10-31
```

---

### 💵 FINANZAS (Admin y Vendedor)

#### **Listar ingresos**
```http
GET /api/financial/incomes
```
**Query Parameters:**
- `campaign_id`: filtrar por campaña
- `source`: sterilization, donation, fundraising, other
- `start_date` y `end_date`: rango de fechas

#### **Registrar ingreso**
```http
POST /api/financial/incomes
```
**Body:**
```json
{
  "campaign_id": 1,
  "concept": "Donación anónima",
  "description": "Descripción detallada",
  "amount": 100000,
  "source": "donation",
  "income_date": "2025-10-15",
  "reference_number": "DON123"
}
```

#### **Listar egresos**
```http
GET /api/financial/expenses
```

#### **Registrar egreso**
```http
POST /api/financial/expenses
```
**Body:**
```json
{
  "campaign_id": 1,
  "concept": "Compra de medicamentos",
  "description": "Anestésicos y antibióticos",
  "amount": 50000,
  "category": "medical",
  "expense_date": "2025-10-15",
  "invoice_number": "INV123",
  "supplier": "Veterinaria Central"
}
```

#### **Resumen financiero**
```http
GET /api/financial/summary?campaign_id=1&start_date=2025-10-01&end_date=2025-10-31
```

**Respuesta:**
```json
{
  "total_income": 500000,
  "total_expense": 300000,
  "balance": 200000,
  "income_by_source": [...],
  "expense_by_category": [...]
}
```

#### **Reporte por campaña (solo Admin)**
```http
GET /api/financial/report-by-campaign?status=active
```

---

## 🔒 Niveles de Acceso

### Público (sin autenticación)
- ✅ Ver campañas activas
- ✅ Consultar estado de esterilización
- ✅ Ver estadísticas públicas
- ✅ Enviar solicitud de contacto

### Vendedor (+ todas las públicas)
- ✅ Gestionar campañas
- ✅ Registrar esterilizaciones
- ✅ Gestionar pagos
- ✅ Ver reportes financieros
- ✅ Registrar ingresos y egresos

### Administrador (+ todas las de vendedor)
- ✅ Gestionar usuarios
- ✅ Registrar nuevos usuarios
- ✅ Ver reportes completos por campaña
- ✅ Acceso total al sistema

---

## 📊 Códigos de Respuesta

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity (validación)
- `500` - Internal Server Error

---

## 🧪 Prueba Rápida

```bash
# Probar que la API funciona
curl http://localhost:8000/api/ping

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@gestionmai.com","password":"admin123"}'

# Listar campañas públicas
curl http://localhost:8000/api/public/campaigns
```

---

**Desarrollado con ❤️ para ayudar a los animalitos** 🐶🐱
