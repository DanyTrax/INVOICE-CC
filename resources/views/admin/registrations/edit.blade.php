@extends('layouts.admin-flowbite')

@section('title', 'Editar solicitud - RAMS')

@section('page-title', 'Editar solicitud')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Solicitudes</a>
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
        <form action="{{ route('admin.registrations.update', $registration) }}" method="POST" enctype="multipart/form-data">
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
                            Cliente <span class="text-gray-500">(Opcional)</span>
                        </label>
                        <div class="relative" x-data="{ 
                            open: false, 
                            search: '{{ old('company_search', $registration->company ? $registration->company->name . ' - ' . ($registration->company->nit_rut ?: 'Sin NIT') . ($registration->company->email ? ' (' . $registration->company->email . ')' : '') : '') }}', 
                            companies: [], 
                            selectedCompany: {{ old('company_id', $registration->company_id) ? json_encode(['id' => old('company_id', $registration->company_id), 'text' => old('company_search', $registration->company ? $registration->company->name . ' - ' . ($registration->company->nit_rut ?: 'Sin NIT') . ($registration->company->email ? ' (' . $registration->company->email . ')' : '') : '')]) : 'null' }},
                            loading: false
                        }">
                            <input type="hidden" name="company_id" :value="selectedCompany ? selectedCompany.id : ''">
                            <input type="text" 
                                   x-model="search"
                                   @input="
                                       if (search.length >= 2) {
                                           loading = true;
                                           fetch('/admin/api/companies/search?q=' + encodeURIComponent(search))
                                               .then(res => res.json())
                                               .then(data => { companies = data; loading = false; open = true; })
                                               .catch(() => { loading = false; });
                                       } else {
                                           companies = [];
                                           open = false;
                                       }
                                   "
                                   @focus="if (companies.length > 0) open = true"
                                   @click.away="open = false"
                                   placeholder="Buscar por nombre, correo o NIT..."
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('company_id') border-red-500 @enderror">
                            
                            <!-- Loading indicator -->
                            <div x-show="loading" class="absolute right-3 top-2.5">
                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            </div>
                            
                            <!-- Dropdown de resultados -->
                            <div x-show="open && companies.length > 0" 
                                 x-cloak
                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                <template x-for="company in companies" :key="company.id">
                                    <div @click="
                                        selectedCompany = company;
                                        search = company.text;
                                        open = false;
                                    " 
                                    class="px-4 py-2 hover:bg-teal-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium text-gray-900" x-text="company.name"></div>
                                        <div class="text-xs text-gray-500" x-text="(company.nit_rut ? 'NIT: ' + company.nit_rut : '') + (company.email ? ' | ' + company.email : '')"></div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Mensaje cuando no hay resultados -->
                            <div x-show="open && !loading && companies.length === 0 && search.length >= 2" 
                                 x-cloak
                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-sm text-gray-500 text-center">
                                No se encontraron clientes
                            </div>
                            
                            <!-- Cliente seleccionado -->
                            <div x-show="selectedCompany" class="mt-2 flex items-center space-x-2 text-sm">
                                <span class="text-teal-600">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <span class="text-gray-700" x-text="selectedCompany ? selectedCompany.text : ''"></span>
                                <button type="button" @click="selectedCompany = null; search = ''" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
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
                            <option value="tramite" {{ old('status', $registration->status) === 'tramite' ? 'selected' : '' }}>En Trámite</option>
                            <option value="vigente" {{ old('status', $registration->status) === 'vigente' ? 'selected' : '' }}>Vigente</option>
                            <option value="requerimiento" {{ old('status', $registration->status) === 'requerimiento' ? 'selected' : '' }}>Requerimiento</option>
                            <option value="vencido" {{ old('status', $registration->status) === 'vencido' ? 'selected' : '' }}>Vencido</option>
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

            <!-- Sección 5: Documentos Existentes -->
            @if($registration->documents && $registration->documents->count() > 0)
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">5.</span> Documentos Existentes
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($registration->documents as $document)
                    @php
                        $hasDrive = !empty($document->drive_id);
                        $hasLocal = !$hasDrive && $document->file_path && 
                                    !str_starts_with($document->file_path, 'temp/') &&
                                    str_starts_with($document->file_path, 'registration-documents/');
                    @endphp
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-3 hover:bg-gray-100">
                        <div class="flex items-center space-x-3 min-w-0 flex-1">
                            <i class="fas fa-file text-teal-600 flex-shrink-0 text-lg"></i>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" title="{{ $document->file_name }}">{{ $document->file_name }}</p>
                                <p class="text-xs text-gray-500">
                                    Subido: {{ $document->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($hasDrive || $hasLocal)
                                <a href="{{ route('admin.registrations.documents.view', [$registration, $document]) }}" 
                                   target="_blank"
                                   class="p-2.5 text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm"
                                   title="Ver documento">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.registrations.documents.download', [$registration, $document]) }}" 
                                   class="p-2.5 text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm"
                                   title="Descargar documento">
                                    <i class="fas fa-download"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sección 6: Agregar Documentos -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">{{ $registration->documents && $registration->documents->count() > 0 ? '6' : '5' }}.</span> Agregar Documentos
                </h3>
                <div>
                    <label for="documents" class="block mb-2 text-sm font-medium text-gray-900">
                        Subir Documentos <span class="text-gray-500">(Opcional)</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="documents" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Clic para seleccionar</span> o arrastra archivos aquí
                                </p>
                                <p class="text-xs text-gray-500">PDF, DOC, DOCX, XLS, XLSX, imágenes (Máx. 10MB por archivo)</p>
                            </div>
                            <input type="file" 
                                   id="documents" 
                                   name="documents[]" 
                                   multiple
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                   class="hidden"
                                   onchange="updateFileList(this)">
                        </label>
                    </div>
                    <div id="file-list" class="mt-4 space-y-2"></div>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Los documentos se suben a Google Drive y se pueden ver y descargar desde la plataforma.
                    </p>
                </div>
            </div>

            @if($registration->drive_folder_url)
            <!-- Sección legacy: Google Drive (solo si ya existía carpeta) -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <span class="text-teal-600">{{ $registration->documents && $registration->documents->count() > 0 ? '7' : '6' }}.</span> Carpeta en Drive (legacy)
                </h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <a href="{{ $registration->drive_folder_url }}" 
                       target="_blank"
                       class="text-sm text-teal-700 hover:underline break-all">
                        <i class="fas fa-folder-open mr-1"></i> {{ $registration->drive_folder_url }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.registrations.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-save mr-2"></i> Actualizar solicitud
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function updateFileList(input) {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-3';
                    fileItem.innerHTML = `
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-file text-teal-600"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">${file.name}</p>
                                <p class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                            </div>
                        </div>
                        <button type="button" onclick="removeFile(${index})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    fileList.appendChild(fileItem);
                });
            }
        }

        function removeFile(index) {
            const input = document.getElementById('documents');
            const dt = new DataTransfer();
            const files = Array.from(input.files);
            
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            input.files = dt.files;
            updateFileList(input);
        }
    </script>
    @endpush
@endsection
