@extends('layouts.app')

@section('title', 'Tambah Jasa')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[#126b56]" href="{{ route('admin.services.index') }}">← Daftar Jasa</a>
            <h1 class="text-2xl font-bold">Tambah Jasa Baru</h1>
        </div>
    </div>

    <section class="border border-[#dce5e1] bg-white p-5 sm:p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="form-label" for="code">Kode Jasa</label>
                <input class="form-field" id="code" name="code" type="text" value="{{ old('code') }}" required maxlength="50" autocomplete="off">
                @error('code')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="name">Nama Jasa</label>
                <input class="form-field" id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="150" autocomplete="off">
                @error('name')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="description">Deskripsi</label>
                <textarea class="form-field" id="description" name="description" rows="3" maxlength="500" autocomplete="off">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="form-label" for="price">Harga</label>
                <input class="form-field" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" required autocomplete="off">
                @error('price')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
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
                <a class="btn-secondary" href="{{ route('admin.services.index') }}">Batal</a>
                <button class="btn-primary" type="submit">Simpan Jasa</button>
            </div>
        </form>
    </section>
@endsection