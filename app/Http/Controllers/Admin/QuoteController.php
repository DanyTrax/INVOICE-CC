<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Listado de cotizaciones.
     */
    public function index(Request $request)
    {
        $query = Quote::with(['client', 'quoteItems.serviceType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('consecutive', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotes = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }
}
