<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Pkb;
use App\Models\PkbSparepart;
use App\Models\Service as WorkshopService;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Owns transactional PKB intake, assignment, cancellation, and print data. */
class PkbWorkflowService
{
    public function __construct(private NumberGeneratorService $numberGenerator) {}

    /** Creates PKB intake data atomically; called by web and API controllers. */
    public function create(array $data, User $creator): Pkb
    {
        return DB::transaction(function () use ($data, $creator): Pkb {
            $this->setCurrentUser($creator);

            try {
                $customer = $this->resolveCustomer($data);
                $vehicle = $this->resolveVehicle($data, $customer);
                $services = $this->servicesFor($data['services']);
                $spareparts = $this->sparepartsFor($data['spareparts'] ?? []);
                $hasEnoughStock = $this->hasEnoughStock($spareparts);
                $mechanic = isset($data['mechanic_id'])
                    ? User::query()->with('role')->lockForUpdate()->findOrFail($data['mechanic_id'])
                    : null;

                if ($mechanic !== null && (! $mechanic->is_active || ! $mechanic->hasRole('MEKANIK'))) {
                    throw ValidationException::withMessages([
                        'mechanic_id' => 'Pengguna yang dipilih harus mekanik aktif.',
                    ]);
                }

                $pkb = Pkb::create([
                    'number' => $this->numberGenerator->next('PKB'),
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'created_by' => $creator->id,
                    'mechanic_id' => $mechanic?->id,
                    'complaint' => $data['complaint'],
                    'inspection_result' => $data['inspection_result'] ?? null,
                    'status' => $hasEnoughStock ? 'WAITING_MECHANIC' : 'WAITING_PARTS',
                    'service_date' => $data['service_date'] ?? now(),
                    'estimated_total' => 0,
                    'final_total' => 0,
                ]);

                foreach ($services as $item) {
                    $quantity = (float) $item['qty'];
                    $unitPrice = (float) $item['service']->price;
                    $pkb->services()->create([
                        'service_id' => $item['service']->id,
                        'name_snapshot' => $item['service']->name,
                        'qty' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => round($quantity * $unitPrice, 2),
                        'documentation_required' => $item['service']->documentation_required,
                    ]);
                }

                foreach ($spareparts as $item) {
                    $quantity = (float) $item['qty_planned'];
                    $unitPrice = (float) $item['sparepart']->selling_price;
                    $pkb->spareparts()->create([
                        'sparepart_id' => $item['sparepart']->id,
                        'name_snapshot' => $item['sparepart']->name,
                        'qty_planned' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => round($quantity * $unitPrice, 2),
                        'documentation_required' => $item['sparepart']->documentation_required,
                    ]);
                }

                $pkb->update(['estimated_total' => $this->calculateTotal($pkb)]);

                return $pkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    public function update(Pkb $pkb, array $data, User $actor): Pkb
    {
        return DB::transaction(function () use ($pkb, $data, $actor): Pkb {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);
                $this->ensureEditable($lockedPkb);

                $lockedPkb->update(collect($data)->only([
                    'complaint',
                    'inspection_result',
                    'service_date',
                ])->all());

                return $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    public function assignMechanic(Pkb $pkb, User $mechanic, User $actor): Pkb
    {
        return DB::transaction(function () use ($pkb, $mechanic, $actor): Pkb {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);
                $mechanic->loadMissing('role');

                if (! $mechanic->is_active || ! $mechanic->hasRole('MEKANIK')) {
                    throw ValidationException::withMessages([
                        'mechanic_id' => 'Pengguna yang dipilih harus mekanik aktif.',
                    ]);
                }

                $lockedPkb->update(['mechanic_id' => $mechanic->id]);

                return $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    public function cancel(Pkb $pkb, string $reason, User $actor): Pkb
    {
        return DB::transaction(function () use ($pkb, $reason, $actor): Pkb {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);

                if (! in_array($lockedPkb->status, ['WAITING_PARTS', 'WAITING_MECHANIC'], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'PKB hanya dapat dibatalkan sebelum pekerjaan dimulai.',
                    ]);
                }

                $lockedPkb->update(['status' => 'CANCELLED', 'cancel_reason' => $reason]);

                return $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    public function forceCancel(Pkb $pkb, string $reason, User $actor): Pkb
    {
        return DB::transaction(function () use ($pkb, $reason, $actor): Pkb {
            $this->setCurrentUser($actor);

            try {
                $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);

                if ($lockedPkb->status === 'CANCELLED') {
                    throw ValidationException::withMessages([
                        'status' => 'PKB sudah dibatalkan.',
                    ]);
                }

                $parts = $lockedPkb->spareparts()
                    ->whereNotNull('qty_used')
                    ->where('qty_used', '>', 0)
                    ->orderBy('sparepart_id')
                    ->get()
                    ->groupBy('sparepart_id');

                foreach ($parts as $sparepartId => $partRows) {
                    $sparepart = Sparepart::query()->lockForUpdate()->findOrFail($sparepartId);
                    $stockBefore = (float) $sparepart->stock_qty;
                    $quantity = (float) $partRows->sum(fn (PkbSparepart $part): float => (float) $part->qty_used);

                    StockMovement::create([
                        'sparepart_id' => $sparepart->id,
                        'type' => 'RETURN',
                        'reference_type' => 'PKB',
                        'reference_id' => $lockedPkb->id,
                        'qty_in' => $quantity,
                        'qty_out' => 0,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore + $quantity,
                        'reason' => 'Reversal force-cancel '.$lockedPkb->number,
                        'created_by' => $actor->id,
                        'created_at' => now(),
                    ]);
                }

                $lockedPkb->update(['status' => 'CANCELLED', 'cancel_reason' => $reason]);

                return $lockedPkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
            } finally {
                $this->clearCurrentUser();
            }
        });
    }

    /** Returns the single data shape shared by the web print view and API print endpoint. */
    public function printData(Pkb $pkb): array
    {
        $pkb->loadMissing(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);

        return [
            'number' => $pkb->number,
            'service_date' => $pkb->service_date?->format('Y-m-d H:i'),
            'status' => $pkb->status,
            'customer' => $pkb->customer->only(['name', 'phone', 'address']),
            'vehicle' => $pkb->vehicle->only(['plate_number', 'brand', 'model', 'year', 'color', 'vin', 'engine_number', 'current_km']),
            'complaint' => $pkb->complaint,
            'inspection_result' => $pkb->inspection_result,
            'services' => $pkb->services->map(fn ($item): array => [
                'name' => $item->name_snapshot,
                'qty' => $item->qty,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ])->all(),
            'spareparts' => $pkb->spareparts->map(fn ($item): array => [
                'name' => $item->name_snapshot,
                'qty_planned' => $item->qty_planned,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ])->all(),
            'mechanic' => $pkb->mechanic?->only(['name']),
            'estimated_total' => $pkb->estimated_total,
        ];
    }

    private function resolveCustomer(array $data): Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::query()->lockForUpdate()->findOrFail($data['customer_id']);
        }

        return Customer::create(collect($data['customer'])->only([
            'name',
            'phone',
            'email',
            'address',
        ])->all());
    }

    private function resolveVehicle(array $data, Customer $customer): Vehicle
    {
        if (! empty($data['vehicle_id'])) {
            $vehicle = Vehicle::query()->lockForUpdate()->findOrFail($data['vehicle_id']);

            if ((int) $vehicle->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Kendaraan bukan milik customer yang dipilih.',
                ]);
            }

            return $vehicle;
        }

        return $customer->vehicles()->create(collect($data['vehicle'])->only([
            'plate_number',
            'brand',
            'model',
            'year',
            'color',
            'vin',
            'engine_number',
            'current_km',
        ])->all());
    }

    private function servicesFor(array $items): Collection
    {
        $services = WorkshopService::query()
            ->whereIn('id', collect($items)->pluck('service_id')->unique())
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($services->count() !== collect($items)->pluck('service_id')->unique()->count()) {
            throw ValidationException::withMessages(['services' => 'Salah satu jasa tidak aktif atau tidak ditemukan.']);
        }

        return collect($items)->map(fn (array $item): array => [
            'service' => $services->get($item['service_id']),
            'qty' => $item['qty'],
        ]);
    }

    private function sparepartsFor(array $items): Collection
    {
        if ($items === []) {
            return collect();
        }

        $spareparts = Sparepart::query()
            ->whereIn('id', collect($items)->pluck('sparepart_id')->unique())
            ->where('is_active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($spareparts->count() !== collect($items)->pluck('sparepart_id')->unique()->count()) {
            throw ValidationException::withMessages(['spareparts' => 'Salah satu sparepart tidak aktif atau tidak ditemukan.']);
        }

        return collect($items)->map(fn (array $item): array => [
            'sparepart' => $spareparts->get($item['sparepart_id']),
            'qty_planned' => $item['qty_planned'],
        ]);
    }

    private function hasEnoughStock(Collection $items): bool
    {
        return $items
            ->groupBy(fn (array $item): int => $item['sparepart']->id)
            ->every(fn (Collection $group): bool => (float) $group->first()['sparepart']->stock_qty >= $group->sum(
                fn (array $item): float => (float) $item['qty_planned'],
            ));
    }

    private function calculateTotal(Pkb $pkb): string
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return (string) DB::selectOne('SELECT fn_calculate_pkb_total(?) AS total', [$pkb->id])->total;
        }

        $servicesTotal = (float) $pkb->services()->sum('subtotal');
        $sparepartsTotal = (float) $pkb->spareparts()->sum('subtotal');

        return number_format($servicesTotal + $sparepartsTotal, 2, '.', '');
    }

    private function ensureEditable(Pkb $pkb): void
    {
        if (! in_array($pkb->status, ['WAITING_PARTS', 'WAITING_MECHANIC'], true)) {
            throw ValidationException::withMessages(['status' => 'PKB tidak dapat diubah setelah pekerjaan dimulai.']);
        }
    }

    /** Sets the actor consumed by the status-history trigger described in File 2 section 4. */
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
