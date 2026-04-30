@extends('layouts.portal')

@section('title', 'Acceso no disponible')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
        <div class="bg-amber-50 border-b border-amber-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Cuenta pendiente de activación</h1>
                    <p class="text-sm text-amber-800 mt-0.5">Su acceso al portal aún no ha sido habilitado.</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-6 text-gray-700 space-y-4">
            <p class="leading-relaxed">
                Estimado(a) <strong>{{ auth()->user()->name }}</strong>,
            </p>
            <p class="leading-relaxed">
                Su usuario en el portal ha sido creado correctamente; sin embargo, el acceso al contenido (resumen, solicitudes e historial de procesos) se encuentra temporalmente deshabilitado hasta que su cuenta sea activada.
            </p>
            <p class="leading-relaxed">
                Para poder consultar sus trámites y el estado de sus procesos, debe <strong>ponerse en contacto con el agente o asesor que gestiona sus trámites regulatorios</strong>. Ellos realizarán la activación de su usuario y, una vez completada, podrá acceder a todo el contenido del portal con normalidad.
            </p>
            <p class="leading-relaxed text-gray-600 text-sm">
                Si ya ha contactado a su asesor y el acceso sigue sin estar disponible, le recomendamos volver a solicitarlo o verificar que la activación se haya realizado desde el sistema de administración.
            </p>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-500">
                ¿Dudas? Utilice el enlace de <strong>Soporte / Ayuda</strong> en el menú cuando esté disponible, o contacte a la agencia por los canales habituales.
            </p>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
