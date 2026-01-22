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
                <button onclick="showTab('agency')" 
                        id="tab-agency"
                        class="tab-button active px-6 py-3 text-sm font-medium border-b-2 border-teal-600 text-teal-600">
                    <i class="fas fa-building mr-2"></i> Datos de la Empresa
                </button>
                <button onclick="showTab('drive')" 
                        id="tab-drive"
                        class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-cloud mr-2"></i> Conexión Google Drive
                </button>
                <button onclick="showTab('mail')" 
                        id="tab-mail"
                        class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-envelope mr-2"></i> Correo & SMTP
                </button>
                <button onclick="showTab('templates')" 
                        id="tab-templates"
                        class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-file-alt mr-2"></i> Plantillas de Email
                </button>
            </nav>
        </div>

        <!-- Tab 1: Datos de la Empresa -->
        <div id="panel-agency" class="tab-panel">
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
        <div id="panel-mail" class="tab-panel hidden">
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
                                   required
                                   placeholder="RAMS Sistema"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
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

        <!-- Tab 4: Plantillas de Email -->
        <div id="panel-templates" class="tab-panel hidden">
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
    
    // Remover active de todos los tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-teal-600', 'text-teal-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Mostrar el panel seleccionado
    document.getElementById('panel-' + tabName).classList.remove('hidden');
    
    // Activar el tab seleccionado
    const tabButton = document.getElementById('tab-' + tabName);
    tabButton.classList.add('active', 'border-teal-600', 'text-teal-600');
    tabButton.classList.remove('border-transparent', 'text-gray-500');
}

// Mostrar el primer tab por defecto
document.addEventListener('DOMContentLoaded', function() {
    showTab('agency');
    toggleProviderFields();
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
</script>
@endpush
