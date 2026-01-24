# 🔍 Debug: Google Drive - Carpetas y Archivos

## Problema
El sistema guarda correctamente pero no crea carpetas en Drive ni carga archivos.

## Pasos para Diagnosticar

### 1. Verificar Configuración de Google Drive

Ve a `/admin/settings/drive` y verifica:
- ✅ **JSON de Service Account** está configurado
- ✅ **Carpeta Base (opcional)** está configurada (o se usará la raíz)
- ✅ El email de la Service Account está visible

### 2. Verificar Logs de Laravel

En el servidor, ejecuta:

```bash
cd /home/acdoblevia2/regulatory.acdoblevia.com
tail -f storage/logs/laravel.log
```

Luego intenta editar un expediente y subir un documento. Deberías ver mensajes como:
- `Carpeta creada para expediente actualizado`
- `Intentando subir documentos`
- `Documento subido a Google Drive`
- O errores si algo falla

### 3. Verificar Permisos de Service Account

1. Abre Google Drive
2. Busca la carpeta base configurada (o la raíz si no hay carpeta base)
3. Haz clic derecho → **Compartir**
4. Verifica que el email de la Service Account (del JSON) tenga permisos de **Editor**

### 4. Probar Conexión

En `/admin/settings/drive`, haz clic en **"Probar Conexión"**. Debería mostrar:
- ✅ "Conexión exitosa" si todo está bien
- ❌ Un error específico si hay problemas

### 5. Verificar Errores Comunes

#### Error: "Google Drive no está configurado"
- **Solución**: Configura el JSON de Service Account en `/admin/settings/drive`

#### Error: "El JSON de Service Account no es válido"
- **Solución**: Verifica que el JSON esté completo y bien formateado

#### Error: "Error al crear carpeta en Google Drive"
- **Causa**: La Service Account no tiene permisos en la carpeta padre
- **Solución**: Comparte la carpeta base con el email de la Service Account

#### Error: "No se puede subir documentos: el expediente no tiene carpeta en Drive"
- **Causa**: No se pudo crear la carpeta (ver errores anteriores)
- **Solución**: Revisa los logs para ver el error específico

### 6. Verificar en Base de Datos

```sql
-- Verificar si se está guardando el drive_folder_id
SELECT id, product_name, drive_folder_id, drive_folder_url 
FROM registrations 
WHERE id = [ID_DEL_EXPEDIENTE];

-- Verificar documentos
SELECT id, file_name, drive_id, created_at 
FROM documents 
WHERE registration_id = [ID_DEL_EXPEDIENTE];
```

### 7. Verificar en Google Drive

1. Abre Google Drive
2. Busca la carpeta del cliente (si tiene cliente asignado)
3. O busca la carpeta base configurada
4. Verifica si se creó la carpeta del expediente
5. Verifica si hay archivos dentro

## Soluciones Rápidas

### Si no se crea la carpeta:
1. Verifica que el JSON de Service Account esté correcto
2. Verifica que la carpeta base tenga permisos para la Service Account
3. Revisa los logs para ver el error específico

### Si no se suben archivos:
1. Verifica que la carpeta se haya creado (drive_folder_id en BD)
2. Verifica que el archivo no exceda 10MB
3. Revisa los logs para ver el error específico

### Si todo parece correcto pero no funciona:
1. Verifica que la API de Google Drive esté habilitada en Google Cloud Console
2. Verifica que la Service Account tenga el rol correcto (Editor u Owner)
3. Intenta crear un nuevo expediente desde cero para ver si funciona

## Comandos Útiles

```bash
# Ver últimos errores en logs
tail -n 100 storage/logs/laravel.log | grep -i "drive\|error"

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```
