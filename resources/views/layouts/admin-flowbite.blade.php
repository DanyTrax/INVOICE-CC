<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RAMS - Regulatory Affairs Management System')</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Flowbite CSS (CDN para desarrollo rápido) -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0" 
               :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
               style="background-color: #1e293b;">
            <div class="h-full px-3 py-4 overflow-y-auto">
                <!-- Logo -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center ps-2.5 mb-5">
                    <span class="self-center text-xl font-semibold whitespace-nowrap text-white">
                        <span class="text-teal-400">R</span> REGULATORY APP
                    </span>
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
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 py-3">
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <i class="fas fa-bars w-5 h-5"></i>
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button type="button" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                <div class="w-8 h-8 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <span>{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down w-3 h-3"></i>
                            </button>
                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
                <!-- Breadcrumb -->
                <nav class="mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-teal-700">
                                <i class="fas fa-home mr-2"></i> Inicio
                            </a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>

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
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>
</html>
