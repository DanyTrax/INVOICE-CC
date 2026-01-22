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
    <!-- Estadísticas Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-widgets.stats-card 
            title="EXPEDIENTES ACTIVOS"
            :value="$stats['active_registrations']"
            icon="clipboard-list"
            color="blue"
            :link="route('admin.registrations.index')"
            subtitle="Total de registros activos"
        />

        <x-widgets.stats-card 
            title="VENCEN ESTE MES"
            :value="$stats['expiring_this_month']"
            icon="exclamation-triangle"
            color="red"
            :link="route('admin.registrations.index', ['filter' => 'expiring'])"
            subtitle="Requieren atención"
        />

        <x-widgets.stats-card 
            title="EN TRÁMITE INVIMA"
            :value="$stats['in_process_invima']"
            icon="hourglass-half"
            color="teal"
            :link="route('admin.registrations.index', ['status' => 'en_tramite'])"
            subtitle="Pendientes de respuesta"
        />

        <x-widgets.stats-card 
            title="CLIENTES TOTALES"
            :value="$stats['total_companies']"
            icon="building"
            color="green"
            :link="route('admin.companies.index')"
            subtitle="Empresas registradas"
        />
    </div>

    <!-- Calendario -->
    <x-widgets.calendar :events="$events" />
@endsection
