@extends('layouts.admin-flowbite')

@section('title', 'Nueva solicitud - RAMS')

@section('page-title', 'Nueva solicitud')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.processes.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Solicitudes</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Nueva solicitud</span>
        </div>
    </li>
@endsection

@section('content')
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <p class="font-medium mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Corrija los errores antes de enviar.</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.processes.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la solicitud (sin cotización)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="client_id" class="block mb-2 text-sm font-medium text-gray-900">Cliente <span class="text-red-500">*</span></label>
                    <select name="client_id" id="client_id" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="">Seleccione...</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="service_type_name" class="block mb-2 text-sm font-medium text-gray-900">Tipo de trámite <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="service_type_name"
                           id="service_type_name"
                           value="{{ old('service_type_name') }}"
                           list="service_types_datalist"
                           placeholder="Escriba o seleccione..."
                           required
                           maxlength="255"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    <p class="mt-1 text-xs text-gray-500">Use el autocompletado para elegir un tipo de la lista.</p>
                </div>
                <div>
                    <label for="product_reference" class="block mb-2 text-sm font-medium text-gray-900">Producto / Referencia</label>
                    <input type="text"
                           name="product_reference"
                           id="product_reference"
                           value="{{ old('product_reference') }}"
                           placeholder="Ej: Producto X, Ref. 123"
                           maxlength="500"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="email_name" class="block mb-2 text-sm font-medium text-gray-900">Nombre de correo</label>
                    <input type="text"
                           name="email_name"
                           id="email_name"
                           value="{{ old('email_name') }}"
                           placeholder="Opcional"
                           maxlength="255"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-save mr-2"></i> Crear solicitud
            </button>
            <a href="{{ route('admin.processes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                Cancelar
            </a>
        </div>
    </form>

    <datalist id="service_types_datalist">
        @foreach($serviceTypes as $st)
            <option value="{{ $st->name }}"></option>
        @endforeach
    </datalist>
@endsection
