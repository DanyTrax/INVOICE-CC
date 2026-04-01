@php
    $ramsAdminTheme = auth()->check() ? (auth()->user()->admin_theme ?? 'light') : 'light';
    if (! in_array($ramsAdminTheme, ['light', 'dark'], true)) {
        $ramsAdminTheme = 'light';
    }
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50 {{ $ramsAdminTheme === 'dark' ? 'dark' : '' }}" data-theme="{{ $ramsAdminTheme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Deshabilitar Cloudflare Insights beacon -->
    <style>
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
    </style>
    <script>
        function setTheme(theme) {
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.setAttribute('data-theme', theme);
            const isDark = theme === 'dark';
            const $body = document.body;
            if ($body) {
                $body.classList.toggle('bg-gray-50', !isDark);
                $body.classList.toggle('bg-slate-900', isDark);
                $body.classList.toggle('text-gray-900', !isDark);
                $body.classList.toggle('text-gray-100', isDark);
            }
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
        document.addEventListener('DOMContentLoaded', function() {
            const serverTheme = @json($ramsAdminTheme);
            setTheme(serverTheme);
            const toggleLight = document.getElementById('theme-light-btn');
            const toggleDark = document.getElementById('theme-dark-btn');
            if (toggleLight) {
                toggleLight.addEventListener('click', function(e) {
                    e.preventDefault();
                    setTheme('light');
                    persistTheme('light');
                });
            }
            if (toggleDark) {
                toggleDark.addEventListener('click', function(e) {
                    e.preventDefault();
                    setTheme('dark');
                    persistTheme('dark');
                });
            }
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
<body class="h-full" x-data="{ 
    sidebarOpen: window.innerWidth >= 1024,
    init() {
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && !this.sidebarOpen) {
                this.sidebarOpen = true;
            }
        });
    }
}">
    <div class="flex h-screen bg-gray-50">
        <!-- Overlay para móvil -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
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
               class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform duration-300 ease-in-out shadow-lg" 
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               style="background-color: #1e293b;">
            <div class="h-full px-3 py-4 overflow-y-auto">
                <!-- Logo -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center ps-2.5 mb-5">
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
                               class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700' : '' }}">
                                <i class="fas fa-home w-5 h-5 shrink-0"></i>
                                <span class="ms-3">Inicio</span>
                            </a>
                        </li>
                    @endif

                    <!-- OPERACIÓN -->
                    @if($permService->userHasPermission('processes', 'view') || $permService->userHasPermission('service_types', 'view'))
                    <li class="pt-4">
                        <span class="text-gray-400 text-xs font-semibold uppercase px-2">OPERACIÓN</span>
                    </li>
                    @php
                        $expedientesLinkActive = (request()->routeIs('admin.processes.*') && ! request()->routeIs('admin.processes.history'))
                            || request()->routeIs('admin.submissions.*');
                        $tramiteActive = request()->routeIs('admin.service-types.*');
                        $expedientesRowActive = $expedientesLinkActive || $tramiteActive;
                        $expedientesSubOpen = $tramiteActive;
                    @endphp
                    @if($permService->userHasPermission('processes', 'view'))
                    <li x-data="{ expedientesOpen: {{ $expedientesSubOpen ? 'true' : 'false' }} }">
                        <div class="flex items-stretch rounded-lg overflow-hidden {{ $expedientesRowActive ? 'bg-teal-700' : 'hover:bg-teal-700/40' }}">
                            <a href="{{ route('admin.processes.monitor') }}"
                               class="flex flex-1 items-center gap-3 min-w-0 p-2 text-white text-sm font-medium">
                                <i class="fas fa-folder w-5 h-5 shrink-0"></i>
                                <span class="truncate">Expedientes</span>
                            </a>
                            @if($permService->userHasPermission('service_types', 'view'))
                            <button type="button"
                                    @click.stop="expedientesOpen = !expedientesOpen"
                                    class="shrink-0 px-2 flex items-center justify-center text-white/90 hover:text-white hover:bg-teal-600/50 border-l border-teal-600/30"
                                    title="Mostrar Trámite">
                                <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': expedientesOpen }"></i>
                            </button>
                            @endif
                        </div>
                        @if($permService->userHasPermission('service_types', 'view'))
                        <ul x-show="expedientesOpen" x-cloak
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
                                    <span class="ms-2 text-sm">Trámite</span>
                                </a>
                            </li>
                        </ul>
                        @endif
                    </li>
                    <li>
                        <a href="{{ route('admin.processes.history') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.processes.history') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-clock-rotate-left w-5 h-5"></i>
                            <span class="ms-3">Historial de Expedientes</span>
                        </a>
                    </li>
                    @elseif($permService->userHasPermission('service_types', 'view'))
                    <li>
                        <a href="{{ route('admin.service-types.index') }}"
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.service-types.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-list-alt w-5 h-5 shrink-0"></i>
                            <span class="ms-3">Trámite</span>
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
                    <li class="pt-4">
                        <span class="text-gray-400 text-xs font-semibold uppercase px-2">CONTABILIDAD</span>
                    </li>
                    @if($permService->userHasPermission('proposals', 'view') || $permService->userHasPermission('concept_catalogs', 'view'))
                    <li x-data="{ propuestasOpen: {{ $propuestasSubOpen ? 'true' : 'false' }} }">
                        <div class="flex items-stretch rounded-lg overflow-hidden {{ $propuestasRowActive ? 'bg-teal-700' : 'hover:bg-teal-700/40' }}">
                            @if($permService->userHasPermission('proposals', 'view'))
                            <a href="{{ route('admin.proposals.index') }}"
                               class="flex flex-1 items-center gap-3 min-w-0 p-2 text-white text-sm font-medium">
                                <i class="fas fa-file-signature w-5 h-5 shrink-0"></i>
                                <span class="truncate">Propuestas</span>
                            </a>
                            @endif
                            @if($permService->userHasPermission('concept_catalogs', 'view'))
                            <button type="button"
                                    @click.stop="propuestasOpen = !propuestasOpen"
                                    class="shrink-0 px-2 flex items-center justify-center text-white/90 hover:text-white hover:bg-teal-600/50 border-l border-teal-600/30 {{ $permService->userHasPermission('proposals', 'view') ? '' : 'flex-1 justify-start pl-3' }}"
                                    title="Mostrar Conceptos">
                                <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': propuestasOpen }"></i>
                            </button>
                            @endif
                        </div>
                        @if($permService->userHasPermission('concept_catalogs', 'view'))
                        <ul x-show="propuestasOpen" x-cloak
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
                                    <span class="ms-2 text-sm">Conceptos</span>
                                </a>
                            </li>
                        </ul>
                        @endif
                    </li>
                    @endif
                    @if($permService->userHasPermission('quotes', 'view') || $permService->userHasPermission('services', 'view'))
                    <li x-data="{ contabilidadOpen: {{ $cotizacionesSubOpen ? 'true' : 'false' }} }">
                        <div class="flex items-stretch rounded-lg overflow-hidden {{ $cotizacionesRowActive ? 'bg-teal-700' : 'hover:bg-teal-700/40' }}">
                            @if($permService->userHasPermission('quotes', 'view'))
                            <a href="{{ route('admin.quotes.index') }}"
                               class="flex flex-1 items-center gap-3 min-w-0 p-2 text-white text-sm font-medium">
                                <i class="fas fa-file-invoice-dollar w-5 h-5 shrink-0"></i>
                                <span class="truncate">Cotizaciones</span>
                            </a>
                            @endif
                            @if($permService->userHasPermission('services', 'view'))
                            <button type="button"
                                    @click.stop="contabilidadOpen = !contabilidadOpen"
                                    class="shrink-0 px-2 flex items-center justify-center text-white/90 hover:text-white hover:bg-teal-600/50 border-l border-teal-600/30 {{ $permService->userHasPermission('quotes', 'view') ? '' : 'flex-1 justify-start pl-3' }}"
                                    title="Mostrar Servicios">
                                <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': contabilidadOpen }"></i>
                            </button>
                            @endif
                        </div>
                        @if($permService->userHasPermission('services', 'view'))
                        <ul x-show="contabilidadOpen" x-cloak
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
                                    <span class="ms-2 text-sm">Servicios</span>
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
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.capacitaciones.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-video w-5 h-5 shrink-0"></i>
                            <span class="ms-3">Capacitaciones</span>
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
                        <li class="pt-4">
                            <span class="text-gray-400 text-xs font-semibold uppercase px-2">SISTEMA</span>
                        </li>
                    @endif
                    @if($permService->userHasPermission('companies', 'view'))
                        <li>
                            <a href="{{ route('admin.companies.index') }}"
                               class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.companies.*') ? 'bg-teal-700' : '' }}">
                                <i class="fas fa-building w-5 h-5"></i>
                                <span class="ms-3">Empresas</span>
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
                                <button @click="directorioOpen = !directorioOpen"
                                        type="button"
                                        class="flex items-center w-full p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.clients.*') || request()->routeIs('admin.agents.*') || request()->routeIs('admin.users.*') ? 'bg-teal-700' : '' }}">
                                    <i class="fas fa-address-book w-5 h-5"></i>
                                    <span class="ms-3 text-left flex-1">Directorio</span>
                                    <i class="fas fa-chevron-down w-4 h-4 transition-transform" :class="{ 'rotate-180': directorioOpen }"></i>
                                </button>
                                <ul x-show="directorioOpen" x-cloak
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
                                            <span class="ms-2 text-sm">Clientes</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.agents.index') }}"
                                           class="flex items-center p-2 rounded-lg text-gray-300 hover:bg-teal-700/50 hover:text-white {{ request()->routeIs('admin.agents.*') || request()->routeIs('admin.users.*') ? 'bg-teal-700/50 text-white' : '' }}">
                                            <i class="fas fa-user-tie w-4 h-4"></i>
                                            <span class="ms-2 text-sm">Especialistas</span>
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
                                   class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.settings.*') ? 'bg-teal-700' : '' }}">
                                    <i class="fas fa-cog w-5 h-5"></i>
                                    <span class="ms-3">Configuración</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    @if($permService->userHasPermission('backups', 'view'))
                    <li>
                        <a href="{{ route('admin.backups.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.backups.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-database w-5 h-5"></i>
                            <span class="ms-3">Backups</span>
                        </a>
                    </li>
                    @endif
                    @if($permService->userHasPermission('permissions', 'view') || $permService->userHasPermission('permissions', 'edit'))
                    <li>
                        <a href="{{ route('admin.permissions.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.permissions.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-shield-alt w-5 h-5"></i>
                            <span class="ms-3">Permisos</span>
                        </a>
                    </li>
                    @endif
                    @if($permService->userHasPermission('activity_logs', 'view'))
                    <li>
                        <a href="{{ route('admin.activity-logs.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-history w-5 h-5"></i>
                            <span class="ms-3">Registros de Actividad</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300" 
             :style="sidebarOpen ? 'margin-left: 16rem;' : 'margin-left: 0;'">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 py-3">
                    <!-- Botón Hamburguesa (siempre visible) -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                            title="Mostrar/Ocultar menú">
                        <i class="fas fa-bars w-5 h-5"></i>
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
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200"
                             style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-user mr-2"></i> Mi Perfil
                                </a>
                                <div class="px-4 py-2 border-y border-gray-200">
                                    <span class="text-xs text-gray-500 uppercase tracking-wider">Tema</span>
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <button id="theme-light-btn" class="w-full text-sm font-medium px-2 py-1 rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">☀️ Claro</button>
                                        <button id="theme-dark-btn" class="w-full text-sm font-medium px-2 py-1 rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">🌙 Oscuro</button>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">

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
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        {!! app(\App\Settings\GeneralSettings::class)->footer_text ?? 'RAMS - Regulatory Affairs Management System' !!}
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
