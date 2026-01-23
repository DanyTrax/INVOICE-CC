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
                            
                            <!-- Editor Visual (Quill) -->
                            <div id="visual-editor-container" class="border border-gray-300 rounded-lg">
                                <div id="template_body_visual" style="height: 400px;"></div>
                                <textarea id="template_body_visual_hidden" 
                                          name="body_visual" 
                                          class="hidden"></textarea>
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

// Variable global para el editor Quill
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
    fetch(`/admin/settings/templates/${templateId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const template = data.template;
                
                // Llenar campos del formulario
                document.getElementById('template_id').value = template.id;
                document.getElementById('template_name').value = template.name;
                document.getElementById('template_type').value = template.type;
                document.getElementById('template_subject').value = template.subject || '';
                
                // Obtener contenido del body - asegurar que no esté vacío
                let bodyContent = template.body || '';
                
                // Si el body está vacío o solo tiene <p><br></p>, loguear para debugging
                if (!bodyContent || bodyContent.trim() === '' || bodyContent.trim() === '<p><br></p>' || bodyContent.trim() === '<p></p>') {
                    console.warn('Plantilla con body vacío o mínimo:', {
                        id: template.id,
                        type: template.type,
                        bodyLength: bodyContent ? bodyContent.length : 0,
                        bodyPreview: bodyContent ? bodyContent.substring(0, 100) : 'vacío'
                    });
                } else {
                    console.log('Cargando plantilla con contenido:', {
                        id: template.id,
                        type: template.type,
                        bodyLength: bodyContent.length,
                        bodyPreview: bodyContent.substring(0, 200)
                    });
                }
                
                // Llenar el textarea HTML PRIMERO (por si el editor falla)
                document.getElementById('template_body').value = bodyContent;
                
                // Mostrar shortcodes disponibles
                displayAvailableShortcodes(template.type);
                
                // Mostrar modal ANTES de inicializar el editor
                document.getElementById('edit-template-modal').classList.remove('hidden');
                
                // Inicializar editor visual DESPUÉS de mostrar el modal
                // Esto asegura que el contenedor esté visible en el DOM
                setTimeout(function() {
                    initializeVisualEditor(bodyContent);
                }, 300);
            } else {
                alert('Error al cargar la plantilla: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la plantilla. Por favor, recarga la página.');
        });
};

// Inicializar editor visual (Quill)
function initializeVisualEditor(initialContent = '') {
    const container = document.getElementById('template_body_visual');
    
    if (!container) {
        console.error('Contenedor del editor no encontrado');
        return;
    }
    
    // Si ya existe un editor, destruirlo completamente primero
    if (templateEditor) {
        try {
            // Quill no tiene método remove oficial, así que limpiamos manualmente
            // Primero, desconectar todos los eventos
            templateEditor.off('text-change');
            templateEditor.off('selection-change');
            
            // Limpiar el contenedor completamente (esto elimina el toolbar también)
            container.innerHTML = '';
            
            // Anular referencia
            templateEditor = null;
        } catch (e) {
            console.error('Error al destruir editor:', e);
            // Forzar limpieza si hay error
            container.innerHTML = '';
            templateEditor = null;
        }
    } else {
        // Asegurar que el contenedor esté limpio
        container.innerHTML = '';
    }
    
    // Esperar un momento para asegurar que el DOM se actualizó completamente
    setTimeout(function() {
        // Verificar que el contenedor aún existe y está limpio
        if (!container || container.querySelector('.ql-toolbar')) {
            console.warn('El contenedor ya tiene un editor, limpiando...');
            container.innerHTML = '';
        }
        
        try {
            // Configurar Quill
            templateEditor = new Quill(container, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['blockquote', 'code-block'],
                        ['clean']
                    ]
                },
                placeholder: 'Escribe el contenido del correo aquí...',
            });
            
            // Establecer contenido inicial DESPUÉS de crear el editor
            if (initialContent && initialContent.trim() !== '' && initialContent.trim() !== '<p><br></p>' && initialContent.trim() !== '<p></p>') {
                console.log('Estableciendo contenido en Quill, longitud:', initialContent.length);
                
                // Esperar un momento más para asegurar que Quill esté completamente inicializado
                setTimeout(function() {
                    try {
                        // Método 1: Usar clipboard.convert (recomendado para HTML complejo)
                        const delta = templateEditor.clipboard.convert({ html: initialContent });
                        templateEditor.setContents(delta, 'silent'); // 'silent' evita disparar eventos
                        
                        // Verificar que se estableció correctamente
                        const loadedContent = templateEditor.root.innerHTML;
                        console.log('Contenido cargado en Quill, longitud:', loadedContent.length);
                        
                        if (loadedContent.trim() === '' || loadedContent.trim() === '<p><br></p>') {
                            console.warn('Quill no cargó el contenido correctamente, intentando método alternativo');
                            // Método alternativo: establecer directamente el HTML
                            templateEditor.root.innerHTML = initialContent;
                        }
                        
                        // Sincronizar con textarea HTML
                        syncToHtmlEditor();
                    } catch (e) {
                        console.error('Error al establecer contenido en Quill:', e);
                        // Fallback: establecer HTML directamente
                        templateEditor.root.innerHTML = initialContent;
                        syncToHtmlEditor();
                    }
                }, 100);
            } else {
                console.warn('Contenido inicial vacío o inválido:', initialContent);
            }
            
            // Sincronizar con textarea HTML cuando cambia el contenido
            templateEditor.on('text-change', function() {
                syncToHtmlEditor();
            });
        } catch (e) {
            console.error('Error al inicializar Quill:', e);
            // Si falla, mostrar el contenido en el textarea HTML
            document.getElementById('template_body').value = initialContent || '';
        }
    }, 150);
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
    if (templateEditor) {
        const visualContent = templateEditor.root.innerHTML;
        document.getElementById('template_body').value = visualContent;
        document.getElementById('template_body_visual_hidden').value = visualContent;
    }
}

// Sincronizar contenido del textarea HTML al editor visual
function syncToVisualEditor() {
    if (templateEditor) {
        const htmlContent = document.getElementById('template_body').value;
        templateEditor.root.innerHTML = htmlContent;
    }
}

// Cerrar modal
window.closeEditTemplateModal = function() {
    // Destruir editor Quill si existe
    if (templateEditor) {
        try {
            // Desconectar eventos
            templateEditor.off('text-change');
            templateEditor.off('selection-change');
            
            // Limpiar contenedor completamente
            const container = document.getElementById('template_body_visual');
            if (container) {
                container.innerHTML = '';
            }
            
            templateEditor = null;
        } catch (e) {
            console.error('Error al cerrar editor:', e);
            const container = document.getElementById('template_body_visual');
            if (container) {
                container.innerHTML = '';
            }
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
</script>
@endpush
