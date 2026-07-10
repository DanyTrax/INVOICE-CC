@extends('layouts.admin-flowbite')

@section('title', 'Dashboard - Recaudos')

@section('page-title', 'Dashboard de Recaudos')

@section('content')
    @if(! auth()->user()->hasTwoFactorEnabled())
        @include('profile.partials.two-factor-setup-wizard', ['routePrefix' => 'admin', 'variant' => 'banner'])
    @endif

    @php
        $perm = app(\App\Services\PermissionService::class);
        $fmtMoney = fn ($n) => '$' . number_format((float) $n, 0, ',', '.');
        $maxMonthly = max(1, (int) ($monthlySeries->max('count') ?? 1));
    @endphp

    @if($perm->userHasPermission('invoices', 'view'))
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Recaudo del mes</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-slate-100">{{ $fmtMoney($stats['amount_month'] ?? 0) }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">
                        <i class="fas fa-coins"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-slate-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Pendiente de cobro</p>
                        <p class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $fmtMoney($stats['amount_pending'] ?? 0) }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                        <i class="fas fa-hourglass-half"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-slate-400">{{ ($stats['invoices_draft'] ?? 0) + ($stats['invoices_sent'] ?? 0) }} cuentas en borrador o enviadas</p>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Cuentas pagadas</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $stats['invoices_paid'] ?? 0 }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        <i class="fas fa-check-circle"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-slate-400">de {{ $stats['invoices_total'] ?? 0 }} emitidas</p>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Asociados activos</p>
                        <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $stats['associates'] ?? '—' }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        <i class="fas fa-users"></i>
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-slate-400">{{ $stats['concepts'] ?? '—' }} conceptos de cobro</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="xl:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Cuentas emitidas por mes</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Últimos 6 meses</p>
                    </div>
                </div>
                <div class="flex items-end justify-between gap-2 h-44">
                    @foreach($monthlySeries as $point)
                        @php $h = max(8, (int) round(($point['count'] / $maxMonthly) * 100)); @endphp
                        <div class="flex-1 flex flex-col items-center gap-2 min-w-0">
                            <span class="text-xs font-medium text-gray-600 dark:text-slate-300">{{ $point['count'] }}</span>
                            <div class="w-full max-w-[3rem] rounded-t-lg bg-gradient-to-t from-teal-600 to-teal-400 dark:from-teal-700 dark:to-teal-500 transition-all"
                                 style="height: {{ $h }}%"></div>
                            <span class="text-[10px] sm:text-xs text-gray-500 dark:text-slate-400 text-center truncate w-full">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Estado de cuentas</h2>
                @php
                    $total = max(1, (int) ($stats['invoices_total'] ?? 0));
                    $statusRows = [
                        ['label' => 'Borrador', 'count' => $stats['invoices_draft'] ?? 0, 'color' => 'bg-gray-400'],
                        ['label' => 'Enviadas', 'count' => $stats['invoices_sent'] ?? 0, 'color' => 'bg-blue-500'],
                        ['label' => 'Pagadas', 'count' => $stats['invoices_paid'] ?? 0, 'color' => 'bg-emerald-500'],
                    ];
                @endphp
                <div class="space-y-4">
                    @foreach($statusRows as $row)
                        @php $pct = round(($row['count'] / $total) * 100); @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-slate-300">{{ $row['label'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-slate-100">{{ $row['count'] }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-slate-700 overflow-hidden">
                                <div class="h-full rounded-full {{ $row['color'] }}" style="width: {{ max($row['count'] > 0 ? 4 : 0, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($perm->userHasPermission('invoices', 'edit'))
                    <a href="{{ route('admin.invoices.create') }}"
                       class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium py-2.5">
                        <i class="fas fa-plus"></i> Nueva cuenta de cobro
                    </a>
                @endif
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Últimas cuentas de cobro</h2>
                <a href="{{ route('admin.invoices.index') }}" class="text-sm text-teal-700 dark:text-teal-400 hover:underline">Ver todas</a>
            </div>
            @if($recentInvoices->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-slate-400">
                    <i class="fas fa-file-invoice text-3xl mb-2 opacity-40"></i>
                    <p>Aún no hay cuentas de cobro registradas.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-slate-900/50 text-gray-500 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3">Número</th>
                                <th class="px-6 py-3">Asociado</th>
                                <th class="px-6 py-3">Concepto</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentInvoices as $invoice)
                                <tr class="border-t border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/40">
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-slate-100">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="hover:text-teal-700 dark:hover:text-teal-400">{{ $invoice->number }}</a>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-slate-300">{{ $invoice->associate?->full_name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-slate-300">{{ $invoice->concept?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-slate-100">{{ $fmtMoney($invoice->total_amount) }}</td>
                                    <td class="px-6 py-3">
                                        @php
                                            $badge = match($invoice->status) {
                                                'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                                'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                                default => 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-300',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $invoice->statusLabel() }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-slate-400">{{ $invoice->issue_date?->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @else
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-8 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center mb-4">
                <i class="fas fa-file-invoice-dollar text-2xl text-teal-700 dark:text-teal-300"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-slate-100 mb-2">Bienvenido al panel</h2>
            <p class="text-gray-600 dark:text-slate-400 max-w-lg mx-auto">
                Use el menú superior para acceder a los módulos disponibles según sus permisos.
            </p>
        </div>
    @endif
@endsection
