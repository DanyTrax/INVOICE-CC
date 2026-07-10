@extends('layouts.admin-flowbite')

@section('title', 'Cuenta '.$invoice->number)
@section('page-title', 'Cuenta de cobro '.$invoice->number)

@section('content')
    @include('admin.partials.flash')

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-wrap gap-3 justify-between items-start mb-6">
            <div>
                <p class="text-sm text-gray-500">Estado: <span class="font-semibold text-gray-800">{{ $invoice->statusLabel() }}</span></p>
                <p class="text-sm text-gray-500">Asociado: <span class="font-semibold text-gray-800">{{ $invoice->associate->full_name }}</span></p>
                <p class="text-sm text-gray-500">Concepto: <span class="font-semibold text-gray-800">{{ $invoice->concept->name }}</span></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Descargar PDF</a>
                @if($invoice->associate->email)
                    <form action="{{ route('admin.invoices.send', $invoice) }}" method="POST">
                        @csrf
                        <button class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Enviar por correo</button>
                    </form>
                @endif
                @if($invoice->status !== 'paid')
                    <form action="{{ route('admin.invoices.mark-paid', $invoice) }}" method="POST">
                        @csrf
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Marcar pagada</button>
                    </form>
                @endif
                @if($invoice->isEditable())
                    <a href="{{ route('admin.invoices.edit', $invoice) }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm">Editar</a>
                @endif
                @if($canDelete ?? false)
                    <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('¿Eliminar esta cuenta de cobro? El número podrá reutilizarse.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">Eliminar</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-gray-500">Elaboración</div>
                <div class="font-semibold">{{ $invoice->issue_date->format('d/m/Y') }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-gray-500">Vencimiento</div>
                <div class="font-semibold">{{ $invoice->due_date->format('d/m/Y') }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-gray-500">Valor total</div>
                <div class="font-semibold text-lg">${{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        @if(!$invoice->associate->email)
            <p class="mt-4 text-sm text-amber-700">El asociado no tiene correo; no se puede enviar por email hasta configurarlo.</p>
        @endif
    </div>
@endsection
