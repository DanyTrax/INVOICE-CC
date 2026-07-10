@extends('layouts.admin-flowbite')

@section('title', 'Dashboard - Recaudos')

@section('page-title', 'Dashboard de Recaudos')

@section('content')
    @if(! auth()->user()->hasTwoFactorEnabled())
        @include('profile.partials.two-factor-setup-wizard', ['routePrefix' => 'admin', 'variant' => 'banner'])
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-teal-100 flex items-center justify-center mb-4">
            <i class="fas fa-file-invoice-dollar text-2xl text-teal-700"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Sistema de Cuentas de Cobro</h2>
        <p class="text-gray-600 max-w-lg mx-auto mb-6">
            Gestione asociados, conceptos de cobro y emisión de cuentas de cobro con PDF y correo.
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            @if(app(\App\Services\PermissionService::class)->userHasPermission('invoices', 'view'))
                <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center gap-2 text-white bg-teal-700 hover:bg-teal-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    <i class="fas fa-file-invoice-dollar"></i> Cuentas de cobro
                </a>
            @endif
            @if(app(\App\Services\PermissionService::class)->userHasPermission('settings_brand', 'view'))
                <a href="{{ route('admin.brand-settings.edit') }}" class="inline-flex items-center gap-2 text-teal-700 bg-teal-50 hover:bg-teal-100 font-medium rounded-lg text-sm px-5 py-2.5 border border-teal-200">
                    <i class="fas fa-palette"></i> Marca blanca
                </a>
            @endif
        </div>
    </div>
@endsection
