@extends('layouts.admin-flowbite')

@section('title', 'Ver video - ' . $video->titulo . ' - RAMS')

@section('page-title', $video->titulo)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.capacitaciones.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Capacitaciones</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Ver video</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 max-w-4xl mx-auto">
        @if($video->descripcion)
            <p class="text-sm text-gray-600 mb-4">{{ $video->descripcion }}</p>
        @endif
        <p class="text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm mb-4">
            <i class="fas fa-info-circle mr-2"></i>
            Debes ver el video completo. No se puede adelantar. Si cierras esta página antes de terminar, deberás volver a iniciarlo desde el principio. Al finalizar se registrará tu visualización.
        </p>
        <div class="relative bg-black rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
            <video id="cap-video"
                    class="w-full h-full"
                    controls
                    controlsList="nodownload"
                    preload="metadata"
                    data-completar-url="{{ route('admin.capacitaciones.completar', $video) }}"
                    data-csrf="{{ csrf_token() }}">
                <source src="{{ route('admin.capacitaciones.stream', $video) }}" type="video/mp4">
                Tu navegador no soporta la reproducción de video.
            </video>
        </div>
        <p id="progress-msg" class="text-sm text-gray-500 mt-2"></p>
        <div class="mt-4 flex gap-3">
            <a href="{{ route('admin.capacitaciones.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Volver a capacitaciones
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        var video = document.getElementById('cap-video');
        if (!video) return;
        var completarUrl = video.getAttribute('data-completar-url');
        var csrf = video.getAttribute('data-csrf');
        var lastValidTime = 0;
        var completed = false;

        video.addEventListener('timeupdate', function() {
            if (video.currentTime > lastValidTime) {
                lastValidTime = video.currentTime;
            }
            var pct = video.duration ? Math.min(100, (video.currentTime / video.duration) * 100) : 0;
            document.getElementById('progress-msg').textContent = 'Progreso: ' + pct.toFixed(0) + '%';
        });

        video.addEventListener('seeking', function() {
            // Bloquear cualquier intento de mover la barra (adelantar o atrasar)
            video.currentTime = lastValidTime;
        });

        video.addEventListener('ended', function() {
            if (completed) return;
            completed = true;
            document.getElementById('progress-msg').textContent = 'Registrando visualización...';
            var form = new FormData();
            form.append('_token', csrf);
            fetch(completarUrl, {
                method: 'POST',
                body: form,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.json().catch(function() { return {} }); }).then(function() {
                document.getElementById('progress-msg').innerHTML = '<span class="text-green-700"><i class="fas fa-check-circle mr-1"></i>Visualización registrada correctamente.</span>';
            }).catch(function() {
                document.getElementById('progress-msg').textContent = 'Error al registrar. Vuelve a ver el video hasta el final.';
                completed = false;
            });
        });

        video.addEventListener('durationchange', function() {
            if (video.duration && video.currentTime >= video.duration - 0.5 && !completed) {
                video.dispatchEvent(new Event('ended'));
            }
        });
    })();
    </script>
    @endpush
@endsection
