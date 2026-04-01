<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Deshabilitar Cloudflare Insights beacon -->
    <meta name="cf-2fa-verify" content="">
    <title>Iniciar Sesión - RAMS</title>
    
    <!-- Suprimir errores de Cloudflare beacon ANTES de que se carguen otros scripts -->
    <script>
        // Ejecutar INMEDIATAMENTE, antes de cualquier otro script
        (function() {
            'use strict';
            
            // Guardar referencias originales
            const originalError = window.console.error;
            const originalWarn = window.console.warn;
            
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
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Flowbite CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        }
    </style>
</head>
<body class="h-full min-h-screen flex flex-col">
    @php
        try {
            $loginSettings = app(\App\Settings\GeneralSettings::class);
            $loginFooterHtml = $loginSettings->footer_text ?? 'RAMS - Regulatory Affairs Management System';
            $loginAgencyName = trim($loginSettings->agency_name ?? '') ?: 'RAMS';
        } catch (\Throwable $e) {
            $loginFooterHtml = 'RAMS - Regulatory Affairs Management System';
            $loginAgencyName = 'RAMS';
        }
    @endphp
    <div class="flex-1 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
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
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset($logoPath) }}" 
                             alt="{{ $agencyName ?? 'Logo' }}" 
                             class="h-16 w-auto object-contain">
                    </div>
                @elseif($hasName)
                    {{-- Solo mostrar nombre de agencia si no hay logo --}}
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-teal-600">{{ $agencyName }}</span>
                    </h1>
                @else
                    {{-- Por defecto: R REGULATORY APP --}}
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-teal-600">R</span> REGULATORY APP
                    </h1>
                @endif
                <p class="text-gray-600 mt-2">Inicia sesión para continuar</p>
            </div>

            <!-- Flash success / error (p. ej. tras registro por invitación) -->
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-teal-800 bg-teal-50 border border-teal-200 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Errors -->
            @if ($errors->any())
                <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="post" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full pl-10 p-2.5" 
                               placeholder="tu@email.com" 
                               value="{{ old('email', request('email')) }}" 
                               required autofocus>
                    </div>
                </div>

                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full pl-10 p-2.5" 
                               placeholder="••••••••" 
                               required>
                    </div>
                </div>

                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                        <label for="remember" class="ml-2 text-sm text-gray-900">Recordarme</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-sm text-teal-700 hover:underline">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" 
                        class="w-full text-white bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </button>
            </form>

        </div>
    </div>
    </div>

    <footer class="w-full max-w-xl mx-auto px-6 pb-8 pt-2 text-center text-sm text-white/95">
        <div class="mb-3 leading-relaxed">{!! $loginFooterHtml !!}</div>
        <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1">
            <a href="{{ route('legal.privacy') }}" class="text-white font-medium hover:underline">Política de Privacidad</a>
            <span class="text-white/50" aria-hidden="true">|</span>
            <a href="{{ route('legal.terms') }}" class="text-white font-medium hover:underline">Términos y Condiciones del Servicio</a>
        </div>
        <p class="mt-4 text-white/75 text-xs">© {{ date('Y') }} {{ $loginAgencyName }}. Todos los derechos reservados.</p>
    </footer>

    <!-- Flowbite JS -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>
