<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageReminders', Reminder::class);

        $today = now()->toDateString();

        $query = Reminder::query()
            ->where('status', 'PENDING')
            ->whereDate('reminder_date', '<=', $today)
            ->with(['pkb.customer', 'pkb.vehicle', 'vehicle.customer'])
            ->orderBy('reminder_date')
            ->orderBy('id');

        $reminders = $query->paginate(20)->withQueryString();

        return view('admin.reminder.index', compact('reminders', 'today'));
    }

    public function send(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('manageReminders', $reminder);

        abort_unless($reminder->status === 'PENDING', 400);

        $reminder->loadMissing(['pkb.customer', 'pkb.vehicle', 'vehicle.customer', 'customer']);

        $phone = $this->formatPhone($reminder->customer->phone ?? $reminder->vehicle->customer->phone ?? '');
        $vehicle = $reminder->vehicle ?? $reminder->pkb->vehicle;

        $message = sprintf(
            'Halo %s, ini pengingat servis berkala untuk kendaraan %s (%s). Silakan hubungi TEFA Automotive untuk menjadwalkan servis. Terima kasih.',
            $reminder->customer->name ?? $reminder->vehicle->customer->name ?? 'Pelanggan',
            $vehicle->plate_number,
            $vehicle->brand.' '.$vehicle->model
        );

        $reminder->update([
            'status' => 'SENT',
            'sent_at' => now(),
            'message' => $message,
        ]);

        $whatsappUrl = $phone !== '' ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message) : null;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl,
                'message' => 'Reminder dikirim dan ditandai SENT.',
            ]);
        }

        if ($whatsappUrl) {
            return redirect()->away($whatsappUrl);
        }

        return back()->with('success', 'Reminder ditandai SENT (nomor WhatsApp tidak tersedia).');
    }

    private function formatPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }
}