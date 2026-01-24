# 📚 Guía: Habilitar API de Google Drive en Google Cloud Console

## 🎯 Objetivo
Habilitar la API de Google Drive para que el sistema RAMS pueda crear carpetas y subir archivos automáticamente.

---

## 📋 Pasos Detallados

### Paso 1: Acceder a Google Cloud Console

1. Abre tu navegador y ve a: **https://console.cloud.google.com/**
2. Inicia sesión con la cuenta de Google asociada a tu proyecto `rams-drive-integration`
3. Si tienes múltiples cuentas, asegúrate de seleccionar la cuenta correcta

---

### Paso 2: Seleccionar el Proyecto Correcto

1. En la parte superior de la página, verás un **selector de proyectos** (junto al logo de Google Cloud)
2. Haz clic en el selector
3. Busca y selecciona el proyecto: **`rams-drive-integration`**
4. Si no lo ves, puedes buscarlo escribiendo "rams-drive" en el campo de búsqueda

---

### Paso 3: Navegar a la Biblioteca de APIs

**Opción A: Desde el Menú Principal**
1. En el menú lateral izquierdo (☰), haz clic en **"APIs & Services"** (APIs y Servicios)
2. Luego haz clic en **"Library"** (Biblioteca)

**Opción B: Enlace Directo**
- Ve directamente a: **https://console.cloud.google.com/apis/library?project=rams-drive-integration**

---

### Paso 4: Buscar la API de Google Drive

1. En la página de la Biblioteca de APIs, verás un campo de búsqueda en la parte superior
2. Escribe: **"Google Drive API"** o **"Drive API"**
3. Presiona Enter o haz clic en el resultado que aparezca

---

### Paso 5: Habilitar la API

1. Verás la página de detalles de **"Google Drive API"**
2. En la parte superior, verás un botón grande que dice:
   - **"ENABLE"** (Habilitar) - si está en inglés
   - **"HABILITAR"** - si está en español
3. Haz clic en ese botón
4. Espera unos segundos mientras Google habilita la API
5. Verás un mensaje de confirmación cuando esté habilitada

---

### Paso 6: Verificar que Está Habilitada

1. Después de habilitar, la página cambiará
2. Verás un botón que dice **"MANAGE"** (Administrar) en lugar de "ENABLE"
3. Esto confirma que la API está habilitada correctamente

---

### Paso 7: Probar en RAMS

1. Regresa a tu sistema RAMS: **https://regulatory.acdoblevia.com/admin/settings/drive**
2. Haz clic en el botón **"Probar Conexión"**
3. Ahora debería mostrar: **✅ "Conexión exitosa con Google Drive API"**

---

## ⚠️ Solución de Problemas

### Problema 1: "No tengo acceso al proyecto"
**Solución:**
- Asegúrate de estar usando la cuenta de Google correcta
- Verifica que tengas permisos de "Editor" o "Owner" en el proyecto
- Si no tienes acceso, contacta al administrador del proyecto

### Problema 2: "No encuentro el proyecto rams-drive-integration"
**Solución:**
- Verifica que estés en la cuenta de Google correcta
- El proyecto podría tener un nombre diferente
- Busca proyectos que contengan "drive" o "rams" en el nombre

### Problema 3: "El botón ENABLE no aparece"
**Solución:**
- La API podría estar habilitada en otro proyecto
- Verifica que estés en el proyecto correcto
- Intenta refrescar la página (F5)

### Problema 4: "Sigue mostrando error después de habilitar"
**Solución:**
- Espera 1-2 minutos (a veces hay un pequeño retraso)
- Refresca la página de configuración en RAMS
- Vuelve a hacer clic en "Probar Conexión"
- Verifica que el JSON de Service Account esté guardado correctamente

---

## 🔍 Verificación Adicional

### Ver APIs Habilitadas en tu Proyecto

1. Ve a: **APIs & Services** → **Dashboard** (Panel)
2. Verás una lista de todas las APIs habilitadas
3. Busca **"Google Drive API"** en la lista
4. Debería aparecer con un estado **"ENABLED"** (Habilitada)

---

## 📝 Notas Importantes

- ✅ La habilitación de la API es **gratuita** (solo pagas por el uso)
- ✅ Una vez habilitada, **permanece habilitada** (no se desactiva automáticamente)
- ✅ Puedes habilitar/deshabilitar APIs en cualquier momento
- ✅ La habilitación es **inmediata** (no requiere esperar horas)

---

## 🎉 Siguiente Paso

Una vez que la API esté habilitada y la prueba de conexión sea exitosa:

1. **Crear un Cliente** → Se creará automáticamente su carpeta en Drive
2. **Crear un Expediente** → Se creará automáticamente su carpeta dentro del cliente
3. **Subir Documentos** → Se subirán automáticamente a la carpeta del expediente

---

## 📞 ¿Necesitas Ayuda?

Si después de seguir estos pasos sigues teniendo problemas:

1. Verifica los logs de Laravel: `tail -f storage/logs/laravel.log`
2. Revisa que el JSON de Service Account esté correctamente guardado
3. Verifica que la carpeta base en Drive esté compartida con el email de la Service Account

---

**Última actualización:** 2026-01-24
