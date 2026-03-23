@extends('layouts.admin-flowbite')

@section('title', 'Nuevo concepto - RAMS')

@section('page-title', 'Nuevo concepto en catálogo')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.concept-catalogs.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Catálogo de conceptos</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Nuevo</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-3xl">
        <form action="{{ route('admin.concept-catalogs.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900 mb-1">Concepto <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="scope" class="block text-sm font-medium text-gray-900 mb-1">Alcance</label>
                    <textarea name="scope" id="scope" rows="5" maxlength="5000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('scope') }}</textarea>
                </div>
                <div>
                    <label for="default_fee" class="block text-sm font-medium text-gray-900 mb-1">Honorario sugerido</label>
                    <input type="number" name="default_fee" id="default_fee" value="{{ old('default_fee') }}" min="0" step="0.01" class="w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-600" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-800">Activo (visible al elegir desde catálogo)</span>
                </label>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">Guardar</button>
                <a href="{{ route('admin.concept-catalogs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
