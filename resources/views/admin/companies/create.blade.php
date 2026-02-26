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
        <form action="{{ route('admin.companies.store') }}" method="POST">
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

                <!-- Nombre Contacto -->
                <div>
                    <label for="contact_person_name" class="block mb-2 text-sm font-medium text-gray-900">
                        Nombre de Contacto
                    </label>
                    <input type="text" 
                           id="contact_person_name" 
                           name="contact_person_name" 
                           value="{{ old('contact_person_name') }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- Email Contacto -->
                <div>
                    <label for="contact_person_email" class="block mb-2 text-sm font-medium text-gray-900">
                        Email de Contacto
                    </label>
                    <input type="email" 
                           id="contact_person_email" 
                           name="contact_person_email" 
                           value="{{ old('contact_person_email') }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 @error('contact_person_email') border-red-500 @enderror">
                    @error('contact_person_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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

                <!-- Enviar correo de invitación -->
                <div class="md:col-span-2">
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="send_invite_email" 
                               name="send_invite_email" 
                               value="1"
                               {{ old('send_invite_email') ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <label for="send_invite_email" class="ml-2 block text-sm text-gray-900">
                            <span class="font-medium">Enviar correo de invitación para registro</span>
                            <span class="block text-gray-500 mt-0.5">Se enviará un enlace de un solo uso al <strong>email de contacto</strong> para que el cliente se registre y acceda al sistema. Debe tener email de contacto.</span>
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
