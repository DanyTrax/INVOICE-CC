@php
    $logoUrl = isset($brandSetting) && $brandSetting->logo_path ? $brandSetting->logoUrl() : null;
    $companyName = $brandSetting->company_name ?? config('app.name', 'Recaudos');
    $nav = app(\App\Services\PermissionService::class)->adminNavigation();

    $recaudosActive = collect($nav['recaudos'])->contains(fn ($item) => $item['active']);
    $directorioActive = collect($nav['directorio'])->contains(fn ($item) => $item['active']);
    $sistemaActive = collect($nav['sistema'])->contains(fn ($item) => $item['active']);
@endphp

<header class="shrink-0 bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700 z-40">
    <div class="flex items-center justify-between gap-4 px-4 lg:px-6 h-16">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button"
                    @click="mobileNavOpen = !mobileNavOpen"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700"
                    aria-label="Menú">
                <i class="fas fa-bars w-5 h-5"></i>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-w-0 shrink-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="h-9 w-auto object-contain">
                @else
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-white font-bold text-sm shadow-sm"
                          style="background: linear-gradient(135deg, #319795 0%, #4895ef 100%);">
                        {{ strtoupper(substr($companyName, 0, 1)) }}
                    </span>
                    <span class="hidden sm:block font-semibold text-gray-900 dark:text-slate-100 truncate max-w-[10rem]">{{ $companyName }}</span>
                @endif
            </a>
        </div>

        <nav class="hidden lg:flex items-center justify-center gap-1 flex-1" aria-label="Navegación principal">
            @if($nav['dashboard'])
                <a href="{{ route('admin.dashboard') }}"
                   class="admin-topnav-link {{ request()->routeIs('admin.dashboard') ? 'admin-topnav-link--active' : '' }}">
                    <i class="fas fa-home text-sm"></i>
                    <span>Inicio</span>
                </a>
            @endif

            @if(count($nav['recaudos']) > 0)
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="admin-topnav-link {{ $recaudosActive ? 'admin-topnav-link--active' : '' }}">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                        <span>Recaudos</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute left-0 top-full mt-1 w-52 rounded-xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg py-1 z-50">
                        @foreach($nav['recaudos'] as $item)
                            <a href="{{ route($item['route']) }}" class="admin-topnav-dropdown-item {{ $item['active'] ? 'admin-topnav-dropdown-item--active' : '' }}">
                                <i class="fas {{ $item['icon'] }} w-4"></i> {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($nav['directorio']) > 0)
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="admin-topnav-link {{ $directorioActive ? 'admin-topnav-link--active' : '' }}">
                        <i class="fas fa-address-book text-sm"></i>
                        <span>Directorio</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute left-0 top-full mt-1 w-48 rounded-xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg py-1 z-50">
                        @foreach($nav['directorio'] as $item)
                            <a href="{{ route($item['route']) }}" class="admin-topnav-dropdown-item {{ $item['active'] ? 'admin-topnav-dropdown-item--active' : '' }}">
                                <i class="fas {{ $item['icon'] }} w-4"></i> {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($nav['sistema']) > 0)
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="admin-topnav-link {{ $sistemaActive ? 'admin-topnav-link--active' : '' }}">
                        <i class="fas fa-cog text-sm"></i>
                        <span>Sistema</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute left-0 top-full mt-1 w-52 rounded-xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg py-1 z-50">
                        @foreach($nav['sistema'] as $item)
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="admin-topnav-dropdown-item {{ $item['active'] ? 'admin-topnav-dropdown-item--active' : '' }}">
                                <i class="fas {{ $item['icon'] }} w-4"></i> {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </nav>

        <div class="flex items-center gap-2 shrink-0">
            @if($nav['settings_section'])
                <a href="{{ route('admin.settings.section', $nav['settings_section']) }}"
                   class="hidden md:inline-flex p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-teal-600"
                   title="Configuración">
                    <i class="fas fa-cog w-5 h-5"></i>
                </a>
            @endif

            <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
                <button @click="userMenuOpen = !userMenuOpen"
                        type="button"
                        class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-slate-200 hover:text-gray-900 focus:outline-none">
                    <div class="w-9 h-9 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold text-xs shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <i class="fas fa-chevron-down w-3 h-3 text-gray-400 hidden sm:block transition-transform"
                       :class="{ 'rotate-180': userMenuOpen }"></i>
                </button>

                <div x-show="userMenuOpen"
                     x-cloak
                     x-transition
                     class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-lg shadow-lg py-1 z-50 border border-gray-200 dark:border-slate-600">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-600">
                        <p class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700">
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
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700">
                                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="mobileNavOpen" x-cloak @click.outside="mobileNavOpen = false"
         class="lg:hidden border-t border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3 space-y-1 max-h-[70vh] overflow-y-auto">
        @if($nav['dashboard'])
            <a href="{{ route('admin.dashboard') }}" class="admin-topnav-mobile-item {{ request()->routeIs('admin.dashboard') ? 'admin-topnav-mobile-item--active' : '' }}">
                <i class="fas fa-home w-5"></i> Inicio
            </a>
        @endif
        @foreach($nav['recaudos'] as $item)
            <a href="{{ route($item['route']) }}" class="admin-topnav-mobile-item {{ $item['active'] ? 'admin-topnav-mobile-item--active' : '' }}">
                <i class="fas {{ $item['icon'] }} w-5"></i> {{ $item['label'] }}
            </a>
        @endforeach
        @foreach($nav['directorio'] as $item)
            <a href="{{ route($item['route']) }}" class="admin-topnav-mobile-item {{ $item['active'] ? 'admin-topnav-mobile-item--active' : '' }}">
                <i class="fas {{ $item['icon'] }} w-5"></i> {{ $item['label'] }}
            </a>
        @endforeach
        @foreach($nav['sistema'] as $item)
            <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="admin-topnav-mobile-item {{ $item['active'] ? 'admin-topnav-mobile-item--active' : '' }}">
                <i class="fas {{ $item['icon'] }} w-5"></i> {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    <div class="hidden sm:block border-t border-gray-100 dark:border-slate-700 px-4 lg:px-6 py-2 bg-gray-50/80 dark:bg-slate-900/40">
        <nav class="text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-1">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-600 dark:text-slate-400 hover:text-teal-700 dark:hover:text-teal-400">
                        <i class="fas fa-home mr-1.5 text-xs"></i> Inicio
                    </a>
                </li>
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    @include('layouts.partials.admin-breadcrumb')
                @endif
            </ol>
        </nav>
    </div>
</header>
