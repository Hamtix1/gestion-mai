# ⚡ INICIO RÁPIDO - GESTIÓN MAI

## 🚀 Comandos para Iniciar el Sistema

### 1️⃣ Iniciar Backend (Laravel)

Abrir PowerShell en `c:\laragon\www\gestion-mai\backend` y ejecutar:

```powershell
# Opción 1: Con Laragon (recomendado)
# Ya está corriendo en http://gestion-mai.test/

# Opción 2: Con artisan serve
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
# Estará disponible en http://127.0.0.1:8000
```

### 2️⃣ Iniciar Frontend (Vue)

Abrir PowerShell en `c:\laragon\www\gestion-mai\frontend` y ejecutar:

```powershell
npm run dev
```

Abrir navegador en: **http://localhost:5173**

---

## 🔐 Credenciales de Acceso

```
Email:    admin@mai.com
Password: admin123
```

---

## 📊 Verificación Rápida del Sistema

### Probar Backend (API)

```powershell
# Desde cualquier ubicación
Invoke-RestMethod -Uri "http://gestion-mai.test/api/ping"
```

**Respuesta esperada:** `{"message": "pong", "timestamp": "..."}`

### Probar Sincronización Automática

```powershell
cd c:\laragon\www\gestion-mai\backend
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe test_complete_flow.php
```

**Resultado esperado:**
```
✅ Sistema en perfecto estado - Sin inconsistencias detectadas
Balance Final: $95,400.00
4 esterilizaciones completamente pagadas → 4 ingresos automáticos
```

---

## 🧪 Testing Manual Rápido (5 minutos)

### Test 1: Login y Dashboard
1. Abrir http://localhost:5173/login
2. Login con credenciales
3. Verificar dashboard carga sin errores
4. **Verificar: NO hay valores $NaN**

### Test 2: Ver Datos Existentes
1. Ir a `/admin/financial`
2. **Verificar:**
   - Total Ingresos: $95,400.00
   - Total Gastos: $0.00
   - Balance: $95,400.00

### Test 3: Sincronización Automática
1. Ir a `/admin/sterilizations`
2. Abrir cualquier esterilización con payment_status = "completed"
3. Ver su ID (ej: #1)
4. Ir a `/admin/financial/incomes`
5. **Verificar:** Existe ingreso con referencia "STER-1"

### Test 4: Crear Gasto
1. Ir a `/admin/financial/expenses`
2. Click "Nuevo Gasto"
3. Crear gasto de prueba ($50)
4. Guardar
5. Volver al Dashboard
6. **Verificar:** Balance disminuyó en $50

### Test 5: Gestión de Usuarios
1. Ir a `/admin/users`
2. Ver lista de usuarios
3. **Verificar:** Todo carga correctamente

---

## 📁 Archivos de Documentación

```
📄 README.md                    - Guía principal
📄 SETUP.md                     - Instalación completa
📄 API_DOCUMENTATION.md         - Documentación de API
📄 EXECUTIVE_SUMMARY.md         - Resumen ejecutivo (ESTE)
📄 PROJECT_COMPLETED.md         - Documentación técnica completa
📄 TESTING_CHECKLIST.md         - 100+ puntos de testing
📄 MANUAL_TESTING_GUIDE.md      - Guía paso a paso
📄 COMANDOS_RAPIDOS.md          - Comandos útiles
```

---

## 🎯 Flujo Completo de Prueba (15 minutos)

### Paso 1: Crear Campaña
```
/admin/campaigns/create
→ Nombre: "Mi Campaña Test"
→ Guardar
```

### Paso 2: Crear Esterilización
```
/admin/sterilizations/create
→ Asociar a campaña creada
→ Costo: $120
→ Guardar
```

### Paso 3: Registrar Pago Parcial
```
/admin/sterilizations/[id]
→ Registrar pago: $50
→ Verificar payment_status = "partial"
```

### Paso 4: Completar Pago
```
Registrar pago: $70
→ Verificar payment_status = "completed"
```

### Paso 5: Verificar Sincronización
```
/admin/financial/incomes
→ DEBE existir ingreso automático
→ Referencia: "STER-[id]"
→ Monto: $120
```

### Paso 6: Crear Gasto
```
/admin/financial/expenses/create
→ Crear gasto de $30
→ Guardar
```

### Paso 7: Verificar Balance
```
/admin/financial
→ Balance debe reflejar:
  Ingresos (+$120) - Gastos (+$30)
```

---

## ✅ Checklist Rápido

- [ ] Backend corriendo (http://gestion-mai.test/)
- [ ] Frontend corriendo (http://localhost:5173)
- [ ] Login exitoso
- [ ] Dashboard carga sin $NaN
- [ ] Datos existentes visibles
- [ ] Sincronización automática verificada
- [ ] CRUD de todos los módulos funciona

---

## 🆘 Solución de Problemas

### Backend no responde
```powershell
# Verificar que Laragon esté corriendo
# O iniciar con artisan serve
cd c:\laragon\www\gestion-mai\backend
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

### Frontend no compila
```powershell
cd c:\laragon\www\gestion-mai\frontend
npm install
npm run dev
```

### Base de datos vacía
```powershell
cd c:\laragon\www\gestion-mai\backend
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate:fresh --seed
```

### Limpiar caché
```powershell
# Backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Frontend
npm run build
```

---

## 📞 Recursos Adicionales

### URLs del Sistema
- **Backend API:** http://gestion-mai.test/api/
- **Frontend:** http://localhost:5173/
- **Ping endpoint:** http://gestion-mai.test/api/ping

### Comandos Útiles
```powershell
# Ver rutas API
php artisan route:list --path=api

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Crear nuevo seeder
php artisan make:seeder NombreSeeder

# Ver logs
tail -f storage/logs/laravel.log
```

---

## 🎉 ¡Sistema Listo!

**Todo está configurado y funcionando.** 

Siguiente paso: Realizar testing manual completo usando `MANUAL_TESTING_GUIDE.md`

**Tiempo estimado:** 15-20 minutos

---

**Desarrollado con ❤️ para mejorar la vida de los animales** 🐾
