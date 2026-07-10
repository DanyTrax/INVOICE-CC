@extends('layouts.admin-flowbite')

@section('title', 'Cuentas de cobro')
@section('page-title', 'Cuentas de cobro')

@section('content')
    @include('admin.partials.flash')

    <div class="flex flex-wrap gap-3 justify-between items-center mb-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <input type="text" name="q" value="{{ request('q') }}" class="border border-gray-300 rounded-lg p-2 text-sm" placeholder="Número o asociado">
            <select name="status" class="border border-gray-300 rounded-lg p-2 text-sm">
                <option value="">Todos los estados</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <label class="text-xs text-gray-500">
                Vence desde
                <input type="date" name="due_from" value="{{ request('due_from') }}" class="border border-gray-300 rounded-lg p-2 text-sm block mt-0.5">
            </label>
            <label class="text-xs text-gray-500">
                Vence hasta
                <input type="date" name="due_to" value="{{ request('due_to') }}" class="border border-gray-300 rounded-lg p-2 text-sm block mt-0.5">
            </label>
            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Filtrar</button>
        </form>
        <a href="{{ route('admin.invoices.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm">Nueva cuenta de cobro</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Número</th>
                    <th class="px-4 py-3 text-left">Asociado</th>
                    <th class="px-4 py-3 text-left">Concepto</th>
                    <th class="px-4 py-3 text-left">Vencimiento</th>
                    <th class="px-4 py-3 text-left">Valor</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3 font-medium">{{ $invoice->number }}</td>
                        <td class="px-4 py-3">{{ $invoice->associate->full_name }}</td>
                        <td class="px-4 py-3">{{ $invoice->concept->name }}</td>
                        <td class="px-4 py-3">{{ $invoice->due_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">${{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $invoice->statusLabel() }}</td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-teal-700">Ver</a>
                            @if($canDelete ?? false)
                                <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar la cuenta de cobro {{ $invoice->number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No hay cuentas de cobro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection
