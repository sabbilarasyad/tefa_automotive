<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SparepartStockAdjustmentRequest;
use App\Http\Requests\Api\StoreSparepartRequest;
use App\Http\Requests\Api\UpdateSparepartRequest;
use App\Models\Sparepart;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;

class SparepartController extends Controller
{
    public function __construct(private StockService $stocks) {}

    public function index(): JsonResponse
    {
        $this->authorize('manageSpareparts', Sparepart::class);

        $query = Sparepart::query()
            ->where('is_active', true)
            ->withCount('stockMovements')
            ->orderBy('name');

        if (request()->boolean('low_stock')) {
            $query->whereRaw('stock_qty <= min_stock');
        }

        $spareparts = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $spareparts,
        ]);
    }

    public function show(Sparepart $sparepart): JsonResponse
    {
        $this->authorize('manageSpareparts', $sparepart);

        return response()->json([
            'success' => true,
            'data' => $sparepart->load('stockMovements.creator'),
        ]);
    }

    public function stockAdjustment(SparepartStockAdjustmentRequest $request, Sparepart $sparepart): JsonResponse
    {
        $data = $request->validated();

        $movement = $this->stocks->adjustStock(
            $sparepart,
            $data['qty'],
            $data['type'],
            $request->user(),
            $data['reason'],
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil disesuaikan.',
            'data' => $movement->load('sparepart', 'creator'),
        ], 201);
    }

    public function masterIndex(): JsonResponse
    {
        $this->authorize('manageSpareparts', Sparepart::class);
        
        $spareparts = \App\Models\Sparepart::query()
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $spareparts,
        ]);
    }

    public function masterStore(StoreSparepartRequest $request): JsonResponse
    {
        $this->authorize('manageSpareparts', Sparepart::class);
        
        $sparepart = \App\Models\Sparepart::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sparepart berhasil ditambahkan.',
            'data' => $sparepart,
        ], 201);
    }

    public function masterShow(\App\Models\Sparepart $sparepart): JsonResponse
    {
        $this->authorize('manageSpareparts', $sparepart);

        return response()->json([
            'success' => true,
            'data' => $sparepart,
        ]);
    }

    public function masterUpdate(UpdateSparepartRequest $request, \App\Models\Sparepart $sparepart): JsonResponse
    {
        $this->authorize('manageSpareparts', $sparepart);
        
        $sparepart->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sparepart berhasil diperbarui.',
            'data' => $sparepart,
        ]);
    }

    public function masterDestroy(\App\Models\Sparepart $sparepart): JsonResponse
    {
        $this->authorize('manageSpareparts', $sparepart);
        
        $sparepart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sparepart berhasil dihapus.',
        ]);
    }
}