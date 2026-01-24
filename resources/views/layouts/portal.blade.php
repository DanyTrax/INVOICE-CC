<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Cliente') - {{ app(\App\Settings\GeneralSettings::class)->agency_name ?? 'RAMS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#0f766e' } } } };
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 font-sans text-gray-900" x-data="{ sidebarOpen: true, userMenuOpen: false }">

<div class="flex h-screen overflow-hidden">
    <aside class="bg-white border-r border-gray-200 flex flex-col transition-all duration-300 z-20"
           :class="sidebarOpen ? 'w-64' : 'w-20'">
        <div class="h-16 flex items-center px-4 border-b border-gray-100 gap-3 overflow-hidden">
            <div class="min-w-[32px] h-8 bg-teal-600 rounded flex items-center justify-center text-white font-bold">
                <i class="fas fa-shield-alt"></i>
            </div>
            <span x-show="sidebarOpen" class="font-bold text-sm tracking-wide text-gray-800 whitespace-nowrap">
                {{ app(\App\Settings\GeneralSettings::class)->agency_name ?? 'RAMS' }}
            </span>
        </div>

        @php
            $portalAgent = null;
            foreach (auth()->user()->companies as $company) {
                $reg = \App\Models\Registration::where('company_id', $company->id)->whereNotNull('assigned_specialist_id')->with('assignedSpecialist')->first();
                if ($reg?->assignedSpecialist) {
                    $portalAgent = $reg->assignedSpecialist;
                    break;
                }
            }
            $portalHelpEmail = $portalAgent?->email ?? (app(\App\Settings\GeneralSettings::class)->agency_email ?? null);
        @endphp
        <nav class="flex-1 py-6 space-y-1 px-3 overflow-y-auto">
            <a href="{{ route('portal.dashboard') }}"
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('portal.dashboard') ? 'bg-teal-50 text-teal-700 font-bold' : 'text-gray-500 hover:bg-gray-50' }}">
                <i class="fas fa-chart-pie w-6 text-center"></i>
                <span x-show="sidebarOpen" class="ml-3 text-sm">Resumen</span>
            </a>
            <a href="{{ route('portal.registrations.index') }}"
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('portal.registrations.*') ? 'bg-teal-50 text-teal-700 font-bold' : 'text-gray-500 hover:bg-gray-50' }}">
                <i class="fas fa-file-contract w-6 text-center"></i>
                <span x-show="sidebarOpen" class="ml-3 text-sm">Mis Expedientes</span>
            </a>
            @if($portalHelpEmail)
            <a href="mailto:{{ $portalHelpEmail }}" class="flex items-center px-3 py-2.5 rounded-lg text-gray-500 hover:bg-gray-50 mt-4">
                <i class="fas fa-life-ring w-6 text-center"></i>
                <span x-show="sidebarOpen" class="ml-3 text-sm">Soporte / Ayuda</span>
            </a>
            @endif
        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-6 shadow-sm z-30 gap-4">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-600 shrink-0">
                <i class="fas fa-bars"></i>
            </button>

            @if($portalAgent ?? null)
            <div class="flex items-center gap-2 md:gap-3 min-w-0 flex-1 flex-wrap">
                <div class="flex items-center gap-1.5 md:gap-2 px-2 md:px-3 py-1 md:py-1.5 rounded-lg bg-blue-50 border border-blue-100">
                    <span class="text-[10px] md:text-xs font-medium text-blue-600">Especialista:</span>
                    <span class="text-xs md:text-sm font-semibold text-gray-800 truncate max-w-[120px] md:max-w-none">{{ $portalAgent->name }}</span>
                </div>
                <a href="mailto:{{ $portalAgent->email }}" class="inline-flex items-center gap-1 md:gap-1.5 px-2 md:px-3 py-1 md:py-1.5 rounded-lg bg-teal-600 text-white text-xs md:text-sm font-medium hover:bg-teal-700 shrink-0" title="Contactar a {{ $portalAgent->name }}">
                    <i class="fas fa-envelope"></i>
                    <span>Ayuda</span>
                </a>
            </div>
            @endif

            <div class="relative shrink-0" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-3 hover:bg-gray-50 p-2 rounded-lg transition-colors focus:outline-none">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-gray-500">{{ auth()->user()->companies->first()?->name ?? 'Cliente' }}</p>
                    </div>
                    <img src="https://ui-avatars.com/api?name={{ urlencode(auth()->user()->name) }}&background=f59e0b&color=fff" alt="" class="w-9 h-9 rounded-full border border-gray-200">
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-1">
                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-auto bg-gray-50 p-6 md:p-8">
            @if(session('success'))
                <div class="mb-4 p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
