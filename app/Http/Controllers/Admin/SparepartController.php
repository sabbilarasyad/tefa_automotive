<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSparepartRequest;
use App\Http\Requests\UpdateSparepartRequest;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparepartController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageSpareparts', Sparepart::class);

        $query = Sparepart::query()->orderBy('name');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $filter = $request->query('filter');
        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        }

        $spareparts = $query->paginate(20)->withQueryString();

        return view('admin.spareparts.index', compact('spareparts', 'search', 'filter'));
    }

    public function create(): View
    {
        $this->authorize('manageSpareparts', Sparepart::class);

        return view('admin.spareparts.create');
    }

    public function store(StoreSparepartRequest $request): RedirectResponse
    {
        $this->authorize('manageSpareparts', Sparepart::class);

        Sparepart::create($request->validated());

        return redirect()->route('admin.spareparts.index')->with('success', 'Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart): View
    {
        $this->authorize('manageSpareparts', $sparepart);

        return view('admin.spareparts.edit', compact('sparepart'));
    }

    public function update(UpdateSparepartRequest $request, Sparepart $sparepart): RedirectResponse
    {
        $this->authorize('manageSpareparts', $sparepart);

        $sparepart->update($request->validated());

        return redirect()->route('admin.spareparts.index')->with('success', 'Sparepart berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart): RedirectResponse
    {
        $this->authorize('manageSpareparts', $sparepart);

        $sparepart->delete();

        return redirect()->route('admin.spareparts.index')->with('success', 'Sparepart berhasil dihapus.');
    }
}