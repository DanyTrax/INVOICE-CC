# 🎯 Estructura Simple - RAMS

## Stack Final (Simple y Fácil)

### Backend
- ✅ **Laravel 12** (Framework base)
- ✅ **Eloquent ORM** (Modelos y relaciones)
- ✅ **Spatie Permission** (Roles y permisos)
- ✅ **Spatie Settings** (Configuración)

### Frontend
- ✅ **Flowbite + Tailwind** (Componentes UI)
- ✅ **FullCalendar.js** (Calendario)
- ✅ **Alpine.js** (Interactividad ligera)
- ✅ **Componentes Blade** (Widgets reutilizables)

---

## 📁 Estructura de Archivos

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── CompanyController.php
│   │   │   ├── RegistrationController.php
│   │   │   ├── UserController.php
│   │   │   └── SettingsController.php
│   │   └── Auth/
│   │       └── LoginController.php
│   └── Requests/
│       ├── CompanyRequest.php
│       ├── RegistrationRequest.php
│       └── UserRequest.php
│
├── Models/
│   ├── User.php
│   ├── Company.php
│   ├── Registration.php
│   └── Document.php
│
└── View/
    └── Components/
        ├── Widgets/
        │   ├── StatsCard.php
        │   ├── Calendar.php
        │   └── Table.php
        └── Layouts/
            └── Admin.php

resources/
├── views/
│   ├── components/
│   │   ├── widgets/
│   │   │   ├── stats-card.blade.php
│   │   │   ├── calendar.blade.php
│   │   │   └── table.blade.php
│   │   └── layouts/
│   │       └── admin.blade.php
│   │
│   ├── admin/
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── companies/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php
│   │   ├── registrations/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php
│   │   ├── users/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php
│   │   └── settings/
│   │       └── index.blade.php
│   │
│   └── auth/
│       └── login.blade.php
│
└── css/
    └── app.css (Tailwind)
```

---

## 🧩 Sistema de Widgets (Súper Fácil)

### Uso de Widgets

```blade
<!-- En cualquier vista -->
<x-widgets.stats-card 
    title="Expedientes Activos"
    value="1,240"
    icon="clipboard-list"
    color="blue"
    link="/admin/registrations"
/>

<x-widgets.calendar :events="$events" />

<x-widgets.table 
    :headers="['Nombre', 'Email', 'Acciones']"
    :rows="$users"
/>
```

### Ventajas

✅ **Súper fácil de usar** - Solo incluir componente
✅ **Reutilizable** - Mismo widget en cualquier vista
✅ **Personalizable** - Props para cambiar contenido
✅ **Sin JavaScript complejo** - Todo en Blade
✅ **Mantenible** - Un archivo por widget

---

## 🎨 Componentes Flowbite Incluidos

- **Cards** - Para estadísticas
- **Tables** - Para listados
- **Forms** - Para formularios
- **Modals** - Para acciones
- **Alerts** - Para mensajes
- **Buttons** - Botones con estilos
- **Calendar** - FullCalendar integrado

---

## 🚀 Ventajas de esta Estructura

1. **Simple**: Sin abstracciones complejas
2. **Fácil**: Widgets como componentes Blade
3. **Rápido**: Desarrollo rápido
4. **Mantenible**: Código claro y organizado
5. **Escalable**: Fácil agregar nuevos widgets
6. **Sin dependencias pesadas**: Solo lo necesario

---

## 📝 Próximos Pasos

1. ✅ Limpiar Filament
2. ✅ Crear componentes de widgets
3. ✅ Migrar CRUDs
4. ✅ Implementar dashboard
5. ✅ Agregar funcionalidades
