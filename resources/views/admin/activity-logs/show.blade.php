@extends('layouts.admin-flowbite')

@section('title', 'Línea de tiempo - ' . $user->name . ' - RAMS')

@section('page-title', 'Línea de tiempo: ' . $user->name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.activity-logs.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Registros de Actividad</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">{{ $user->name }}</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                <i class="fas fa-user text-teal-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>
        </div>
        <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>

    <!-- Línea de tiempo -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">Actividad desde el primer ingreso</h3>
            <p class="text-sm text-gray-500 mt-1">Ordenado del más reciente al más antiguo</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($logs as $log)
                <div class="flex gap-4 p-4 hover:bg-gray-50/50">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                        @if($log->action === 'login') bg-green-100 text-green-600
                        @elseif($log->action === 'logout') bg-gray-100 text-gray-600
                        @elseif($log->action === 'created') bg-blue-100 text-blue-600
                        @elseif($log->action === 'updated') bg-amber-100 text-amber-600
                        @elseif($log->action === 'deleted') bg-red-100 text-red-600
                        @elseif($log->action === 'mutation') bg-indigo-100 text-indigo-600
                        @else bg-gray-100 text-gray-600
                        @endif">
                        @if($log->action === 'login')
                            <i class="fas fa-sign-in-alt"></i>
                        @elseif($log->action === 'logout')
                            <i class="fas fa-sign-out-alt"></i>
                        @elseif($log->action === 'created')
                            <i class="fas fa-plus"></i>
                        @elseif($log->action === 'updated')
                            <i class="fas fa-edit"></i>
                        @elseif($log->action === 'deleted')
                            <i class="fas fa-trash"></i>
                        @elseif($log->action === 'sent_email')
                            <i class="fas fa-envelope"></i>
                        @elseif($log->action === 'mutation')
                            <i class="fas fa-paper-plane"></i>
                        @else
                            <i class="fas fa-circle"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                @if($log->action === 'login') bg-green-100 text-green-800
                                @elseif($log->action === 'logout') bg-gray-100 text-gray-800
                                @elseif($log->action === 'created') bg-blue-100 text-blue-800
                                @elseif($log->action === 'updated') bg-amber-100 text-amber-800
                                @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                @elseif($log->action === 'sent_email') bg-teal-100 text-teal-800
                                @elseif($log->action === 'mutation') bg-indigo-100 text-indigo-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $actionLabels[$log->action] ?? $log->action }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            @if($log->ip_address)
                                <span class="text-xs text-gray-400">· {{ $log->ip_address }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-900">{{ $log->description }}</p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-stream text-4xl text-gray-300 mb-2 block"></i>
                    No hay actividad registrada para este usuario.
                </div>
            @endforelse
        </div>
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
