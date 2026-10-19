<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReminderSendRequest;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;

class ReminderController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Reminder::query()
            ->with(['pkb.customer', 'pkb.vehicle', 'vehicle.customer'])
            ->orderBy('reminder_date')
            ->orderBy('id');

        if (request()->has('status')) {
            $query->where('status', request()->get('status'));
        }

        if (request()->has('due_date')) {
            $query->whereDate('reminder_date', '<=', request()->get('due_date'));
        }

        $reminders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reminders,
        ]);
    }

    public function send(ReminderSendRequest $request, Reminder $reminder): JsonResponse
    {
        $reminder->loadMissing(['pkb.customer', 'pkb.vehicle', 'vehicle.customer', 'customer']);

        abort_unless($reminder->status === 'PENDING', 400);

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

        return response()->json([
            'success' => true,
            'whatsapp_url' => $whatsappUrl,
            'message' => 'Reminder dikirim dan ditandai SENT.',
        ]);
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