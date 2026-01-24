@extends('layouts.admin-flowbite')

@section('title', 'Configuración - RAMS')

@section('page-title', 'Configuración del Sistema')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Configuración</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                @php
                    $activeSection = $activeSection ?? 'agency';
                @endphp
                <a href="{{ route('admin.settings.section', 'agency') }}" 
                        id="tab-agency"
                        class="tab-link px-6 py-3 text-sm font-medium border-b-2 {{ $activeSection === 'agency' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-building mr-2"></i> Datos de la Empresa
                </a>
                <a href="{{ route('admin.settings.section', 'drive') }}" 
                        id="tab-drive"
                        class="tab-link px-6 py-3 text-sm font-medium border-b-2 {{ $activeSection === 'drive' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-cloud mr-2"></i> Conexión Google Drive
                </a>
                <a href="{{ route('admin.settings.section', 'mail') }}" 
                        id="tab-mail"
                        class="tab-link px-6 py-3 text-sm font-medium border-b-2 {{ $activeSection === 'mail' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-envelope mr-2"></i> Correo & SMTP
                </a>
                <a href="{{ route('admin.settings.section', 'templates') }}" 
                        id="tab-templates"
                        class="tab-link px-6 py-3 text-sm font-medium border-b-2 {{ $activeSection === 'templates' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-file-alt mr-2"></i> Plantillas de Email
                </a>
                <a href="{{ route('admin.settings.section', 'history') }}" 
                        id="tab-history"
                        class="tab-link px-6 py-3 text-sm font-medium border-b-2 {{ $activeSection === 'history' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-history mr-2"></i> Histórico y Pruebas
                </a>
                @if(auth()->user()->hasRole('super_admin'))
                <a href="{{ route('admin.settings.section', 'system') }}" 
                        id="tab-system"
                        class="tab-link px-6 py-3 text-sm font-medium border-b-2 {{ $activeSection === 'system' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-cog mr-2"></i> Sistema
                </a>
                @endif
            </nav>
        </div>

        <!-- Tab 1: Datos de la Empresa -->
        <div id="panel-agency" class="tab-panel {{ $activeSection === 'agency' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-building text-teal-600 mr-2"></i>
                    Información de la Empresa (White Label)
                </h3>
                <p class="text-sm text-gray-600 mb-6">
                    Configura los datos de tu empresa que aparecerán en el sistema.
                </p>

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="agency">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre de la Empresa -->
                        <div>
                            <label for="agency_name" class="block mb-2 text-sm font-medium text-gray-900">
                                Nombre de la Empresa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="agency_name" 
                                   name="agency_name" 
                                   value="{{ old('agency_name', $settings->agency_name ?? 'RAMS') }}"
                                   required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- NIT -->
                        <div>
                            <label for="agency_nit" class="block mb-2 text-sm font-medium text-gray-900">
                                NIT
                            </label>
                            <input type="text" 
                                   id="agency_nit" 
                                   name="agency_nit" 
                                   value="{{ old('agency_nit', $settings->agency_nit ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="agency_address" class="block mb-2 text-sm font-medium text-gray-900">
                                Dirección
                            </label>
                            <input type="text" 
                                   id="agency_address" 
                                   name="agency_address" 
                                   value="{{ old('agency_address', $settings->agency_address ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="agency_phone" class="block mb-2 text-sm font-medium text-gray-900">
                                Teléfono
                            </label>
                            <input type="text" 
                                   id="agency_phone" 
                                   name="agency_phone" 
                                   value="{{ old('agency_phone', $settings->agency_phone ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="agency_email" class="block mb-2 text-sm font-medium text-gray-900">
                                Email
                            </label>
                            <input type="email" 
                                   id="agency_email" 
                                   name="agency_email" 
                                   value="{{ old('agency_email', $settings->agency_email ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Sitio Web -->
                        <div>
                            <label for="agency_website" class="block mb-2 text-sm font-medium text-gray-900">
                                Sitio Web
                            </label>
                            <input type="url" 
                                   id="agency_website" 
                                   name="agency_website" 
                                   value="{{ old('agency_website', $settings->agency_website ?? '') }}"
                                   placeholder="https://..."
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Logo -->
                        <div class="md:col-span-2">
                            <label for="agency_logo" class="block mb-2 text-sm font-medium text-gray-900">
                                Logo de la Empresa
                            </label>
                            
                            <!-- Vista previa del logo actual -->
                            @php
                                $logoPath = $settings->agency_logo ?? null;
                                $logoExists = $logoPath && file_exists(public_path($logoPath));
                            @endphp
                            @if($logoExists)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Logo actual:</p>
                                    <img src="{{ asset($logoPath) }}" 
                                         alt="Logo actual" 
                                         class="h-16 w-auto object-contain border border-gray-200 rounded-lg p-2 bg-white">
                                </div>
                            @endif
                            
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" 
                                           id="agency_logo" 
                                           name="agency_logo" 
                                           accept="image/*"
                                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                    <p class="mt-1 text-xs text-gray-500">
                                        Formatos permitidos: JPG, PNG, GIF, SVG. Tamaño máximo: 2MB
                                    </p>
                                </div>
                            </div>
                            
                            @if($logoExists)
                                <div class="mt-2">
                                    <label class="flex items-center space-x-2 text-sm text-gray-600">
                                        <input type="checkbox" 
                                               name="remove_logo" 
                                               value="1"
                                               class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <span>Eliminar logo actual</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                            <i class="fas fa-save mr-2"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 2: Google Drive -->
        <div id="panel-drive" class="tab-panel {{ $activeSection === 'drive' ? '' : 'hidden' }}">
            <div class="space-y-6">
                <!-- Submenú de Google Drive -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex space-x-4 border-b border-gray-200">
                        <button onclick="switchDriveTab('config')" 
                                id="drive-tab-config"
                                class="drive-subtab px-6 py-3 text-sm font-medium border-b-2 border-teal-600 text-teal-600 focus:outline-none">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </button>
                        <button onclick="switchDriveTab('history')" 
                                id="drive-tab-history"
                                class="drive-subtab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="fas fa-history mr-2"></i> Historial de Operaciones
                        </button>
                    </div>
                </div>

                <!-- Panel de Configuración -->
                <div id="drive-panel-config" class="drive-subpanel">
                    <!-- Formulario de Configuración -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-cloud text-teal-600 mr-2"></i>
                            Configuración de Google Drive
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Configura la integración con Google Drive para almacenar documentos del sistema.
                        </p>

                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="section" value="drive">

                            <div class="space-y-6">
                                <!-- JSON de Service Account -->
                                <div>
                                    <label for="drive_service_account_json" class="block mb-2 text-sm font-medium text-gray-900">
                                        JSON de Service Account <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="drive_service_account_json" 
                                              name="drive_service_account_json" 
                                              rows="12"
                                              placeholder='{"type": "service_account", "project_id": "...", "private_key_id": "...", "private_key": "...", "client_email": "...", "client_id": "...", "auth_uri": "...", "token_uri": "...", "auth_provider_x509_cert_url": "...", "client_x509_cert_url": "..."}'
                                              required
                                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono text-xs">{{ old('drive_service_account_json', $settings->drive_service_account_json ?? '') }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Pega aquí el contenido completo del archivo JSON descargado de Google Cloud Console.
                                    </p>
                                    @if($settings->drive_service_account_json)
                                        @php
                                            try {
                                                $jsonData = json_decode($settings->drive_service_account_json, true);
                                                $serviceEmail = $jsonData['client_email'] ?? null;
                                            } catch (\Exception $e) {
                                                $serviceEmail = null;
                                            }
                                        @endphp
                                        @if($serviceEmail)
                                            <div class="mt-2 bg-green-50 border border-green-200 rounded p-3">
                                                <p class="text-xs text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    <strong>Service Account configurado:</strong> {{ $serviceEmail }}
                                                </p>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <!-- ID de Carpeta de Drive (Opcional) -->
                                <div>
                                    <label for="drive_folder_id" class="block mb-2 text-sm font-medium text-gray-900">
                                        ID de Carpeta de Drive (Opcional)
                                    </label>
                                    <input type="text" 
                                           id="drive_folder_id" 
                                           name="drive_folder_id" 
                                           value="{{ old('drive_folder_id', $settings->drive_folder_id ?? '') }}"
                                           placeholder="1a2b3c4d5e6f7g8h9i0j"
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                    <p class="mt-2 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Si quieres que todos los documentos se suban a una carpeta específica, ingresa el ID de la carpeta. 
                                        Puedes obtenerlo desde la URL de la carpeta en Google Drive (la parte después de <code class="bg-gray-100 px-1 rounded">folders/</code>).
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" 
                                        onclick="testDriveConnection()"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-vial mr-2"></i> Probar Conexión
                                </button>
                                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                                    <i class="fas fa-save mr-2"></i> Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Instructivo (Colapsable) -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                        <button type="button" 
                                onclick="toggleDriveInstructions()"
                                class="w-full flex items-center justify-between text-left focus:outline-none">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-book text-teal-600 mr-2"></i>
                                Instructivo de Configuración
                            </h3>
                            <i id="drive-instructions-icon" class="fas fa-chevron-down text-gray-500 transition-transform"></i>
                        </button>
                        
                        <div id="drive-instructions-content" class="mt-4 space-y-4 hidden">
                        <!-- Paso 1 -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold mr-4">
                                    1
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2">Crear Proyecto en Google Cloud</h4>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-4">
                                        <li>Ve a <a href="https://console.cloud.google.com/" target="_blank" class="text-teal-600 hover:underline">Google Cloud Console</a></li>
                                        <li>Inicia sesión con tu cuenta de Google</li>
                                        <li>Haz clic en el selector de proyectos (arriba a la izquierda)</li>
                                        <li>Haz clic en <strong>"Nuevo Proyecto"</strong></li>
                                        <li>Ingresa un nombre (ej: "RAMS Drive Integration")</li>
                                        <li>Haz clic en <strong>"Crear"</strong></li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2 -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold mr-4">
                                    2
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2">Habilitar API de Google Drive</h4>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-4">
                                        <li>En el menú lateral, ve a <strong>"APIs y servicios"</strong> → <strong>"Biblioteca"</strong></li>
                                        <li>Busca <strong>"Google Drive API"</strong></li>
                                        <li>Haz clic en el resultado</li>
                                        <li>Haz clic en el botón <strong>"Habilitar"</strong></li>
                                        <li>Espera a que se habilite (puede tardar unos segundos)</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3 -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold mr-4">
                                    3
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2">Crear Service Account</h4>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-4">
                                        <li>Ve a <strong>"APIs y servicios"</strong> → <strong>"Credenciales"</strong></li>
                                        <li>Haz clic en <strong>"Crear credenciales"</strong> → <strong>"Cuenta de servicio"</strong></li>
                                        <li>Completa los campos:
                                            <ul class="list-disc list-inside ml-6 mt-1">
                                                <li><strong>Nombre:</strong> "RAMS Drive Service"</li>
                                                <li><strong>Descripción:</strong> "Service account para integración con Google Drive en RAMS"</li>
                                            </ul>
                                        </li>
                                        <li>Haz clic en <strong>"Crear y continuar"</strong></li>
                                        <li>En <strong>"Conceder a esta cuenta de servicio acceso al proyecto"</strong>:
                                            <ul class="list-disc list-inside ml-6 mt-1">
                                                <li>Selecciona el rol: <strong>"Editor"</strong> o <strong>"Propietario"</strong></li>
                                            </ul>
                                        </li>
                                        <li>Haz clic en <strong>"Continuar"</strong> y luego en <strong>"Listo"</strong></li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 4 -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold mr-4">
                                    4
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2">Crear y Descargar Clave JSON</h4>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-4">
                                        <li>En la lista de cuentas de servicio, haz clic en la que acabas de crear</li>
                                        <li>Ve a la pestaña <strong>"Claves"</strong></li>
                                        <li>Haz clic en <strong>"Agregar clave"</strong> → <strong>"Crear nueva clave"</strong></li>
                                        <li>Selecciona el formato <strong>"JSON"</strong></li>
                                        <li>Haz clic en <strong>"Crear"</strong></li>
                                        <li>Se descargará automáticamente un archivo JSON (guárdalo en un lugar seguro)</li>
                                    </ol>
                                    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded p-3">
                                        <p class="text-xs text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            <strong>Importante:</strong> Este archivo JSON contiene credenciales sensibles. No lo compartas públicamente ni lo subas a repositorios públicos.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 5 -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold mr-4">
                                    5
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2">Compartir Carpeta en Google Drive</h4>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-4">
                                        <li>Abre <a href="https://drive.google.com" target="_blank" class="text-teal-600 hover:underline">Google Drive</a></li>
                                        <li>Crea una carpeta nueva (ej: "RAMS Documentos") o selecciona una existente</li>
                                        <li>Haz clic derecho en la carpeta → <strong>"Compartir"</strong></li>
                                        <li>En el campo de búsqueda, pega el <strong>email de la Service Account</strong>
                                            <ul class="list-disc list-inside ml-6 mt-1">
                                                <li>Este email está en el archivo JSON descargado, en el campo <code class="bg-gray-100 px-1 rounded">"client_email"</code></li>
                                                <li>Ejemplo: <code class="bg-gray-100 px-1 rounded">rams-drive-service@tu-proyecto.iam.gserviceaccount.com</code></li>
                                            </ul>
                                        </li>
                                        <li>Selecciona el email de la lista de sugerencias</li>
                                        <li>Cambia el permiso a <strong>"Editor"</strong> (o "Administrador" si necesitas más control)</li>
                                        <li>Haz clic en <strong>"Enviar"</strong> o <strong>"Listo"</strong></li>
                                    </ol>
                                    <div class="mt-3 bg-blue-50 border border-blue-200 rounded p-3">
                                        <p class="text-xs text-blue-800">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Nota:</strong> No necesitas enviar una notificación por correo. Solo comparte la carpeta con el email de la Service Account.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 6 -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-teal-50">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center font-bold mr-4">
                                    6
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2">Configurar en RAMS</h4>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 ml-4">
                                        <li>Abre el archivo JSON descargado con un editor de texto</li>
                                        <li>Copia <strong>TODO</strong> el contenido del archivo (desde <code class="bg-gray-100 px-1 rounded">{</code> hasta <code class="bg-gray-100 px-1 rounded">}</code>)</li>
                                        <li>Pega el contenido en el campo <strong>"JSON de Service Account"</strong> más abajo</li>
                                        <li>Si tienes el ID de la carpeta de Drive, pégalo en el campo opcional</li>
                                        <li>Haz clic en <strong>"Guardar Configuración"</strong></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Historial -->
                <div id="drive-panel-history" class="drive-subpanel hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-history text-teal-600 mr-2"></i>
                                Historial de Operaciones de Google Drive
                            </h3>
                            <button type="button" 
                                    onclick="loadDriveOperationsLog()"
                                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                <i class="fas fa-sync-alt mr-1"></i> Actualizar
                            </button>
                        </div>

                        <div id="drive-operations-container" class="space-y-4">
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Cargando historial...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Correo & SMTP -->
        <div id="panel-mail" class="tab-panel {{ $activeSection === 'mail' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-envelope text-teal-600 mr-2"></i>
                    Configuración de Correo
                </h3>
                <p class="text-sm text-gray-600 mb-6">
                    Selecciona el proveedor de correo y configura los parámetros correspondientes.
                </p>

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="mail">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Proveedor de Correo -->
                        <div class="md:col-span-2">
                            <label for="mail_provider" class="block mb-2 text-sm font-medium text-gray-900">
                                Proveedor de Correo <span class="text-red-500">*</span>
                            </label>
                            <select id="mail_provider" 
                                    name="mail_provider" 
                                    required
                                    onchange="toggleProviderFields()"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <option value="smtp" {{ old('mail_provider', $settings->mail_provider ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Gmail, Outlook, etc.)</option>
                                <option value="zoho" {{ old('mail_provider', $settings->mail_provider ?? 'smtp') === 'zoho' ? 'selected' : '' }}>Zoho Mail API</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Selecciona el método de envío de correos</p>
                        </div>

                        <!-- Campos SMTP -->
                        <div id="smtp-fields" class="md:col-span-2 {{ old('mail_provider', $settings->mail_provider ?? 'smtp') === 'zoho' ? 'hidden' : '' }}">
                            <div class="border-t border-gray-200 pt-6 mb-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Configuración SMTP</h4>
                            </div>
                            
                            <!-- Mailer -->
                            <div>
                                <label for="mail_mailer" class="block mb-2 text-sm font-medium text-gray-900">
                                    Mailer <span class="text-red-500">*</span>
                                </label>
                                <select id="mail_mailer" 
                                        name="mail_mailer" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                    <option value="smtp" {{ old('mail_mailer', $settings->mail_mailer ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ old('mail_mailer', $settings->mail_mailer ?? 'smtp') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                </select>
                            </div>

                        <!-- Host -->
                        <div>
                            <label for="mail_host" class="block mb-2 text-sm font-medium text-gray-900">
                                Host SMTP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="mail_host" 
                                   name="mail_host" 
                                   value="{{ old('mail_host', $settings->mail_host ?? 'smtp.gmail.com') }}"
                                   required
                                   placeholder="smtp.gmail.com"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Puerto -->
                        <div>
                            <label for="mail_port" class="block mb-2 text-sm font-medium text-gray-900">
                                Puerto <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="mail_port" 
                                   name="mail_port" 
                                   value="{{ old('mail_port', $settings->mail_port ?? 587) }}"
                                   required
                                   placeholder="587"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="mail_username" class="block mb-2 text-sm font-medium text-gray-900">
                                Usuario
                            </label>
                            <input type="text" 
                                   id="mail_username" 
                                   name="mail_username" 
                                   value="{{ old('mail_username', $settings->mail_username ?? '') }}"
                                   placeholder="tu@email.com"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="mail_password" class="block mb-2 text-sm font-medium text-gray-900">
                                Contraseña
                            </label>
                            <input type="password" 
                                   id="mail_password" 
                                   name="mail_password" 
                                   value="{{ old('mail_password', $settings->mail_password ?? '') }}"
                                   placeholder="••••••••"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- Encryption -->
                        <div>
                            <label for="mail_encryption" class="block mb-2 text-sm font-medium text-gray-900">
                                Encriptación
                            </label>
                            <select id="mail_encryption" 
                                    name="mail_encryption" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <option value="">Ninguna</option>
                                <option value="tls" {{ old('mail_encryption', $settings->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', $settings->mail_encryption ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>

                        <!-- From Address -->
                        <div>
                            <label for="mail_from_address" class="block mb-2 text-sm font-medium text-gray-900">
                                Email Remitente <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="mail_from_address" 
                                   name="mail_from_address" 
                                   value="{{ old('mail_from_address', $settings->mail_from_address ?? 'noreply@rams.com') }}"
                                   required
                                   placeholder="noreply@tudominio.com"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>

                        <!-- From Name -->
                        <div>
                            <label for="mail_from_name" class="block mb-2 text-sm font-medium text-gray-900">
                                Nombre Remitente <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="mail_from_name" 
                                   name="mail_from_name" 
                                   value="{{ old('mail_from_name', $settings->mail_from_name ?? 'RAMS Sistema') }}"
                                   placeholder="RAMS Sistema"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>
                        </div>

                        <!-- Campos Zoho Mail API -->
                        <div id="zoho-fields" class="md:col-span-2 {{ old('mail_provider', $settings->mail_provider ?? 'smtp') === 'smtp' ? 'hidden' : '' }}">
                            <div class="border-t border-gray-200 pt-6 mb-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Configuración Zoho Mail API</h4>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-blue-800 mb-2">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Instrucciones completas:</strong> Consulta el archivo <code class="bg-blue-100 px-2 py-1 rounded">INSTRUCTIVO_ZOHO_MAIL.md</code> en la raíz del proyecto para una guía paso a paso.
                                    </p>
                                    <p class="text-sm text-blue-700">
                                        <strong>Pasos rápidos:</strong>
                                    </p>
                                    <ol class="text-sm text-blue-700 list-decimal list-inside ml-2 space-y-1">
                                        <li>Crear aplicación en <a href="https://api-console.zoho.com/" target="_blank" class="underline font-semibold">Zoho API Console</a></li>
                                        <li>Configurar Redirect URI: <code class="bg-blue-100 px-1 rounded">{{ route('admin.settings.zoho.callback') }}</code></li>
                                        <li>Obtener Client ID y Client Secret</li>
                                        <li>Completar <strong>Email Remitente</strong>, Client ID y Client Secret aquí; <strong>guardar</strong>.</li>
                                        <li>Autorizar con Zoho (iniciar sesión en Zoho con el <strong>mismo correo</strong> que Email Remitente).</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Redirect URI Info -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-yellow-800 mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>URL de Confianza (Redirect URI):</strong>
                                </p>
                                <div class="bg-white border border-yellow-300 rounded p-3 mb-2">
                                    <code class="text-sm font-mono text-gray-900 break-all" id="redirect-uri-display">{{ route('admin.settings.zoho.callback') }}</code>
                                    <button type="button" 
                                            onclick="copyRedirectUri()"
                                            class="ml-2 text-teal-600 hover:text-teal-700 text-xs">
                                        <i class="fas fa-copy mr-1"></i>Copiar
                                    </button>
                                </div>
                                <p class="text-xs text-yellow-700 mb-2">
                                    ⚠️ <strong>IMPORTANTE:</strong> Esta URL debe estar configurada EXACTAMENTE igual (carácter por carácter) en Zoho API Console.
                                </p>
                                <div class="bg-white border border-yellow-300 rounded p-3 mt-2">
                                    <p class="text-xs text-yellow-800 font-semibold mb-1">Pasos para configurar en Zoho:</p>
                                    <ol class="text-xs text-yellow-700 list-decimal list-inside space-y-1 ml-2">
                                        <li>Ve a <a href="https://api-console.zoho.com/" target="_blank" class="underline font-semibold">Zoho API Console</a></li>
                                        <li>Selecciona tu aplicación "Regulatory"</li>
                                        <li>Ve a la pestaña "Settings" o "Client Details"</li>
                                        <li>En "Authorized Redirect URIs", haz clic en el botón "+" o "Add"</li>
                                        <li>Copia y pega EXACTAMENTE esta URL: <code class="bg-yellow-100 px-1 rounded font-mono text-xs">{{ route('admin.settings.zoho.callback') }}</code></li>
                                        <li>Guarda los cambios en Zoho</li>
                                        <li>Vuelve aquí y haz clic en el botón de autorización nuevamente</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Client ID -->
                            <div>
                                <label for="zoho_client_id" class="block mb-2 text-sm font-medium text-gray-900">
                                    Client ID <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="zoho_client_id" 
                                       name="zoho_client_id" 
                                       value="{{ old('zoho_client_id', $settings->zoho_client_id ?? '') }}"
                                       placeholder="1000.XXXXXXXXXXXX"
                                       oninput="checkZohoCredentials()"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono text-sm">
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <strong>Dónde encontrarlo:</strong> Zoho API Console → Tu aplicación → Client ID
                                </p>
                            </div>

                            <!-- Client Secret -->
                            <div>
                                <label for="zoho_client_secret" class="block mb-2 text-sm font-medium text-gray-900">
                                    Client Secret <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="zoho_client_secret" 
                                           name="zoho_client_secret" 
                                           value="{{ old('zoho_client_secret', $settings->zoho_client_secret ?? '') }}"
                                           placeholder="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
                                           oninput="checkZohoCredentials()"
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono text-sm">
                                    <button type="button" 
                                            onclick="togglePasswordVisibility('zoho_client_secret')"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-eye" id="eye-zoho_client_secret"></i>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <strong>Dónde encontrarlo:</strong> Zoho API Console → Tu aplicación → Client Secret
                                </p>
                            </div>

                            <!-- Email Remitente (DEBE ir antes de autorizar) -->
                            <div>
                                <label for="zoho_from_email" class="block mb-2 text-sm font-medium text-gray-900">
                                    Email Remitente <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="zoho_from_email" 
                                       name="zoho_from_email" 
                                       value="{{ old('zoho_from_email', $settings->zoho_from_email ?? '') }}"
                                       placeholder="noreply@tudominio.com"
                                       oninput="checkZohoCredentials()"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Email verificado en tu cuenta de Zoho Mail. Este será el remitente de todos los correos.
                                </p>
                                <p class="mt-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                                    <strong>Crítico:</strong> Debes <strong>guardar los cambios</strong> y luego autorizar con Zoho iniciando sesión con <strong>este mismo correo</strong>. El token se vincula a la cuenta que autorice.
                                </p>
                            </div>

                            <!-- Refresh Token -->
                            <div>
                                <label for="zoho_refresh_token" class="block mb-2 text-sm font-medium text-gray-900">
                                    Refresh Token <span class="text-red-500">*</span>
                                </label>
                                
                                <!-- Botón de Autorización Automática (PRIMERO) -->
                                <div class="mb-4">
                                    <div id="zoho-warning-box" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                                        <p class="text-sm text-yellow-800 mb-2">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <strong>Paso 1:</strong> Completa <strong>Email Remitente</strong>, Client ID y Client Secret arriba. <strong>Guarda los cambios</strong>. Luego usa el botón de abajo.
                                        </p>
                                        <p class="text-sm text-yellow-800">
                                            <strong>Paso 2:</strong> Al autorizar, inicia sesión en Zoho con el <strong>mismo correo</strong> que configuraste como Email Remitente. El token se vincula a esa cuenta; si autorizas con otra, los envíos fallarán.
                                        </p>
                                    </div>
                                    
                                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-3 mb-3" id="zoho-account-hint">
                                        <p class="text-sm text-red-800">
                                            <i class="fas fa-user-shield mr-2"></i>
                                            <strong>Debes autorizar con la cuenta:</strong> <span id="zoho-account-hint-email" class="font-mono">—</span>
                                        </p>
                                        <p class="text-xs text-red-700 mt-1">Cierra sesión en Zoho o usa ventana privada si sueles entrar con otro correo.</p>
                                    </div>
                                    
                                    <div class="bg-teal-50 border-2 border-teal-300 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-teal-900 mb-1">
                                                    <i class="fas fa-magic mr-2"></i>
                                                    Generación Automática de Refresh Token
                                                </h4>
                                                <p class="text-xs text-teal-700">
                                                    Redirige a Zoho para autorizar y obtiene el Refresh Token automáticamente. Usa la misma cuenta que el Email Remitente.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('admin.settings.zoho.authorize') }}" 
                                           id="zoho-authorize-btn"
                                           onclick="return validateZohoCredentials(event)"
                                           class="inline-flex items-center justify-center w-full px-6 py-3 bg-teal-600 text-white text-base font-semibold rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300 transition-all shadow-lg hover:shadow-xl {{ (!empty($settings->zoho_client_id) && !empty($settings->zoho_client_secret) && !empty($settings->zoho_from_email)) ? '' : 'opacity-50 cursor-not-allowed pointer-events-none' }}">
                                            <i class="fas fa-check-circle mr-2 text-lg"></i>
                                            <span>Autorizar con Zoho y Generar Refresh Token Automáticamente</span>
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                        <p class="text-xs text-teal-600 mt-2 text-center">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Redirect URI en Zoho API Console configurada y cambios guardados antes de hacer clic.
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Campo de Refresh Token (se llena automáticamente) -->
                                <div class="mb-2">
                                    <div class="relative">
                                        <textarea id="zoho_refresh_token" 
                                                  name="zoho_refresh_token" 
                                                  rows="3"
                                                  placeholder="1000.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX (se llenará automáticamente después de autorizar)"
                                                  class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 p-2.5 font-mono text-xs pr-20">{{ old('zoho_refresh_token', $settings->zoho_refresh_token ?? '') }}</textarea>
                                        @if(!empty($settings->zoho_refresh_token))
                                        <button type="button" 
                                                onclick="clearRefreshToken()"
                                                class="absolute top-2 right-2 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                                                title="Limpiar Refresh Token para regenerarlo">
                                            <i class="fas fa-trash-alt mr-1"></i>Limpiar
                                        </button>
                                        @endif
                                    </div>
                                    @if(!empty($settings->zoho_refresh_token))
                                        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800">
                                            <p class="text-green-600 mb-1">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Refresh Token configurado correctamente
                                            </p>
                                            <p class="text-blue-700 mt-1">
                                                <strong>⚠️ Si recibes el error "URL_RULE_NOT_CONFIGURED":</strong> Haz clic en "Limpiar" arriba, guarda los cambios, y luego usa el botón "Autorizar con Zoho" para regenerar el token.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Información del Proceso -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                    <p class="text-xs text-blue-800 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>¿Cómo funciona el botón?</strong>
                                    </p>
                                    <ol class="text-xs text-blue-700 list-decimal list-inside space-y-1 ml-2">
                                        <li>La plataforma envía tu Client ID a Zoho</li>
                                        <li>Zoho te muestra una pantalla de autorización</li>
                                        <li>Autorizas la aplicación en Zoho</li>
                                        <li>Zoho confirma y redirige de vuelta con un código</li>
                                        <li>La plataforma intercambia el código por Refresh Token automáticamente</li>
                                        <li>El Refresh Token se guarda automáticamente en el campo de arriba</li>
                                    </ol>
                                </div>
                                
                                <!-- Método Manual (Alternativo) -->
                                <details class="mt-4">
                                    <summary class="text-xs text-gray-600 cursor-pointer hover:text-gray-800">
                                        <i class="fas fa-chevron-down mr-1"></i>
                                        Ver método manual (alternativo)
                                    </summary>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mt-2">
                                        <p class="text-xs text-gray-700 mb-2">
                                            <strong>Si prefieres hacerlo manualmente:</strong>
                                        </p>
                                        <ol class="text-xs text-gray-600 list-decimal list-inside space-y-1 ml-2">
                                            <li>Construye la URL de autorización (ver instructivo)</li>
                                            <li>Autoriza la aplicación en el navegador</li>
                                            <li>Obtén el código de la URL de redirect</li>
                                            <li>Usa cURL o Postman para intercambiar el código por Refresh Token</li>
                                        </ol>
                                        <p class="text-xs text-gray-600 mt-2">
                                            <a href="https://www.zoho.com/mail/help/api/using-oauth.html" target="_blank" class="text-teal-600 hover:text-teal-700 underline font-semibold">
                                                <i class="fas fa-external-link-alt mr-1"></i>Ver documentación completa
                                            </a>
                                        </p>
                                    </div>
                                </details>
                            </div>

                            <!-- Verificación -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                                <p class="text-sm text-green-800 mb-2">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Verificación de Configuración:</strong>
                                </p>
                                <ul class="text-xs text-green-700 list-disc list-inside space-y-1 ml-2">
                                    <li>Client ID y Client Secret copiados correctamente desde Zoho API Console</li>
                                    <li>Redirect URI configurada en Zoho API Console: <code class="bg-green-100 px-1 rounded">{{ route('admin.settings.zoho.callback') }}</code></li>
                                    <li>Refresh Token generado usando OAuth2 con <code class="bg-green-100 px-1 rounded">access_type=offline</code></li>
                                    <li>Email remitente verificado en tu cuenta de Zoho Mail</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                            <i class="fas fa-save mr-2"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 5: Histórico y Pruebas -->
        <div id="panel-history" class="tab-panel {{ $activeSection === 'history' ? '' : 'hidden' }}">
            <div class="space-y-6">
                <!-- Enviar Correo de Prueba -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-paper-plane text-teal-600 mr-2"></i>
                        Enviar Correo de Prueba
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Envía un correo de prueba para verificar que la configuración de correo esté funcionando correctamente.
                    </p>

                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="test_email">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Email Destinatario -->
                            <div class="md:col-span-2">
                                <label for="test_email_to" class="block mb-2 text-sm font-medium text-gray-900">
                                    Email Destinatario <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="test_email_to" 
                                       name="test_email_to" 
                                       value="{{ old('test_email_to', auth()->user()->email) }}"
                                       required
                                       placeholder="destinatario@example.com"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <p class="mt-1 text-xs text-gray-500">
                                    El correo se enviará a esta dirección usando el proveedor configurado ({{ $settings->mail_provider === 'zoho' ? 'Zoho Mail API' : 'SMTP' }})
                                </p>
                            </div>

                            <!-- Asunto -->
                            <div>
                                <label for="test_email_subject" class="block mb-2 text-sm font-medium text-gray-900">
                                    Asunto
                                </label>
                                <input type="text" 
                                       id="test_email_subject" 
                                       name="test_email_subject" 
                                       value="{{ old('test_email_subject', 'Correo de Prueba - RAMS') }}"
                                       placeholder="Correo de Prueba - RAMS"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            </div>

                            <!-- Cuerpo del Mensaje -->
                            <div class="md:col-span-2">
                                <label for="test_email_body" class="block mb-2 text-sm font-medium text-gray-900">
                                    Cuerpo del Mensaje (HTML)
                                </label>
                                <textarea id="test_email_body" 
                                          name="test_email_body" 
                                          rows="6"
                                          placeholder="<h1>Correo de Prueba</h1><p>Este es un correo de prueba...</p>"
                                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono text-xs">{{ old('test_email_body', '<h1>Correo de Prueba</h1><p>Este es un correo de prueba enviado desde el sistema RAMS.</p><p>Si recibes este correo, la configuración de correo está funcionando correctamente.</p>') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    Puedes usar HTML para formatear el correo
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300">
                                <i class="fas fa-paper-plane mr-2"></i> Enviar Correo de Prueba
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Histórico de Correos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history text-teal-600 mr-2"></i>
                        Histórico de Correos Enviados
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Registro de todos los correos enviados desde el sistema.
                    </p>

                    @if($emailLogs->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3">Fecha</th>
                                        <th class="px-4 py-3">Destinatario</th>
                                        <th class="px-4 py-3">Asunto</th>
                                        <th class="px-4 py-3">Proveedor</th>
                                        <th class="px-4 py-3">Estado</th>
                                        <th class="px-4 py-3">Tipo</th>
                                        <th class="px-4 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emailLogs as $log)
                                        <tr class="bg-white border-b hover:bg-gray-50" data-log-id="{{ $log->id }}">
                                            <td class="px-4 py-3">
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="font-medium text-gray-900">{{ $log->to }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-gray-900">{{ strlen($log->subject) > 50 ? substr($log->subject, 0, 50) . '...' : $log->subject }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $log->provider === 'zoho' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}" title="{{ strtoupper($log->provider) }}">
                                                    <i class="fas {{ $log->provider === 'zoho' ? 'fa-envelope-circle-check' : 'fa-server' }}"></i>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($log->status === 'sent')
                                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full" title="Enviado">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                @elseif($log->status === 'failed')
                                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full" title="Fallido">
                                                        <i class="fas fa-times-circle"></i>
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full" title="Pendiente">
                                                        <i class="fas fa-clock"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($log->is_test)
                                                    <span class="px-2 py-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full" title="Correo de Prueba">
                                                        <i class="fas fa-flask"></i>
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full" title="Correo Normal">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center space-x-2">
                                                    <button type="button" 
                                                            onclick="if(typeof window.showEmailDetails === 'function') { window.showEmailDetails({{ $log->id }}); } else { console.error('showEmailDetails no disponible'); }"
                                                            class="text-teal-600 hover:text-teal-700 p-2 rounded-lg hover:bg-teal-50 transition-colors"
                                                            title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            onclick="if(typeof window.deleteEmailLog === 'function') { window.deleteEmailLog({{ $log->id }}); } else { console.error('deleteEmailLog no disponible'); }"
                                                            class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-4">
                            {{ $emailLogs->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                            <p>No hay correos en el historial</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 4: Plantillas de Email -->
        <div id="panel-templates" class="tab-panel {{ $activeSection === 'templates' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-file-alt text-teal-600 mr-2"></i>
                    Plantillas de Email
                </h3>
                <p class="text-sm text-gray-600 mb-6">
                    Gestiona las plantillas de correo electrónico del sistema. Haz clic en "Editar" para modificar cada plantilla.
                </p>

                @if($emailTemplates->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p class="mb-2">No hay plantillas configuradas</p>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 max-w-md mx-auto">
                            <p class="text-sm text-yellow-800 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Plantillas no encontradas</strong>
                            </p>
                            <p class="text-xs text-yellow-700 mb-3">
                                Ejecuta este comando en tu servidor para restaurar las plantillas:
                            </p>
                            <code class="block bg-yellow-100 px-3 py-2 rounded text-xs text-yellow-900">
                                php artisan db:seed --class=EmailTemplateSeeder
                            </code>
                        </div>
                    </div>
                @else
                    <!-- Tabla de Plantillas -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nombre</th>
                                    <th scope="col" class="px-6 py-3">Tipo</th>
                                    <th scope="col" class="px-6 py-3">Asunto</th>
                                    <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($emailTemplates as $template)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $template->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $template->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600 truncate block max-w-md" title="{{ $template->subject }}">
                                                {{ Str::limit($template->subject, 60) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button type="button" 
                                                    onclick="openEditTemplateModal({{ $template->id }})"
                                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300">
                                                <i class="fas fa-edit mr-2"></i>
                                                Editar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        @if(auth()->user()->hasRole('super_admin'))
        <!-- Tab 6: Sistema (Solo Super Admin) -->
        <div id="panel-system" class="tab-panel {{ $activeSection === 'system' ? '' : 'hidden' }}">
            <div class="space-y-6">
                <!-- Git Pull -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-code-branch text-teal-600 mr-2"></i>
                        Actualización del Sistema (Git)
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Ejecuta <code class="bg-gray-100 px-2 py-1 rounded">git pull</code> para actualizar el código desde el repositorio remoto.
                    </p>

                    <div class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-0.5"></i>
                                <div class="text-sm text-yellow-800">
                                    <strong>Advertencia:</strong> Esta acción actualizará el código del sistema desde el repositorio remoto. 
                                    Asegúrate de tener una copia de seguridad antes de continuar.
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <button type="button" 
                                        onclick="executeGitPull('main')"
                                        class="px-6 py-2.5 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300">
                                    <i class="fas fa-download mr-2"></i> Git Pull (main)
                                </button>
                                <button type="button" 
                                        onclick="executeGitPull('origin main')"
                                        class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300">
                                    <i class="fas fa-download mr-2"></i> Git Pull (origin main)
                                </button>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Comandos Laravel</h4>
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" 
                                            onclick="executeArtisanCommand('php artisan view:clear')"
                                            class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-4 focus:outline-none focus:ring-purple-300">
                                        <i class="fas fa-broom mr-2"></i> View:Clear
                                    </button>
                                    <button type="button" 
                                            onclick="executeArtisanCommand('php artisan cache:clear')"
                                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300">
                                        <i class="fas fa-trash-alt mr-2"></i> Cache:Clear
                                    </button>
                                    <button type="button" 
                                            onclick="executeArtisanCommand('php artisan view:clear && php artisan cache:clear')"
                                            class="px-4 py-2 bg-pink-600 text-white text-sm font-medium rounded-lg hover:bg-pink-700 focus:ring-4 focus:outline-none focus:ring-pink-300">
                                        <i class="fas fa-sync mr-2"></i> View:Clear + Cache:Clear
                                    </button>
                                    <button type="button" 
                                            onclick="executeArtisanCommand('php artisan config:clear')"
                                            class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:ring-4 focus:outline-none focus:ring-yellow-300">
                                        <i class="fas fa-cog mr-2"></i> Config:Clear
                                    </button>
                                    <button type="button" 
                                            onclick="executeArtisanCommand('php artisan route:clear')"
                                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300">
                                        <i class="fas fa-route mr-2"></i> Route:Clear
                                    </button>
                                    <button type="button" 
                                            onclick="executeArtisanCommand('php artisan optimize:clear')"
                                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300">
                                        <i class="fas fa-broom mr-2"></i> Optimize:Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="git-pull-output" class="hidden mt-4 bg-gray-900 text-green-400 font-mono text-sm rounded-lg p-4 max-h-96 overflow-y-auto">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-white font-semibold">Salida del comando:</span>
                                <button type="button" onclick="document.getElementById('git-pull-output').classList.add('hidden')" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <pre id="git-pull-result" class="whitespace-pre-wrap"></pre>
                        </div>
                    </div>
                </div>

                <!-- Personalización del Sistema -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-paint-brush text-teal-600 mr-2"></i>
                        Personalización del Sistema
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Personaliza los textos que aparecen en el sistema.
                    </p>

                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="system">

                        <div class="space-y-6">
                            <!-- Texto del Footer -->
                            <div>
                                <label for="footer_text" class="block mb-2 text-sm font-medium text-gray-900">
                                    Texto del Footer <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="footer_text" 
                                       name="footer_text" 
                                       value="{{ old('footer_text', $settings->footer_text ?? 'RAMS - Regulatory Affairs Management System') }}"
                                       required
                                       placeholder="RAMS - Regulatory Affairs Management System"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <p class="mt-1 text-xs text-gray-500">
                                    Este texto aparecerá en el footer de todas las páginas del sistema.
                                </p>
                            </div>

                            <!-- Nombre del Sistema (para plantillas) -->
                            <div>
                                <label for="system_name" class="block mb-2 text-sm font-medium text-gray-900">
                                    Nombre del Sistema (para plantillas) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="system_name" 
                                       name="system_name" 
                                       value="{{ old('system_name', $settings->system_name ?? 'Sistema de Gestión Regulatoria') }}"
                                       required
                                       placeholder="Sistema de Gestión Regulatoria"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <p class="mt-1 text-xs text-gray-500">
                                    Este nombre aparecerá en las plantillas de correo electrónico donde se mencione "Sistema de Gestión Regulatoria".
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300">
                                <i class="fas fa-save mr-2"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal de Edición de Plantilla -->
        <div id="edit-template-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-edit text-teal-600 mr-2"></i>
                        Editar Plantilla
                    </h3>
                    <button type="button" 
                            onclick="closeEditTemplateModal()"
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="edit-template-form" onsubmit="saveTemplate(event)">
                    @csrf
                    <input type="hidden" name="section" value="email_template">
                    <input type="hidden" id="template_id" name="template_id" value="">

                    <div class="space-y-4">
                        <!-- Nombre de la Plantilla (solo lectura) -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Nombre de la Plantilla
                            </label>
                            <input type="text" 
                                   id="template_name" 
                                   readonly
                                   class="bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg block w-full p-2.5 cursor-not-allowed">
                        </div>

                        <!-- Tipo de Plantilla (solo lectura) -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Tipo
                            </label>
                            <input type="text" 
                                   id="template_type" 
                                   readonly
                                   class="bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg block w-full p-2.5 cursor-not-allowed">
                        </div>

                        <!-- Shortcodes Disponibles -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">
                                <i class="fas fa-code mr-2"></i>
                                Shortcodes Disponibles
                            </h4>
                            <div id="available-shortcodes" class="flex flex-wrap gap-2">
                                <!-- Se llenará dinámicamente -->
                            </div>
                            <p class="text-xs text-blue-700 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Haz clic en un shortcode para copiarlo al portapapeles
                            </p>
                        </div>

                        <!-- Asunto -->
                        <div>
                            <label for="template_subject" class="block mb-2 text-sm font-medium text-gray-900">
                                Asunto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="template_subject" 
                                   name="subject" 
                                   required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            <p class="mt-1 text-xs text-gray-500">
                                Puedes usar shortcodes como {name}, {email}, etc.
                            </p>
                        </div>

                        <!-- Cuerpo del Mensaje -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label for="template_body" class="block text-sm font-medium text-gray-900">
                                    Cuerpo del Mensaje <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center space-x-2">
                                    <button type="button" 
                                            id="toggle-editor-btn"
                                            onclick="toggleEditorView()"
                                            class="px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <i class="fas fa-code mr-1" id="toggle-editor-icon"></i>
                                        <span id="toggle-editor-text">Ver HTML</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Editor Visual (TinyMCE) -->
                            <div id="visual-editor-container" class="border border-gray-300 rounded-lg">
                                <textarea id="template_body_visual" 
                                          name="body_visual" 
                                          style="height: 400px;"></textarea>
                            </div>
                            
                            <!-- Editor HTML (Textarea) -->
                            <div id="html-editor-container" class="hidden">
                                <textarea id="template_body" 
                                          name="body" 
                                          rows="15"
                                          required
                                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono text-xs">{{ old('body') }}</textarea>
                            </div>
                            
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Puedes alternar entre vista visual y código HTML. Los shortcodes funcionan en ambas vistas.
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" 
                                    onclick="closeEditTemplateModal()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Definir funciones globales INMEDIATAMENTE - sin IIFE para evitar problemas
window.showEmailDetails = function(logId) {
    console.log('Intentando cargar detalles del correo ID:', logId);
    
    // Mostrar indicador de carga
    const loadingModal = `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="email-detail-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-teal-600 text-2xl mb-4"></i>
                    <p class="text-gray-600">Cargando detalles del correo...</p>
                </div>
            </div>
        </div>
    `;
    
    // Eliminar modal anterior si existe
    const existingModal = document.getElementById('email-detail-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', loadingModal);
    
    // Hacer una petición AJAX para obtener los detalles del correo
    fetch(`/admin/settings/email-logs/${logId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('Respuesta recibida:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            if (data.success && data.log) {
                const log = data.log;
                
                // Escapar HTML en el mensaje de error para evitar problemas de seguridad
                const escapeHtml = (text) => {
                    if (!text) return '';
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                };
                
                const modal = `
                    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="email-detail-modal">
                        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
                            <div class="mt-3">
                                <div class="flex justify-between items-center mb-4 border-b pb-3">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <i class="fas fa-envelope text-teal-600 mr-2"></i>Detalles del Correo
                                    </h3>
                                    <button onclick="if(typeof window.closeEmailDetails === 'function') { window.closeEmailDetails(); }" class="text-gray-400 hover:text-gray-600 text-xl">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><strong class="text-gray-700">Fecha:</strong> <span class="text-gray-900">${new Date(log.created_at).toLocaleString('es-ES')}</span></div>
                                        <div><strong class="text-gray-700">Estado:</strong> 
                                            <span class="px-2 py-1 text-xs font-medium rounded-full ${log.status === 'sent' ? 'bg-green-100 text-green-800' : log.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">
                                                ${log.status === 'sent' ? '✅ Enviado' : log.status === 'failed' ? '❌ Fallido' : '⏳ Pendiente'}
                                            </span>
                                        </div>
                                    </div>
                                    <div><strong class="text-gray-700">Destinatario:</strong> <span class="text-gray-900">${escapeHtml(log.to)}</span></div>
                                    <div><strong class="text-gray-700">Remitente:</strong> <span class="text-gray-900">${escapeHtml(log.from_email)} ${log.from_name ? '(' + escapeHtml(log.from_name) + ')' : '(Sin nombre)'}</span></div>
                                    <div><strong class="text-gray-700">Asunto:</strong> <span class="text-gray-900">${escapeHtml(log.subject)}</span></div>
                                    <div><strong class="text-gray-700">Proveedor:</strong> 
                                        <span class="px-2 py-1 text-xs font-medium rounded-full ${log.provider === 'zoho' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                                            ${log.provider.toUpperCase()}
                                        </span>
                                    </div>
                                    ${log.error_message ? `
                                    <div class="mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-red-600 mr-2 mt-1"></i>
                                            <div class="flex-1">
                                                <strong class="text-red-800 block mb-2">Mensaje de Error:</strong>
                                                <pre class="text-xs text-red-700 whitespace-pre-wrap font-mono bg-white p-3 rounded border border-red-200 overflow-x-auto">${escapeHtml(log.error_message)}</pre>
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                    <div class="border-t pt-4 mt-4">
                                        <strong class="text-gray-700 block mb-2">Cuerpo del Mensaje:</strong>
                                        <div class="mt-2 p-3 bg-gray-50 rounded border max-h-64 overflow-y-auto text-xs">
                                            ${escapeHtml(log.body)}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end border-t pt-4">
                                    <button onclick="if(typeof window.closeEmailDetails === 'function') { window.closeEmailDetails(); }" class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                                        <i class="fas fa-times mr-2"></i>Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Reemplazar el modal de carga con el modal de detalles
                const existingModal = document.getElementById('email-detail-modal');
                if (existingModal) {
                    existingModal.outerHTML = modal;
                } else {
                    document.body.insertAdjacentHTML('beforeend', modal);
                }
            } else {
                throw new Error('No se recibieron los datos del correo correctamente');
            }
        })
        .catch(error => {
            console.error('Error al cargar detalles:', error);
            
            // Mostrar error en el modal
            const errorModal = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="email-detail-modal">
                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-red-600">Error al Cargar Detalles</h3>
                                <button onclick="if(typeof window.closeEmailDetails === 'function') { window.closeEmailDetails(); }" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="p-4 bg-red-50 rounded border border-red-200">
                                <p class="text-red-700 mb-2"><strong>No se pudieron cargar los detalles del correo.</strong></p>
                                <p class="text-sm text-red-600">Error: ${error.message}</p>
                                <p class="text-xs text-gray-500 mt-2">Por favor, verifica la consola del navegador para más detalles.</p>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button onclick="if(typeof window.closeEmailDetails === 'function') { window.closeEmailDetails(); }" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('email-detail-modal');
            if (existingModal) {
                existingModal.outerHTML = errorModal;
            } else {
                document.body.insertAdjacentHTML('beforeend', errorModal);
            }
        });
};

// Función para cerrar el modal
window.closeEmailDetails = function() {
    const modal = document.getElementById('email-detail-modal');
    if (modal) {
        modal.remove();
    }
};

// Función para eliminar un correo del historial
window.deleteEmailLog = function(logId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este correo del historial?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch(`/admin/settings/email-logs/${logId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Eliminar la fila de la tabla con animación
                const row = document.querySelector(`tr[data-log-id="${logId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Si no quedan más correos, recargar la página
                        const remainingRows = document.querySelectorAll('tbody tr[data-log-id]');
                        if (remainingRows.length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                } else {
                    // Si no encontramos la fila, recargar la página
                    window.location.reload();
                }
            } else {
                alert('Error al eliminar el correo: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error al eliminar correo:', error);
            alert('Error al eliminar el correo. Por favor, intenta de nuevo.');
        });
};

function showTab(tabName) {
    // Ocultar todos los paneles
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });
    
    // Remover clase active de todos los tabs
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('border-teal-600', 'text-teal-600');
        link.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Mostrar el panel correspondiente
    const panel = document.getElementById('panel-' + tabName);
    if (panel) {
        panel.classList.remove('hidden');
    }
    
    // Activar el tab correspondiente
    const tab = document.getElementById('tab-' + tabName);
    if (tab) {
        tab.classList.add('border-teal-600', 'text-teal-600');
        tab.classList.remove('border-transparent', 'text-gray-500');
    }
    
    // Actualizar URL sin recargar (opcional, para mejor UX)
    updateUrl(tabName);
}

// Función para actualizar la URL sin recargar la página (opcional, para mejor UX)
function updateUrl(section) {
    const url = new URL(window.location);
    url.pathname = '/admin/settings/' + section;
    window.history.pushState({}, '', url);
}

// Mostrar el tab activo basado en la URL actual
document.addEventListener('DOMContentLoaded', function() {
    // Obtener la sección activa desde la URL
    const pathParts = window.location.pathname.split('/');
    const currentSection = pathParts[pathParts.length - 1] || 'agency';
    
    // Validar que sea una sección válida
    const validSections = ['agency', 'drive', 'mail', 'templates', 'history'];
    const activeSection = validSections.includes(currentSection) ? currentSection : 'agency';
    
    // Inicializar campos según el proveedor
    toggleProviderFields();
    
    // Si hay un parámetro tab en la sesión (después de guardar), redirigir a esa sección
    const tabFromSession = '{{ session('tab', '') }}';
    if (tabFromSession && validSections.includes(tabFromSession) && tabFromSession !== activeSection) {
        // Redirigir a la sección de la sesión si es diferente a la actual
        window.location.href = '/admin/settings/' + tabFromSession;
        return;
    }
});

// Función para mostrar/ocultar campos según el proveedor seleccionado
function toggleProviderFields() {
    const provider = document.getElementById('mail_provider').value;
    const smtpFields = document.getElementById('smtp-fields');
    const zohoFields = document.getElementById('zoho-fields');
    
    if (provider === 'smtp') {
        smtpFields.classList.remove('hidden');
        zohoFields.classList.add('hidden');
        // Hacer campos SMTP requeridos
        document.getElementById('mail_host').required = true;
        document.getElementById('mail_port').required = true;
        document.getElementById('mail_from_address').required = true;
        document.getElementById('mail_from_name').required = true;
        // Quitar requeridos de Zoho
        document.getElementById('zoho_client_id').required = false;
        document.getElementById('zoho_client_secret').required = false;
        document.getElementById('zoho_refresh_token').required = false;
        document.getElementById('zoho_from_email').required = false;
    } else {
        smtpFields.classList.add('hidden');
        zohoFields.classList.remove('hidden');
        // Quitar requeridos de SMTP
        document.getElementById('mail_host').required = false;
        document.getElementById('mail_port').required = false;
        document.getElementById('mail_from_address').required = false;
        document.getElementById('mail_from_name').required = false;
        // Hacer campos Zoho requeridos
        document.getElementById('zoho_client_id').required = true;
        document.getElementById('zoho_client_secret').required = true;
        document.getElementById('zoho_refresh_token').required = true;
        document.getElementById('zoho_from_email').required = true;
    }
}

// Función para mostrar/ocultar contraseña
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const eyeIcon = document.getElementById('eye-' + fieldId);
    
    if (field.type === 'password') {
        field.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

// Función para verificar si los campos de Zoho están completos y habilitar/deshabilitar el botón
function checkZohoCredentials() {
    const clientId = document.getElementById('zoho_client_id');
    const clientSecret = document.getElementById('zoho_client_secret');
    const fromEmail = document.getElementById('zoho_from_email');
    const authorizeBtn = document.getElementById('zoho-authorize-btn');
    const warningBox = document.getElementById('zoho-warning-box');
    const accountHint = document.getElementById('zoho-account-hint');
    const accountHintEmail = document.getElementById('zoho-account-hint-email');
    
    if (!clientId || !clientSecret || !authorizeBtn) {
        return;
    }
    
    const hasClientId = clientId.value.trim().length > 0;
    const hasClientSecret = clientSecret.value.trim().length > 0;
    const hasFromEmail = fromEmail && fromEmail.value.trim().length > 0;
    
    if (accountHintEmail && fromEmail) {
        accountHintEmail.textContent = hasFromEmail ? fromEmail.value.trim() : '—';
    }
    
    if (hasClientId && hasClientSecret && hasFromEmail) {
        authorizeBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        authorizeBtn.classList.add('hover:bg-teal-700', 'cursor-pointer');
        authorizeBtn.style.pointerEvents = 'auto';
        authorizeBtn.style.opacity = '1';
        if (warningBox) warningBox.style.display = 'none';
        if (accountHint) accountHint.style.display = 'block';
    } else {
        authorizeBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        authorizeBtn.classList.remove('hover:bg-teal-700', 'cursor-pointer');
        authorizeBtn.style.pointerEvents = 'none';
        authorizeBtn.style.opacity = '0.5';
        if (warningBox) warningBox.style.display = 'block';
        if (accountHint) accountHint.style.display = hasFromEmail ? 'block' : 'none';
    }
}

// Función para validar antes de hacer clic
function validateZohoCredentials(event) {
    const clientId = document.getElementById('zoho_client_id');
    const clientSecret = document.getElementById('zoho_client_secret');
    const fromEmail = document.getElementById('zoho_from_email');
    
    if (!clientId || !clientSecret || !fromEmail) {
        event.preventDefault();
        alert('Completa Email Remitente, Client ID y Client Secret.');
        return false;
    }
    
    const hasClientId = clientId.value.trim().length > 0;
    const hasClientSecret = clientSecret.value.trim().length > 0;
    const hasFromEmail = fromEmail.value.trim().length > 0;
    
    if (!hasClientId || !hasClientSecret || !hasFromEmail) {
        event.preventDefault();
        alert('Completa Email Remitente, Client ID y Client Secret. Luego guarda los cambios antes de autorizar.');
        return false;
    }
    
    const redirectUri = '{{ route("admin.settings.zoho.callback") }}';
    const email = fromEmail.value.trim();
    const confirmMessage = 'IMPORTANTE:\n\n' +
        '1. ¿Ya guardaste los cambios? (El token usa los datos guardados.)\n\n' +
        '2. ¿Redirect URI en Zoho API Console?\n' + redirectUri + '\n\n' +
        '3. Al autorizar, inicia sesión en Zoho con este correo:\n' + email + '\n\n' +
        '¿Continuar a Zoho para autorizar?';
    
    if (!confirm(confirmMessage)) {
        event.preventDefault();
        return false;
    }
    
    return true;
}

// Función para copiar Redirect URI al portapapeles
function copyRedirectUri() {
    const redirectUri = '{{ route("admin.settings.zoho.callback") }}';
    navigator.clipboard.writeText(redirectUri).then(function() {
        // Mostrar mensaje de éxito
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copiado!';
        button.classList.add('text-green-600');
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('text-green-600');
        }, 2000);
    }).catch(function(err) {
        alert('Error al copiar. Por favor, copia manualmente: ' + redirectUri);
    });
}

// Función para limpiar el Refresh Token
function clearRefreshToken() {
    if (confirm('¿Estás seguro de que deseas limpiar el Refresh Token? Deberás autorizar nuevamente con Zoho para generar uno nuevo.')) {
        const tokenField = document.getElementById('zoho_refresh_token');
        if (tokenField) {
            tokenField.value = '';
            tokenField.focus();
            // Mostrar mensaje temporal
            const message = document.createElement('div');
            message.className = 'mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800';
            message.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Refresh Token limpiado. Guarda los cambios y luego haz clic en "Autorizar con Zoho" para generar uno nuevo.';
            tokenField.parentElement.parentElement.appendChild(message);
            setTimeout(function() {
                message.remove();
            }, 5000);
        }
    }
}

// Verificar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un momento para que los campos se carguen
    setTimeout(function() {
        checkZohoCredentials();
    }, 100);
    
    // También verificar cuando se cambia de tab (si se usa JavaScript para cambiar)
    // Nota: Ahora los tabs usan enlaces reales, pero mantenemos esta funcionalidad
    // para compatibilidad con código existente que pueda llamar showTab()
    const originalShowTab = showTab;
    showTab = function(tabName) {
        originalShowTab(tabName);
        if (tabName === 'mail') {
            setTimeout(function() {
                checkZohoCredentials();
            }, 100);
        }
    };
});

// ========== FUNCIONES PARA MODAL DE PLANTILLAS ==========

// Variables disponibles por tipo de plantilla
const templateVariables = {
    'client_invitation': {
        'name': 'Nombre del cliente',
        'email': 'Email del cliente',
        'link': 'Link de registro/acceso',
        'company_name': 'Nombre de la empresa',
        'agency_name': 'Nombre de la agencia',
        'agency_email': 'Email de la agencia',
        'agency_phone': 'Teléfono de la agencia',
        'agency_address': 'Dirección de la agencia',
        'agency_website': 'Sitio web de la agencia',
    },
    'expiration_reminder': {
        'name': 'Nombre del destinatario',
        'product_name': 'Nombre del producto/registro',
        'expiration_date': 'Fecha de vencimiento',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'agency_name': 'Nombre de la agencia',
    },
    'new_registration': {
        'name': 'Nombre del destinatario',
        'product_name': 'Nombre del producto/registro',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'status': 'Estado del registro',
        'expiration_date': 'Fecha de vencimiento',
        'assigned_specialist': 'Especialista asignado',
        'agency_name': 'Nombre de la agencia',
    },
    'status_change': {
        'name': 'Nombre del destinatario',
        'product_name': 'Nombre del producto/registro',
        'status': 'Nuevo estado',
        'previous_status': 'Estado anterior',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'observations': 'Observaciones',
        'agency_name': 'Nombre de la agencia',
    },
    'pending_documents': {
        'name': 'Nombre del destinatario',
        'product_name': 'Nombre del producto/registro',
        'pending_documents': 'Lista de documentos pendientes',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'agency_name': 'Nombre de la agencia',
    },
    'specialist_assignment': {
        'name': 'Nombre del destinatario',
        'product_name': 'Nombre del producto/registro',
        'specialist_name': 'Nombre del especialista',
        'specialist_email': 'Email del especialista',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'agency_name': 'Nombre de la agencia',
    },
    'important_date_reminder': {
        'name': 'Nombre del destinatario',
        'event_name': 'Nombre del evento',
        'event_date': 'Fecha del evento',
        'product_name': 'Nombre del producto/registro',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'agency_name': 'Nombre de la agencia',
    },
    'requirement_notification': {
        'name': 'Nombre del destinatario',
        'product_name': 'Nombre del producto/registro',
        'requirement_type': 'Tipo de requerimiento',
        'requirement_description': 'Descripción del requerimiento',
        'company_name': 'Nombre de la empresa',
        'registration_number': 'Número de registro',
        'agency_name': 'Nombre de la agencia',
    },
};

// Variable global para el editor TinyMCE
let templateEditor = null;
let currentEditorView = 'visual'; // 'visual' o 'html'

// Abrir modal de edición
window.openEditTemplateModal = function(templateId) {
    // Primero, asegurar que no haya editor previo
    if (templateEditor) {
        try {
            const container = document.getElementById('template_body_visual');
            if (container) {
                container.innerHTML = '';
            }
            templateEditor = null;
        } catch (e) {
            console.error('Error limpiando editor previo:', e);
        }
    }
    
    // Obtener datos de la plantilla
    console.log('Cargando plantilla ID:', templateId);
    
    fetch(`/admin/settings/templates/${templateId}`)
        .then(response => {
            console.log('Respuesta recibida, status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (data.success) {
                const template = data.template;
                
                console.log('Template data:', {
                    id: template.id,
                    name: template.name,
                    type: template.type,
                    subject: template.subject,
                    bodyLength: template.body ? template.body.length : 0,
                    bodyPreview: template.body ? template.body.substring(0, 300) : 'VACÍO'
                });
                
                // Llenar campos del formulario
                document.getElementById('template_id').value = template.id;
                document.getElementById('template_name').value = template.name;
                document.getElementById('template_type').value = template.type;
                document.getElementById('template_subject').value = template.subject || '';
                
                // Obtener contenido del body - asegurar que no esté vacío
                let bodyContent = template.body || '';
                
                console.log('Body content recibido:', {
                    length: bodyContent.length,
                    isEmpty: !bodyContent || bodyContent.trim() === '',
                    isMinimal: bodyContent.trim() === '<p><br></p>' || bodyContent.trim() === '<p></p>',
                    preview: bodyContent.substring(0, 500)
                });
                
                // Si el body está vacío o solo tiene <p><br></p>, loguear para debugging
                if (!bodyContent || bodyContent.trim() === '' || bodyContent.trim() === '<p><br></p>' || bodyContent.trim() === '<p></p>') {
                    console.warn('⚠️ Plantilla con body vacío o mínimo:', {
                        id: template.id,
                        type: template.type,
                        bodyLength: bodyContent ? bodyContent.length : 0,
                        bodyPreview: bodyContent ? bodyContent.substring(0, 100) : 'vacío'
                    });
                } else {
                    console.log('✅ Cargando plantilla con contenido:', {
                        id: template.id,
                        type: template.type,
                        bodyLength: bodyContent.length,
                        bodyPreview: bodyContent.substring(0, 200)
                    });
                }
                
                // Llenar el textarea HTML PRIMERO (por si el editor falla)
                const textarea = document.getElementById('template_body');
                if (textarea) {
                    textarea.value = bodyContent;
                    console.log('Textarea HTML llenado, valor:', textarea.value.substring(0, 200));
                }
                
                // Mostrar shortcodes disponibles
                displayAvailableShortcodes(template.type);
                
                // Mostrar modal ANTES de inicializar el editor
                document.getElementById('edit-template-modal').classList.remove('hidden');
                
                // Inicializar editor visual DESPUÉS de mostrar el modal
                // Esto asegura que el contenedor esté visible en el DOM
                setTimeout(function() {
                    console.log('Inicializando editor visual con contenido de', bodyContent.length, 'caracteres');
                    initializeVisualEditor(bodyContent);
                }, 500);
            } else {
                console.error('Error en respuesta:', data);
                alert('Error al cargar la plantilla: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar plantilla:', error);
            alert('Error al cargar la plantilla. Por favor, recarga la página. Ver consola para más detalles.');
        });
};

// Inicializar editor visual (TinyMCE)
function initializeVisualEditor(initialContent = '') {
    const textareaId = 'template_body_visual';
    const textarea = document.getElementById(textareaId);
    
    if (!textarea) {
        console.error('Textarea del editor no encontrado');
        return;
    }
    
    // Si ya existe un editor, destruirlo primero
    if (templateEditor) {
        try {
            tinymce.remove('#' + textareaId);
            templateEditor = null;
        } catch (e) {
            console.error('Error al destruir editor previo:', e);
        }
    }
    
    // Extraer solo el contenido del body si es un documento HTML completo
    let contentToLoad = initialContent || '';
    
    if (contentToLoad && (contentToLoad.includes('<!DOCTYPE') || contentToLoad.includes('<html') || contentToLoad.includes('<body'))) {
        console.log('Detectado documento HTML completo, extrayendo contenido del body...');
        const bodyMatch = contentToLoad.match(/<body[^>]*>([\s\S]*)<\/body>/i);
        if (bodyMatch && bodyMatch[1]) {
            contentToLoad = bodyMatch[1].trim();
            console.log('✅ Contenido extraído del body, nueva longitud:', contentToLoad.length);
        } else {
            // Limpiar DOCTYPE y etiquetas html/head/body
            contentToLoad = contentToLoad
                .replace(/<!DOCTYPE[^>]*>/gi, '')
                .replace(/<html[^>]*>/gi, '')
                .replace(/<\/html>/gi, '')
                .replace(/<head[^>]*>[\s\S]*?<\/head>/gi, '')
                .replace(/<body[^>]*>/gi, '')
                .replace(/<\/body>/gi, '')
                .trim();
        }
    }
    
    // Establecer contenido en el textarea antes de inicializar TinyMCE
    textarea.value = contentToLoad;
    
    // Inicializar TinyMCE
    try {
        tinymce.init({
            selector: '#' + textareaId,
            license_key: 'gpl', // Open source license, no API key required
            height: 400,
            menubar: false,
            promotion: false,
            branding: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help | code',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
            setup: function(editor) {
                templateEditor = editor;
                
                // Sincronizar con textarea HTML cuando cambia el contenido
                editor.on('change keyup', function() {
                    syncToHtmlEditor();
                });
            },
            init_instance_callback: function(editor) {
                console.log('✅ TinyMCE inicializado correctamente');
                // Esperar un momento para que TinyMCE cargue el contenido del textarea
                setTimeout(function() {
                    syncToHtmlEditor();
                }, 100);
            }
        });
    } catch (e) {
        console.error('Error al inicializar TinyMCE:', e);
        // Si falla, mostrar el contenido en el textarea HTML
        document.getElementById('template_body').value = initialContent || '';
    }
}

// Alternar entre vista visual y HTML
window.toggleEditorView = function() {
    const visualContainer = document.getElementById('visual-editor-container');
    const htmlContainer = document.getElementById('html-editor-container');
    const toggleBtn = document.getElementById('toggle-editor-btn');
    const toggleIcon = document.getElementById('toggle-editor-icon');
    const toggleText = document.getElementById('toggle-editor-text');
    
    if (currentEditorView === 'visual') {
        // Cambiar a vista HTML
        currentEditorView = 'html';
        
        // Sincronizar contenido de visual a HTML antes de cambiar
        syncToHtmlEditor();
        
        // Ocultar visual, mostrar HTML
        visualContainer.classList.add('hidden');
        htmlContainer.classList.remove('hidden');
        
        // Actualizar botón
        toggleIcon.className = 'fas fa-eye mr-1';
        toggleText.textContent = 'Ver Visual';
        toggleBtn.classList.remove('bg-teal-50', 'text-teal-700', 'border-teal-200');
        toggleBtn.classList.add('bg-gray-50', 'text-gray-700', 'border-gray-200');
    } else {
        // Cambiar a vista visual
        currentEditorView = 'visual';
        
        // Sincronizar contenido de HTML a visual antes de cambiar
        syncToVisualEditor();
        
        // Ocultar HTML, mostrar visual
        htmlContainer.classList.add('hidden');
        visualContainer.classList.remove('hidden');
        
        // Actualizar botón
        toggleIcon.className = 'fas fa-code mr-1';
        toggleText.textContent = 'Ver HTML';
        toggleBtn.classList.remove('bg-gray-50', 'text-gray-700', 'border-gray-200');
        toggleBtn.classList.add('bg-teal-50', 'text-teal-700', 'border-teal-200');
    }
};

// Sincronizar contenido del editor visual al textarea HTML
function syncToHtmlEditor() {
    if (templateEditor && typeof templateEditor.getContent === 'function') {
        try {
            const visualContent = templateEditor.getContent();
            const textarea = document.getElementById('template_body');
            if (textarea) {
                // Si el contenido original tenía DOCTYPE/html/head/body, mantenerlo
                // De lo contrario, usar el contenido del editor directamente
                const originalContent = textarea.value || '';
                
                // Si el contenido original era un documento HTML completo, reconstruirlo
                if (originalContent.includes('<!DOCTYPE') || originalContent.includes('<html')) {
                    // Extraer las partes del documento original
                    const bodyMatch = originalContent.match(/<body[^>]*>([\s\S]*)<\/body>/i);
                    if (bodyMatch) {
                        // Reemplazar solo el contenido del body
                        const newContent = originalContent.replace(
                            /<body[^>]*>[\s\S]*<\/body>/i,
                            '<body>' + visualContent + '</body>'
                        );
                        textarea.value = newContent;
                    } else {
                        // Si no se encuentra body, insertar el contenido en el lugar apropiado
                        textarea.value = originalContent.replace(
                            /(<body[^>]*>)([\s\S]*)(<\/body>)/i,
                            '$1' + visualContent + '$3'
                        ) || visualContent;
                    }
                } else {
                    // Si no es un documento completo, usar el contenido directamente
                    textarea.value = visualContent;
                }
            }
        } catch (e) {
            console.error('Error al sincronizar contenido:', e);
        }
    }
}

// Sincronizar contenido del textarea HTML al editor visual
function syncToVisualEditor() {
    if (templateEditor) {
        const htmlContent = document.getElementById('template_body').value;
        
        // Extraer solo el contenido del body si es un documento HTML completo
        let contentToLoad = htmlContent;
        if (htmlContent && (htmlContent.includes('<!DOCTYPE') || htmlContent.includes('<html') || htmlContent.includes('<body'))) {
            const bodyMatch = htmlContent.match(/<body[^>]*>([\s\S]*)<\/body>/i);
            if (bodyMatch && bodyMatch[1]) {
                contentToLoad = bodyMatch[1].trim();
            } else {
                contentToLoad = htmlContent
                    .replace(/<!DOCTYPE[^>]*>/gi, '')
                    .replace(/<html[^>]*>/gi, '')
                    .replace(/<\/html>/gi, '')
                    .replace(/<head[^>]*>[\s\S]*?<\/head>/gi, '')
                    .replace(/<body[^>]*>/gi, '')
                    .replace(/<\/body>/gi, '')
                    .trim();
            }
        }
        
        templateEditor.setContent(contentToLoad);
    }
}

// Cerrar modal
window.closeEditTemplateModal = function() {
    // Destruir editor TinyMCE si existe
    if (templateEditor) {
        try {
            tinymce.remove('#template_body_visual');
            templateEditor = null;
        } catch (e) {
            console.error('Error al cerrar editor:', e);
            templateEditor = null;
        }
    }
    
    // Resetear vista a visual
    currentEditorView = 'visual';
    const visualContainer = document.getElementById('visual-editor-container');
    const htmlContainer = document.getElementById('html-editor-container');
    if (visualContainer) visualContainer.classList.remove('hidden');
    if (htmlContainer) htmlContainer.classList.add('hidden');
    
    // Resetear botón
    const toggleIcon = document.getElementById('toggle-editor-icon');
    const toggleText = document.getElementById('toggle-editor-text');
    const toggleBtn = document.getElementById('toggle-editor-btn');
    if (toggleIcon) toggleIcon.className = 'fas fa-code mr-1';
    if (toggleText) toggleText.textContent = 'Ver HTML';
    if (toggleBtn) {
        toggleBtn.classList.remove('bg-gray-50', 'text-gray-700', 'border-gray-200');
        toggleBtn.classList.add('bg-teal-50', 'text-teal-700', 'border-teal-200');
    }
    
    // Ocultar modal
    const modal = document.getElementById('edit-template-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
    
    // Limpiar formulario
    const form = document.getElementById('edit-template-form');
    if (form) {
        form.reset();
    }
};

// Mostrar shortcodes disponibles
function displayAvailableShortcodes(templateType) {
    const container = document.getElementById('available-shortcodes');
    container.innerHTML = '';
    
    const variables = templateVariables[templateType] || {};
    
    Object.keys(variables).forEach(key => {
        const shortcode = `{${key}}`;
        const description = variables[key];
        
        const badge = document.createElement('button');
        badge.type = 'button';
        badge.className = 'px-3 py-1.5 text-xs font-medium text-blue-800 bg-blue-100 border border-blue-200 rounded-lg hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500';
        badge.innerHTML = `<code class="font-mono">${shortcode}</code> <span class="ml-1 text-blue-600">${description}</span>`;
        badge.title = `Clic para copiar: ${shortcode}`;
        badge.onclick = function() {
            copyShortcode(shortcode, badge);
        };
        
        container.appendChild(badge);
    });
    
    if (Object.keys(variables).length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-600">No hay shortcodes específicos para este tipo de plantilla.</p>';
    }
}

// Copiar shortcode al portapapeles
function copyShortcode(shortcode, button) {
    navigator.clipboard.writeText(shortcode).then(function() {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copiado!';
        button.classList.add('bg-green-100', 'text-green-800', 'border-green-300');
        button.classList.remove('bg-blue-100', 'text-blue-800', 'border-blue-200');
        
        setTimeout(function() {
            button.innerHTML = originalHTML;
            button.classList.remove('bg-green-100', 'text-green-800', 'border-green-300');
            button.classList.add('bg-blue-100', 'text-blue-800', 'border-blue-200');
        }, 2000);
    }).catch(function(err) {
        alert('Error al copiar. Por favor, copia manualmente: ' + shortcode);
    });
}

// Guardar plantilla
window.saveTemplate = function(event) {
    event.preventDefault();
    
    // Sincronizar contenido antes de guardar
    if (currentEditorView === 'visual') {
        syncToHtmlEditor();
    }
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHTML = submitButton.innerHTML;
    
    // Deshabilitar botón y mostrar loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
    
    fetch('{{ route("admin.settings.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            alert('Plantilla guardada exitosamente');
            // Recargar página para ver cambios
            window.location.reload();
        } else {
            alert('Error al guardar: ' + (data.message || 'Error desconocido'));
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHTML;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la plantilla. Por favor, intenta de nuevo.');
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonHTML;
    });
};

// Cerrar modal al hacer clic fuera
document.getElementById('edit-template-modal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeEditTemplateModal();
    }
});

// Ejecutar Git Pull
function executeGitPull(branch) {
    const outputDiv = document.getElementById('git-pull-output');
    const resultPre = document.getElementById('git-pull-result');
    
    // Mostrar contenedor de salida
    outputDiv.classList.remove('hidden');
    resultPre.textContent = 'Ejecutando git pull...\n';
    
    // Deshabilitar botones mientras se ejecuta
    const buttons = document.querySelectorAll('button[onclick*="executeGitPull"]');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch('{{ route("admin.settings.git-pull") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ branch: branch })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultPre.textContent = '✅ ' + data.message + '\n\n' + data.output;
            resultPre.classList.remove('text-red-400');
            resultPre.classList.add('text-green-400');
        } else {
            resultPre.textContent = '❌ ' + data.message + '\n\n' + (data.output || '');
            resultPre.classList.remove('text-green-400');
            resultPre.classList.add('text-red-400');
        }
    })
    .catch(error => {
        resultPre.textContent = '❌ Error: ' + error.message;
        resultPre.classList.remove('text-green-400');
        resultPre.classList.add('text-red-400');
    })
    .finally(() => {
        // Rehabilitar botones
        buttons.forEach(btn => btn.disabled = false);
    });
}

// Probar conexión con Google Drive
function testDriveConnection() {
    const jsonField = document.getElementById('drive_service_account_json');
    const jsonValue = jsonField.value.trim();
    
    if (!jsonValue) {
        alert('Por favor, ingresa el JSON de Service Account primero.');
        return;
    }
    
    // Validar JSON básico
    try {
        const jsonData = JSON.parse(jsonValue);
        if (jsonData.type !== 'service_account') {
            alert('El JSON no corresponde a una Service Account de Google Cloud.');
            return;
        }
        if (!jsonData.client_email) {
            alert('El JSON no contiene el campo "client_email" requerido.');
            return;
        }
    } catch (e) {
        alert('El JSON proporcionado no es válido. Por favor, verifica el formato.\n\nError: ' + e.message);
        return;
    }
    
    // Deshabilitar botón mientras se prueba
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Probando...';
    
    // Guardar primero el JSON para que el servidor pueda usarlo
    const form = jsonField.closest('form');
    const formData = new FormData(form);
    formData.append('section', 'drive');
    
    // Primero guardar, luego probar
    fetch('{{ route("admin.settings.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(() => {
        // Ahora probar conexión
        return fetch('{{ route("admin.settings.test-drive-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error al probar conexión: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Ejecutar Comando Artisan
function executeArtisanCommand(command) {
    const outputDiv = document.getElementById('git-pull-output');
    const resultPre = document.getElementById('git-pull-result');
    
    // Mostrar contenedor de salida
    outputDiv.classList.remove('hidden');
    resultPre.textContent = 'Ejecutando: ' + command + '\n';
    
    // Deshabilitar botones mientras se ejecuta
    const buttons = document.querySelectorAll('button[onclick*="executeArtisanCommand"]');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch('{{ route("admin.settings.artisan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ command: command })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultPre.textContent = '✅ ' + data.message + '\n\n' + data.output;
            resultPre.classList.remove('text-red-400');
            resultPre.classList.add('text-green-400');
        } else {
            resultPre.textContent = '❌ ' + data.message + '\n\n' + (data.output || '');
            resultPre.classList.remove('text-green-400');
            resultPre.classList.add('text-red-400');
        }
    })
    .catch(error => {
        resultPre.textContent = '❌ Error: ' + error.message;
        resultPre.classList.remove('text-green-400');
        resultPre.classList.add('text-red-400');
    })
    .finally(() => {
        // Rehabilitar botones
        buttons.forEach(btn => btn.disabled = false);
    });
}

// Cargar historial de operaciones de Google Drive
function loadDriveOperationsLog() {
    const container = document.getElementById('drive-operations-container');
    container.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p>Cargando historial...</p></div>';

    fetch('{{ route("admin.settings.drive-operations-log") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.operations.data.length > 0) {
            let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operación</th>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recurso</th>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expediente</th>';
            html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>';
            html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

            data.operations.data.forEach(op => {
                const statusColor = op.status === 'success' ? 'green' : op.status === 'failed' ? 'red' : 'yellow';
                const statusIcon = op.status === 'success' ? 'check-circle' : op.status === 'failed' ? 'times-circle' : 'clock';
                const operationLabels = {
                    'upload': 'Subir Archivo',
                    'create_folder': 'Crear Carpeta',
                    'move': 'Mover',
                    'delete': 'Eliminar',
                    'update': 'Actualizar'
                };
                
                html += '<tr class="hover:bg-gray-50">';
                html += '<td class="px-4 py-3 text-sm text-gray-900">' + new Date(op.created_at).toLocaleString('es-ES') + '</td>';
                html += '<td class="px-4 py-3 text-sm text-gray-900"><i class="fas fa-' + (op.operation_type === 'upload' ? 'upload' : op.operation_type === 'create_folder' ? 'folder-plus' : 'file') + ' mr-2"></i>' + (operationLabels[op.operation_type] || op.operation_type) + '</td>';
                html += '<td class="px-4 py-3 text-sm text-gray-900">' + op.resource_name + '</td>';
                html += '<td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded-full text-xs font-medium bg-' + statusColor + '-100 text-' + statusColor + '-800"><i class="fas fa-' + statusIcon + ' mr-1"></i>' + (op.status === 'success' ? 'Éxito' : op.status === 'failed' ? 'Error' : 'Pendiente') + '</span></td>';
                html += '<td class="px-4 py-3 text-sm text-gray-900">' + (op.user ? op.user.name : '-') + '</td>';
                html += '<td class="px-4 py-3 text-sm text-gray-900">' + (op.registration ? 'Expediente #' + op.registration.id : '-') + '</td>';
                html += '<td class="px-4 py-3 text-sm">';
                if (op.drive_url) {
                    html += '<a href="' + op.drive_url + '" target="_blank" class="text-teal-600 hover:text-teal-800"><i class="fas fa-external-link-alt"></i></a>';
                }
                if (op.error_message) {
                    html += ' <button onclick="showError(\'' + op.error_message.replace(/'/g, "\\'") + '\')" class="text-red-600 hover:text-red-800 ml-2"><i class="fas fa-exclamation-triangle"></i></button>';
                }
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';

            // Paginación
            if (data.operations.links && data.operations.links.length > 3) {
                html += '<div class="mt-4 flex justify-center">';
                data.operations.links.forEach(link => {
                    if (link.url) {
                        html += '<a href="' + link.url + '" class="px-3 py-1 mx-1 border rounded ' + (link.active ? 'bg-teal-600 text-white' : 'bg-white text-gray-700') + '">' + (link.label.includes('Previous') ? '«' : link.label.includes('Next') ? '»' : link.label) + '</a>';
                    }
                });
                html += '</div>';
            }

            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-inbox text-3xl mb-2"></i><p>No hay operaciones registradas aún.</p></div>';
        }
    })
    .catch(error => {
        container.innerHTML = '<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error al cargar historial: ' + error.message + '</p></div>';
    });
}

// Mostrar error en modal
function showError(message) {
    alert('Error: ' + message);
}

// Toggle instructivo de Google Drive
function toggleDriveInstructions() {
    const content = document.getElementById('drive-instructions-content');
    const icon = document.getElementById('drive-instructions-icon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Cambiar entre tabs de Google Drive
function switchDriveTab(tab) {
    // Ocultar todos los paneles
    document.querySelectorAll('.drive-subpanel').forEach(panel => {
        panel.classList.add('hidden');
    });
    
    // Desactivar todos los tabs
    document.querySelectorAll('.drive-subtab').forEach(btn => {
        btn.classList.remove('border-teal-600', 'text-teal-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Mostrar panel seleccionado
    const panel = document.getElementById('drive-panel-' + tab);
    if (panel) {
        panel.classList.remove('hidden');
    }
    
    // Activar tab seleccionado
    const tabBtn = document.getElementById('drive-tab-' + tab);
    if (tabBtn) {
        tabBtn.classList.remove('border-transparent', 'text-gray-500');
        tabBtn.classList.add('border-teal-600', 'text-teal-600');
    }
    
    // Si se cambia al historial, cargarlo
    if (tab === 'history') {
        loadDriveOperationsLog();
    }
}

// Cargar historial al entrar a la pestaña
document.addEventListener('DOMContentLoaded', function() {
    const driveTab = document.getElementById('tab-drive');
    if (driveTab) {
        driveTab.addEventListener('click', function() {
            setTimeout(() => {
                if (document.getElementById('panel-drive') && !document.getElementById('panel-drive').classList.contains('hidden')) {
                    // Por defecto mostrar configuración
                    switchDriveTab('config');
                }
            }, 100);
        });
    }
    
    // Si ya estamos en la pestaña drive, mostrar configuración por defecto
    if (document.getElementById('panel-drive') && !document.getElementById('panel-drive').classList.contains('hidden')) {
        switchDriveTab('config');
    }
});
</script>
@endpush
