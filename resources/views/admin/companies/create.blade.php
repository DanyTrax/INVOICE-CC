@extends('layouts.admin-flowbite')

@section('title', 'Nueva Empresa - RAMS')

@section('page-title', 'Nueva Empresa')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.companies.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Empresas</a>
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
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">
                        Nombre de la Empresa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIT/RUT -->
                <div>
                    <label for="nit_rut" class="block mb-2 text-sm font-medium text-gray-900">
                        NIT/RUT <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nit_rut" 
                           name="nit_rut" 
                           value="{{ old('nit_rut') }}"
                           required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('nit_rut') border-red-500 @enderror">
                    @error('nit_rut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dirección -->
                <div>
                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900">
                        Dirección
                    </label>
                    <input type="text" 
                           id="address" 
                           name="address" 
                           value="{{ old('address') }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- País -->
                <div>
                    <label for="country" class="block mb-2 text-sm font-medium text-gray-900">
                        País
                    </label>
                    @include('admin.companies.partials.country-selector', ['countries' => $countries ?? [], 'value' => old('country')])
                    <p class="mt-1 text-xs text-gray-500">Seleccione un país de la lista (escriba para buscar, ej: mex o col); en Drive la empresa quedará en Base → País → Empresa.</p>
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">
                        Teléfono
                    </label>
                    <input type="text" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <div class="md:col-span-2">
                    <label for="company_logo" class="block mb-2 text-sm font-medium text-gray-900">
                        Logo de la empresa (opcional)
                    </label>
                    <input type="file" name="logo" id="company_logo" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF, WebP o SVG. Máx. 2&nbsp;MB. Se guarda en la base de datos (base64) para respaldos.</p>
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.companies.partials.client-assignments', [
                    'clientUsers' => $clientUsers,
                    'assignments' => old('client_assignments', [['user_id' => '', 'description' => '']]),
                ])

                <!-- Drive Folder ID -->
                <div class="md:col-span-2">
                    <label for="drive_folder_id" class="block mb-2 text-sm font-medium text-gray-900">
                        ID de Carpeta Google Drive
                    </label>
                    <input type="text" 
                           id="drive_folder_id" 
                           name="drive_folder_id" 
                           value="{{ old('drive_folder_id') }}"
                           placeholder="Opcional: ID de la carpeta en Google Drive"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- Préstamos de Tasa INVIMA -->
                <div class="md:col-span-2">
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="allows_loans" 
                               name="allows_loans" 
                               value="1"
                               {{ old('allows_loans') ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <label for="allows_loans" class="ml-2 block text-sm text-gray-900">
                            <span class="font-medium">¿Este cliente opera con Préstamos de Tasa INVIMA?</span>
                            <span class="block text-gray-500 mt-0.5">Si está marcado, en las cotizaciones se podrá agregar ítems de préstamo (suplidos).</span>
                        </label>
                    </div>
                </div>

                <!-- Invitar correo externo (aún no es usuario) -->
                <div class="md:col-span-2">
                    <label for="invite_email" class="block mb-2 text-sm font-medium text-gray-900">
                        Invitar por correo (opcional)
                    </label>
                    <input type="email"
                           id="invite_email"
                           name="invite_email"
                           value="{{ old('invite_email') }}"
                           placeholder="correo@ejemplo.com — si aún no existe como usuario cliente"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full max-w-xl p-2.5 @error('invite_email') border-red-500 @enderror">
                    @error('invite_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="flex items-start mt-3">
                        <input type="checkbox"
                               id="send_invite_email"
                               name="send_invite_email"
                               value="1"
                               {{ old('send_invite_email') ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <label for="send_invite_email" class="ml-2 block text-sm text-gray-900">
                            <span class="font-medium">Enviar correo de invitación al correo indicado</span>
                            <span class="block text-gray-500 mt-0.5">Solo si rellenó el campo de arriba: se envía un enlace de un solo uso para registro. Los clientes ya creados se asignan arriba sin necesidad de invitación.</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.companies.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    <i class="fas fa-save mr-2"></i> Guardar Empresa
                </button>
            </div>
        </form>
    </div>
@endsection
