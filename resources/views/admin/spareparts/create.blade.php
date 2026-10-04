@extends('layouts.app')

@section('title', 'Tambah Sparepart')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[#126b56]" href="{{ route('admin.spareparts.index') }}">← Daftar Sparepart</a>
            <h1 class="text-2xl font-bold">Tambah Sparepart Baru</h1>
        </div>
    </div>

    <section class="border border-[#dce5e1] bg-white p-5 sm:p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.spareparts.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="form-label" for="code">Kode Sparepart</label>
                <input class="form-field" id="code" name="code" type="text" value="{{ old('code') }}" required maxlength="50" autocomplete="off">
                @error('code')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="name">Nama Sparepart</label>
                <input class="form-field" id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="150" autocomplete="off">
                @error('name')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="unit">Satuan</label>
                <input class="form-field" id="unit" name="unit" type="text" value="{{ old('unit', 'pcs') }}" required maxlength="30" autocomplete="off">
                @error('unit')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label" for="selling_price">Harga Jual</label>
                    <input class="form-field" id="selling_price" name="selling_price" type="number" step="0.01" min="0" value="{{ old('selling_price') }}" required autocomplete="off">
                    @error('selling_price')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="cost_price">Harga Pokok (HPP)</label>
                    <input class="form-field" id="cost_price" name="cost_price" type="number" step="0.01" min="0" value="{{ old('cost_price') }}" autocomplete="off">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label" for="stock_qty">Stok Awal</label>
                    <input class="form-field" id="stock_qty" name="stock_qty" type="number" step="0.001" min="0" value="{{ old('stock_qty', 0) }}" autocomplete="off">
                </div>
                <div>
                    <label class="form-label" for="min_stock">Stok Minimum</label>
                    <input class="form-field" id="min_stock" name="min_stock" type="number" step="0.001" min="0" value="{{ old('min_stock', 0) }}" autocomplete="off">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input class="form-checkbox" id="documentation_required" name="documentation_required" type="checkbox" value="1" {{ old('documentation_required') ? 'checked' : '' }}>
                <label class="form-label cursor-pointer mb-0" for="documentation_required">Dokumentasi wajib (foto)</label>
            </div>

            <div class="flex items-center gap-2">
                <input class="form-checkbox" id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-label cursor-pointer mb-0" for="is_active">Aktif</label>
            </div>

            <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-[#dce5e1]">
                <a class="btn-secondary" href="{{ route('admin.spareparts.index') }}">Batal</a>
                <button class="btn-primary" type="submit">Simpan Sparepart</button>
            </div>
        </form>
    </section>
@endsection