<?php

namespace App\Services;

use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function adjustStock(
        Sparepart $sparepart,
        int|float $qty,
        string $type,
        User $actor,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): StockMovement {
        $validTypes = ['MANUAL_IN', 'ADJUSTMENT', 'RETURN'];
        if (! in_array($type, $validTypes, true)) {
            throw ValidationException::withMessages([
                'type' => 'Tipe pergerakan stok tidak valid.',
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan wajib diisi untuk penyesuaian stok manual.',
            ]);
        }

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah harus lebih dari nol.',
            ]);
        }

        return DB::transaction(function () use ($sparepart, $qty, $type, $actor, $reason, $referenceType, $referenceId): StockMovement {
            $lockedSparepart = Sparepart::query()->lockForUpdate()->findOrFail($sparepart->id);

            $stockBefore = (float) $lockedSparepart->stock_qty;
            $qtyIn = 0;
            $qtyOut = 0;

            if ($type === 'MANUAL_IN') {
                $qtyIn = (float) $qty;
            } elseif ($type === 'ADJUSTMENT') {
                $qtyIn = (float) $qty;
            } elseif ($type === 'RETURN') {
                $qtyIn = (float) $qty;
            }

            $stockAfter = $stockBefore + $qtyIn - $qtyOut;

            $movement = StockMovement::create([
                'sparepart_id' => $lockedSparepart->id,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
                'created_by' => $actor->id,
            ]);

            $lockedSparepart->update(['stock_qty' => $stockAfter]);

            return $movement->load('sparepart', 'creator');
        });
    }

    /** @return Collection<int, Sparepart> */
    public function getLowStockParts(): Collection
    {
        return Sparepart::query()
            ->where('is_active', true)
            ->whereRaw('stock_qty <= min_stock')
            ->orderBy('stock_qty')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, Sparepart> */
    public function getAllActiveParts(): Collection
    {
        return Sparepart::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, StockMovement> */
    public function getMovementsForPart(Sparepart $sparepart, int $limit = 50): Collection
    {
        return $sparepart->stockMovements()
            ->with('creator')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}