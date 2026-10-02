<?php

namespace App\Services;

use App\Models\Documentation;
use App\Models\Invoice;
use App\Models\Pkb;
use App\Models\PkbService;
use App\Models\PkbSparepart;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/** Owns atomic work progression, file documentation, stock deduction, and invoice generation. */
class WorkProgressService
{
    public function __construct(private NumberGeneratorService $numberGenerator) {}

    public function startWork(Pkb $pkb, User $actor): Pkb
    {
        return DB::transaction(function () use ($pkb, $actor): Pkb {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);

                if ($lockedPkb->status !== 'WAITING_MECHANIC') {
                    throw ValidationException::withMessages([
                        'status' => 'PKB hanya dapat dimulai saat berstatus Menunggu Dikerjakan.',
                    ]);
                }

                $lockedPkb->update(['status' => 'IN_PROGRESS', 'started_at' => now()]);

                return $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    /** @param array{spareparts: array<int, array{pkb_sparepart_id: int, qty_used: float|int|string}>} $data */
    public function updateSparepartUsage(Pkb $pkb, array $data, User $actor): Pkb
    {
        return DB::transaction(function () use ($pkb, $data, $actor): Pkb {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);
                $this->ensureInProgress($lockedPkb);

                foreach ($data['spareparts'] as $item) {
                    $part = PkbSparepart::query()
                        ->where('pkb_id', $lockedPkb->id)
                        ->lockForUpdate()
                        ->find($item['pkb_sparepart_id']);

                    if ($part === null) {
                        throw ValidationException::withMessages([
                            'spareparts' => 'Item sparepart tidak ditemukan pada PKB ini.',
                        ]);
                    }

                    $quantity = (float) $item['qty_used'];
                    $part->update([
                        'qty_used' => $quantity,
                        'subtotal' => round($quantity * (float) $part->unit_price, 2),
                    ]);
                }

                return $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    /** @param array{pkb_service_id?: int|null, pkb_sparepart_id?: int|null} $data */
    public function uploadDocumentation(Pkb $pkb, array $data, UploadedFile $file, User $actor): Documentation
    {
        return DB::transaction(function () use ($pkb, $data, $file, $actor): Documentation {
            $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);
            $this->ensureInProgress($lockedPkb);
            $serviceId = $data['pkb_service_id'] ?? null;
            $sparepartId = $data['pkb_sparepart_id'] ?? null;

            if (($serviceId === null) === ($sparepartId === null)) {
                throw ValidationException::withMessages([
                    'item' => 'Pilih tepat satu item jasa atau sparepart untuk dokumentasi.',
                ]);
            }

            if ($serviceId !== null && ! PkbService::query()->where('pkb_id', $lockedPkb->id)->whereKey($serviceId)->exists()) {
                throw ValidationException::withMessages(['pkb_service_id' => 'Jasa bukan bagian dari PKB ini.']);
            }

            if ($sparepartId !== null && ! PkbSparepart::query()->where('pkb_id', $lockedPkb->id)->whereKey($sparepartId)->exists()) {
                throw ValidationException::withMessages(['pkb_sparepart_id' => 'Sparepart bukan bagian dari PKB ini.']);
            }

            $path = $file->store('documentations', 'public');

            if (! is_string($path) || $path === '') {
                throw new RuntimeException('File dokumentasi gagal disimpan.');
            }

            try {
                return Documentation::create([
                    'pkb_id' => $lockedPkb->id,
                    'pkb_service_id' => $serviceId,
                    'pkb_sparepart_id' => $sparepartId,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize() ?: 0,
                    'uploaded_by' => $actor->id,
                    'uploaded_at' => now(),
                ]);
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($path);

                throw $exception;
            }
        });
    }

    public function deleteDocumentation(Pkb $pkb, int $documentationId, User $actor): void
    {
        DB::transaction(function () use ($pkb, $documentationId): void {
            $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);
            $this->ensureInProgress($lockedPkb);
            $documentation = Documentation::query()
                ->where('pkb_id', $lockedPkb->id)
                ->lockForUpdate()
                ->findOrFail($documentationId);

            Storage::disk('public')->delete($documentation->file_path);
            $documentation->delete();
        });
    }

    /** @return array{pkb: Pkb, documentation_complete: bool} */
    public function validateComplete(Pkb $pkb, User $actor): array
    {
        return DB::transaction(function () use ($pkb, $actor): array {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);
                $this->ensureInProgress($lockedPkb);
                $parts = PkbSparepart::query()
                    ->where('pkb_id', $lockedPkb->id)
                    ->orderBy('sparepart_id')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($parts->contains(fn (PkbSparepart $part): bool => $part->qty_used === null)) {
                    throw ValidationException::withMessages([
                        'spareparts' => 'Isi jumlah aktual untuk setiap sparepart sebelum validasi selesai.',
                    ]);
                }

                $spareparts = Sparepart::query()
                    ->whereIn('id', $parts->pluck('sparepart_id')->unique())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $stockErrors = $this->stockErrors($parts, $spareparts);

                if ($stockErrors !== []) {
                    throw ValidationException::withMessages(['stock' => $stockErrors]);
                }

                $documentationComplete = $this->documentationComplete($lockedPkb->id);

                foreach ($lockedPkb->services()->lockForUpdate()->get() as $service) {
                    $service->update(['is_done' => true]);
                }

                $stockBySparepart = $spareparts->mapWithKeys(fn (Sparepart $part): array => [
                    $part->id => (float) $part->stock_qty,
                ]);

                foreach ($parts as $part) {
                    $quantity = (float) $part->qty_used;
                    $subtotal = round($quantity * (float) $part->unit_price, 2);
                    $part->update(['subtotal' => $subtotal]);

                    if ($quantity <= 0) {
                        continue;
                    }

                    $stockBefore = $stockBySparepart->get($part->sparepart_id);
                    $stockAfter = $stockBefore - $quantity;
                    StockMovement::create([
                        'sparepart_id' => $part->sparepart_id,
                        'type' => 'OUT',
                        'reference_type' => 'PKB',
                        'reference_id' => $lockedPkb->id,
                        'qty_in' => 0,
                        'qty_out' => $quantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reason' => 'Pemakaian PKB '.$lockedPkb->number,
                        'created_by' => $actor->id,
                        'created_at' => now(),
                    ]);
                    $stockBySparepart->put($part->sparepart_id, $stockAfter);
                }

                $total = $this->calculatePkbTotal($lockedPkb);
                $lockedPkb->update([
                    'final_total' => $total,
                    'status' => 'WAITING_PAYMENT',
                    'completed_at' => now(),
                ]);

                Invoice::create([
                    'number' => $this->numberGenerator->next('INVOICE'),
                    'pkb_id' => $lockedPkb->id,
                    'subtotal' => $total,
                    'discount' => 0,
                    'tax' => 0,
                    'grand_total' => $total,
                    'status' => 'UNPAID',
                    'issued_at' => now(),
                ]);

                return [
                    'pkb' => $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts', 'invoice']),
                    'documentation_complete' => $documentationComplete,
                ];
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    /** @param EloquentCollection<int, PkbSparepart> $parts
     * @param  Collection<int, Sparepart>  $spareparts
     * @return array<int, string>
     */
    private function stockErrors(EloquentCollection $parts, Collection $spareparts): array
    {
        $errors = [];

        foreach ($parts->groupBy('sparepart_id') as $sparepartId => $partRows) {
            $sparepart = $spareparts->get($sparepartId);
            $required = (float) $partRows->sum(fn (PkbSparepart $part): float => (float) $part->qty_used);
            $available = (float) $sparepart->stock_qty;

            if ($required > $available) {
                $errors[] = sprintf(
                    'Stok %s tidak mencukupi. Tersedia %s, dibutuhkan %s.',
                    $sparepart->name,
                    rtrim(rtrim(number_format($available, 3, '.', ''), '0'), '.'),
                    rtrim(rtrim(number_format($required, 3, '.', ''), '0'), '.'),
                );
            }
        }

        return $errors;
    }

    private function calculatePkbTotal(Pkb $pkb): string
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return (string) DB::selectOne('SELECT fn_calculate_pkb_total(?) AS total', [$pkb->id])->total;
        }

        $serviceTotal = (float) $pkb->services()->sum('subtotal');
        $sparepartTotal = (float) $pkb->spareparts()->sum('subtotal');

        return number_format($serviceTotal + $sparepartTotal, 2, '.', '');
    }

    private function documentationComplete(int $pkbId): bool
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return (bool) DB::selectOne('SELECT fn_documentation_complete(?) AS complete', [$pkbId])->complete;
        }

        $servicesComplete = ! PkbService::query()
            ->where('pkb_id', $pkbId)
            ->where('documentation_required', true)
            ->whereDoesntHave('documentations')
            ->exists();
        $sparepartsComplete = ! PkbSparepart::query()
            ->where('pkb_id', $pkbId)
            ->where('documentation_required', true)
            ->whereDoesntHave('documentations')
            ->exists();

        return $servicesComplete && $sparepartsComplete;
    }

    private function ensureInProgress(Pkb $pkb): void
    {
        if ($pkb->status !== 'IN_PROGRESS') {
            throw ValidationException::withMessages([
                'status' => 'Aksi progres hanya dapat dilakukan saat pekerjaan sedang dikerjakan.',
            ]);
        }
    }

    private function setCurrentUser(User $actor): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET @current_user_id = ?', [$actor->id]);
        }
    }

    private function clearCurrentUser(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET @current_user_id = NULL');
        }
    }
}
