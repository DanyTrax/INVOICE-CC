@extends('layouts.admin-flowbite')

@section('title', 'Subir video - Capacitaciones - RAMS')

@section('page-title', 'Subir video de capacitación')

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
            <span class="text-sm font-medium text-gray-500">Subir video</span>
        </div>
    </li>
@endsection

@section('content')
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.capacitaciones.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 max-w-2xl">
        @csrf
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-900 mb-1">Título <span class="text-red-500">*</span></label>
            <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}" required maxlength="255"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500 @error('titulo') border-red-500 @enderror">
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-900 mb-1">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" maxlength="5000"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-6">
            <label for="video" class="block text-sm font-medium text-gray-900 mb-1">Video (MP4) <span class="text-red-500">*</span></label>
            <input type="file" id="video" name="video" accept=".mp4,video/mp4" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500 @error('video') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">Solo formato MP4. Máximo 512 MB.</p>
            @error('video')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-upload mr-2"></i> Subir
            </button>
            <a href="{{ route('admin.capacitaciones.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Cancelar</a>
        </div>
    </form>
@endsection
