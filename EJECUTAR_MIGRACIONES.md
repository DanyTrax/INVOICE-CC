# 🔧 Guía para Ejecutar Migraciones

## Problema
La migración de settings está fallando porque intenta crear settings que ya existen.

## Solución

### Opción 1: Ejecutar solo las nuevas migraciones (Recomendada)

Ejecuta las migraciones específicas que faltan:

```bash
# Ejecutar solo las nuevas migraciones
php artisan migrate --path=database/migrations/2026_01_24_012841_add_drive_folder_id_to_registrations_table.php
php artisan migrate --path=database/migrations/2026_01_24_013512_make_company_id_nullable_in_registrations_table.php
```

### Opción 2: Marcar la migración de settings como ejecutada

Si la migración de settings ya se ejecutó parcialmente, puedes marcarla como ejecutada:

```bash
# Insertar registro en migrations table para marcar como ejecutada
php artisan tinker
```

Luego ejecuta:
```php
DB::table('migrations')->insert([
    'migration' => '2026_01_22_063941_create_general_settings',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
exit
```

Después ejecuta:
```bash
php artisan migrate
```

### Opción 3: Ejecutar migraciones ignorando errores de settings

Si solo quieres ejecutar las nuevas migraciones sin tocar settings:

```bash
# Ejecutar todas las migraciones pendientes excepto settings
php artisan migrate --pretend  # Ver qué se ejecutaría
php artisan migrate --force    # Ejecutar forzadamente
```

### Opción 4: Ejecutar migraciones de settings manualmente

Si prefieres ejecutar la migración de settings manualmente con el código corregido:

```bash
# La migración ahora verifica antes de crear, así que debería funcionar
php artisan migrate --path=database/settings/2026_01_22_063941_create_general_settings.php
```

## Verificación

Después de ejecutar las migraciones, verifica:

```bash
# Verificar que las columnas existen
php artisan tinker
```

```php
// Verificar drive_folder_id en registrations
Schema::hasColumn('registrations', 'drive_folder_id'); // Debe retornar true

// Verificar que company_id es nullable
$column = DB::select("SHOW COLUMNS FROM registrations WHERE Field = 'company_id'");
$column[0]->Null; // Debe ser 'YES'

exit
```

## Nota Importante

La migración de settings ahora verifica si cada setting existe antes de crearlo, por lo que puedes ejecutarla múltiples veces sin errores.
