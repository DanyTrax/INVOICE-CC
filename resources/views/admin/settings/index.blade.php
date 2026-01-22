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
        <div id="panel-drive" class="tab-panel hidden">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-cloud text-teal-600 mr-2"></i>
                    Conexión con Google Drive
                </h3>
                <p class="text-sm text-gray-600 mb-6">
                    Configura la integración con Google Drive para almacenar documentos. Necesitas un archivo JSON de Service Account.
                </p>

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="drive">

                    <div class="mb-4">
                        <label for="drive_service_account_json" class="block mb-2 text-sm font-medium text-gray-900">
                            JSON de Service Account
                        </label>
                        <textarea id="drive_service_account_json" 
                                  name="drive_service_account_json" 
                                  rows="10"
                                  placeholder='{"type": "service_account", "project_id": "...", ...}'
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono text-xs">{{ old('drive_service_account_json', $settings->drive_service_account_json ?? '') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">
                            Pega aquí el contenido completo del archivo JSON de tu Service Account de Google Cloud.
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                            <i class="fas fa-save mr-2"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
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
                                        <li>Configurar Redirect URI: <code class="bg-blue-100 px-1 rounded">{{ url('/admin/settings') }}</code></li>
                                        <li>Obtener Client ID y Client Secret</li>
                                        <li>Generar Refresh Token usando OAuth2</li>
                                        <li>Completar los campos a continuación</li>
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

                            <!-- Refresh Token -->
                            <div>
                                <label for="zoho_refresh_token" class="block mb-2 text-sm font-medium text-gray-900">
                                    Refresh Token <span class="text-red-500">*</span>
                                </label>
                                
                                <!-- Botón de Autorización Automática (PRIMERO) -->
                                <div class="mb-4">
                                    <div id="zoho-warning-box" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3" style="display: {{ (!empty($settings->zoho_client_id) && !empty($settings->zoho_client_secret)) ? 'none' : 'block' }};">
                                        <p class="text-sm text-yellow-800 mb-2">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <strong>Paso 1:</strong> Primero completa Client ID y Client Secret arriba, luego usa el botón de abajo.
                                        </p>
                                    </div>
                                    
                                    <div class="bg-teal-50 border-2 border-teal-300 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-teal-900 mb-1">
                                                    <i class="fas fa-magic mr-2"></i>
                                                    Generación Automática de Refresh Token
                                                </h4>
                                                <p class="text-xs text-teal-700">
                                                    Este botón enviará la información a Zoho, te redirigirá para autorizar y obtendrá automáticamente el Refresh Token.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('admin.settings.zoho.authorize') }}" 
                                           id="zoho-authorize-btn"
                                           onclick="return validateZohoCredentials(event)"
                                           class="inline-flex items-center justify-center w-full px-6 py-3 bg-teal-600 text-white text-base font-semibold rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300 transition-all shadow-lg hover:shadow-xl {{ (!empty($settings->zoho_client_id) && !empty($settings->zoho_client_secret)) ? '' : 'opacity-50 cursor-not-allowed pointer-events-none' }}">
                                            <i class="fas fa-check-circle mr-2 text-lg"></i>
                                            <span>Autorizar con Zoho y Generar Refresh Token Automáticamente</span>
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                        <p class="text-xs text-teal-600 mt-2 text-center">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Asegúrate de haber configurado la Redirect URI en Zoho API Console antes de hacer clic
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Campo de Refresh Token (se llena automáticamente) -->
                                <div class="mb-2">
                                    <textarea id="zoho_refresh_token" 
                                              name="zoho_refresh_token" 
                                              rows="3"
                                              placeholder="1000.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX (se llenará automáticamente después de autorizar)"
                                              class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 p-2.5 font-mono text-xs">{{ old('zoho_refresh_token', $settings->zoho_refresh_token ?? '') }}</textarea>
                                    @if(!empty($settings->zoho_refresh_token))
                                        <p class="text-xs text-green-600 mt-1">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Refresh Token configurado correctamente
                                        </p>
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

                            <!-- From Email -->
                            <div>
                                <label for="zoho_from_email" class="block mb-2 text-sm font-medium text-gray-900">
                                    Email Remitente <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="zoho_from_email" 
                                       name="zoho_from_email" 
                                       value="{{ old('zoho_from_email', $settings->zoho_from_email ?? '') }}"
                                       placeholder="noreply@tudominio.com"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Email verificado y autorizado en tu cuenta de Zoho Mail. Este será el remitente de todos los correos.
                                </p>
                            </div>
                            
                            <!-- Verificación -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                                <p class="text-sm text-green-800 mb-2">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Verificación de Configuración:</strong>
                                </p>
                                <ul class="text-xs text-green-700 list-disc list-inside space-y-1 ml-2">
                                    <li>Client ID y Client Secret copiados correctamente desde Zoho API Console</li>
                                    <li>Redirect URI configurada en Zoho API Console: <code class="bg-green-100 px-1 rounded">{{ url('/admin/settings') }}</code></li>
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
                                        <tr class="bg-white border-b hover:bg-gray-50">
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
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $log->provider === 'zoho' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ strtoupper($log->provider) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($log->status === 'sent')
                                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                        <i class="fas fa-check-circle mr-1"></i> Enviado
                                                    </span>
                                                @elseif($log->status === 'failed')
                                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                        <i class="fas fa-times-circle mr-1"></i> Fallido
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                        <i class="fas fa-clock mr-1"></i> Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($log->is_test)
                                                    <span class="px-2 py-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full">
                                                        <i class="fas fa-flask mr-1"></i> Prueba
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                        Normal
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <button type="button" 
                                                        onclick="showEmailDetails({{ $log->id }})"
                                                        class="text-teal-600 hover:text-teal-700 text-sm">
                                                    <i class="fas fa-eye mr-1"></i> Ver
                                                </button>
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
                    Gestiona las plantillas de correo electrónico del sistema.
                </p>

                <div class="space-y-6">
                    @foreach($emailTemplates as $template)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">{{ $template->name }}</h4>
                            <p class="text-sm text-gray-600 mb-4">{{ $template->description }}</p>

                            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="section" value="email_template">
                                <input type="hidden" name="template_id" value="{{ $template->id }}">

                                <div>
                                    <label for="subject_{{ $template->id }}" class="block mb-2 text-sm font-medium text-gray-900">
                                        Asunto
                                    </label>
                                    <input type="text" 
                                           id="subject_{{ $template->id }}" 
                                           name="subject" 
                                           value="{{ old('subject', $template->subject) }}"
                                           required
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                                </div>

                                <div>
                                    <label for="body_{{ $template->id }}" class="block mb-2 text-sm font-medium text-gray-900">
                                        Cuerpo del Mensaje
                                    </label>
                                    <textarea id="body_{{ $template->id }}" 
                                              name="body" 
                                              rows="8"
                                              required
                                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('body', $template->body) }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Puedes usar variables: {name}, {email}, {link}, etc.
                                    </p>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">
                                        <i class="fas fa-save mr-2"></i> Guardar Plantilla
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach

                    @if($emailTemplates->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                            <p>No hay plantillas configuradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
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

// Función para mostrar detalles del correo
function showEmailDetails(logId) {
    // Hacer una petición AJAX para obtener los detalles del correo
    fetch(`/admin/settings/email-logs/${logId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const log = data.log;
                const modal = `
                    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="email-detail-modal">
                        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                            <div class="mt-3">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Detalles del Correo</h3>
                                    <button onclick="closeEmailDetails()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div><strong>Fecha:</strong> ${new Date(log.created_at).toLocaleString('es-ES')}</div>
                                    <div><strong>Destinatario:</strong> ${log.to}</div>
                                    <div><strong>Remitente:</strong> ${log.from_email} (${log.from_name || 'Sin nombre'})</div>
                                    <div><strong>Asunto:</strong> ${log.subject}</div>
                                    <div><strong>Proveedor:</strong> ${log.provider.toUpperCase()}</div>
                                    <div><strong>Estado:</strong> ${log.status === 'sent' ? '✅ Enviado' : log.status === 'failed' ? '❌ Fallido' : '⏳ Pendiente'}</div>
                                    ${log.error_message ? `<div class="text-red-600 mt-2 p-3 bg-red-50 rounded border border-red-200"><strong>Error:</strong><br><pre class="mt-2 text-xs whitespace-pre-wrap">${log.error_message}</pre></div>` : ''}
                                    <div class="border-t pt-3">
                                        <strong>Cuerpo del Mensaje:</strong>
                                        <div class="mt-2 p-3 bg-gray-50 rounded border max-h-64 overflow-y-auto">
                                            ${log.body}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button onclick="closeEmailDetails()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modal);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del correo');
        });
}

function closeEmailDetails() {
    const modal = document.getElementById('email-detail-modal');
    if (modal) {
        modal.remove();
    }
}

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
    const authorizeBtn = document.getElementById('zoho-authorize-btn');
    const warningBox = document.getElementById('zoho-warning-box');
    
    if (!clientId || !clientSecret || !authorizeBtn) {
        return; // Los elementos no existen (no estamos en el tab de Zoho)
    }
    
    const hasClientId = clientId.value.trim().length > 0;
    const hasClientSecret = clientSecret.value.trim().length > 0;
    
    if (hasClientId && hasClientSecret) {
        // Habilitar botón
        authorizeBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        authorizeBtn.classList.add('hover:bg-teal-700', 'cursor-pointer');
        authorizeBtn.style.pointerEvents = 'auto';
        authorizeBtn.style.opacity = '1';
        
        // Ocultar advertencia
        if (warningBox) {
            warningBox.style.display = 'none';
        }
    } else {
        // Deshabilitar botón
        authorizeBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        authorizeBtn.classList.remove('hover:bg-teal-700', 'cursor-pointer');
        authorizeBtn.style.pointerEvents = 'none';
        authorizeBtn.style.opacity = '0.5';
        
        // Mostrar advertencia
        if (warningBox) {
            warningBox.style.display = 'block';
        }
    }
}

// Función para validar antes de hacer clic
function validateZohoCredentials(event) {
    const clientId = document.getElementById('zoho_client_id');
    const clientSecret = document.getElementById('zoho_client_secret');
    
    if (!clientId || !clientSecret) {
        event.preventDefault();
        alert('Los campos de Client ID y Client Secret no están disponibles.');
        return false;
    }
    
    const hasClientId = clientId.value.trim().length > 0;
    const hasClientSecret = clientSecret.value.trim().length > 0;
    
    if (!hasClientId || !hasClientSecret) {
        event.preventDefault();
        alert('Por favor, completa el Client ID y Client Secret antes de continuar.');
        return false;
    }
    
    // Recordar al usuario sobre la Redirect URI
    const redirectUri = '{{ route("admin.settings.zoho.callback") }}';
    const confirmMessage = 'IMPORTANTE: Asegúrate de haber configurado esta Redirect URI en Zoho API Console:\n\n' + 
                          redirectUri + 
                          '\n\n¿Ya configuraste la Redirect URI en Zoho API Console?';
    
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
@endpush
