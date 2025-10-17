-- ================================================
-- Script de Configuración Inicial - Gestión MAI
-- Base de Datos: MariaDB/MySQL 8.0+
-- ================================================

-- 1. CREAR BASE DE DATOS
CREATE DATABASE IF NOT EXISTS gestion_mai 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. CREAR USUARIO (Recomendado para producción)
-- IMPORTANTE: Cambiar 'tu_contraseña_segura' por una contraseña fuerte
CREATE USER IF NOT EXISTS 'gestion_mai_user'@'localhost' 
IDENTIFIED BY 'tu_contraseña_segura';

-- 3. OTORGAR PERMISOS
GRANT ALL PRIVILEGES ON gestion_mai.* 
TO 'gestion_mai_user'@'localhost';

FLUSH PRIVILEGES;

-- 4. VERIFICAR USUARIO CREADO
SELECT User, Host FROM mysql.user WHERE User = 'gestion_mai_user';

-- 5. USAR LA BASE DE DATOS
USE gestion_mai;

-- ================================================
-- NOTAS IMPORTANTES:
-- ================================================
-- 
-- Después de ejecutar este script:
-- 
-- 1. Configura el archivo .env del backend:
--    DB_CONNECTION=mysql
--    DB_HOST=127.0.0.1
--    DB_PORT=3306
--    DB_DATABASE=gestion_mai
--    DB_USERNAME=gestion_mai_user
--    DB_PASSWORD=tu_contraseña_segura
--
-- 2. Ejecuta las migraciones de Laravel:
--    php artisan migrate --force
--
-- 3. Ejecuta los seeders para crear roles y usuario admin:
--    php artisan db:seed --class=RoleSeeder
--    php artisan db:seed --class=AdminUserSeeder
--
-- 4. Credenciales iniciales del admin:
--    Email: admin@gestionmai.com
--    Password: admin123
--    ⚠️ CAMBIAR EN PRODUCCIÓN ⚠️
--
-- ================================================

-- OPCIONAL: Ver configuración de la base de datos
SELECT 
    SCHEMA_NAME as 'Database',
    DEFAULT_CHARACTER_SET_NAME as 'Charset',
    DEFAULT_COLLATION_NAME as 'Collation'
FROM information_schema.SCHEMATA 
WHERE SCHEMA_NAME = 'gestion_mai';
