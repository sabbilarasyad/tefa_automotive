<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SparepartStockAdjustmentRequest;
use App\Models\Sparepart;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;

class SparepartController extends Controller
{
    public function __construct(private StockService $stocks) {}

    public function index(): JsonResponse
    {
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
        $spareparts = \App\Models\Sparepart::query()
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $spareparts,
        ]);
    }

    public function masterStore(\App\Http\Requests\StoreSparepartRequest $request): JsonResponse
    {
        $sparepart = \App\Models\Sparepart::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sparepart berhasil ditambahkan.',
            'data' => $sparepart,
        ], 201);
    }

    public function masterShow(\App\Models\Sparepart $sparepart): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $sparepart,
        ]);
    }

    public function masterUpdate(\App\Http\Requests\UpdateSparepartRequest $request, \App\Models\Sparepart $sparepart): JsonResponse
    {
        $sparepart->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sparepart berhasil diperbarui.',
            'data' => $sparepart,
        ]);
    }

    public function masterDestroy(\App\Models\Sparepart $sparepart): JsonResponse
    {
        $sparepart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sparepart berhasil dihapus.',
        ]);
    }
}