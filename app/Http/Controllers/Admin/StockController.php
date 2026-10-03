<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestockSparepartRequest;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private StockService $stocks) {}

    public function index(Request $request): View
    {
        $this->authorize('manageSpareparts', Sparepart::class);

        $query = Sparepart::query()
            ->where('is_active', true)
            ->withCount('stockMovements')
            ->orderBy('name');

        $filter = $request->query('filter');
        if ($filter === 'low') {
            $query->whereRaw('stock_qty <= min_stock');
        } elseif ($filter === 'ok') {
            $query->whereRaw('stock_qty > min_stock');
        }

        $spareparts = $query->paginate(20)->withQueryString();

        $lowStockCount = Sparepart::query()
            ->where('is_active', true)
            ->whereRaw('stock_qty <= min_stock')
            ->count();

        return view('admin.stock.index', compact('spareparts', 'lowStockCount', 'filter'));
    }

    public function restock(RestockSparepartRequest $request, Sparepart $sparepart): RedirectResponse
    {
        $this->authorize('adjustStock', Sparepart::class);

        $this->stocks->adjustStock(
            $sparepart,
            $request->validated('qty'),
            'MANUAL_IN',
            $request->user(),
            $request->validated('reason')
        );

        return back()->with('success', "Stok {$sparepart->name} berhasil ditambah.");
    }

    public function history(Sparepart $sparepart): View
    {
        $this->authorize('manageSpareparts', Sparepart::class);

        $movements = $this->stocks->getMovementsForPart($sparepart, 100);

        return view('admin.stock.history', compact('sparepart', 'movements'));
    }
}