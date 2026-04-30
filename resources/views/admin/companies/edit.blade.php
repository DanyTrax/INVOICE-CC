@extends('layouts.admin-flowbite')

@section('title', 'Editar Empresa - RAMS')

@section('page-title', 'Editar Empresa')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.companies.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Clientes</a>
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
        <form action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">
                        Nombre de la Empresa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $company->name) }}"
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
                           value="{{ old('nit_rut', $company->nit_rut) }}"
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
                           value="{{ old('address', $company->address) }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- País -->
                <div>
                    <label for="country" class="block mb-2 text-sm font-medium text-gray-900">
                        País
                    </label>
                    @include('admin.companies.partials.country-selector', ['countries' => $countries ?? [], 'value' => old('country', $company->country)])
                    <p class="mt-1 text-xs text-gray-500">Seleccione un país de la lista. Si cambia el país, la carpeta en Drive (y todas sus solicitudes) se moverá al nuevo país.</p>
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
                           value="{{ old('phone', $company->phone) }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>

                <!-- Logo (se guarda en base de datos en base64; incluido en respaldos JSON) -->
                <div class="md:col-span-2">
                    <label for="company_logo" class="block mb-2 text-sm font-medium text-gray-900">
                        Logo de la empresa
                    </label>
                    @if($company->hasLogo())
                        <div class="flex items-center gap-4 mb-2">
                            <img src="{{ $company->logoSrcForImg() }}" alt="Logo" class="h-14 w-auto max-w-[200px] object-contain border border-gray-200 rounded-lg p-2 bg-white">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                Quitar logo
                            </label>
                        </div>
                    @endif
                    <input type="file" name="logo" id="company_logo" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF, WebP o SVG. Máx. 2&nbsp;MB. Se almacena en la base de datos para incluirse en copias de seguridad.</p>
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @php
                    $assignments = old('client_assignments');
                    if ($assignments === null) {
                        $assignments = $company->users->filter(fn ($u) => $u->hasRole('client'))->map(function ($u) {
                            return ['user_id' => $u->id, 'description' => $u->pivot->description ?? ''];
                        })->values()->all();
                        if (count($assignments) === 0) {
                            $assignments = [['user_id' => '', 'description' => '']];
                        }
                    }
                @endphp
                @include('admin.companies.partials.client-assignments', [
                    'clientUsers' => $clientUsers,
                    'assignments' => $assignments,
                ])

                <!-- Drive Folder ID -->
                <div class="md:col-span-2">
                    <label for="drive_folder_id" class="block mb-2 text-sm font-medium text-gray-900">
                        ID de Carpeta Google Drive
                    </label>
                    <input type="text" 
                           id="drive_folder_id" 
                           name="drive_folder_id" 
                           value="{{ old('drive_folder_id', $company->drive_folder_id) }}"
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
                               {{ old('allows_loans', $company->allows_loans) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <label for="allows_loans" class="ml-2 block text-sm text-gray-900">
                            <span class="font-medium">¿Este cliente opera con Préstamos de Tasa INVIMA?</span>
                            <span class="block text-gray-500 mt-0.5">Si está marcado, en las cotizaciones se podrá agregar ítems de préstamo (suplidos).</span>
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
                    <i class="fas fa-save mr-2"></i> Actualizar Empresa
                </button>
            </div>
        </form>
    </div>
@endsection
