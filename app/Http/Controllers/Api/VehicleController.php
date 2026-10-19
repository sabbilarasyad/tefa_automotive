<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\ServiceHistory;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function serviceHistories(Vehicle $vehicle): JsonResponse
    {
        $histories = ServiceHistory::query()
            ->where('vehicle_id', $vehicle->id)
            ->with(['pkb.customer', 'pkb.vehicle', 'pkb.services', 'pkb.spareparts'])
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $histories,
        ]);
    }
}