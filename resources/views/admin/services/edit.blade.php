@extends('layouts.admin-flowbite')

@section('title', 'Editar Servicio - RAMS')

@section('page-title', 'Editar Servicio')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.services.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Servicios</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Editar</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.services.update', $service) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Servicio <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $service->name) }}" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="default_scope" class="block mb-2 text-sm font-medium text-gray-900">Alcance (texto por defecto)</label>
                    <textarea id="default_scope" name="default_scope" rows="5"
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('default_scope') border-red-500 @enderror">{{ old('default_scope', $service->default_scope) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Este texto se usará por defecto en la cotización al seleccionar este servicio; el usuario podrá editarlo en cada ítem.</p>
                    @error('default_scope')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-900">Activo</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-save mr-2"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
