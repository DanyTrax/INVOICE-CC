# 🔧 Solución: Error MissingSettings

## Problema
Error: `MissingSettings - Tried loading settings 'App\Settings\GeneralSettings', and the following properties were missing`

## Causa
Los valores de configuración no están inicializados en la base de datos.

## Solución

### Opción 1: Ejecutar Migración Automática (Recomendada)

El sistema intentará ejecutar la migración automáticamente cuando accedas a `/admin/settings`. Si no funciona, usa la Opción 2.

### Opción 2: Ejecutar Migración Manualmente

En el servidor, ejecuta:

```bash
cd /home/acdoblevia2/regulatory.acdoblevia.com
php artisan settings:migrate
```

Esto creará todos los valores por defecto en la base de datos.

### Opción 3: Si la Opción 2 no funciona

Ejecuta directamente desde la base de datos:

```sql
-- Verificar si existe la tabla settings
SHOW TABLES LIKE 'settings';

-- Si no existe, crear (aunque debería existir)
-- La tabla se crea con: php artisan migrate

-- Insertar valores por defecto manualmente
INSERT INTO `settings` (`group`, `name`, `locked`, `payload`, `created_at`, `updated_at`) VALUES
('general', 'agency_name', 0, '"RAMS"', NOW(), NOW()),
('general', 'agency_nit', 0, '""', NOW(), NOW()),
('general', 'agency_address', 0, '""', NOW(), NOW()),
('general', 'agency_phone', 0, '""', NOW(), NOW()),
('general', 'agency_email', 0, '""', NOW(), NOW()),
('general', 'agency_website', 0, '""', NOW(), NOW()),
('general', 'agency_logo', 0, '""', NOW(), NOW()),
('general', 'drive_service_account_json', 0, '""', NOW(), NOW()),
('general', 'mail_mailer', 0, '"smtp"', NOW(), NOW()),
('general', 'mail_host', 0, '"smtp.gmail.com"', NOW(), NOW()),
('general', 'mail_port', 0, '587', NOW(), NOW()),
('general', 'mail_username', 0, '""', NOW(), NOW()),
('general', 'mail_password', 0, '""', NOW(), NOW()),
('general', 'mail_encryption', 0, '"tls"', NOW(), NOW()),
('general', 'mail_from_address', 0, '"noreply@rams.com"', NOW(), NOW()),
('general', 'mail_from_name', 0, '"RAMS Sistema"', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
```

## Verificación

Después de ejecutar la migración:

1. Accede a `/admin/settings`
2. Deberías ver la página de configuración sin errores
3. Los campos tendrán valores por defecto

## Valores por Defecto

- **Agency Name:** RAMS
- **Mail Host:** smtp.gmail.com
- **Mail Port:** 587
- **Mail Encryption:** tls
- **Mail From Address:** noreply@rams.com
- **Mail From Name:** RAMS Sistema

Todos los demás campos estarán vacíos y listos para configurar.

## Nota

Una vez que ejecutes la migración, podrás editar todos los valores desde la interfaz web en `/admin/settings`.
