@extends('layouts.admin-flowbite')

@section('title', 'Editar plantilla PDF de propuesta - RAMS')

@section('page-title', 'Editar plantilla PDF de propuesta')

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
            <span class="text-sm font-medium text-gray-500">{{ $template->name }}</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.settings.proposal-pdf-templates.update', $template) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.proposal-pdf-templates._form', ['template' => $template])
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                    <i class="fas fa-save mr-2"></i> Guardar cambios
                </button>
                <a href="{{ route('admin.settings.section', 'proposal-pdf') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
@php
    $tinymcePlaceholder = 'Ej: Bogotá D. C. {{fecha}}... Señor(a) {{destinatario}}...';
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#body_html',
            license_key: 'gpl',
            height: 320,
            menubar: false,
            plugins: 'lists link code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
            content_style: 'body { font-family: sans-serif; font-size: 12px; }',
            placeholder: @json($tinymcePlaceholder)
        });
    }
});
</script>
@endpush
