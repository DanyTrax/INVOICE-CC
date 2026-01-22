# 🚀 Plan de Migración: Filament → AdminLTE 3

## Objetivo
Reemplazar completamente Filament por AdminLTE 3 para tener control total y velocidad de desarrollo.

---

## 📦 Stack Tecnológico Final

### Frontend
- **AdminLTE 3** (Template admin completo)
- **Bootstrap 5** (incluido en AdminLTE)
- **FullCalendar.js** (incluido en AdminLTE)
- **jQuery** (incluido en AdminLTE)
- **DataTables** (para tablas avanzadas)
- **Select2** (para selects mejorados)
- **SweetAlert2** (para alertas bonitas)

### Backend
- **Laravel 12** (mantener)
- **Eloquent ORM** (mantener)
- **Spatie Permission** (mantener)
- **Laravel Auditing** (mantener)
- **Spatie Settings** (mantener)

---

## 📁 Estructura de Archivos Propuesta

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
resources/
├── views/
│   ├── layouts/
│   │   └── admin.blade.php (Layout principal AdminLTE)
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
│   └── auth/
│       └── login.blade.php
│
public/
├── adminlte/ (Assets de AdminLTE)
└── css/
    └── custom.css (Estilos personalizados)
```

---

## 🎯 Fases de Implementación

### FASE 1: Setup Base (2-3 horas)
1. **Instalar AdminLTE 3**
   - Descargar desde GitHub o usar npm
   - Copiar assets a `public/adminlte/`
   - Crear layout base `resources/views/layouts/admin.blade.php`

2. **Configurar Autenticación**
   - Crear `LoginController` y vista de login
   - Configurar middleware de autenticación
   - Crear rutas de autenticación

3. **Crear Dashboard Base**
   - `DashboardController` básico
   - Vista con estructura AdminLTE
   - Sidebar con menú

### FASE 2: Migrar CRUDs (1 día)
1. **Companies (Clientes)**
   - `CompanyController` con CRUD completo
   - Vistas: index (tabla), create, edit, show
   - Validación con Form Requests

2. **Registrations (Expedientes)**
   - `RegistrationController` con CRUD completo
   - Formulario reactivo (autocomplete de company)
   - Vistas completas

3. **Users (Usuarios)**
   - `UserController` con CRUD completo
   - Gestión de roles (Shield)
   - Vistas completas

### FASE 3: Dashboard Avanzado (4-6 horas)
1. **Estadísticas**
   - Cards con métricas (igual que mockup)
   - Consultas optimizadas

2. **Calendario**
   - Integrar FullCalendar.js (ya incluido en AdminLTE)
   - Eventos desde base de datos
   - Navegación mes anterior/siguiente

3. **Widgets**
   - Gráficos (Chart.js si es necesario)
   - Tablas resumen

### FASE 4: Funcionalidades Especiales (1 día)
1. **Settings**
   - Página de configuración
   - Tabs para diferentes secciones
   - Guardar con Spatie Settings

2. **Acciones Especiales**
   - "Invitar" cliente (modal + email)
   - "Ver Expedientes" (filtro)
   - Audit Log visual

3. **Google Drive Integration**
   - Placeholder para integración futura

### FASE 5: Panel Cliente (Opcional, 1 día)
1. **ClientPanel**
   - Layout simplificado
   - Solo lectura de registros
   - Dashboard básico

---

## 🔧 Componentes AdminLTE a Usar

### Layout
- **Sidebar**: Menú lateral con iconos
- **Navbar**: Barra superior con usuario
- **Content Wrapper**: Área principal
- **Footer**: Pie de página

### Componentes
- **Cards**: Para estadísticas y contenido
- **Tables**: DataTables para listados
- **Forms**: Bootstrap 5 forms
- **Modals**: Para acciones rápidas
- **Alerts**: Para mensajes
- **Calendar**: FullCalendar integrado

---

## 📋 Ventajas de AdminLTE 3

✅ **Template Completo**: Todo listo, solo personalizar
✅ **FullCalendar Incluido**: Sin problemas de carga
✅ **DataTables**: Tablas avanzadas sin código
✅ **Componentes Listos**: Modals, alerts, cards, etc.
✅ **Documentación Excelente**: Fácil de seguir
✅ **Comunidad Grande**: Muchos ejemplos
✅ **Responsive**: Funciona en móvil
✅ **Temas**: Múltiples colores disponibles

---

## 🚫 Lo que NO Necesitas

❌ Filament (remover completamente)
❌ Livewire (opcional, no necesario)
❌ Vite (solo para assets custom)
❌ Dependencias complejas de JS

---

## 📝 Pasos Inmediatos

1. **Remover Filament** (opcional, puede coexistir)
2. **Instalar AdminLTE 3**
3. **Crear layout base**
4. **Migrar dashboard primero** (para ver resultados rápido)
5. **Migrar CRUDs uno por uno**
6. **Ajustar estilos al mockup**

---

## ⏱️ Tiempo Total Estimado

- **Setup Base**: 2-3 horas
- **CRUDs**: 1 día
- **Dashboard**: 4-6 horas
- **Funcionalidades**: 1 día
- **Ajustes y pulido**: 4-6 horas

**Total: 3-4 días de desarrollo**

---

## 🎨 Personalización al Mockup

### Colores
- **Primary**: Teal (#0f766e) - igual que Filament
- **Sidebar**: Azul oscuro (como mockup)
- **Cards**: Blanco con bordes
- **Eventos**: Rojo (vencimientos), Azul (radicaciones)

### Layout
- Sidebar izquierdo (como mockup)
- Navbar superior
- Contenido principal centrado
- Cards de estadísticas en fila

---

## 📚 Recursos

- **AdminLTE 3 Docs**: https://adminlte.io/docs/3.2/
- **FullCalendar Docs**: https://fullcalendar.io/docs
- **DataTables Docs**: https://datatables.net/
- **Bootstrap 5 Docs**: https://getbootstrap.com/docs/5.3/

---

## ✅ Checklist de Migración

- [ ] Instalar AdminLTE 3
- [ ] Crear layout base
- [ ] Configurar autenticación
- [ ] Crear dashboard
- [ ] Migrar Companies
- [ ] Migrar Registrations
- [ ] Migrar Users
- [ ] Implementar calendario
- [ ] Implementar Settings
- [ ] Agregar acciones especiales
- [ ] Ajustar estilos al mockup
- [ ] Testing completo
- [ ] Remover Filament (opcional)

---

## 🚀 ¿Empezamos?

Este plan te dará un sistema completo, rápido y sin problemas de dependencias. ¿Quieres que comience con la FASE 1?
