# 🔧 Solución Error 403 Forbidden

## Problema
Después de hacer login, aparece error **403 Forbidden** al intentar acceder a `/admin`.

## Causa
El usuario no tiene los roles necesarios asignados o Shield no está configurado correctamente.

## Solución Paso a Paso

### 1. Ejecutar Migraciones (si no se han ejecutado)

```bash
php artisan migrate --force
```

### 2. Ejecutar Seeder para Crear Roles y Usuario Admin

```bash
php artisan db:seed --force
```

Esto creará:
- Roles: `super_admin`, `panel_user`, `agent`, `client`
- Usuario: `admin@rams.com` / `password` con rol `super_admin`

### 3. Verificar y Asignar Rol al Usuario Actual

Si ya tienes un usuario creado, necesitas asignarle el rol `super_admin`:

**Opción A: Por SSH/Terminal**

```bash
php artisan tinker
```

Luego ejecuta:
```php
$user = \App\Models\User::where('email', 'tu_email@ejemplo.com')->first();
$user->assignRole('super_admin');
exit
```

**Opción B: Crear Usuario Admin Manualmente**

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// Crear rol si no existe
Role::firstOrCreate(['name' => 'super_admin']);

// Crear o actualizar usuario
$user = User::firstOrCreate(
    ['email' => 'admin@rams.com'],
    [
        'name' => 'Admin User',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]
);

// Asignar rol
$user->assignRole('super_admin');
exit
```

### 4. Generar Permisos de Shield

```bash
php artisan shield:generate --all
```

### 5. Limpiar Caché

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 6. Verificar Configuración

Asegúrate de que el archivo `app/Providers/Filament/AdminPanelProvider.php` tenga:

```php
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

// En el método panel():
->plugin(FilamentShieldPlugin::make())
```

## Verificación Rápida

Para verificar que todo está correcto:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'admin@rams.com')->first();
$user->hasRole('super_admin'); // Debe retornar true
$user->roles; // Debe mostrar el rol super_admin
exit
```

## Credenciales por Defecto

- **Email:** `admin@rams.com`
- **Password:** `password`

## Si el Problema Persiste

1. Verifica los logs en `storage/logs/laravel.log`
2. Asegúrate de que las tablas `roles` y `model_has_roles` existen
3. Verifica que el usuario tenga `is_active = true`
4. Revisa que `APP_URL` en `.env` sea correcto

## Comandos Completos (Todo en Uno)

```bash
# 1. Migraciones y Seeders
php artisan migrate --force
php artisan db:seed --force

# 2. Generar permisos Shield
php artisan shield:generate --all

# 3. Limpiar todo
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Si necesitas crear el usuario manualmente
php artisan tinker
# Luego ejecuta el código de la Opción B arriba
```

---

**Después de estos pasos, deberías poder acceder al panel sin el error 403.** ✅
