<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $user = $request->user();
        $role = $user->role->name;

        if (in_array($role, ['SUPER_ADMIN', 'HEAD_TEFA', 'ADMIN_CS'])) {
            $data = $this->reports->dashboardData();

            return view('admin.reports.dashboard', compact('data'));
        }

        if ($role === 'KASIR') {
            return redirect()->route('cashier.index');
        }

        return redirect()->route('mechanic.tasks');
    }

    public function daily(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $data = $this->reports->dailyRecap($date);

        return view('admin.reports.daily', compact('data', 'date'));
    }

    public function dailyExport(Request $request): RedirectResponse
    {
        $this->authorize('view', \App\Models\Report::class);

        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $data = $this->reports->dailyRecap($date);
        $export = $this->reports->exportCsv('daily', $data);

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename']);
    }

    public function monthly(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $data = $this->reports->monthlyRecap($year, $month);

        return view('admin.reports.monthly', compact('data', 'year', 'month'));
    }

    public function monthlyExport(Request $request): RedirectResponse
    {
        $this->authorize('view', \App\Models\Report::class);

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $data = $this->reports->monthlyRecap($year, $month);
        $export = $this->reports->exportCsv('monthly', $data);

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename']);
    }

    public function stock(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $data = $this->reports->stockReport();

        return view('admin.reports.stock', compact('data'));
    }

    public function stockExport(Request $request): RedirectResponse
    {
        $this->authorize('view', \App\Models\Report::class);

        $data = $this->reports->stockReport();
        $export = $this->reports->exportCsv('stock', $data);

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename']);
    }

    public function documentation(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $mechanicId = $request->query('mechanic_id') ? (int) $request->query('mechanic_id') : null;
        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $data = $this->reports->documentationReport($mechanicId, $from, $to);
        $mechanics = \App\Models\User::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'MEKANIK'))
            ->orderBy('name')
            ->get();

        return view('admin.reports.documentation', compact('data', 'mechanics', 'mechanicId', 'from', 'to'));
    }

    public function documentationExport(Request $request): RedirectResponse
    {
        $this->authorize('view', \App\Models\Report::class);

        $mechanicId = $request->query('mechanic_id') ? (int) $request->query('mechanic_id') : null;
        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $data = $this->reports->documentationReport($mechanicId, $from, $to);
        $export = $this->reports->exportCsv('documentation', $data);

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename']);
    }

    public function incompletePayments(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $data = $this->reports->incompleteDocumentationPayments($from, $to);

        return view('admin.reports.incomplete-payments', compact('data', 'from', 'to'));
    }

    public function incompletePaymentsExport(Request $request): RedirectResponse
    {
        $this->authorize('view', \App\Models\Report::class);

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $data = $this->reports->incompleteDocumentationPayments($from, $to);
        $export = $this->reports->exportCsv('payments_incomplete', $data);

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename']);
    }

    public function reminder(Request $request): View
    {
        $this->authorize('view', \App\Models\Report::class);

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $data = $this->reports->reminderReport($from, $to);

        return view('admin.reports.reminder', compact('data', 'from', 'to'));
    }

    public function reminderExport(Request $request): RedirectResponse
    {
        $this->authorize('view', \App\Models\Report::class);

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $data = $this->reports->reminderReport($from, $to);
        $export = $this->reports->exportCsv('reminder', $data);

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename']);
    }
}