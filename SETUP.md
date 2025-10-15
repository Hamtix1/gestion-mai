# 🚀 COMANDOS PARA INICIALIZAR EL PROYECTO

## ⚙️ Paso 1: Configurar PHP en el PATH (PowerShell)

```powershell
# Verifica tu versión de PHP en Laragon
dir C:\laragon\bin\php

# Agrega PHP al PATH (ajusta la versión según la que tengas)
$env:Path += ";C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64"

# Verifica que PHP esté disponible
php --version
```

---

## 📦 Paso 2: Instalar Laravel Sanctum

```powershell
cd C:\laragon\www\gestion-mai\backend

# Instalar Sanctum
composer require laravel/sanctum

# Publicar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## 🗄️ Paso 3: Configurar Base de Datos

### Opción A: MySQL (Recomendado)

```powershell
# 1. Abre phpMyAdmin o MySQL Workbench
# 2. Crea una base de datos llamada: gestion_mai

# O por línea de comandos:
mysql -u root -p
CREATE DATABASE gestion_mai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Opción B: SQLite (Para desarrollo rápido)

```powershell
# Edita el archivo .env y cambia:
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# Crea el archivo de base de datos
New-Item -Path ".\database\database.sqlite" -ItemType File
```

---

## 🔑 Paso 4: Generar Key y Migrar Base de Datos

```powershell
# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones (crea las tablas)
php artisan migrate

# Ejecutar seeders (crea roles y usuario admin)
php artisan db:seed
```

**✅ Credenciales del Administrador:**
- Email: `admin@gestionmai.com`
- Password: `admin123`

---

## 🎨 Paso 5: Configurar el Frontend

```powershell
cd C:\laragon\www\gestion-mai\frontend

# Instalar dependencias
npm install
```

---

## ▶️ Paso 6: Iniciar Servidores de Desarrollo

### Terminal 1 - Backend:
```powershell
cd C:\laragon\www\gestion-mai\backend
php artisan serve
```
**Backend corriendo en:** http://localhost:8000

### Terminal 2 - Frontend:
```powershell
cd C:\laragon\www\gestion-mai\frontend
npm run dev
```
**Frontend corriendo en:** http://localhost:5173

---

## 🧪 Paso 7: Probar la API (Opcional)

```powershell
# Ver las rutas disponibles
php artisan route:list

# Limpiar caché si es necesario
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 📊 Paso 8: Verificar Instalación

### Probar Backend:
1. Abre: http://localhost:8000
2. Deberías ver la página de bienvenida de Laravel

### Probar Frontend:
1. Abre: http://localhost:5173
2. Deberías ver la aplicación Vue.js

---

## 🔄 Comandos Útiles para Desarrollo

### Refrescar Base de Datos (⚠️ BORRA TODOS LOS DATOS)
```powershell
php artisan migrate:fresh --seed
```

### Ver logs en tiempo real:
```powershell
php artisan tail
```

### Crear un controlador:
```powershell
php artisan make:controller Api/NombreController --resource
```

### Crear un request de validación:
```powershell
php artisan make:request NombreRequest
```

### Crear un middleware:
```powershell
php artisan make:middleware NombreMiddleware
```

---

## 🐛 Solución de Problemas Comunes

### Error: "php no se reconoce"
**Solución:** Agrega PHP al PATH como se indica en el Paso 1

### Error: "SQLSTATE[HY000] [1045] Access denied"
**Solución:** Verifica las credenciales de MySQL en el archivo `.env`

### Error: "npm no se reconoce"
**Solución:** Instala Node.js desde https://nodejs.org/

### Error: "Class 'Laravel\Sanctum\SanctumServiceProvider' not found"
**Solución:** Ejecuta `composer require laravel/sanctum`

### Error en migraciones
**Solución:** 
```powershell
php artisan migrate:fresh
php artisan db:seed
```

---

## 📝 Próximos Pasos

Una vez que el proyecto esté corriendo:

1. ✅ Implementar controladores de la API
2. ✅ Crear rutas de la API
3. ✅ Implementar autenticación con Sanctum
4. ✅ Crear middleware de roles
5. ✅ Desarrollar componentes Vue.js
6. ✅ Implementar stores de Pinia
7. ✅ Crear las vistas del frontend

---

**¡Listo para desarrollar! 🎉**
