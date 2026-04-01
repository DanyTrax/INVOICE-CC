@extends('layouts.admin-flowbite')

@section('title', 'Capacitaciones - RAMS')

@section('page-title', 'Capacitaciones')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Capacitaciones</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-gray-600">Videos de capacitación. Debes ver cada video completo (no se puede adelantar). Si cierras antes de terminar, deberás volver a iniciarlo.</p>
        @if($canManage)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.capacitaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                    <i class="fas fa-plus mr-2"></i> Subir video
                </a>
                <a href="{{ route('admin.capacitaciones.reporte.pdf') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i> Reporte completo (PDF)
                </a>
            </div>
        @endif
    </div>

    <div class="space-y-6">
        @forelse($videos as $video)
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $video->titulo }}</h3>
                        @if($video->descripcion)
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($video->descripcion, 120) }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @php $miCompletion = $video->completions->firstWhere('user_id', auth()->id()); @endphp
                        <a href="{{ route('admin.capacitaciones.ver', $video) }}" class="inline-flex items-center px-3 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
                            <i class="fas fa-play mr-2"></i>
                            {{ $miCompletion ? 'Volver a ver' : 'Ver video' }}
                            @if($miCompletion)
                                <span class="ml-2 px-1.5 py-0.5 text-xs bg-green-100 text-green-800 rounded">Visto {{ $miCompletion->completed_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </a>
                        @if($canManage)
                            <a href="{{ route('admin.capacitaciones.edit', $video) }}" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">Editar</a>
                            <a href="{{ route('admin.capacitaciones.reporte.video.pdf', $video) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm" target="_blank">Reporte PDF</a>
                            <form action="{{ route('admin.capacitaciones.destroy', $video) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este video de capacitación?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Eliminar</button>
                            </form>
                        @endif
                    </div>
                </div>
                @if($canManage && $especialistas->isNotEmpty())
                    <div class="border-t border-gray-100 px-4 py-3 bg-gray-50">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Quién vio este video (check y fecha)</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($especialistas as $agente)
                                @php $comp = $video->completions->firstWhere('user_id', $agente->id); @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $comp ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $agente->name }}
                                    @if($comp)
                                        <i class="fas fa-check ml-1"></i> {{ $comp->completed_at->format('d/m/Y H:i') }}
                                    @else
                                        <span class="ml-1">—</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
                <i class="fas fa-video text-4xl text-gray-300 mb-3"></i>
                <p>No hay videos de capacitación.</p>
                @if($canManage)
                    <a href="{{ route('admin.capacitaciones.create') }}" class="inline-block mt-3 text-teal-600 hover:underline">Subir el primero</a>
                @endif
            </div>
        @endforelse
    </div>
@endsection
