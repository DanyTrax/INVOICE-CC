<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RAMS - Regulatory Affairs Management System')</title>
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Flowbite CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    
    <!-- FullCalendar CSS - No necesario en v6, el JS lo inyecta automáticamente -->
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- TinyMCE CDN con API key -->
    <script src="https://cdn.tiny.cloud/1/p8c81q7d73jr9r6f8pkn3wr5d822yeinngzsiz1hxzpwxpf3/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
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
                
                <!-- Menu -->
                <ul class="space-y-2 font-medium">
                    <!-- PRINCIPAL -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-home w-5 h-5"></i>
                            <span class="ms-3">Inicio</span>
                        </a>
                    </li>
                    
                    <!-- OPERACIÓN -->
                    <li class="pt-4">
                        <span class="text-gray-400 text-xs font-semibold uppercase px-2">OPERACIÓN</span>
                    </li>
                    <li>
                        <a href="{{ route('admin.companies.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.companies.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-building w-5 h-5"></i>
                            <span class="ms-3">Directorio Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.registrations.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.registrations.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-clipboard-list w-5 h-5"></i>
                            <span class="ms-3">Registros (Expedientes)</span>
                        </a>
                    </li>
                    
                    <!-- SISTEMA -->
                    <li class="pt-4">
                        <span class="text-gray-400 text-xs font-semibold uppercase px-2">SISTEMA</span>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.users.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-users w-5 h-5"></i>
                            <span class="ms-3">Agentes / Usuarios</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center p-2 rounded-lg text-white hover:bg-teal-700 {{ request()->routeIs('admin.settings.*') ? 'bg-teal-700' : '' }}">
                            <i class="fas fa-cog w-5 h-5"></i>
                            <span class="ms-3">Configuración</span>
                        </a>
                    </li>
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
                        <strong>RAMS</strong> - Regulatory Affairs Management System
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
