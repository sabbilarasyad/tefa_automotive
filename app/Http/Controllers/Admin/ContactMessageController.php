<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageContactMessages', ContactMessage::class);

        $query = ContactMessage::query()->latest();

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $messages = $query->paginate(20)->withQueryString();

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function markAsRead(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorize('manageContactMessages', $contactMessage);

        $contactMessage->update(['read_at' => now()]);

        return back()->with('success', 'Pesan ditandai sebagai sudah dibaca.');
    }

    public function markAsUnread(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorize('manageContactMessages', $contactMessage);

        $contactMessage->update(['read_at' => null]);

        return back()->with('success', 'Pesan ditandai sebagai belum dibaca.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }
}