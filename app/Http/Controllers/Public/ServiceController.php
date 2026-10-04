<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        return view('public.services', compact('services'));
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('public.service-show', compact('service'));
    }
}