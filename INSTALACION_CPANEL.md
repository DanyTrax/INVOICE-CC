# 🚀 Instalación RAMS en cPanel

Guía paso a paso para instalar RAMS (Regulatory Affairs Management System) en cPanel.

## 📋 Requisitos Previos

- cPanel con acceso SSH (recomendado) o File Manager
- PHP 8.2 o superior (8.4 recomendado)
- MySQL 5.7+ o MariaDB 10.3+
- Composer instalado en el servidor
- Extensiones PHP requeridas:
  - `intl`
  - `mbstring`
  - `openssl`
  - `pdo`
  - `pdo_mysql`
  - `tokenizer`
  - `xml`
  - `curl`
  - `zip`
  - `gd` o `imagick`

## 📦 Paso 1: Subir Archivos al Servidor

### Opción A: Usando Git (Recomendado)

1. Conecta por SSH a tu servidor:
```bash
ssh usuario@tudominio.com
cd public_html  # o la carpeta donde esté tu dominio
```

2. Clona el repositorio:
```bash
git clone https://github.com/DanyTrax/ACRegulatory.git .
```

### Opción B: Usando File Manager de cPanel

1. Accede a **File Manager** en cPanel
2. Navega a `public_html` (o la carpeta de tu dominio)
3. Sube todos los archivos del proyecto (excepto `vendor/` y `node_modules/`)
4. Extrae el archivo ZIP si lo subiste comprimido

## ⚙️ Paso 2: Configurar Permisos

Ejecuta estos comandos por SSH o desde el File Manager:

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

O desde File Manager:
- Click derecho en `storage/` → Cambiar Permisos → 775
- Click derecho en `bootstrap/cache/` → Cambiar Permisos → 775

## 🗄️ Paso 3: Crear Base de Datos

1. En cPanel, ve a **MySQL Databases**
2. Crea una nueva base de datos (ej: `usuario_rams`)
3. Crea un usuario de base de datos (ej: `usuario_rams_user`)
4. Asigna el usuario a la base de datos con **todos los privilegios**
5. Anota las credenciales:
   - Nombre BD: `usuario_rams`
   - Usuario: `usuario_rams_user`
   - Password: `tu_password`
   - Host: `localhost` (generalmente)

## 🔧 Paso 4: Configurar .env

1. En File Manager, busca el archivo `.env.example`
2. Cópialo y renómbralo a `.env`
3. Edita `.env` con estos valores:

```env
APP_NAME="RAMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usuario_rams
DB_USERNAME=usuario_rams_user
DB_PASSWORD=tu_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🔑 Paso 5: Generar APP_KEY

Por SSH, ejecuta:

```bash
cd public_html  # o tu carpeta del proyecto
php artisan key:generate
```

O desde cPanel → **Terminal** (si está disponible):
```bash
php artisan key:generate
```

## 📥 Paso 6: Instalar Dependencias

Por SSH, ejecuta:

```bash
composer install --no-dev --optimize-autoloader
```

Si no tienes Composer instalado globalmente:
```bash
php composer.phar install --no-dev --optimize-autoloader
```

## 🗃️ Paso 7: Ejecutar Migraciones

Por SSH:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Esto creará:
- Todas las tablas necesarias
- Roles del sistema (super_admin, panel_user, agent, client)
- Usuario administrador por defecto

## 🔐 Paso 8: Configurar Shield (Permisos)

```bash
php artisan shield:generate --all
```

## 🔒 Paso 9: Configurar Permisos de Archivos

```bash
chown -R usuario:usuario storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Reemplaza `usuario` con tu usuario de cPanel.

## 🌐 Paso 10: Configurar Document Root

### Opción A: Si el proyecto está en public_html directamente

No necesitas hacer nada, ya está configurado.

### Opción B: Si el proyecto está en una subcarpeta

1. En cPanel, ve a **Subdomains** o **Addon Domains**
2. Configura el Document Root para que apunte a `public_html/ACRegulatory/public`

O crea un `.htaccess` en `public_html` que redirija:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ ACRegulatory/public/$1 [L]
</IfModule>
```

## ✅ Paso 11: Verificar Instalación

1. Accede a: `https://tudominio.com/install`
2. Completa el asistente de instalación (si es necesario)
3. O accede directamente a: `https://tudominio.com/admin`

### Credenciales por Defecto

- **Email:** `admin@rams.com`
- **Password:** `password`

⚠️ **IMPORTANTE:** Cambia la contraseña inmediatamente después del primer acceso.

## 🔧 Configuración Adicional

### Optimizar para Producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Configurar Cron Jobs

En cPanel → **Cron Jobs**, agrega:

```bash
* * * * * cd /home/usuario/public_html && php artisan schedule:run >> /dev/null 2>&1
```

Reemplaza `/home/usuario/public_html` con la ruta completa de tu proyecto.

### Configurar Queue Worker (Opcional)

Si usas colas, configura un supervisor o cron job para:

```bash
php artisan queue:work --tries=3
```

## 🐛 Solución de Problemas

### Error: "No application encryption key has been specified"

```bash
php artisan key:generate
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"

Verifica en `.env`:
- `DB_HOST=127.0.0.1` (no `localhost`)
- Credenciales correctas de la base de datos

### Error: "The stream or file could not be opened"

```bash
chmod -R 775 storage
chown -R usuario:usuario storage
```

### Error 500 después de la instalación

1. Verifica los logs en `storage/logs/laravel.log`
2. Verifica permisos de `storage/` y `bootstrap/cache/`
3. Limpia caché: `php artisan cache:clear`

### Panel no carga

1. Verifica que `APP_URL` en `.env` sea correcto
2. Ejecuta: `php artisan config:clear`
3. Verifica permisos de archivos

## 📞 Soporte

Si encuentras problemas, verifica:
- Logs en `storage/logs/laravel.log`
- Permisos de archivos y carpetas
- Configuración de `.env`
- Extensiones PHP habilitadas

## 🔄 Actualizaciones Futuras

Para actualizar el proyecto:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**¡Listo!** Tu sistema RAMS debería estar funcionando en cPanel. 🎉
