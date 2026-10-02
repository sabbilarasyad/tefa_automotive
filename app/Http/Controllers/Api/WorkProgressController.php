<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompletePkbWorkRequest;
use App\Http\Requests\DeletePkbDocumentationRequest;
use App\Http\Requests\StartPkbWorkRequest;
use App\Http\Requests\UpdateSparepartUsageRequest;
use App\Http\Requests\UploadPkbDocumentationRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Pkb;
use App\Services\WorkProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use LogicException;

class WorkProgressController extends Controller
{
    public function __construct(private WorkProgressService $progress) {}

    public function start(StartPkbWorkRequest $request, Pkb $pkb): JsonResponse
    {
        return ApiResponse::success($this->progress->startWork($pkb, $request->user()), 'Pekerjaan dimulai.');
    }

    public function updateSpareparts(UpdateSparepartUsageRequest $request, Pkb $pkb): JsonResponse
    {
        return ApiResponse::success(
            $this->progress->updateSparepartUsage($pkb, $request->validated(), $request->user()),
            'Pemakaian sparepart aktual berhasil diperbarui.',
        );
    }

    public function uploadDocumentation(UploadPkbDocumentationRequest $request, Pkb $pkb): JsonResponse
    {
        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            throw new LogicException('Validated documentation file is missing.');
        }

        $documentation = $this->progress->uploadDocumentation(
            $pkb,
            $request->safe()->only(['pkb_service_id', 'pkb_sparepart_id']),
            $file,
            $request->user(),
        );

        return ApiResponse::success($documentation, 'Dokumentasi berhasil diunggah.', 201);
    }

    public function deleteDocumentation(DeletePkbDocumentationRequest $request, Pkb $pkb): JsonResponse
    {
        $this->progress->deleteDocumentation($pkb, (int) $request->validated('documentation_id'), $request->user());

        return ApiResponse::success(null, 'Dokumentasi berhasil dihapus.');
    }

    public function complete(CompletePkbWorkRequest $request, Pkb $pkb): JsonResponse
    {
        return ApiResponse::success(
            $this->progress->validateComplete($pkb, $request->user()),
            'Pekerjaan divalidasi selesai dan invoice berhasil dibuat.',
        );
    }
}
