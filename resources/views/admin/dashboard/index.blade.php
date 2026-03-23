@extends('layouts.admin-flowbite')

@section('title', 'Dashboard - RAMS')

@section('page-title', 'Resumen General')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
    @if(! auth()->user()->hasTwoFactorEnabled())
        @include('profile.partials.two-factor-setup-wizard', ['routePrefix' => 'admin', 'variant' => 'banner'])
    @endif

    <!-- Estadísticas por etapa del flujo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-widgets.stats-card 
            title="EXPEDIENTES ACTIVOS"
            :value="$stats['total_active']"
            icon="clipboard-list"
            color="blue"
            :link="route('admin.processes.monitor')"
            subtitle="En Recolección, Sometimiento, Radicado o Auto"
        />

        <x-widgets.stats-card 
            title="EN RECOLECCIÓN"
            :value="$stats['recoleccion']"
            icon="folder-open"
            color="yellow"
            :link="route('admin.processes.monitor', ['step' => \App\Models\Process::STEP_RECOLECCION])"
            subtitle="Checklist en curso o rechazo; sin sometimiento pendiente"
        />

        <x-widgets.stats-card 
            title="EN SOMETIMIENTO (TURNO)"
            :value="$stats['sometimiento']"
            icon="paper-plane"
            color="teal"
            :link="route('admin.processes.monitor', ['step' => \App\Models\Process::STEP_SOMETIMIENTO])"
            subtitle="Sometimiento, pendiente de radicado INVIMA"
        />

        <x-widgets.stats-card 
            title="RADICADOS INVIMA"
            :value="$stats['radicado']"
            icon="stamp"
            color="blue"
            :link="route('admin.processes.monitor', ['step' => \App\Models\Process::STEP_RADICADO])"
            subtitle="Radicado, listo para Resolución o AUTO"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <x-widgets.stats-card 
            title="TRÁMITE AUTO"
            :value="$stats['en_requerimiento']"
            icon="exclamation-triangle"
            color="yellow"
            :link="route('admin.processes.monitor', ['step' => \App\Models\Process::STEP_AUTO])"
            subtitle="AUTO (Recolección), (Sometimiento), (Radicado)… hasta cerrar"
        />

        <x-widgets.stats-card 
            title="FINALIZADOS (HISTORIAL)"
            :value="$stats['finalizados']"
            icon="flag-checkered"
            color="green"
            :link="route('admin.processes.history')"
            subtitle="Ver expedientes aprobados"
        />

        <x-widgets.stats-card 
            title="CLIENTES TOTALES"
            :value="$stats['total_companies']"
            icon="building"
            color="teal"
            :link="route('admin.companies.index')"
            subtitle="Empresas registradas"
        />
    </div>

    <!-- Calendario de vencimientos de AUTO -->
    <x-widgets.calendar :events="$events" />
@endsection
