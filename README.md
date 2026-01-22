# RAMS - Regulatory Affairs Management System

Sistema SAAS para gestión de expedientes regulatorios sanitarios construido con **Laravel 12** y **FilamentPHP v5**.

## 🚀 Características

- ✅ Panel Administrativo (Teal) con gestión completa
- ✅ Panel Cliente (Amber) con acceso restringido
- ✅ Gestión de expedientes regulatorios
- ✅ Sistema de roles y permisos (Shield)
- ✅ Auditoría de cambios (Laravel Auditing)
- ✅ Integración con Google Drive (pendiente)
- ✅ Generación de reportes PDF y Excel
- ✅ Auto-instalador visual

## 📋 Requisitos

- **PHP 8.2.0 o superior** (compatible con 8.2, 8.3, 8.4)
- MySQL 5.7+ o MariaDB 10.3+
- Composer 2.x
- Extensiones PHP: intl, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, curl, zip

> **Nota:** El proyecto está configurado para funcionar con PHP 8.2. Si tu servidor tiene PHP 8.2.x, el proyecto funcionará sin problemas.

## 🛠️ Instalación Rápida

### Para Desarrollo Local

```bash
# Clonar repositorio
git clone https://github.com/DanyTrax/ACRegulatory.git
cd ACRegulatory

# Instalar dependencias
composer install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=nombre_bd
# DB_USERNAME=usuario
# DB_PASSWORD=password

# Ejecutar migraciones
php artisan migrate
php artisan db:seed

# Configurar permisos
php artisan shield:generate --all

# Iniciar servidor
php artisan serve
```

Accede a: `http://localhost:8000/admin`

### Para cPanel

📖 **Ver guía completa:** [INSTALACION_CPANEL.md](INSTALACION_CPANEL.md)

## 🔐 Credenciales por Defecto

- **Email:** `admin@rams.com`
- **Password:** `password`

⚠️ **IMPORTANTE:** Cambia la contraseña inmediatamente después del primer acceso.

## 📁 Estructura del Proyecto

```
ACRegulatory/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # Recursos Filament
│   │   └── Pages/              # Páginas personalizadas
│   ├── Models/                 # Modelos Eloquent
│   └── Settings/               # Configuración Spatie Settings
├── database/
│   ├── migrations/             # Migraciones
│   └── seeders/                # Seeders
├── public/                     # Punto de entrada público
├── resources/
└── storage/                    # Archivos y logs
```

## 🎯 Paneles

### Admin Panel (`/admin`)
- Color: Teal (#0f766e)
- Roles: super_admin, panel_user
- Gestión completa del sistema

### Client Panel (`/portal`)
- Color: Amber (#f59e0b)
- Acceso restringido por empresa
- Solo lectura de expedientes

## 📦 Paquetes Utilizados

- **FilamentPHP v5** - Panel administrativo
- **Filament Shield** - Sistema de roles y permisos
- **Laravel Auditing** - Auditoría de cambios
- **DomPDF** - Generación de PDFs
- **Maatwebsite Excel** - Exportación a Excel
- **Spatie Settings** - Configuración global
- **Laravel Installer** - Auto-instalador

## 🔧 Configuración

### Variables de Entorno Importantes

```env
APP_NAME="RAMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=nombre_bd
DB_USERNAME=usuario
DB_PASSWORD=password
```

### Optimización para Producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 📝 Estado del Proyecto

Ver detalles completos en: [ESTADO_PROYECTO.md](ESTADO_PROYECTO.md)

### ✅ Completado
- Estructura base y migraciones
- Modelos con relaciones
- Recursos Filament básicos
- Sistema de roles y permisos
- Auto-instalador

### 🔄 Pendiente
- Personalización avanzada de formularios
- Integración completa con Google Drive
- Widget de calendario personalizado
- Acciones avanzadas en recursos

## 🐛 Solución de Problemas

### Error: "No application encryption key"
```bash
php artisan key:generate
```

### Error de permisos
```bash
chmod -R 775 storage bootstrap/cache
```

### Limpiar caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📞 Soporte

Para problemas o consultas:
1. Revisa los logs en `storage/logs/laravel.log`
2. Verifica la configuración en `.env`
3. Consulta [INSTALACION_CPANEL.md](INSTALACION_CPANEL.md) para problemas de instalación

## 📄 Licencia

Este proyecto es privado y de uso exclusivo.

## 👨‍💻 Autor

Desarrollado para gestión de expedientes regulatorios sanitarios.

---

**Versión:** 1.0.0  
**Última actualización:** Enero 2026
