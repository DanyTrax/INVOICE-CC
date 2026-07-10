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

        /* Top navigation */
        .admin-topnav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        html.dark .admin-topnav-link { color: #cbd5e1; }
        .admin-topnav-link:hover {
            background-color: #f3f4f6;
            color: #0d9488;
        }
        html.dark .admin-topnav-link:hover {
            background-color: #334155;
            color: #2dd4bf;
        }
        .admin-topnav-link--active {
            background-color: #ecfdf5;
            color: #0f766e;
        }
        html.dark .admin-topnav-link--active {
            background-color: rgba(13, 148, 136, 0.2);
            color: #5eead4;
        }
        .admin-topnav-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            color: #374151;
        }
        html.dark .admin-topnav-dropdown-item { color: #e2e8f0; }
        .admin-topnav-dropdown-item:hover {
            background-color: #f9fafb;
            color: #0d9488;
        }
        html.dark .admin-topnav-dropdown-item:hover { background-color: #334155; }
        .admin-topnav-dropdown-item--active {
            background-color: #ecfdf5;
            color: #0f766e;
            font-weight: 600;
        }
        html.dark .admin-topnav-dropdown-item--active {
            background-color: rgba(13, 148, 136, 0.15);
            color: #5eead4;
        }
        .admin-topnav-mobile-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        html.dark .admin-topnav-mobile-item { color: #e2e8f0; }
        .admin-topnav-mobile-item--active {
            background-color: #ecfdf5;
            color: #0f766e;
        }
        html.dark .admin-topnav-mobile-item--active {
            background-color: rgba(13, 148, 136, 0.2);
            color: #5eead4;
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
<body class="h-full overflow-hidden" x-data="{ mobileNavOpen: false }">
    <div id="rams-admin-app" class="flex h-full min-h-0 flex-col overflow-hidden bg-gray-50">
        @include('layouts.partials.admin-topnav')

        <main class="rams-admin-scroll flex-1 min-h-0 overflow-y-auto overflow-x-hidden bg-gray-50 p-4 sm:p-6">

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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-6">@yield('page-title', 'Dashboard')</h1>

                <!-- Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="shrink-0 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 py-4 px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                    <span class="text-sm text-gray-600 dark:text-slate-400 text-center sm:text-left">
                        {!! nl2br(e(app(\App\Settings\GeneralSettings::class)->footer_text ?? 'Dashboard de Recaudos')) !!}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-slate-400">Versión {{ config('app.version') }}</span>
                </div>
            </footer>
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
