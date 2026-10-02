<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignMechanicRequest;
use App\Http\Requests\CancelPkbRequest;
use App\Http\Requests\ForceCancelPkbRequest;
use App\Http\Requests\StorePkbRequest;
use App\Http\Requests\UpdatePkbRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Pkb;
use App\Models\User;
use App\Services\PkbWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PkbController extends Controller
{
    public function __construct(private PkbWorkflowService $workflow) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pkb::class);
        $query = Pkb::query()->with(['customer', 'vehicle', 'mechanic', 'services', 'spareparts']);
        $user = $request->user();

        if ($user->hasPermission('pkb.view_all')) {
            // The permission matrix grants this role an unscoped list.
        } elseif ($user->hasPermission('pkb.view_own')) {
            $query->where('mechanic_id', $user->id);
        } elseif ($user->hasPermission('pkb.view_waiting_payment')) {
            $query->where('status', 'WAITING_PAYMENT');
        }

        $page = $query->orderByDesc('service_date')->orderByDesc('id')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(StorePkbRequest $request): JsonResponse
    {
        $pkb = $this->workflow->create($request->validated(), $request->user());

        return ApiResponse::success($pkb, 'PKB berhasil dibuat.', 201);
    }

    public function show(Pkb $pkb): JsonResponse
    {
        $this->authorize('view', $pkb);

        return ApiResponse::success(
            $pkb->load(['customer', 'vehicle', 'mechanic', 'services', 'spareparts', 'documentations', 'statusHistories']),
            'Detail PKB berhasil diambil.',
        );
    }

    public function update(UpdatePkbRequest $request, Pkb $pkb): JsonResponse
    {
        return ApiResponse::success(
            $this->workflow->update($pkb, $request->validated(), $request->user()),
            'PKB berhasil diperbarui.',
        );
    }

    public function assignMechanic(AssignMechanicRequest $request, Pkb $pkb): JsonResponse
    {
        $mechanic = User::findOrFail($request->validated('mechanic_id'));

        return ApiResponse::success(
            $this->workflow->assignMechanic($pkb, $mechanic, $request->user()),
            'Mekanik berhasil ditugaskan.',
        );
    }

    public function cancel(CancelPkbRequest $request, Pkb $pkb): JsonResponse
    {
        return ApiResponse::success(
            $this->workflow->cancel($pkb, $request->validated('cancel_reason'), $request->user()),
            'PKB berhasil dibatalkan.',
        );
    }

    public function forceCancel(ForceCancelPkbRequest $request, Pkb $pkb): JsonResponse
    {
        return ApiResponse::success(
            $this->workflow->forceCancel($pkb, $request->validated('cancel_reason'), $request->user()),
            'PKB dibatalkan dan stok yang terpakai telah dikembalikan.',
        );
    }

    public function print(Pkb $pkb): JsonResponse
    {
        $this->authorize('print', $pkb);

        return ApiResponse::success($this->workflow->printData($pkb), 'Data cetak PKB berhasil diambil.');
    }

    public function statusHistories(Pkb $pkb): JsonResponse
    {
        $this->authorize('view', $pkb);

        return ApiResponse::success(
            $pkb->statusHistories()->with('changer')->orderBy('created_at')->get(),
            'Riwayat status PKB berhasil diambil.',
        );
    }
}
