@php
    $ramsAdminTheme = auth()->check() ? (auth()->user()->admin_theme ?? 'light') : 'light';
    if (! in_array($ramsAdminTheme, ['light', 'dark', 'system'], true)) {
        $ramsAdminTheme = 'light';
    }
    $ramsUiFontScale = 100;
    if (auth()->check()) {
        $rawFontScale = (int) (auth()->user()->admin_ui_font_scale ?? 100);
        $ramsUiFontScale = in_array($rawFontScale, [90, 100, 110, 125], true) ? $rawFontScale : 100;
    }
    try {
        $adminSidebarExpandedDefault = (bool) (app(\App\Settings\GeneralSettings::class)->admin_sidebar_expanded_default ?? false);
    } catch (\Throwable $e) {
        $adminSidebarExpandedDefault = false;
    }
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full overflow-hidden bg-gray-50" data-theme="{{ $ramsAdminTheme }}">
<head>
    <meta charset="UTF-8">
    <script>
        (function () {
            var pref = @json($ramsAdminTheme);
            if (pref !== 'light' && pref !== 'dark' && pref !== 'system') {
                pref = 'light';
            }
            var dark = pref === 'dark'
                || (pref === 'system' && typeof matchMedia !== 'undefined' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.setAttribute('data-theme', pref);
        })();
        (function () {
            var scale = @json($ramsUiFontScale);
            if ([90, 100, 110, 125].indexOf(scale) === -1) {
                scale = 100;
            }
            document.documentElement.style.fontSize = scale + '%';
            document.documentElement.setAttribute('data-ui-font-scale', String(scale));
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Deshabilitar Cloudflare Insights beacon -->
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }
        .rams-admin-scroll {
            scrollbar-gutter: stable;
        }
        html.dark { background-color: #0f172a; color: #e2e8f0; }
        html.dark body { background-color: #0f172a; color: #e2e8f0; }
        html.dark .bg-gray-50 { background-color: #0f172a !important; }
        html.dark .bg-white { background-color: #1e293b !important; }
        html.dark .bg-gray-100 { background-color: #1e293b !important; }
        html.dark .bg-gray-200 { background-color: #1e293b !important; }
        html.dark .bg-gray-300 { background-color: #1e293b !important; }
        html.dark .bg-blue-50 { background-color: #0f172a !important; }
        html.dark .bg-teal-50 { background-color: #0f172a !important; }
        html.dark .bg-green-50 { background-color: #0f172a !important; }
        html.dark .bg-amber-100 { background-color: #0f172a !important; }
        html.dark .bg-yellow-50 { background-color: #0f172a !important; }

        html.dark .border-blue-100 { border-color: #2c3a52 !important; }
        html.dark .border-teal-200 { border-color: #2c3a52 !important; }
        html.dark .border-gray-200 { border-color: #334155 !important; }
        html.dark .border-gray-100 { border-color: #334155 !important; }

        html.dark .text-gray-600, html.dark .text-gray-700, html.dark .text-gray-800 {
            color: #cbd5e1 !important;
        }

        /* Inputs/textarea y controles de formularios dark mode */
        html.dark input,
        html.dark textarea,
        html.dark select,
        html.dark [contenteditable="true"],
        html.dark .outline-none,
        html.dark .form-input,
        html.dark .form-textarea,
        html.dark .form-select {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        html.dark input::placeholder,
        html.dark textarea::placeholder,
        html.dark select::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }

        html.dark input:focus,
        html.dark textarea:focus,
        html.dark select:focus {
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.24) !important;
            border-color: #38bdf8 !important;
        }

        /* Calendario (sabados/domingo con contraste) */
        html.dark .fc-day-sat, html.dark .fc-day-sun,
        html.dark .fc-col-header-cell-sat, html.dark .fc-col-header-cell-sun {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
        }

        /* Expte timeline cards etiquetas de estado */
        html.dark .timeline-card-sometimiento,
        html.dark .timeline-card-radicado,
        html.dark .timeline-card-auto,
        html.dark .timeline-card-auto-radicado {
            background-color: #1f2937 !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        /* Monitor / historial: badges de estado (bg-gray-100/bg-amber-100 genéricos se aplastan arriba) */
        html.dark .process-step-badge--auto {
            background-color: rgba(146, 64, 14, 0.45) !important;
            color: #fde68a !important;
            border: 1px solid rgba(251, 191, 36, 0.55) !important;
        }
        html.dark .process-step-badge--recoleccion {
            background-color: #334155 !important;
            color: #f1f5f9 !important;
            border: 1px solid #64748b !important;
        }

        html.dark .text-black,
        html.dark .text-slate-900,
        html.dark .text-gray-900 { color: #e2e8f0 !important; }
        html.dark .text-slate-800,
        html.dark .text-gray-800,
        html.dark .text-gray-700 { color: #cbd5e1 !important; }
        html.dark .text-slate-600,
        html.dark .text-gray-600,
        html.dark .text-gray-500 { color: #94a3b8 !important; }
        html.dark .text-slate-400,
        html.dark .text-gray-400,
        html.dark .text-gray-300 { color: #64748b !important; }

        html.dark .border-gray-200,
        html.dark .border-gray-300,
        html.dark .border-gray-400 { border-color: #334155 !important; }
        html.dark .hover\:bg-gray-100:hover { background-color: #334155 !important; }
        html.dark .hover\:bg-gray-200:hover { background-color: #334155 !important; }
        html.dark .hover\:bg-gray-50:hover { background-color: #334155 !important; }

        /* Tablas (Gestión Documental, Documentos en Drive, monitor…): hover legible en oscuro */
        html.dark table tbody tr:hover {
            background-color: #334155 !important;
        }

        /* Configuración → Sistema: tarjeta versión Git (evita parche claro sobre fondo oscuro) */
        html.dark .settings-git-version-card {
            background-color: #0f172a !important;
            border-color: #475569 !important;
            color: #e2e8f0 !important;
        }
        html.dark .settings-git-version-card .settings-git-version-heading {
            color: #f1f5f9 !important;
        }
        html.dark .settings-git-version-card dt,
        html.dark .settings-git-version-card .text-slate-600 {
            color: #94a3b8 !important;
        }
        html.dark .settings-git-version-card dd {
            color: #cbd5e1 !important;
        }
        html.dark .settings-git-version-card .text-teal-800 {
            color: #2dd4bf !important;
        }
        html.dark .settings-git-version-card .text-slate-500 {
            color: #64748b !important;
        }
        html.dark .settings-git-version-card .border-slate-200 {
            border-color: #334155 !important;
        }
        html.dark .settings-git-version-card .border-t {
            border-top-color: #334155 !important;
        }
        html.dark .settings-git-version-card .text-slate-700 {
            color: #cbd5e1 !important;
        }

        /* Selector de tema (segmento tipo píldora: sol / luna / sistema) */
        .theme-segment-bar {
            background-color: #f3f4f6;
        }
        html.dark .theme-segment-bar {
            background-color: rgba(51, 65, 85, 0.55);
        }
        .theme-segment-btn {
            border-radius: 0.375rem;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .theme-segment-btn--active {
            background-color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        html.dark .theme-segment-btn--active {
            background-color: #475569;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        }
        .theme-segment-btn--active i {
            color: #2563eb !important;
        }
        html.dark .theme-segment-btn--active i {
            color: #60a5fa !important;
        }
        .theme-segment-btn:not(.theme-segment-btn--active) i {
            color: #9ca3af;
        }
        html.dark .theme-segment-btn:not(.theme-segment-btn--active) i {
            color: #64748b;
        }
    </style>
    <script>
        function resolvePrefersDark(pref) {
            if (pref === 'dark') {
                return true;
            }
            if (pref === 'light') {
                return false;
            }
            if (pref === 'system' && typeof matchMedia !== 'undefined') {
                return matchMedia('(prefers-color-scheme: dark)').matches;
            }
            return false;
        }
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            var isDark = resolvePrefersDark(theme);
            document.documentElement.classList.toggle('dark', isDark);
            var $body = document.body;
            if ($body) {
                $body.classList.toggle('bg-gray-50', !isDark);
                $body.classList.toggle('bg-slate-900', isDark);
                $body.classList.toggle('text-gray-900', !isDark);
                $body.classList.toggle('text-gray-100', isDark);
            }
            syncThemeSegmentButtons(theme);
        }
        function syncThemeSegmentButtons(pref) {
            ['light', 'dark', 'system'].forEach(function (key) {
                var el = document.getElementById('theme-' + key + '-btn');
                if (!el) {
                    return;
                }
                var active = pref === key;
                el.classList.toggle('theme-segment-btn--active', active);
                el.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }
        function persistTheme(theme) {
            fetch(@json(route('admin.preferences.theme')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ theme: theme })
            }).catch(function () {});
        }
        function setFontScale(scale) {
            scale = parseInt(scale, 10);
            if ([90, 100, 110, 125].indexOf(scale) === -1) {
                scale = 100;
            }
            document.documentElement.style.fontSize = scale + '%';
            document.documentElement.setAttribute('data-ui-font-scale', String(scale));
            syncFontScaleButtons(scale);
        }
        function syncFontScaleButtons(scale) {
            [90, 100, 110, 125].forEach(function (key) {
                var el = document.getElementById('font-scale-' + key + '-btn');
                if (!el) {
                    return;
                }
                var active = scale === key;
                el.classList.toggle('theme-segment-btn--active', active);
                el.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }
        function persistFontScale(scale) {
            fetch(@json(route('admin.preferences.font-scale')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ scale: scale })
            }).catch(function () {});
        }
        document.addEventListener('DOMContentLoaded', function () {
            var serverTheme = @json($ramsAdminTheme);
            setTheme(serverTheme);
            setFontScale(@json($ramsUiFontScale));
            if (typeof matchMedia !== 'undefined') {
                matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                    if (document.documentElement.getAttribute('data-theme') === 'system') {
                        setTheme('system');
                    }
                });
            }
            ['light', 'dark', 'system'].forEach(function (key) {
                var btn = document.getElementById('theme-' + key + '-btn');
                if (!btn) {
                    return;
                }
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    setTheme(key);
                    persistTheme(key);
                });
            });
            [90, 100, 110, 125].forEach(function (key) {
                var btn = document.getElementById('font-scale-' + key + '-btn');
                if (!btn) {
                    return;
                }
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    setFontScale(key);
                    persistFontScale(key);
                });
            });
        });
    </script>
    <meta name="cf-2fa-verify" content="">
    <title>@yield('title', 'RAMS - Regulatory Affairs Management System')</title>
    
    <!-- Tailwind CSS (CDN) - Suprimir advertencia de producción -->
    <script>
        // Suprimir advertencia de Tailwind CDN en producción
        if (typeof console !== 'undefined' && console.warn) {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com should not be used in production')) {
                    return; // Suprimir esta advertencia específica
                }
                originalWarn.apply(console, args);
            };
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>

    <!-- Flowbite CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    
    <!-- FullCalendar CSS - No necesario en v6, el JS lo inyecta automáticamente -->
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- TinyMCE Editor (Self-hosted, open source, no API key required) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Suprimir errores de Cloudflare beacon ANTES de que se carguen otros scripts -->
    <script>
        // Ejecutar INMEDIATAMENTE, antes de cualquier otro script
        (function() {
            'use strict';
            
            // Guardar referencias originales
            const originalError = window.console.error;
            const originalWarn = window.console.warn;
            const originalLog = window.console.log;
            
            // Función para verificar si el mensaje debe ser suprimido
            function shouldSuppress(message) {
                if (!message || typeof message !== 'string') return false;
                const lowerMessage = message.toLowerCase();
                return lowerMessage.includes('cloudflareinsights.com') ||
                       lowerMessage.includes('beacon.min.js') ||
                       (lowerMessage.includes('integrity') && lowerMessage.includes('sha512') && lowerMessage.includes('cloudflare')) ||
                       (lowerMessage.includes('solicitud de origen cruzado') && lowerMessage.includes('cloudflare')) ||
                       (lowerMessage.includes('cors') && lowerMessage.includes('cloudflare')) ||
                       lowerMessage.includes('cdn.tailwindcss.com should not be used in production');
            }
            
            // Sobrescribir console.error
            window.console.error = function(...args) {
                const message = args.map(arg => 
                    typeof arg === 'string' ? arg : 
                    typeof arg === 'object' ? JSON.stringify(arg) : 
                    String(arg)
                ).join(' ');
                
                if (shouldSuppress(message)) {
                    return; // Suprimir estos errores específicos
                }
                originalError.apply(console, args);
            };
            
            // Sobrescribir console.warn
            window.console.warn = function(...args) {
                const message = args.map(arg => 
                    typeof arg === 'string' ? arg : 
                    typeof arg === 'object' ? JSON.stringify(arg) : 
                    String(arg)
                ).join(' ');
                
                if (shouldSuppress(message)) {
                    return; // Suprimir estas advertencias específicas
                }
                originalWarn.apply(console, args);
            };
            
            // Capturar errores globales ANTES de que se muestren
            const errorHandler = function(e) {
                const message = e.message || e.reason || (e.target && e.target.src) || '';
                if (shouldSuppress(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            };
            
            // Registrar múltiples listeners para capturar todos los tipos de errores
            window.addEventListener('error', errorHandler, true);
            window.addEventListener('unhandledrejection', errorHandler, true);
            
            // Interceptar errores de recursos (imágenes, scripts, etc.)
            document.addEventListener('error', function(e) {
                if (e.target && (
                    (e.target.src && e.target.src.includes('cloudflareinsights.com')) ||
                    (e.target.href && e.target.href.includes('cloudflareinsights.com'))
                )) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
        })();
    </script>
    
    @stack('styles')
</head>
<body class="h-full overflow-hidden" x-data="{
    sidebarExpanded: @json($adminSidebarExpandedDefault),
    mobileOpen: false,
    winLg: typeof window !== 'undefined' ? window.innerWidth >= 1024 : true,
    init() {
        this.winLg = window.innerWidth >= 1024;
        window.addEventListener('resize', () => {
            this.winLg = window.innerWidth >= 1024;
            if (this.winLg) {
                this.mobileOpen = false;
            }
        });
    },
    toggleSidebar() {
        if (this.winLg) {
            this.sidebarExpanded = !this.sidebarExpanded;
        } else {
            this.mobileOpen = !this.mobileOpen;
        }
    },
    asideWidthClass() {
        if (!this.winLg) {
            return 'w-64';
        }
        return this.sidebarExpanded ? 'w-64' : 'w-16 min-w-[4rem]';
    },
    asideXClass() {
        if (this.winLg) {
            return 'translate-x-0';
        }
        return this.mobileOpen ? 'translate-x-0' : '-translate-x-full';
    },
    mainMarginStyle() {
        if (!this.winLg) {
            return '';
        }
        return this.sidebarExpanded ? 'margin-left: 16rem' : 'margin-left: 4rem';
    },
}">
    <div id="rams-admin-app" class="flex h-full min-h-0 overflow-hidden bg-gray-50">
        <!-- Overlay para móvil -->
        <div x-show="!winLg && mobileOpen" 
             x-cloak
             @click="mobileOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-30 lg:hidden"
             style="display: none;">
        </div>

        <!-- Sidebar -->
        <aside id="sidebar" 
               class="fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out shadow-lg" 
               :class="[asideWidthClass(), asideXClass()]"
               style="background-color: #1e293b;">
            <div class="h-full py-4 overflow-y-auto overflow-x-hidden"
                 :class="winLg && !sidebarExpanded ? 'px-1.5' : 'px-3'">
                <!-- Logo (compacto cuando el menú está solo iconos en escritorio) -->
                <div class="mb-5" x-show="winLg && !sidebarExpanded" x-cloak>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center rounded-lg py-2 text-white hover:bg-teal-700/50" title="Inicio">
                        <span class="text-2xl font-bold text-teal-400">R</span>
                    </a>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center ps-2.5 mb-5" x-show="!winLg || sidebarExpanded" x-cloak>
                    @php
                        try {
                            $settings = app(\App\Settings\GeneralSettings::class);
                            $logoPath = $settings->agency_logo ?? null;
                            $agencyName = $settings->agency_name ?? null;
                            $hasLogo = $logoPath && file_exists(public_path($logoPath));
                            $hasName = !empty($agencyName) && $agencyName !== 'RAMS';
                        } catch (\Exception $e) {
                            $logoPath = null;
                            $agencyName = null;
                            $hasLogo = false;
                            $hasName = false;
                        }
                    @endphp
                    
                    @if($hasLogo)
                        {{-- Solo mostrar logo si existe --}}
                        <img src="{{ asset($logoPath) }}" 
                             alt="{{ $agencyName ?? 'Logo' }}" 
                             class="h-10 w-auto object-contain">
                    @elseif($hasName)
                        {{-- Solo mostrar nombre de agencia si no hay logo --}}
                        <span class="self-center text-xl font-semibold whitespace-nowrap text-white">
                            <span class="text-teal-400">{{ $agencyName }}</span>
                        </span>
                    @else
                        {{-- Por defecto: R REGULATORY APP --}}
                        <span class="self-center text-xl font-semibold whitespace-nowrap text-white">
                            <span class="text-teal-400">R</span> REGULATORY APP
                        </span>
                    @endif
                </a>
                
                @php
                    $permService = app(\App\Services\PermissionService::class);
                @endphp
                <!-- Menu -->
                <ul class="space-y-2 font-medium">
                    @if($permService->userHasPermission('dashboard', 'view'))
                        <li>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700' : '' }}"
                               :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                               title="Inicio">
                                <i class="fas fa-home w-5 h-5 shrink-0"></i>
                                <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Inicio</span>
                            </a>
                        </li>
                    @endif

                    <!-- OPERACIÓN -->
                    @if($permService->userHasPermission('processes', 'view') || $permService->userHasPermission('service_types', 'view'))
                    <li class="pt-4" x-show="!winLg || sidebarExpanded" x-cloak>
                        <span class="text-gray-400 text-xs font-semibold uppercase px-2">OPERACIÓN</span>
                    </li>
                    @php
                        $solicitudesLinkActive = (request()->routeIs('admin.processes.*') && ! request()->routeIs('admin.processes.history'))
                            || request()->routeIs('admin.submissions.*');
                        $tramiteActive = request()->routeIs('admin.service-types.*');
                        $solicitudesRowActive = $solicitudesLinkActive || $tramiteActive;
                        $solicitudesSubOpen = $tramiteActive;
                    @endphp
                    @if($permService->userHasPermission('processes', 'view'))
                    <li x-data="{ solicitudesOpen: {{ $solicitudesSubOpen ? 'true' : 'false' }} }">
                        <div class="flex items-stretch rounded-lg overflow-hidden {{ $solicitudesRowActive ? 'bg-teal-700' : 'hover:bg-teal-700/40' }}"
                             :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''">
                            <a href="{{ route('admin.processes.monitor') }}"
                               class="flex flex-1 items-center gap-3 min-w-0 p-2 text-white text-sm font-medium"
                               :class="(winLg && !sidebarExpanded) ? 'justify-center flex-none' : ''"
                               title="Solicitudes">
                                <i class="fas fa-folder w-5 h-5 shrink-0"></i>
                                <span class="truncate" x-show="!winLg || sidebarExpanded" x-cloak>Solicitudes</span>
                            </a>
                            @if($permService->userHasPermission('service_types', 'view'))
                            <button type="button"
                                    x-show="!winLg || sidebarExpanded"
                                    x-cloak
                                    @click.stop="solicitudesOpen = !solicitudesOpen"
                                    class="shrink-0 px-2 flex items-center justify-center text-white/90 hover:text-white hover:bg-teal-600/50 border-l border-teal-600/30"
                                    title="Mostrar Trámite">
                                <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': solicitudesOpen }"></i>
                            </button>
                            @endif
                        </div>
                        @if($permService->userHasPermission('service_types', 'view'))
                        <ul x-show="solicitudesOpen && (!winLg || sidebarExpanded)" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="ms-4 mt-1 space-y-1 border-l border-gray-600 pl-2">
                            <li>
                                <a href="{{ route('admin.service-types.index') }}"
                                   class="flex items-center p-2 rounded-lg text-gray-300 hover:bg-teal-700/50 hover:text-white {{ $tramiteActive ? 'bg-teal-700/50 text-white' : '' }}">
                                    <i class="fas fa-list-alt w-4 h-4"></i>
                                    <span class="ms-2 text-sm" x-show="!winLg || sidebarExpanded" x-cloak>Trámite</span>
                                </a>
                            </li>
                        </ul>
                        @endif
                    </li>
                    <li>
                        <a href="{{ route('admin.processes.history') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.processes.history') ? 'bg-teal-700' : '' }}"
                           :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                           title="Historial de solicitudes">
                            <i class="fas fa-clock-rotate-left w-5 h-5"></i>
                            <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Historial de solicitudes</span>
                        </a>
                    </li>
                    @elseif($permService->userHasPermission('service_types', 'view'))
                    <li>
                        <a href="{{ route('admin.service-types.index') }}"
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.service-types.*') ? 'bg-teal-700' : '' }}"
                           :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                           title="Trámite">
                            <i class="fas fa-list-alt w-5 h-5 shrink-0"></i>
                            <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Trámite</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    <!-- CONTABILIDAD -->
                    @if($permService->userHasPermission('proposals', 'view') || $permService->userHasPermission('concept_catalogs', 'view') || $permService->userHasPermission('quotes', 'view') || $permService->userHasPermission('services', 'view'))
                    @php
                        $propuestasLinkActive = request()->routeIs('admin.proposals.*');
                        $conceptosActive = request()->routeIs('admin.concept-catalogs.*');
                        $propuestasRowActive = $propuestasLinkActive || $conceptosActive;
                        $propuestasSubOpen = $conceptosActive;

                        $cotizacionesLinkActive = request()->routeIs('admin.quotes.*');
                        $serviciosActive = request()->routeIs('admin.services.*');
                        $cotizacionesRowActive = $cotizacionesLinkActive || $serviciosActive;
                        $cotizacionesSubOpen = $serviciosActive;
                    @endphp
                    <li class="pt-4" x-show="!winLg || sidebarExpanded" x-cloak>
                        <span class="text-gray-400 text-xs font-semibold uppercase px-2">CONTABILIDAD</span>
                    </li>
                    @if($permService->userHasPermission('proposals', 'view') || $permService->userHasPermission('concept_catalogs', 'view'))
                    <li x-data="{ propuestasOpen: {{ $propuestasSubOpen ? 'true' : 'false' }} }">
                        <div class="flex items-stretch rounded-lg overflow-hidden {{ $propuestasRowActive ? 'bg-teal-700' : 'hover:bg-teal-700/40' }}"
                             :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''">
                            @if($permService->userHasPermission('proposals', 'view'))
                            <a href="{{ route('admin.proposals.index') }}"
                               class="flex flex-1 items-center gap-3 min-w-0 p-2 text-white text-sm font-medium"
                               :class="(winLg && !sidebarExpanded) ? 'justify-center flex-none' : ''"
                               title="Propuestas">
                                <i class="fas fa-file-signature w-5 h-5 shrink-0"></i>
                                <span class="truncate" x-show="!winLg || sidebarExpanded" x-cloak>Propuestas</span>
                            </a>
                            @endif
                            @if($permService->userHasPermission('concept_catalogs', 'view'))
                            <button type="button"
                                    x-show="!winLg || sidebarExpanded"
                                    x-cloak
                                    @click.stop="propuestasOpen = !propuestasOpen"
                                    class="shrink-0 px-2 flex items-center justify-center text-white/90 hover:text-white hover:bg-teal-600/50 border-l border-teal-600/30 {{ $permService->userHasPermission('proposals', 'view') ? '' : 'flex-1 justify-start pl-3' }}"
                                    title="Mostrar Conceptos">
                                <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': propuestasOpen }"></i>
                            </button>
                            @endif
                        </div>
                        @if($permService->userHasPermission('concept_catalogs', 'view'))
                        <ul x-show="propuestasOpen && (!winLg || sidebarExpanded)" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="ms-4 mt-1 space-y-1 border-l border-gray-600 pl-2">
                            <li>
                                <a href="{{ route('admin.concept-catalogs.index') }}"
                                   class="flex items-center p-2 rounded-lg text-gray-300 hover:bg-teal-700/50 hover:text-white {{ $conceptosActive ? 'bg-teal-700/50 text-white' : '' }}">
                                    <i class="fas fa-list-ul w-4 h-4"></i>
                                    <span class="ms-2 text-sm" x-show="!winLg || sidebarExpanded" x-cloak>Conceptos</span>
                                </a>
                            </li>
                        </ul>
                        @endif
                    </li>
                    @endif
                    @if($permService->userHasPermission('quotes', 'view') || $permService->userHasPermission('services', 'view'))
                    <li x-data="{ contabilidadOpen: {{ $cotizacionesSubOpen ? 'true' : 'false' }} }">
                        <div class="flex items-stretch rounded-lg overflow-hidden {{ $cotizacionesRowActive ? 'bg-teal-700' : 'hover:bg-teal-700/40' }}"
                             :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''">
                            @if($permService->userHasPermission('quotes', 'view'))
                            <a href="{{ route('admin.quotes.index') }}"
                               class="flex flex-1 items-center gap-3 min-w-0 p-2 text-white text-sm font-medium"
                               :class="(winLg && !sidebarExpanded) ? 'justify-center flex-none' : ''"
                               title="Cotizaciones">
                                <i class="fas fa-file-invoice-dollar w-5 h-5 shrink-0"></i>
                                <span class="truncate" x-show="!winLg || sidebarExpanded" x-cloak>Cotizaciones</span>
                            </a>
                            @endif
                            @if($permService->userHasPermission('services', 'view'))
                            <button type="button"
                                    x-show="!winLg || sidebarExpanded"
                                    x-cloak
                                    @click.stop="contabilidadOpen = !contabilidadOpen"
                                    class="shrink-0 px-2 flex items-center justify-center text-white/90 hover:text-white hover:bg-teal-600/50 border-l border-teal-600/30 {{ $permService->userHasPermission('quotes', 'view') ? '' : 'flex-1 justify-start pl-3' }}"
                                    title="Mostrar Servicios">
                                <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': contabilidadOpen }"></i>
                            </button>
                            @endif
                        </div>
                        @if($permService->userHasPermission('services', 'view'))
                        <ul x-show="contabilidadOpen && (!winLg || sidebarExpanded)" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="ms-4 mt-1 space-y-1 border-l border-gray-600 pl-2">
                            <li>
                                <a href="{{ route('admin.services.index') }}"
                                   class="flex items-center p-2 rounded-lg text-gray-300 hover:bg-teal-700/50 hover:text-white {{ $serviciosActive ? 'bg-teal-700/50 text-white' : '' }}">
                                    <i class="fas fa-concierge-bell w-4 h-4"></i>
                                    <span class="ms-2 text-sm" x-show="!winLg || sidebarExpanded" x-cloak>Servicios</span>
                                </a>
                            </li>
                        </ul>
                        @endif
                    </li>
                    @endif
                    @endif

                    @if($permService->userHasPermission('capacitaciones', 'view'))
                    <li class="pt-4">
                        <a href="{{ route('admin.capacitaciones.index') }}"
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.capacitaciones.*') ? 'bg-teal-700' : '' }}"
                           :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                           title="Capacitaciones">
                            <i class="fas fa-video w-5 h-5 shrink-0"></i>
                            <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Capacitaciones</span>
                        </a>
                    </li>
                    @endif

                    <!-- SISTEMA -->
                    @php
                        $sistemaSectionVisible = $permService->userHasPermission('companies', 'view')
                            || $permService->userHasPermission('users', 'view')
                            || $permService->userHasPermission('settings_agency', 'view')
                            || $permService->userHasPermission('settings_drive', 'view')
                            || $permService->userHasPermission('settings_drive_operations_log', 'view')
                            || $permService->userHasPermission('settings_mail', 'view')
                            || $permService->userHasPermission('settings_templates', 'view')
                            || $permService->userHasPermission('settings_history', 'view')
                            || $permService->userHasPermission('settings_system', 'view')
                            || $permService->userHasPermission('backups', 'view')
                            || $permService->userHasPermission('permissions', 'view')
                            || $permService->userHasPermission('permissions', 'edit')
                            || $permService->userHasPermission('activity_logs', 'view');
                    @endphp
                    @if($sistemaSectionVisible)
                        <li class="pt-4" x-show="!winLg || sidebarExpanded" x-cloak>
                            <span class="text-gray-400 text-xs font-semibold uppercase px-2">SISTEMA</span>
                        </li>
                    @endif
                    @if($permService->userHasPermission('companies', 'view'))
                        <li>
                            <a href="{{ route('admin.companies.index') }}"
                               class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.companies.*') ? 'bg-teal-700' : '' }}"
                               :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                               title="Empresas">
                                <i class="fas fa-building w-5 h-5 shrink-0"></i>
                                <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Empresas</span>
                            </a>
                        </li>
                    @endif
                    @if($permService->userHasPermission('users', 'view')
                        || $permService->userHasPermission('settings_agency', 'view') 
                        || $permService->userHasPermission('settings_drive', 'view')
                        || $permService->userHasPermission('settings_drive_operations_log', 'view')
                        || $permService->userHasPermission('settings_mail', 'view')
                        || $permService->userHasPermission('settings_templates', 'view')
                        || $permService->userHasPermission('settings_history', 'view')
                        || $permService->userHasPermission('settings_system', 'view'))
                        @if($permService->userHasPermission('users', 'view'))
                            <li x-data="{ directorioOpen: {{ request()->routeIs('admin.clients.*') || request()->routeIs('admin.agents.*') || request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                                <button @click="(winLg && !sidebarExpanded) ? (sidebarExpanded = true) : (directorioOpen = !directorioOpen)"
                                        type="button"
                                        class="flex items-center w-full p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.clients.*') || request()->routeIs('admin.agents.*') || request()->routeIs('admin.users.*') ? 'bg-teal-700' : '' }}"
                                        :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                                        title="Directorio">
                                    <i class="fas fa-address-book w-5 h-5 shrink-0"></i>
                                    <span class="ms-3 text-left flex-1" x-show="!winLg || sidebarExpanded" x-cloak>Directorio</span>
                                    <i class="fas fa-chevron-down w-4 h-4 transition-transform shrink-0" x-show="!winLg || sidebarExpanded" x-cloak :class="{ 'rotate-180': directorioOpen }"></i>
                                </button>
                                <ul x-show="directorioOpen && (!winLg || sidebarExpanded)" x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="ms-4 mt-1 space-y-1 border-l border-gray-600 pl-2">
                                    <li>
                                        <a href="{{ route('admin.clients.index') }}"
                                           class="flex items-center p-2 rounded-lg text-gray-300 hover:bg-teal-700/50 hover:text-white {{ request()->routeIs('admin.clients.*') ? 'bg-teal-700/50 text-white' : '' }}">
                                            <i class="fas fa-user-friends w-4 h-4"></i>
                                            <span class="ms-2 text-sm" x-show="!winLg || sidebarExpanded" x-cloak>Clientes</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.agents.index') }}"
                                           class="flex items-center p-2 rounded-lg text-gray-300 hover:bg-teal-700/50 hover:text-white {{ request()->routeIs('admin.agents.*') || request()->routeIs('admin.users.*') ? 'bg-teal-700/50 text-white' : '' }}">
                                            <i class="fas fa-user-tie w-4 h-4"></i>
                                            <span class="ms-2 text-sm" x-show="!winLg || sidebarExpanded" x-cloak>Especialistas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if($permService->userHasPermission('settings_agency', 'view') 
                            || $permService->userHasPermission('settings_drive', 'view')
                            || $permService->userHasPermission('settings_drive_operations_log', 'view')
                            || $permService->userHasPermission('settings_mail', 'view')
                            || $permService->userHasPermission('settings_templates', 'view')
                            || $permService->userHasPermission('settings_history', 'view')
                            || $permService->userHasPermission('settings_system', 'view'))
                            <li>
                                <a href="{{ route('admin.settings.index') }}" 
                                   class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.settings.*') ? 'bg-teal-700' : '' }}"
                                   :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                                   title="Configuración">
                                    <i class="fas fa-cog w-5 h-5 shrink-0"></i>
                                    <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Configuración</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    @if($permService->userHasPermission('backups', 'view'))
                    <li>
                        <a href="{{ route('admin.backups.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.backups.*') ? 'bg-teal-700' : '' }}"
                           :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                           title="Backups">
                            <i class="fas fa-database w-5 h-5 shrink-0"></i>
                            <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Backups</span>
                        </a>
                    </li>
                    @endif
                    @if($permService->userHasPermission('permissions', 'view') || $permService->userHasPermission('permissions', 'edit'))
                    <li>
                        <a href="{{ route('admin.permissions.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.permissions.*') ? 'bg-teal-700' : '' }}"
                           :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                           title="Permisos">
                            <i class="fas fa-shield-alt w-5 h-5 shrink-0"></i>
                            <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Permisos</span>
                        </a>
                    </li>
                    @endif
                    @if($permService->userHasPermission('activity_logs', 'view'))
                    <li>
                        <a href="{{ route('admin.activity-logs.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-teal-700' : '' }}"
                           :class="(winLg && !sidebarExpanded) ? 'justify-center' : ''"
                           title="Registros de Actividad">
                            <i class="fas fa-history w-5 h-5 shrink-0"></i>
                            <span class="ms-3" x-show="!winLg || sidebarExpanded" x-cloak>Registros de Actividad</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden transition-all duration-300" 
             :style="mainMarginStyle()">
            <!-- Top Navbar -->
            <header class="shrink-0 bg-white shadow-sm border-b border-gray-200 z-30">
                <div class="flex items-center justify-between px-4 py-3">
                    <!-- Botón Hamburguesa (móvil: drawer; escritorio: expandir/contraer menú) -->
                    <button type="button"
                            @click="toggleSidebar()" 
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                            :title="winLg ? (sidebarExpanded ? 'Contraer menú lateral' : 'Expandir menú lateral') : 'Abrir o cerrar menú'">
                        <i class="fas w-5 h-5" :class="winLg && sidebarExpanded ? 'fa-chevron-left' : 'fa-bars'"></i>
                    </button>
                    
                    <!-- Breadcrumb y título (centro) -->
                    <div class="flex-1 mx-4">
                        <nav class="text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-700 hover:text-teal-700">
                                        <i class="fas fa-home mr-2"></i> Inicio
                                    </a>
                                </li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Usuario (derecha) -->
                    <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" 
                                type="button" 
                                class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none transition-colors">
                            <div class="w-9 h-9 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold text-xs shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                            <i class="fas fa-chevron-down w-3 h-3 text-gray-400 transition-transform" 
                               :class="{ 'rotate-180': userMenuOpen }"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="userMenuOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-lg shadow-lg py-1 z-50 border border-gray-200 dark:border-slate-600"
                             style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-600">
                                <p class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                                    <i class="fas fa-user mr-2"></i> Mi Perfil
                                </a>
                                <div class="px-4 py-2 border-y border-gray-200 dark:border-slate-600">
                                    <span class="text-xs text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tema</span>
                                    <div class="theme-segment-bar mt-2 flex rounded-lg p-0.5 gap-0.5" role="group" aria-label="Tema del panel">
                                        <button type="button" id="theme-light-btn" title="Claro" aria-label="Tema claro" aria-pressed="{{ $ramsAdminTheme === 'light' ? 'true' : 'false' }}"
                                                class="theme-segment-btn flex flex-1 items-center justify-center py-2 rounded-md {{ $ramsAdminTheme === 'light' ? 'theme-segment-btn--active' : '' }}">
                                            <i class="fas fa-sun text-base"></i>
                                        </button>
                                        <button type="button" id="theme-dark-btn" title="Oscuro" aria-label="Tema oscuro" aria-pressed="{{ $ramsAdminTheme === 'dark' ? 'true' : 'false' }}"
                                                class="theme-segment-btn flex flex-1 items-center justify-center py-2 rounded-md {{ $ramsAdminTheme === 'dark' ? 'theme-segment-btn--active' : '' }}">
                                            <i class="fas fa-moon text-base"></i>
                                        </button>
                                        <button type="button" id="theme-system-btn" title="Sistema" aria-label="Según el sistema" aria-pressed="{{ $ramsAdminTheme === 'system' ? 'true' : 'false' }}"
                                                class="theme-segment-btn flex flex-1 items-center justify-center py-2 rounded-md {{ $ramsAdminTheme === 'system' ? 'theme-segment-btn--active' : '' }}">
                                            <i class="fas fa-desktop text-base"></i>
                                        </button>
                                    </div>
                                    <span class="mt-3 block text-xs text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tamaño de texto</span>
                                    @php
                                        $uiFontScaleGlyphs = [90 => 'A−', 100 => 'A', 110 => 'A+', 125 => 'A++'];
                                        $uiFontScaleTitles = [90 => 'Reducir texto', 100 => 'Tamaño normal', 110 => 'Aumentar texto', 125 => 'Texto muy grande'];
                                    @endphp
                                    <div class="theme-segment-bar mt-2 flex rounded-lg p-0.5 gap-0.5" role="group" aria-label="Tamaño de texto del panel">
                                        @foreach ($uiFontScaleGlyphs as $scale => $scaleGlyph)
                                            <button type="button"
                                                    id="font-scale-{{ $scale }}-btn"
                                                    title="{{ $uiFontScaleTitles[$scale] }} ({{ $scale }}%)"
                                                    aria-label="{{ $uiFontScaleTitles[$scale] }}"
                                                    aria-pressed="{{ $ramsUiFontScale === $scale ? 'true' : 'false' }}"
                                                    class="theme-segment-btn flex flex-1 items-center justify-center py-2 rounded-md text-xs font-semibold leading-none tabular-nums {{ $ramsUiFontScale === $scale ? 'theme-segment-btn--active' : '' }}">
                                                {{ $scaleGlyph }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="rams-admin-scroll flex-1 min-h-0 overflow-y-auto overflow-x-hidden bg-gray-50 p-6">

                <!-- Alerts -->
                @if(session('success'))
                    <div class="mb-4 p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-4 p-4 text-sm text-blue-800 bg-blue-50 border border-blue-200 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Page Title -->
                <h1 class="text-2xl font-bold text-gray-900 mb-6">@yield('page-title', 'Dashboard')</h1>

                <!-- Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="shrink-0 bg-white border-t border-gray-200 py-4 px-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        {!! nl2br(e(app(\App\Settings\GeneralSettings::class)->footer_text ?? 'RAMS - Regulatory Affairs Management System')) !!}
                    </span>
                    <span class="text-sm text-gray-600">Versión 1.0</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Flowbite JS -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Estilos para botones de SweetAlert2 - asegurar visibilidad -->
    <style>
        /* Asegurar que los botones de SweetAlert2 sean siempre visibles y opacos */
        .swal2-styled.swal2-confirm {
            background-color: #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
            border: none !important;
            border: 0 !important;
            opacity: 1 !important;
            visibility: visible !important;
            font-weight: 600 !important;
            padding: 10px 24px !important;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4) !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
        }
        .swal2-styled.swal2-confirm:hover {
            background-color: #b91c1c !important;
            background: #b91c1c !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.6) !important;
            transform: translateY(-1px) !important;
        }
        .swal2-styled.swal2-confirm:active {
            background-color: #991b1b !important;
            background: #991b1b !important;
            transform: translateY(0) !important;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.4) !important;
        }
        .swal2-styled.swal2-cancel {
            background-color: #4b5563 !important;
            background: #4b5563 !important;
            color: #ffffff !important;
            border: none !important;
            border: 0 !important;
            opacity: 1 !important;
            visibility: visible !important;
            font-weight: 600 !important;
            padding: 10px 24px !important;
            box-shadow: 0 2px 6px rgba(75, 85, 99, 0.4) !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
        }
        .swal2-styled.swal2-cancel:hover {
            background-color: #374151 !important;
            background: #374151 !important;
            box-shadow: 0 4px 12px rgba(75, 85, 99, 0.6) !important;
            transform: translateY(-1px) !important;
        }
        .swal2-styled.swal2-cancel:active {
            background-color: #1f2937 !important;
            background: #1f2937 !important;
            transform: translateY(0) !important;
            box-shadow: 0 2px 4px rgba(75, 85, 99, 0.4) !important;
        }
        .swal2-styled.swal2-deny {
            background-color: #6b7280 !important;
            background: #6b7280 !important;
            color: #ffffff !important;
            border: none !important;
            border: 0 !important;
            opacity: 1 !important;
            visibility: visible !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        }
        .swal2-actions {
            display: flex !important;
            gap: 10px !important;
            margin-top: 20px !important;
        }
        .swal2-styled {
            opacity: 1 !important;
            visibility: visible !important;
            display: inline-block !important;
        }
        /* Asegurar que los botones no sean transparentes ni blancos */
        .swal2-popup .swal2-actions button {
            opacity: 1 !important;
            background-color: inherit !important;
            color: #ffffff !important;
        }
        /* Forzar colores en todos los estados */
        .swal2-popup .swal2-actions .swal2-styled {
            background-color: #4b5563 !important;
            background: #4b5563 !important;
            color: #ffffff !important;
        }
        .swal2-popup .swal2-actions .swal2-confirm {
            background-color: #dc2626 !important;
            background: #dc2626 !important;
            color: #ffffff !important;
        }
        .swal2-popup .swal2-actions .swal2-confirm:hover {
            background-color: #b91c1c !important;
            background: #b91c1c !important;
        }
        .swal2-popup .swal2-actions .swal2-cancel:hover {
            background-color: #374151 !important;
            background: #374151 !important;
        }
        /* Ocultar botón deny (No) si no se necesita */
        .swal2-styled.swal2-deny {
            display: none !important;
        }
    </style>

    @stack('scripts')
    
    <!-- Asegurar que todos los scripts estén cargados antes de inicializar componentes -->
    <script>
        // Evento global para notificar que todos los scripts están listos
        window.addEventListener('load', function() {
            window.allScriptsLoaded = true;
            document.dispatchEvent(new Event('scriptsLoaded'));
        });
    </script>
</body>
</html>
