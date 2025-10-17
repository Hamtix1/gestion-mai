# 🚀 Guía de Despliegue en Producción - Gestión MAI

## 📋 Requisitos del Servidor

### Software Necesario:
- ✅ **PHP**: 8.1 o superior
- ✅ **MariaDB/MySQL**: 8.0 o superior
- ✅ **Composer**: 2.x
- ✅ **Node.js**: 18.x o superior
- ✅ **NPM**: 9.x o superior
- ✅ **Servidor Web**: Apache o Nginx

### Extensiones PHP Requeridas:
```bash
- php-mbstring
- php-xml
- php-pdo
- php-mysql
- php-curl
- php-zip
- php-gd
- php-fileinfo
```

---

## 🔧 Configuración de Base de Datos

### 1. Crear Base de Datos en el Servidor

Conéctate a MySQL/MariaDB y ejecuta:

```sql
CREATE DATABASE gestion_mai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario (opcional, recomendado en producción)
CREATE USER 'gestion_mai_user'@'localhost' IDENTIFIED BY 'tu_contraseña_segura';
GRANT ALL PRIVILEGES ON gestion_mai.* TO 'gestion_mai_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configurar Credenciales

Edita el archivo `.env` del backend:

```env
APP_NAME="Gestión MAI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_mai
DB_USERNAME=gestion_mai_user
DB_PASSWORD=tu_contraseña_segura

# IMPORTANTE: Genera una nueva APP_KEY
# php artisan key:generate
```

---

## 📦 Despliegue del Backend (Laravel)

### Paso 1: Subir Archivos al Servidor

Sube el contenido de la carpeta `backend/` al servidor (vía FTP, Git, etc.)

**Estructura recomendada:**
```
/var/www/gestion-mai/
├── backend/
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/         ← Este será el DocumentRoot
│   ├── routes/
│   ├── storage/        ← Asegurar permisos 775
│   ├── vendor/
│   ├── .env           ← Configurar
│   ├── composer.json
│   └── artisan
└── frontend/
    ├── dist/          ← Archivos compilados
    └── ...
```

### Paso 2: Instalar Dependencias

```bash
cd /var/www/gestion-mai/backend
composer install --optimize-autoloader --no-dev
```

### Paso 3: Configurar Permisos

```bash
# Dar permisos de escritura a storage y bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Paso 4: Ejecutar Migraciones

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder
```

### Paso 5: Optimizar para Producción

```bash
# Generar key de aplicación
php artisan key:generate

# Optimizar configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar caché de desarrollo
php artisan cache:clear
```

---

## 🌐 Despliegue del Frontend (Vue.js)

### Paso 1: Configurar Variables de Entorno

Edita `frontend/.env.production`:

```env
VITE_API_URL=https://tu-dominio.com/api
VITE_APP_URL=https://tu-dominio.com
```

### Paso 2: Compilar para Producción

```bash
cd /ruta/local/frontend
npm install
npm run build
```

Esto generará la carpeta `dist/` con los archivos optimizados.

### Paso 3: Subir Archivos Compilados

Sube el contenido de `dist/` a la carpeta pública del servidor:

```bash
# Opción 1: Dentro del mismo dominio
/var/www/gestion-mai/frontend/dist/

# Opción 2: Servidor web separado (recomendado)
/var/www/html/gestion-mai/
```

---

## ⚙️ Configuración del Servidor Web

### Opción A: Apache

Crea un archivo VirtualHost: `/etc/apache2/sites-available/gestion-mai.conf`

```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    DocumentRoot /var/www/gestion-mai/backend/public

    <Directory /var/www/gestion-mai/backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/gestion-mai-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-mai-access.log combined
</VirtualHost>

# Frontend (si está en el mismo servidor)
<VirtualHost *:80>
    ServerName app.tu-dominio.com
    DocumentRoot /var/www/gestion-mai/frontend/dist

    <Directory /var/www/gestion-mai/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # SPA Routing
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>
</VirtualHost>
```

Activar el sitio:
```bash
sudo a2ensite gestion-mai.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### Opción B: Nginx

Crea un archivo de configuración: `/etc/nginx/sites-available/gestion-mai`

```nginx
# Backend
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/gestion-mai/backend/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}

# Frontend
server {
    listen 80;
    server_name app.tu-dominio.com;
    root /var/www/gestion-mai/frontend/dist;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, immutable";
    }
}
```

Activar el sitio:
```bash
sudo ln -s /etc/nginx/sites-available/gestion-mai /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Configuración SSL (HTTPS)

### Usando Let's Encrypt (Certbot)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache  # Para Apache
# O
sudo apt install certbot python3-certbot-nginx   # Para Nginx

# Obtener certificados SSL
sudo certbot --apache -d tu-dominio.com -d app.tu-dominio.com
# O
sudo certbot --nginx -d tu-dominio.com -d app.tu-dominio.com

# Auto-renovación (verificar)
sudo certbot renew --dry-run
```

---

## 🔐 Seguridad en Producción

### 1. Cambiar Contraseñas por Defecto

```bash
php artisan tinker
```

```php
$admin = User::where('email', 'admin@gestionmai.com')->first();
$admin->password = bcrypt('TU_NUEVA_CONTRASEÑA_SEGURA');
$admin->save();
```

### 2. Configurar CORS

Edita `backend/config/cors.php`:

```php
'allowed_origins' => [
    'https://app.tu-dominio.com',
    'https://tu-dominio.com',
],
```

### 3. Variables de Entorno Críticas

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=app.tu-dominio.com
```

### 4. Proteger Archivos Sensibles

```bash
# Asegurar que .env no sea accesible
chmod 600 .env

# Agregar en .htaccess (Apache)
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

---

## 📊 Monitoreo y Mantenimiento

### Logs de Laravel

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

### Backups de Base de Datos

```bash
# Crear backup
mysqldump -u gestion_mai_user -p gestion_mai > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u gestion_mai_user -p gestion_mai < backup_20251016.sql
```

### Automatizar Backups (Crontab)

```bash
# Editar crontab
crontab -e

# Agregar backup diario a las 2 AM
0 2 * * * mysqldump -u gestion_mai_user -pPASSWORD gestion_mai > /backups/gestion_mai_$(date +\%Y\%m\%d).sql
```

---

## ✅ Checklist de Despliegue

- [ ] Base de datos creada y configurada
- [ ] Usuario de base de datos con permisos adecuados
- [ ] `.env` configurado con credenciales de producción
- [ ] `APP_DEBUG=false` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] Nueva `APP_KEY` generada
- [ ] Dependencias de Composer instaladas
- [ ] Migraciones ejecutadas
- [ ] Seeders ejecutados (roles y admin)
- [ ] Permisos de `storage/` configurados
- [ ] Frontend compilado para producción
- [ ] Variables de entorno del frontend configuradas
- [ ] VirtualHost/Server block configurado
- [ ] SSL/HTTPS configurado
- [ ] Contraseña de admin cambiada
- [ ] CORS configurado correctamente
- [ ] Backups automatizados configurados
- [ ] Logs monitoreados

---

## 🆘 Solución de Problemas

### Error 500: Internal Server Error
```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Verificar permisos
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Error de Base de Datos
```bash
# Verificar conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

### CORS Issues
```bash
# Limpiar caché de configuración
php artisan config:clear
```

### Frontend no carga
- Verificar que `VITE_API_URL` apunte al backend correcto
- Verificar CORS en el backend
- Verificar que los archivos estén en el DocumentRoot correcto

---

## 📞 Soporte

Para más información o soporte técnico:
- Documentación Laravel: https://laravel.com/docs
- Documentación Vue: https://vuejs.org/guide/
- Repositorio: https://github.com/Hamtix1/gestion-mai

---

**¡Despliegue exitoso! 🎉**
