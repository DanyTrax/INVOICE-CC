# Instructivo: Actualización en el servidor (RAMS)

Después de subir cambios al repositorio y hacer **pull** en el servidor, ejecutar los siguientes comandos en la terminal **dentro del directorio del proyecto** (por ejemplo `regulatory.acdoblevia.com`).

## 1. Actualizar base de datos

```bash
php artisan migrate --force
```

- **`--force`** permite ejecutar migraciones en entorno de producción sin pedir confirmación.
- Si aparece **"Nothing to migrate"** es normal: no hay migraciones nuevas pendientes.

## 2. Enlace de almacenamiento (solo la primera vez o si se borró)

```bash
php artisan storage:link
```

- Si aparece **"The [public/storage] link already exists"** es normal: el enlace ya está creado. No hace falta hacer nada.

## 3. Limpiar cachés

```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

- **config:clear**: limpia la caché de configuración.
- **cache:clear**: limpia la caché de la aplicación.
- **view:clear**: limpia las vistas compiladas de Blade.

Así la aplicación usa la configuración y el código más recientes después del despliegue.

---

## Resumen rápido (copiar y pegar)

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

**Orden recomendado:** primero `git pull` (o el método que uses para traer los cambios), luego los comandos de arriba.
