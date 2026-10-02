<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompletePkbWorkRequest;
use App\Http\Requests\DeletePkbDocumentationRequest;
use App\Http\Requests\StartPkbWorkRequest;
use App\Http\Requests\UpdateSparepartUsageRequest;
use App\Http\Requests\UploadPkbDocumentationRequest;
use App\Models\Pkb;
use App\Services\WorkProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use LogicException;

class PkbProgressController extends Controller
{
    public function __construct(private WorkProgressService $progress) {}

    public function index(): View
    {
        $this->authorize('viewProgressList', Pkb::class);

        $pkbs = Pkb::query()
            ->with(['customer', 'vehicle', 'mechanic'])
            ->whereIn('status', ['WAITING_MECHANIC', 'IN_PROGRESS'])
            ->orderByRaw("CASE WHEN status = 'IN_PROGRESS' THEN 0 ELSE 1 END")
            ->orderBy('service_date')
            ->paginate(15);

        return view('admin.pkb.progress.index', compact('pkbs'));
    }

    public function show(Pkb $pkb): View
    {
        $this->authorize('viewProgress', $pkb);
        $pkb->load([
            'customer',
            'vehicle',
            'mechanic',
            'services.documentations.uploader',
            'spareparts.sparepart',
            'spareparts.documentations.uploader',
            'invoice',
        ]);

        return view('admin.pkb.progress.show', compact('pkb'));
    }

    public function mechanicTasks(Request $request): View
    {
        $this->authorize('viewOwnTasks', Pkb::class);
        $tab = $request->query('tab') === 'history' ? 'history' : 'active';
        $statuses = $tab === 'history'
            ? ['PAID', 'CLOSED', 'CANCELLED']
            : ['WAITING_PARTS', 'WAITING_MECHANIC', 'IN_PROGRESS', 'WAITING_PAYMENT'];

        $pkbs = Pkb::query()
            ->with(['customer', 'vehicle', 'services', 'spareparts', 'documentations'])
            ->where('mechanic_id', $request->user()->id)
            ->whereIn('status', $statuses)
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('mechanic.tasks.index', compact('pkbs', 'tab'));
    }

    public function start(StartPkbWorkRequest $request, Pkb $pkb): RedirectResponse
    {
        $this->progress->startWork($pkb, $request->user());

        return redirect()->route('admin.pkb.progress.show', $pkb)->with('success', 'Pekerjaan dimulai.');
    }

    public function updateSpareparts(UpdateSparepartUsageRequest $request, Pkb $pkb): RedirectResponse
    {
        $this->progress->updateSparepartUsage($pkb, $request->validated(), $request->user());

        return back()->with('success', 'Pemakaian sparepart aktual berhasil disimpan. Stok belum dipotong.');
    }

    public function uploadDocumentation(UploadPkbDocumentationRequest $request, Pkb $pkb): RedirectResponse
    {
        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            throw new LogicException('Validated documentation file is missing.');
        }

        $this->progress->uploadDocumentation(
            $pkb,
            $request->safe()->only(['pkb_service_id', 'pkb_sparepart_id']),
            $file,
            $request->user(),
        );

        return back()->with('success', 'Dokumentasi berhasil diunggah.');
    }

    public function deleteDocumentation(DeletePkbDocumentationRequest $request, Pkb $pkb): RedirectResponse
    {
        $this->progress->deleteDocumentation($pkb, (int) $request->validated('documentation_id'), $request->user());

        return back()->with('success', 'Dokumentasi berhasil dihapus.');
    }

    public function complete(CompletePkbWorkRequest $request, Pkb $pkb): RedirectResponse
    {
        $result = $this->progress->validateComplete($pkb, $request->user());
        $message = 'Pekerjaan divalidasi selesai dan invoice '.$result['pkb']->invoice->number.' berhasil dibuat.';

        if (! $result['documentation_complete']) {
            $message .= ' Masih ada dokumentasi wajib yang belum lengkap.';
        }

        return redirect()->route('admin.pkb.show', $pkb)->with('success', $message);
    }
}
