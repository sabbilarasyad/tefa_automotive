<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignMechanicRequest;
use App\Http\Requests\CancelPkbRequest;
use App\Http\Requests\ForceCancelPkbRequest;
use App\Http\Requests\StorePkbRequest;
use App\Http\Requests\UpdatePkbRequest;
use App\Models\Customer;
use App\Models\Pkb;
use App\Models\Service as WorkshopService;
use App\Models\Sparepart;
use App\Models\User;
use App\Services\PkbWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PkbController extends Controller
{
    public function __construct(private PkbWorkflowService $workflow) {}

    public function index(): View
    {
        $this->authorize('viewAny', Pkb::class);

        $query = Pkb::query()->with(['customer', 'vehicle', 'mechanic']);
        $user = request()->user();

        if ($user->hasPermission('pkb.view_all')) {
            // The permission matrix grants this role an unscoped list.
        } elseif ($user->hasPermission('pkb.view_own')) {
            $query->where('mechanic_id', $user->id);
        } elseif ($user->hasPermission('pkb.view_waiting_payment')) {
            $query->where('status', 'WAITING_PAYMENT');
        }

        return view('admin.pkb.index', [
            'pkbs' => $query->orderByDesc('service_date')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Pkb::class);
        $customers = Customer::query()->where('is_active', true)->with('vehicles')->orderBy('name')->get();
        $services = WorkshopService::query()->where('is_active', true)->orderBy('name')->get();
        $spareparts = Sparepart::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.pkb.create', [
            'customers' => $customers,
            'customerOptions' => $customers->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'vehicles' => $customer->vehicles->map(fn ($vehicle): array => [
                    'id' => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                ])->all(),
            ])->all(),
            'serviceOptions' => $services->map(fn (WorkshopService $service): array => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
            ])->all(),
            'sparepartOptions' => $spareparts->map(fn (Sparepart $sparepart): array => [
                'id' => $sparepart->id,
                'name' => $sparepart->name,
                'price' => (float) $sparepart->selling_price,
                'stock' => (float) $sparepart->stock_qty,
                'unit' => $sparepart->unit,
            ])->all(),
            'mechanics' => User::query()->where('is_active', true)->whereHas('role', fn ($query) => $query->where('name', 'MEKANIK'))->orderBy('name')->get(),
        ]);
    }

    public function store(StorePkbRequest $request): RedirectResponse
    {
        $pkb = $this->workflow->create($request->validated(), $request->user());

        return redirect()->route('admin.pkb.show', $pkb)->with('success', 'PKB berhasil dibuat.');
    }

    public function show(Pkb $pkb): View
    {
        $this->authorize('view', $pkb);
        $pkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts', 'statusHistories.changer']);

        return view('admin.pkb.show', [
            'pkb' => $pkb,
            'mechanics' => User::query()->where('is_active', true)->whereHas('role', fn ($query) => $query->where('name', 'MEKANIK'))->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePkbRequest $request, Pkb $pkb): RedirectResponse
    {
        $this->workflow->update($pkb, $request->validated(), $request->user());

        return back()->with('success', 'Data PKB berhasil diperbarui.');
    }

    public function assignMechanic(AssignMechanicRequest $request, Pkb $pkb): RedirectResponse
    {
        $mechanic = User::findOrFail($request->validated('mechanic_id'));
        $this->workflow->assignMechanic($pkb, $mechanic, $request->user());

        return back()->with('success', 'Mekanik berhasil ditugaskan.');
    }

    public function cancel(CancelPkbRequest $request, Pkb $pkb): RedirectResponse
    {
        $this->workflow->cancel($pkb, $request->validated('cancel_reason'), $request->user());

        return back()->with('success', 'PKB berhasil dibatalkan.');
    }

    public function forceCancel(ForceCancelPkbRequest $request, Pkb $pkb): RedirectResponse
    {
        $this->workflow->forceCancel($pkb, $request->validated('cancel_reason'), $request->user());

        return back()->with('success', 'PKB dibatalkan dan stok yang terpakai telah dikembalikan.');
    }

    public function print(Pkb $pkb): View
    {
        $this->authorize('print', $pkb);
        $printData = $this->workflow->printData($pkb);

        return view('admin.pkb.print', ['document' => $printData]);
    }
}
