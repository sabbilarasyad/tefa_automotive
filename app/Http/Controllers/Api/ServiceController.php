<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreServiceRequest;
use App\Http\Requests\Api\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manageServices', Service::class);
        
        $query = Service::query()->orderBy('name');

        $search = request()->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $this->authorize('manageServices', Service::class);
        
        $service = Service::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Jasa berhasil ditambahkan.',
            'data' => $service,
        ], 201);
    }

    public function show(Service $service): JsonResponse
    {
        $this->authorize('manageServices', $service);

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $this->authorize('manageServices', $service);
        
        $service->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Jasa berhasil diperbarui.',
            'data' => $service,
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->authorize('manageServices', $service);
        
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jasa berhasil dihapus.',
        ]);
    }
}