@php
    $isService = $itemType === 'service';
    $itemIdField = $isService ? 'pkb_service_id' : 'pkb_sparepart_id';
    $itemDocs = $item->documentations;
@endphp
<article class="border-t border-[#e7eeea] pt-4 first:border-t-0 first:pt-0">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><p class="font-semibold">{{ $item->name_snapshot }}</p><p class="text-xs {{ $item->documentation_required ? 'text-[#9a5b10]' : 'text-[var(--muted)]' }}">{{ $item->documentation_required ? 'Dokumentasi wajib' : 'Dokumentasi opsional' }} · {{ $isService ? 'Jasa' : 'Sparepart' }}</p></div>
        @if ($itemDocs->isNotEmpty())<span class="text-xs font-semibold text-[var(--orange-deep)]">{{ $itemDocs->count() }} foto tersimpan</span>@endif
    </div>
    @if ($itemDocs->isNotEmpty())
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($itemDocs as $documentation)
                <figure class="min-w-0 border border-[var(--line)] bg-[#f8faf9] p-2">
                    <a href="{{ Storage::disk('public')->url($documentation->file_path) }}" target="_blank" rel="noopener" aria-label="Buka {{ $documentation->file_name }}">
                        <img class="h-28 w-full object-cover" src="{{ Storage::disk('public')->url($documentation->file_path) }}" alt="Dokumentasi {{ $item->name_snapshot }}" loading="lazy">
                    </a>
                    <figcaption class="mt-2 truncate text-xs text-[var(--muted)]">{{ $documentation->file_name }}</figcaption>
                    @can('deleteDocumentation', $pkb)
                        <form class="mt-2" method="POST" action="{{ route('admin.pkb.progress.documentations.destroy', $pkb) }}" onsubmit="return confirm('Hapus foto dokumentasi ini?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="documentation_id" value="{{ $documentation->id }}">
                            <button class="text-xs font-semibold text-[var(--red)]" type="submit">Hapus foto</button>
                        </form>
                    @endcan
                </figure>
            @endforeach
        </div>
    @endif
    @can('uploadDocumentation', $pkb)
        <form class="mt-3 flex flex-wrap items-end gap-3" method="POST" enctype="multipart/form-data" action="{{ route('admin.pkb.progress.documentations.store', $pkb) }}">
            @csrf
            <input type="hidden" name="{{ $itemIdField }}" value="{{ $item->id }}">
            <div class="min-w-0 flex-1">
                <label class="form-label" for="file_{{ $itemType }}_{{ $item->id }}">Tambah foto {{ $item->documentation_required ? '(wajib)' : '(opsional)' }}</label>
                <input class="form-field text-sm" id="file_{{ $itemType }}_{{ $item->id }}" name="file" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="preview_{{ $itemType }}_{{ $item->id }}" required>
            </div>
            <button class="btn-secondary text-sm" type="submit">Unggah</button>
            <img class="hidden size-20 rounded-sm border border-[var(--line)] object-cover" id="preview_{{ $itemType }}_{{ $item->id }}" alt="Pratinjau foto {{ $item->name_snapshot }}">
        </form>
    @endcan
</article>
