<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Associate;
use App\Models\Concept;
use App\Models\Invoice;
use App\Services\PermissionService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $perm = app(PermissionService::class);

        $stats = [
            'associates' => $perm->userHasPermission('associates', 'view') ? Associate::count() : null,
            'concepts' => $perm->userHasPermission('concepts', 'view') ? Concept::count() : null,
            'invoices_total' => $perm->userHasPermission('invoices', 'view') ? Invoice::count() : null,
            'invoices_draft' => $perm->userHasPermission('invoices', 'view') ? Invoice::where('status', Invoice::STATUS_DRAFT)->count() : null,
            'invoices_sent' => $perm->userHasPermission('invoices', 'view') ? Invoice::where('status', Invoice::STATUS_SENT)->count() : null,
            'invoices_paid' => $perm->userHasPermission('invoices', 'view') ? Invoice::where('status', Invoice::STATUS_PAID)->count() : null,
            'amount_month' => $perm->userHasPermission('invoices', 'view')
                ? (float) Invoice::query()
                    ->whereMonth('issue_date', now()->month)
                    ->whereYear('issue_date', now()->year)
                    ->sum('total_amount')
                : null,
            'amount_pending' => $perm->userHasPermission('invoices', 'view')
                ? (float) Invoice::query()
                    ->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_SENT])
                    ->sum('total_amount')
                : null,
        ];

        $recentInvoices = $perm->userHasPermission('invoices', 'view')
            ? Invoice::query()->with(['associate', 'concept'])->latest()->limit(6)->get()
            : collect();

        $monthlySeries = $perm->userHasPermission('invoices', 'view')
            ? $this->monthlyInvoiceSeries(6)
            : collect();

        return view('admin.dashboard.index', compact('stats', 'recentInvoices', 'monthlySeries'));
    }

    /**
     * @return Collection<int, array{label: string, count: int, amount: float}>
     */
    private function monthlyInvoiceSeries(int $months): Collection
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $rows = Invoice::query()
            ->where('issue_date', '>=', $start->toDateString())
            ->selectRaw('YEAR(issue_date) as y, MONTH(issue_date) as m, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as amt')
            ->groupByRaw('YEAR(issue_date), MONTH(issue_date)')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->y, $row->m));

        $series = collect();
        for ($i = 0; $i < $months; $i++) {
            $date = $start->copy()->addMonths($i);
            $key = $date->format('Y-m');
            $row = $rows->get($key);
            $series->push([
                'label' => $date->locale('es')->translatedFormat('M Y'),
                'count' => (int) ($row->cnt ?? 0),
                'amount' => (float) ($row->amt ?? 0),
            ]);
        }

        return $series;
    }
}
