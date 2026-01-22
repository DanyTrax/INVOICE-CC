# 📦 Guía de Instalación: AdminLTE 3 en Laravel

## Opción 1: Instalación Manual (Recomendada)

### Paso 1: Descargar AdminLTE 3

```bash
# Opción A: Desde GitHub
cd public
git clone https://github.com/ColorlibHQ/AdminLTE.git adminlte
cd adminlte
git checkout v3.2.0

# Opción B: Descargar ZIP desde https://github.com/ColorlibHQ/AdminLTE/releases
# Extraer en public/adminlte/
```

### Paso 2: Estructura de Archivos

```
public/
└── adminlte/
    ├── dist/
    │   ├── css/
    │   ├── js/
    │   └── img/
    ├── plugins/
    │   ├── fullcalendar/
    │   ├── datatables/
    │   └── select2/
    └── ...
```

### Paso 3: Referencias en Layout

En tu `resources/views/layouts/admin.blade.php`:

```html
<!-- CSS -->
<link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
<link rel="stylesheet" href="{{ asset('adminlte/plugins/fullcalendar/main.min.css') }}">

<!-- JS -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/fullcalendar/main.min.js') }}"></script>
```

---

## Opción 2: Usar CDN (Más Rápido para Desarrollo)

### En tu layout:

```html
<!-- AdminLTE CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
```

**Ventaja**: No necesitas descargar nada, funciona inmediatamente.

---

## Opción 3: NPM (Para Producción)

```bash
npm install admin-lte@3.2.0
npm install fullcalendar@6.1.15
npm install datatables.net-bs5
npm install select2
```

Luego compilar con Vite/Mix.

---

## ✅ Recomendación

**Para velocidad de desarrollo**: Usa **Opción 2 (CDN)** primero.

**Para producción**: Migra a **Opción 1 (Archivos locales)** o **Opción 3 (NPM)**.

---

## 🎨 Personalización de Colores

AdminLTE permite cambiar colores fácilmente. Para el color Teal del mockup:

```html
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <!-- ... -->
  </div>
</body>
```

Y en CSS custom:

```css
:root {
  --primary: #0f766e; /* Teal */
  --sidebar-bg: #1e293b; /* Azul oscuro */
}
```

---

## 📚 Próximos Pasos

1. Elegir método de instalación
2. Crear layout base
3. Configurar rutas
4. Empezar a migrar vistas
