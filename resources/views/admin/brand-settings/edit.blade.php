@extends('layouts.admin-flowbite')

@section('title', 'Marca Blanca')

@section('page-title', 'Marca Blanca')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.brand-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <section>
                <h4 class="text-sm font-semibold text-gray-800 uppercase mb-4">Datos de la organización</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm font-medium">Razón social *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $setting->company_name) }}" required class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">NIT</label>
                        <input type="text" name="nit" value="{{ old('nit', $setting->nit) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-medium">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $setting->address) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Ciudad</label>
                        <input type="text" name="city" value="{{ old('city', $setting->city) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $setting->phone) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Correo institucional</label>
                        <input type="email" name="email" value="{{ old('email', $setting->email) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Correo de soporte</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $setting->support_email) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Logo</label>
                        @if($setting->logo_path)
                            <img src="{{ $setting->logoUrl() }}" alt="Logo" class="h-16 mb-2 object-contain">
                            <label class="text-sm"><input type="checkbox" name="remove_logo" value="1"> Quitar logo</label>
                        @endif
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml,.jpg,.jpeg,.png,.gif,.webp,.svg" class="mt-2 block w-full text-sm">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF, WebP o SVG. Máximo 2 MB.</p>
                    </div>
                </div>
            </section>

            <section>
                <h4 class="text-sm font-semibold text-gray-800 uppercase mb-4">Datos bancarios</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label class="block mb-2 text-sm font-medium">Banco</label><input type="text" name="bank_name" value="{{ old('bank_name', $setting->bank_name) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5"></div>
                    <div><label class="block mb-2 text-sm font-medium">Tipo de cuenta</label><input type="text" name="bank_account_type" value="{{ old('bank_account_type', $setting->bank_account_type) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5"></div>
                    <div><label class="block mb-2 text-sm font-medium">Número</label><input type="text" name="bank_account_number" value="{{ old('bank_account_number', $setting->bank_account_number) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5"></div>
                </div>
            </section>

            <section>
                <h4 class="text-sm font-semibold text-gray-800 uppercase mb-4">Firma del tesorero (PDF)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="block mb-2 text-sm font-medium">Título bajo la firma</label><input type="text" name="treasurer_signature_title" value="{{ old('treasurer_signature_title', $setting->treasurer_signature_title) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5"></div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Imagen de firma</label>
                        @if($setting->treasurer_signature_path)
                            <img src="{{ asset('storage/'.$setting->treasurer_signature_path) }}" alt="Firma" class="h-16 mb-2 object-contain">
                            <label class="text-sm"><input type="checkbox" name="remove_treasurer_signature" value="1"> Quitar firma</label>
                        @endif
                        <input type="file" name="treasurer_signature" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml,.jpg,.jpeg,.png,.gif,.webp,.svg" class="mt-2 block w-full text-sm">
                    </div>
                </div>
            </section>

            <section>
                <h4 class="text-sm font-semibold text-gray-800 uppercase mb-4">Plantilla de correo (cuenta de cobro)</h4>
                <p class="text-xs text-gray-500 mb-3">Variables: @{{nombre}}, @{{concepto}}, @{{numero}}, @{{valor}}</p>
                <div class="space-y-4">
                    <div><label class="block mb-2 text-sm font-medium">Asunto</label><input type="text" name="invoice_email_subject" value="{{ old('invoice_email_subject', $setting->invoice_email_subject) }}" class="bg-gray-50 border border-gray-300 rounded-lg w-full p-2.5"></div>
                    <div>
                        <label class="block mb-2 text-sm font-medium">Cuerpo del mensaje</label>
                        <div id="invoice_email_body_editor" class="bg-white border border-gray-300 rounded-lg min-h-[180px]"></div>
                        <input type="hidden" name="invoice_email_body" id="invoice_email_body" value="{{ old('invoice_email_body', $setting->invoice_email_body) }}">
                    </div>
                </div>
            </section>

            <div class="flex justify-end pt-4 border-t">
                <button type="submit" class="text-white bg-teal-700 hover:bg-teal-800 font-medium rounded-lg text-sm px-5 py-2.5">Guardar</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hidden = document.getElementById('invoice_email_body');
    const quill = new Quill('#invoice_email_body_editor', { theme: 'snow' });
    if (hidden.value) quill.root.innerHTML = hidden.value;
    document.querySelector('form').addEventListener('submit', function () { hidden.value = quill.root.innerHTML; });
});
</script>
@endpush
