# 📧 Instructivo: Configuración de Zoho Mail API

Este documento explica paso a paso cómo configurar Zoho Mail API en el sistema RAMS.

---

## 📋 Requisitos Previos

1. Cuenta de Zoho Mail activa
2. Acceso a [Zoho API Console](https://api-console.zoho.com/)
3. Dominio verificado en Zoho Mail (opcional pero recomendado)

---

## 🔧 Paso 1: Crear Aplicación en Zoho API Console

### 1.1 Acceder a Zoho API Console

1. Ve a: **https://api-console.zoho.com/**
2. Inicia sesión con tu cuenta de Zoho
3. Si es la primera vez, acepta los términos y condiciones

### 1.2 Crear Nueva Aplicación

1. Haz clic en **"ADD CLIENT"** o **"Agregar Cliente"**
2. Selecciona **"Server-based Applications"** (Aplicaciones basadas en servidor)
3. Completa el formulario:

   **Información de la Aplicación:**
   - **Client Name**: `RAMS Sistema` (o el nombre que prefieras)
   - **Homepage URL**: `https://tu-dominio.com` (URL de tu aplicación)
   - **Authorized Redirect URIs**: 
     ```
     https://tu-dominio.com/admin/settings
     https://tu-dominio.com/callback/zoho
     ```
     ⚠️ **IMPORTANTE**: Reemplaza `tu-dominio.com` con tu dominio real

4. Haz clic en **"CREATE"** o **"Crear"**

### 1.3 Obtener Credenciales

Después de crear la aplicación, verás:

- **Client ID**: `1000.XXXXXXXXXXXX` (copia este valor)
- **Client Secret**: `XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX` (copia este valor)

⚠️ **GUARDA ESTAS CREDENCIALES DE FORMA SEGURA**

---

## 🔑 Paso 2: Generar Refresh Token

### 2.1 Preparar la URL de Autorización

Necesitas construir una URL con los siguientes parámetros:

```
https://accounts.zoho.com/oauth/v2/auth?
scope=ZohoMail.messages.CREATE,ZohoMail.accounts.READ&
client_id=TU_CLIENT_ID&
response_type=code&
access_type=offline&
redirect_uri=TU_REDIRECT_URI
```

**Parámetros:**
- `scope`: `ZohoMail.messages.CREATE,ZohoMail.accounts.READ`
- `client_id`: Tu Client ID obtenido en el paso 1.3
- `response_type`: `code`
- `access_type`: `offline` (importante para obtener refresh token)
- `redirect_uri`: Debe coincidir EXACTAMENTE con una de las URLs autorizadas en el paso 1.2

**Ejemplo completo:**
```
https://accounts.zoho.com/oauth/v2/auth?scope=ZohoMail.messages.CREATE,ZohoMail.accounts.READ&client_id=1000.XXXXXXXXXXXX&response_type=code&access_type=offline&redirect_uri=https://tu-dominio.com/callback/zoho
```

### 2.2 Autorizar la Aplicación

1. Copia la URL completa del paso 2.1
2. Pégalo en tu navegador y presiona Enter
3. Inicia sesión con tu cuenta de Zoho si es necesario
4. Autoriza la aplicación cuando se te solicite
5. Serás redirigido a la URL de redirect con un código en la URL:
   ```
   https://tu-dominio.com/callback/zoho?code=1000.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
   ```

### 2.3 Obtener Refresh Token

**Opción A: Usando cURL (Recomendado)**

Abre tu terminal y ejecuta:

```bash
curl -X POST https://accounts.zoho.com/oauth/v2/token \
  -d "grant_type=authorization_code" \
  -d "client_id=TU_CLIENT_ID" \
  -d "client_secret=TU_CLIENT_SECRET" \
  -d "redirect_uri=TU_REDIRECT_URI" \
  -d "code=CODIGO_OBTENIDO_EN_PASO_2.2"
```

**Reemplaza:**
- `TU_CLIENT_ID`: Tu Client ID
- `TU_CLIENT_SECRET`: Tu Client Secret
- `TU_REDIRECT_URI`: La misma URL que usaste en el paso 2.1
- `CODIGO_OBTENIDO_EN_PASO_2.2`: El código que aparece en la URL después de autorizar

**Respuesta esperada:**
```json
{
  "access_token": "1000.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
  "refresh_token": "1000.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
  "expires_in": 3600,
  "token_type": "Bearer"
}
```

**Copia el valor de `refresh_token`** - este es el que necesitas.

**Opción B: Usando Postman o herramienta similar**

1. Método: **POST**
2. URL: `https://accounts.zoho.com/oauth/v2/token`
3. Body (x-www-form-urlencoded):
   - `grant_type`: `authorization_code`
   - `client_id`: Tu Client ID
   - `client_secret`: Tu Client Secret
   - `redirect_uri`: Tu Redirect URI
   - `code`: El código obtenido en el paso 2.2

---

## ⚙️ Paso 3: Configurar en RAMS

### 3.1 Acceder a Configuración

1. Inicia sesión en el sistema RAMS
2. Ve a **Configuración** → **Correo & SMTP**

### 3.2 Seleccionar Proveedor

1. En el campo **"Proveedor de Correo"**, selecciona **"Zoho Mail API"**

### 3.3 Completar Campos

**Client ID:**
- Campo: **Client ID**
- Valor: Pega el Client ID obtenido en el paso 1.3
- Ejemplo: `1000.XXXXXXXXXXXX`

**Client Secret:**
- Campo: **Client Secret**
- Valor: Pega el Client Secret obtenido en el paso 1.3
- Ejemplo: `XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`

**Refresh Token:**
- Campo: **Refresh Token**
- Valor: Pega el Refresh Token obtenido en el paso 2.3
- Ejemplo: `1000.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`

**Email Remitente:**
- Campo: **Email Remitente**
- Valor: El email autorizado en tu cuenta de Zoho Mail
- Ejemplo: `noreply@tudominio.com`
- ⚠️ Este email debe estar verificado en tu cuenta de Zoho

### 3.4 Guardar Configuración

1. Haz clic en **"Guardar Configuración"**
2. Espera el mensaje de confirmación

---

## ✅ Paso 4: Verificar Configuración

### 4.1 Verificar en el Sistema

1. Revisa que todos los campos estén guardados correctamente
2. El sistema automáticamente obtendrá un Access Token cuando sea necesario

### 4.2 Probar Envío de Correo

Puedes probar el envío de correo desde el código:

```php
use App\Services\MailService;

$mailService = app(MailService::class);

$resultado = $mailService->send(
    to: 'destinatario@example.com',
    subject: 'Prueba de correo',
    body: '<h1>Este es un correo de prueba</h1><p>Si recibes esto, la configuración está correcta.</p>'
);

if ($resultado) {
    echo "Correo enviado exitosamente";
} else {
    echo "Error al enviar correo. Revisa los logs.";
}
```

---

## 🔍 Solución de Problemas

### Error: "Invalid Client ID"

- Verifica que el Client ID esté correctamente copiado
- Asegúrate de que no haya espacios adicionales

### Error: "Invalid Client Secret"

- Verifica que el Client Secret esté correctamente copiado
- Asegúrate de que no haya espacios adicionales

### Error: "Invalid Refresh Token"

- Verifica que el Refresh Token sea el correcto
- Asegúrate de que se haya generado con `access_type=offline`
- Intenta generar un nuevo Refresh Token

### Error: "Invalid Redirect URI"

- Verifica que la Redirect URI en la configuración coincida EXACTAMENTE con la registrada en Zoho API Console
- Las URLs son case-sensitive y deben coincidir carácter por carácter

### Error: "Access Denied"

- Verifica que hayas autorizado la aplicación en el paso 2.2
- Asegúrate de que el scope incluya `ZohoMail.messages.CREATE`

### Error: "Email no autorizado"

- Verifica que el email remitente esté verificado en tu cuenta de Zoho Mail
- Asegúrate de que el email tenga permisos para enviar correos

---

## 📝 Notas Importantes

1. **Seguridad:**
   - Nunca compartas tus credenciales (Client ID, Client Secret, Refresh Token)
   - Mantén estas credenciales en un lugar seguro
   - Si sospechas que se han comprometido, revócalas y genera nuevas

2. **Refresh Token:**
   - El Refresh Token no expira (a menos que lo revoques)
   - Guárdalo de forma segura
   - Si lo pierdes, deberás generar uno nuevo

3. **Access Token:**
   - El Access Token expira después de 1 hora
   - El sistema lo renueva automáticamente usando el Refresh Token
   - No necesitas gestionarlo manualmente

4. **Límites:**
   - Zoho Mail tiene límites en el número de correos que puedes enviar
   - Revisa los límites de tu plan en Zoho

5. **URLs de Confianza:**
   - Las URLs de redirect deben usar HTTPS en producción
   - Asegúrate de que las URLs estén correctamente configuradas en Zoho API Console

---

## 🔗 Enlaces Útiles

- [Zoho API Console](https://api-console.zoho.com/)
- [Documentación Zoho Mail API](https://www.zoho.com/mail/help/api/)
- [Guía de OAuth2 de Zoho](https://www.zoho.com/mail/help/api/using-oauth.html)
- [Scopes disponibles](https://www.zoho.com/mail/help/api/api-scopes.html)

---

## 📞 Soporte

Si tienes problemas con la configuración:

1. Revisa los logs del sistema en `storage/logs/laravel.log`
2. Verifica que todas las credenciales estén correctas
3. Asegúrate de que las URLs de redirect coincidan exactamente
4. Contacta al administrador del sistema si el problema persiste

---

**Última actualización:** Enero 2026
