<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceHistory;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view', ServiceHistory::class);

        $search = $request->query('search');
        $vehicle = null;
        $histories = collect();

        if ($search !== null && $search !== '') {
            $vehicle = Vehicle::query()
                ->where(function ($q) use ($search) {
                    $q->where('plate_number', 'like', "%{$search}%")
                        ->orWhere('vin', 'like', "%{$search}%")
                        ->orWhere('engine_number', 'like', "%{$search}%");
                })
                ->with('customer')
                ->first();

            if ($vehicle) {
                $histories = ServiceHistory::query()
                    ->where('vehicle_id', $vehicle->id)
                    ->with(['pkb.customer', 'pkb.vehicle', 'pkb.services', 'pkb.spareparts'])
                    ->orderByDesc('service_date')
                    ->orderByDesc('id')
                    ->paginate(20)
                    ->withQueryString();
            }
        }

        return view('admin.service-history.index', compact('vehicle', 'histories', 'search'));
    }

    public function show(ServiceHistory $serviceHistory): View
    {
        $this->authorize('view', $serviceHistory);

        $serviceHistory->load([
            'pkb.customer',
            'pkb.vehicle',
            'pkb.services',
            'pkb.spareparts',
        ]);

        return view('admin.service-history.show', compact('serviceHistory'));
    }
}