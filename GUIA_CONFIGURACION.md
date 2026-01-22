# 📋 Guía de Configuración - RAMS

## 🎯 Configuración Paso a Paso

Esta guía te explica cómo configurar cada sección del sistema.

---

## 📍 Acceso a Configuración

1. Inicia sesión en el sistema
2. Ve al menú lateral → **SISTEMA** → **Configuración**
3. O accede directamente a: `/admin/settings`

---

## 📑 Sección 1: Datos de la Empresa (White Label)

### ¿Qué es?
Configura los datos de tu empresa que aparecerán en todo el sistema (marca blanca).

### Campos a Configurar:

#### 1. **Nombre de la Empresa** ⭐ (Requerido)
- **Qué es:** Nombre oficial de tu empresa
- **Ejemplo:** "AC Doblevia Regulatory"
- **Dónde aparece:** Encabezados, emails, reportes

#### 2. **NIT**
- **Qué es:** Número de identificación tributaria
- **Ejemplo:** "900123456-7"
- **Dónde aparece:** Facturas, documentos oficiales

#### 3. **Dirección**
- **Qué es:** Dirección física de la empresa
- **Ejemplo:** "Calle 123 #45-67, Bogotá"
- **Dónde aparece:** Documentos, emails

#### 4. **Teléfono**
- **Qué es:** Número de contacto principal
- **Ejemplo:** "+57 1 234 5678"
- **Dónde aparece:** Documentos, emails

#### 5. **Email**
- **Qué es:** Email de contacto general
- **Ejemplo:** "contacto@acdoblevia.com"
- **Dónde aparece:** Documentos, emails

#### 6. **Sitio Web**
- **Qué es:** URL de tu sitio web
- **Ejemplo:** "https://www.acdoblevia.com"
- **Dónde aparece:** Documentos, emails

#### 7. **URL del Logo**
- **Qué es:** URL pública de tu logo
- **Ejemplo:** "https://www.acdoblevia.com/logo.png"
- **Dónde aparece:** Encabezados, emails, reportes

### ✅ Pasos:
1. Completa el formulario con los datos de tu empresa
2. Haz clic en **"Guardar Configuración"**
3. Los cambios se aplican inmediatamente

---

## 📑 Sección 2: Conexión Google Drive

### ¿Qué es?
Configura la integración con Google Drive para almacenar documentos automáticamente.

### Requisitos Previos:
1. Tener una cuenta de Google Cloud Platform
2. Crear un proyecto en Google Cloud
3. Habilitar la API de Google Drive
4. Crear una Service Account
5. Descargar el archivo JSON de la Service Account

### Pasos para Obtener el JSON:

#### Paso 1: Crear Proyecto en Google Cloud
1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente

#### Paso 2: Habilitar API de Google Drive
1. Ve a **APIs & Services** → **Library**
2. Busca "Google Drive API"
3. Haz clic en **Enable**

#### Paso 3: Crear Service Account
1. Ve a **APIs & Services** → **Credentials**
2. Haz clic en **Create Credentials** → **Service Account**
3. Completa:
   - **Name:** "RAMS Drive Service"
   - **Description:** "Service account para RAMS"
4. Haz clic en **Create and Continue**
5. En **Grant this service account access to project:**
   - Selecciona rol: **Editor** o **Owner**
6. Haz clic en **Done**

#### Paso 4: Crear y Descargar JSON
1. En la lista de Service Accounts, haz clic en la que acabas de crear
2. Ve a la pestaña **Keys**
3. Haz clic en **Add Key** → **Create new key**
4. Selecciona **JSON**
5. Haz clic en **Create**
6. Se descargará un archivo JSON

#### Paso 5: Compartir Carpeta en Drive
1. Abre Google Drive
2. Crea una carpeta llamada "RAMS"
3. Haz clic derecho → **Share**
4. Pega el email de la Service Account (está en el JSON, campo `client_email`)
5. Dale permisos de **Editor**
6. Haz clic en **Send**

#### Paso 6: Copiar JSON al Sistema
1. Abre el archivo JSON descargado
2. Copia TODO el contenido
3. Pégalo en el campo **"JSON de Service Account"** en RAMS
4. Haz clic en **"Guardar Configuración"**

### ⚠️ Importante:
- El JSON contiene información sensible, mantenlo seguro
- No compartas el JSON públicamente
- Si lo pierdes, puedes crear uno nuevo desde Google Cloud

---

## 📑 Sección 3: Correo & SMTP

### ¿Qué es?
Configura el servidor SMTP para enviar correos electrónicos desde el sistema.

### Configuración para Gmail:

#### 1. **Mailer** ⭐
- **Valor:** `smtp`
- **No cambiar** (a menos que uses otro servicio)

#### 2. **Host SMTP** ⭐
- **Gmail:** `smtp.gmail.com`
- **Outlook:** `smtp-mail.outlook.com`
- **Otros:** Consulta con tu proveedor

#### 3. **Puerto** ⭐
- **Gmail con TLS:** `587`
- **Gmail con SSL:** `465`
- **Outlook:** `587`

#### 4. **Usuario**
- **Gmail:** Tu email completo (ej: `tu@gmail.com`)
- **Outlook:** Tu email completo

#### 5. **Contraseña**
- **Gmail:** Necesitas una "Contraseña de aplicación"
  - Ve a tu cuenta Google → **Seguridad**
  - **Verificación en 2 pasos** → Activar
  - **Contraseñas de aplicaciones** → Generar nueva
  - Usa esa contraseña (16 caracteres)
- **Outlook:** Tu contraseña normal o contraseña de app

#### 6. **Encriptación**
- **TLS:** Usa puerto 587 (recomendado)
- **SSL:** Usa puerto 465

#### 7. **Email Remitente** ⭐
- **Ejemplo:** `noreply@rams.com`
- Este email aparecerá como remitente

#### 8. **Nombre Remitente** ⭐
- **Ejemplo:** `RAMS Sistema`
- Este nombre aparecerá como remitente

### ✅ Pasos:
1. Completa todos los campos marcados con ⭐
2. Configura usuario y contraseña según tu proveedor
3. Haz clic en **"Guardar Configuración"**
4. Prueba enviando un email de prueba (si hay función disponible)

### 🔧 Configuración para Otros Proveedores:

#### SendGrid:
- Host: `smtp.sendgrid.net`
- Puerto: `587`
- Usuario: `apikey`
- Contraseña: Tu API Key de SendGrid

#### Mailgun:
- Host: `smtp.mailgun.org`
- Puerto: `587`
- Usuario: Tu dominio de Mailgun
- Contraseña: Tu contraseña de Mailgun

---

## 📑 Sección 4: Plantillas de Email

### ¿Qué es?
Gestiona las plantillas de correo que se envían desde el sistema.

### Plantillas Disponibles:

#### 1. **Invitación de Cliente**
- **Cuándo se usa:** Al invitar un nuevo cliente
- **Variables disponibles:**
  - `{name}` - Nombre del cliente
  - `{email}` - Email del cliente
  - `{link}` - Link de registro
  - `{company_name}` - Nombre de la empresa

#### 2. **Recordatorio de Vencimiento**
- **Cuándo se usa:** Alerta de expediente próximo a vencer
- **Variables disponibles:**
  - `{product_name}` - Nombre del producto
  - `{expiration_date}` - Fecha de vencimiento
  - `{company_name}` - Nombre del cliente

### ✅ Pasos para Editar una Plantilla:

1. Selecciona la pestaña **"Plantillas de Email"**
2. Encuentra la plantilla que quieres editar
3. Modifica el **Asunto** (título del email)
4. Modifica el **Cuerpo del Mensaje** (contenido)
5. Usa las variables disponibles entre llaves: `{variable}`
6. Haz clic en **"Guardar Plantilla"**

### 📝 Ejemplo de Plantilla:

**Asunto:**
```
Bienvenido a RAMS - {company_name}
```

**Cuerpo:**
```
Hola {name},

Bienvenido al sistema RAMS. Tu empresa {company_name} ha sido registrada exitosamente.

Para acceder al sistema, haz clic en el siguiente enlace:
{link}

Saludos,
Equipo RAMS
```

---

## 🔄 Orden Recomendado de Configuración

1. **Primero:** Datos de la Empresa (básico, siempre necesario)
2. **Segundo:** Correo & SMTP (si vas a enviar emails)
3. **Tercero:** Google Drive (si vas a usar integración)
4. **Cuarto:** Plantillas de Email (personalizar mensajes)

---

## ✅ Verificación

Después de configurar cada sección:

1. **Datos de la Empresa:**
   - Verifica que aparezcan en el dashboard
   - Revisa emails enviados

2. **Google Drive:**
   - Intenta crear un expediente y subir un documento
   - Verifica que se cree la carpeta en Drive

3. **Correo & SMTP:**
   - Envía un email de prueba
   - Verifica que llegue correctamente

4. **Plantillas:**
   - Envía un email usando una plantilla
   - Verifica que las variables se reemplacen correctamente

---

## 🆘 Solución de Problemas

### Error: "Settings no encontrado"
- Ejecuta: `php artisan settings:migrate`

### Error: "No se puede guardar configuración"
- Verifica permisos de la base de datos
- Revisa los logs: `storage/logs/laravel.log`

### Error: "Email no se envía"
- Verifica configuración SMTP
- Revisa que el puerto no esté bloqueado
- Para Gmail, usa contraseña de aplicación

### Error: "Google Drive no funciona"
- Verifica que el JSON sea válido
- Revisa que la Service Account tenga permisos
- Verifica que la carpeta esté compartida

---

## 📞 Soporte

Si tienes problemas con la configuración:
1. Revisa esta guía
2. Consulta los logs del sistema
3. Verifica la documentación de cada servicio

---

**Última actualización:** Enero 2026
