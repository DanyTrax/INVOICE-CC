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

                <div>
                    <label for="code_abbreviation" class="block mb-2 text-sm font-medium text-gray-900">
                        Siglas de solicitud <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text"
                               id="code_abbreviation"
                               name="code_abbreviation"
                               value="{{ old('code_abbreviation', $company->code_abbreviation) }}"
                               required
                               maxlength="10"
                               pattern="[A-Za-z]{2,10}"
                               autocomplete="off"
                               placeholder="Ej: PG, PEG"
                               title="Solo letras, 2 a 10 caracteres"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full max-w-xs p-2.5 uppercase @error('code_abbreviation') border-red-500 @enderror">
                        <button type="button" id="btn_suggest_abbr"
                                class="shrink-0 px-3 py-2 text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100">
                            Sugerir desde el nombre
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Código visible en solicitudes (<span class="font-mono">PG-001</span>, etc.). Si cambia las siglas, las solicitudes ya creadas conservan su número.</p>
                    @error('code_abbreviation')
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

@push('scripts')
<script>
(function () {
    function suggestFromName(name) {
        name = name.trim().replace(/\s+/g, ' ');
        if (!name) return '';
        const parts = name.split(/\s+/).filter(Boolean);
        if (!parts.length) return '';
        if (parts.length === 1) {
            const only = parts[0];
            const letters = only.replace(/[^a-zA-ZÀ-ÿ]/g, '');
            const src = letters || only;
            return src.slice(0, 3).toUpperCase();
        }
        let out = '';
        for (const p of parts.slice(0, 4)) {
            const letters = p.replace(/[^a-zA-ZÀ-ÿ]/g, '');
            out += letters ? letters[0] : (p[0] || '');
        }
        return out.slice(0, 3).toUpperCase();
    }
    const nameEl = document.getElementById('name');
    const abbrEl = document.getElementById('code_abbreviation');
    const btn = document.getElementById('btn_suggest_abbr');
    if (!nameEl || !abbrEl) return;
    btn && btn.addEventListener('click', function () {
        abbrEl.value = suggestFromName(nameEl.value);
    });
})();
</script>
@endpush
