# Restaurar Plantillas de Email

Si las plantillas se borraron o están vacías, ejecuta este comando para restaurarlas:

```bash
php artisan db:seed --class=EmailTemplateSeeder
```

O si quieres ejecutar todos los seeders:

```bash
php artisan db:seed
```

Esto restaurará todas las plantillas con sus valores por defecto.
