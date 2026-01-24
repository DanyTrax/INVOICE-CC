# 🔧 Agregar Settings Faltantes

## Problema
Los nuevos settings (drive_folder_id, footer_text, system_name) pueden no estar en la base de datos.

## Solución Automática

El sistema los creará automáticamente cuando accedas a Configuración. Pero si quieres agregarlos manualmente:

### Opción 1: Acceder a Configuración
Simplemente ve a `/admin/settings` y el sistema los creará automáticamente.

### Opción 2: Ejecutar SQL Manual

```sql
-- Verificar qué settings faltan
SELECT name FROM settings WHERE `group` = 'general';

-- Agregar settings faltantes
INSERT INTO `settings` (`group`, `name`, `locked`, `payload`, `created_at`, `updated_at`) VALUES
('general', 'drive_folder_id', 0, '""', NOW(), NOW()),
('general', 'footer_text', 0, '"RAMS - Regulatory Affairs Management System"', NOW(), NOW()),
('general', 'system_name', 0, '"Sistema de Gestión Regulatoria"', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
```

### Opción 3: Usar Tinker

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\DB;

$settings = [
    'drive_folder_id' => '',
    'footer_text' => 'RAMS - Regulatory Affairs Management System',
    'system_name' => 'Sistema de Gestión Regulatoria',
];

foreach ($settings as $name => $value) {
    $exists = DB::table('settings')
        ->where('group', 'general')
        ->where('name', $name)
        ->exists();
    
    if (!$exists) {
        DB::table('settings')->insert([
            'group' => 'general',
            'name' => $name,
            'locked' => false,
            'payload' => json_encode($value),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✅ Agregado: {$name}\n";
    } else {
        echo "⏭️  Ya existe: {$name}\n";
    }
}

exit
```
