<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('public.contact');
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        // Honeypot check - if filled, silently ignore (don't store, but return success)
        if ($request->filled('website')) {
            return back()->with('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
        }

        ContactMessage::create($request->validated());

        return back()->with('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
    }
}