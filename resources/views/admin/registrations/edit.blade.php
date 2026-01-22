@extends('layouts.admin-flowbite')

@section('title', 'Editar Expediente - RAMS')

@section('page-title', 'Editar Expediente')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Expedientes</a>
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
        <form action="{{ route('admin.registrations.update', $registration) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Sección 1: Información del Cliente -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">1.</span> Información del Cliente
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_id" class="block mb-2 text-sm font-medium text-gray-900">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <select id="company_id" 
                                name="company_id" 
                                required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('company_id') border-red-500 @enderror">
                            <option value="">Seleccionar cliente...</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $registration->company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }} - {{ $company->nit_rut }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sección 2: Datos del Trámite y Producto -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">2.</span> Datos del Trámite y Producto
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="product_name" class="block mb-2 text-sm font-medium text-gray-900">
                            Nombre del Producto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="product_name" 
                               name="product_name" 
                               value="{{ old('product_name', $registration->product_name) }}"
                               required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('product_name') border-red-500 @enderror">
                        @error('product_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="transaction_type" class="block mb-2 text-sm font-medium text-gray-900">
                            Tipo de Trámite
                        </label>
                        <input type="text" 
                               id="transaction_type" 
                               name="transaction_type" 
                               value="{{ old('transaction_type', $registration->transaction_type) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="status" class="block mb-2 text-sm font-medium text-gray-900">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status" 
                                required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            <option value="pendiente" {{ old('status', $registration->status) === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_tramite" {{ old('status', $registration->status) === 'en_tramite' ? 'selected' : '' }}>En Trámite</option>
                            <option value="aprobado" {{ old('status', $registration->status) === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="rechazado" {{ old('status', $registration->status) === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                    </div>

                    <div>
                        <label for="assigned_specialist_id" class="block mb-2 text-sm font-medium text-gray-900">
                            Especialista Asignado
                        </label>
                        <select id="assigned_specialist_id" 
                                name="assigned_specialist_id" 
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            <option value="">Sin asignar</option>
                            @foreach($specialists as $specialist)
                                <option value="{{ $specialist->id }}" {{ old('assigned_specialist_id', $registration->assigned_specialist_id) == $specialist->id ? 'selected' : '' }}>
                                    {{ $specialist->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sección 3: Cronograma y Radicados -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">3.</span> Cronograma y Radicados
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="quotation_number" class="block mb-2 text-sm font-medium text-gray-900">
                            N° Cotización
                        </label>
                        <input type="text" 
                               id="quotation_number" 
                               name="quotation_number" 
                               value="{{ old('quotation_number', $registration->quotation_number) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="client_request_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Solicitud Cliente
                        </label>
                        <input type="date" 
                               id="client_request_date" 
                               name="client_request_date" 
                               value="{{ old('client_request_date', $registration->client_request_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="radication_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Radicación
                        </label>
                        <input type="date" 
                               id="radication_date" 
                               name="radication_date" 
                               value="{{ old('radication_date', $registration->radication_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="radication_number" class="block mb-2 text-sm font-medium text-gray-900">
                            N° Radicación
                        </label>
                        <input type="text" 
                               id="radication_number" 
                               name="radication_number" 
                               value="{{ old('radication_number', $registration->radication_number) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="submission_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Presentación
                        </label>
                        <input type="date" 
                               id="submission_date" 
                               name="submission_date" 
                               value="{{ old('submission_date', $registration->submission_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="expiration_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Vencimiento
                        </label>
                        <input type="date" 
                               id="expiration_date" 
                               name="expiration_date" 
                               value="{{ old('expiration_date', $registration->expiration_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="invima_auto_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Auto INVIMA
                        </label>
                        <input type="date" 
                               id="invima_auto_date" 
                               name="invima_auto_date" 
                               value="{{ old('invima_auto_date', $registration->invima_auto_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="response_limit_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Límite Respuesta
                        </label>
                        <input type="date" 
                               id="response_limit_date" 
                               name="response_limit_date" 
                               value="{{ old('response_limit_date', $registration->response_limit_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="response_radication_date" class="block mb-2 text-sm font-medium text-gray-900">
                            Fecha Radicación Respuesta
                        </label>
                        <input type="date" 
                               id="response_radication_date" 
                               name="response_radication_date" 
                               value="{{ old('response_radication_date', $registration->response_radication_date?->format('Y-m-d')) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="registration_number" class="block mb-2 text-sm font-medium text-gray-900">
                            N° Registro
                        </label>
                        <input type="text" 
                               id="registration_number" 
                               name="registration_number" 
                               value="{{ old('registration_number', $registration->registration_number) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="key_code" class="block mb-2 text-sm font-medium text-gray-900">
                            Código Clave
                        </label>
                        <input type="text" 
                               id="key_code" 
                               name="key_code" 
                               value="{{ old('key_code', $registration->key_code) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="resolution_number" class="block mb-2 text-sm font-medium text-gray-900">
                            N° Resolución
                        </label>
                        <input type="text" 
                               id="resolution_number" 
                               name="resolution_number" 
                               value="{{ old('resolution_number', $registration->resolution_number) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    </div>
                </div>
            </div>

            <!-- Sección 4: Detalles y Observaciones -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">4.</span> Detalles y Observaciones
                </h3>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="client_requirement" class="block mb-2 text-sm font-medium text-gray-900">
                            Requerimiento del Cliente
                        </label>
                        <textarea id="client_requirement" 
                                  name="client_requirement" 
                                  rows="3"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('client_requirement', $registration->client_requirement) }}</textarea>
                    </div>

                    <div>
                        <label for="invima_requirement" class="block mb-2 text-sm font-medium text-gray-900">
                            Requerimiento INVIMA
                        </label>
                        <textarea id="invima_requirement" 
                                  name="invima_requirement" 
                                  rows="3"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('invima_requirement', $registration->invima_requirement) }}</textarea>
                    </div>

                    <div>
                        <label for="pending_docs" class="block mb-2 text-sm font-medium text-gray-900">
                            Documentos Pendientes
                        </label>
                        <textarea id="pending_docs" 
                                  name="pending_docs" 
                                  rows="3"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('pending_docs', $registration->pending_docs) }}</textarea>
                    </div>

                    <div>
                        <label for="observations" class="block mb-2 text-sm font-medium text-gray-900">
                            Observaciones
                        </label>
                        <textarea id="observations" 
                                  name="observations" 
                                  rows="4"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('observations', $registration->observations) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sección 5: Google Drive -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">5.</span> Documentos en Google Drive
                </h3>
                <div>
                    <label for="drive_folder_url" class="block mb-2 text-sm font-medium text-gray-900">
                        URL de Carpeta en Drive
                    </label>
                    <input type="url" 
                           id="drive_folder_url" 
                           name="drive_folder_url" 
                           value="{{ old('drive_folder_url', $registration->drive_folder_url) }}"
                           placeholder="https://drive.google.com/drive/folders/..."
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                    <p class="mt-1 text-xs text-gray-500">
                        La carpeta se creará automáticamente en: /RAMS/{Cliente}/{Expediente}/
                    </p>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.registrations.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-save mr-2"></i> Actualizar Expediente
                </button>
            </div>
        </form>
    </div>
@endsection
