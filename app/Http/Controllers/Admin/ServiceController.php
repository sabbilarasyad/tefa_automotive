<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageServices', Service::class);

        $query = Service::query()->orderBy('name');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate(20)->withQueryString();

        return view('admin.services.index', compact('services', 'search'));
    }

    public function create(): View
    {
        $this->authorize('manageServices', Service::class);

        return view('admin.services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->authorize('manageServices', Service::class);

        Service::create($request->validated());

        return redirect()->route('admin.services.index')->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        $this->authorize('manageServices', $service);

        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('manageServices', $service);

        $service->update($request->validated());

        return redirect()->route('admin.services.index')->with('success', 'Jasa berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('manageServices', $service);

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Jasa berhasil dihapus.');
    }
}