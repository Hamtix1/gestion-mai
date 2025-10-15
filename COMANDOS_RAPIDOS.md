# ⚡ COMANDOS RÁPIDOS DE INICIALIZACIÓN

## 🔧 Configurar PATH en PowerShell (Ejecuta primero)

```powershell
# Agregar PHP y Composer al PATH de la sesión actual
$env:Path += ";C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64"
$env:Path += ";C:\laragon\bin\composer"

# Verificar que estén disponibles
php --version
composer --version
```

> **Nota:** Ajusta la versión de PHP según la que tengas instalada. 
> Ejecuta `dir C:\laragon\bin\php` para ver las versiones disponibles.

---

## 📦 Instalar Dependencias Backend

```powershell
cd C:\laragon\www\gestion-mai\backend

# Instalar Laravel Sanctum
composer require laravel/sanctum

# Publicar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## 🗄️ Configurar Base de Datos

### IMPORTANTE: Edita el archivo .env primero

Abre el archivo `backend\.env` y configura:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_mai
DB_USERNAME=root
DB_PASSWORD=
```

### Crear la base de datos en MySQL

**Opción 1: Usando Laragon Terminal**
```powershell
mysql -u root -p
CREATE DATABASE gestion_mai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Opción 2: Usar HeidiSQL o phpMyAdmin que vienen con Laragon**
- Abre HeidiSQL o phpMyAdmin desde Laragon
- Crea una base de datos llamada: `gestion_mai`

---

## 🚀 Ejecutar Migraciones y Seeders

```powershell
cd C:\laragon\www\gestion-mai\backend

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones (crear tablas)
php artisan migrate

# Ejecutar seeders (crear roles y usuario admin)
php artisan db:seed
```

**✅ Si todo sale bien, verás:**
```
Roles creados exitosamente!
Usuario administrador creado exitosamente!
Email: admin@gestionmai.com
Password: admin123
```

---

## 📦 Instalar Dependencias Frontend

```powershell
cd C:\laragon\www\gestion-mai\frontend

# Instalar dependencias de Node.js
npm install
```

---

## ▶️ Iniciar Servidores

### Terminal 1 - Backend:
```powershell
cd C:\laragon\www\gestion-mai\backend
php artisan serve
```

### Terminal 2 - Frontend:
```powershell
cd C:\laragon\www\gestion-mai\frontend
npm run dev
```

---

## 🎯 URLs de Acceso

- **Backend API:** http://localhost:8000
- **Frontend:** http://localhost:5173

---

## 🔐 Credenciales de Acceso

**Usuario Administrador:**
- Email: `admin@gestionmai.com`
- Password: `admin123`

⚠️ **Importante:** Cambia esta contraseña en producción

---

## ✅ Verificar Instalación

```powershell
# Ver rutas de la API
php artisan route:list

# Verificar que las tablas se crearon
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Role::count()
>>> exit
```

---

## 🆘 Si algo falla

### Limpiar todo y empezar de nuevo:
```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan migrate:fresh --seed
```

### Ver logs de errores:
```powershell
# Ver últimas líneas del log
Get-Content storage/logs/laravel.log -Tail 50
```

---

**¡Ejecuta estos comandos en orden y avísame cuando termines! 🚀**
