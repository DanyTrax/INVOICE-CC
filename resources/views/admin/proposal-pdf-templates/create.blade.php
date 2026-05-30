@extends('layouts.admin-flowbite')

@section('title', 'Nueva plantilla PDF de propuesta - RAMS')

@section('page-title', 'Nueva plantilla PDF de propuesta')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.settings.section', 'proposal-pdf') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Plantilla PDF de Propuestas</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Nueva</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.settings.proposal-pdf-templates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.proposal-pdf-templates._form', ['template' => null])
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                    <i class="fas fa-save mr-2"></i> Guardar plantilla
                </button>
                <a href="{{ route('admin.settings.section', 'proposal-pdf') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection

@include('admin.partials.pdf-template-body-tinymce-init', [
    'tinymcePlaceholder' => 'Ej: Bogotá D. C. {{fecha}}… Señor(a) {{destinatario}}…',
])
