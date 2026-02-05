@extends('layouts.admin-flowbite')

@section('title', 'Editar Tipo de Trámite - RAMS')

@section('page-title', 'Editar Tipo de Trámite')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.service-types.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Tipos de Trámite</a>
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
        <form action="{{ route('admin.service-types.update', $serviceType) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $serviceType->name) }}" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900">Código</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $serviceType->code) }}" maxlength="32"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="default_price" class="block mb-2 text-sm font-medium text-gray-900">Precio por defecto</label>
                    <input type="number" id="default_price" name="default_price" value="{{ old('default_price', $serviceType->default_price) }}" min="0" step="0.01"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('default_price') border-red-500 @enderror">
                    @error('default_price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center mt-6">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $serviceType->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-900">Activo</label>
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-900">Descripción</label>
                    <textarea id="description" name="description" rows="3"
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('description') border-red-500 @enderror">{{ old('description', $serviceType->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.service-types.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-save mr-2"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
