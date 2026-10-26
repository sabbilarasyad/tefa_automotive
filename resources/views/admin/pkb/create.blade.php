@extends('layouts.app')

@section('title', 'Servis Baru')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Penerimaan kendaraan</p>
            <h1 class="text-2xl font-bold tracking-normal">Servis Baru</h1>
        </div>
        <a href="{{ route('admin.pkb.index') }}" class="btn-secondary text-sm">Daftar PKB</a>
    </div>

    <form method="POST" action="{{ route('admin.pkb.store') }}" id="pkb-form" class="space-y-5">
        @csrf
        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid size-8 place-items-center rounded-full bg-[var(--orange-light)] text-sm font-bold text-[var(--orange-deep)]">01</span>
                <h2 class="text-base font-bold">Customer & kendaraan</h2>
            </div>
            <div class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <label class="form-label mb-0" for="customer-search">Customer</label>
                        <button type="button" id="new-customer-toggle" class="text-sm font-semibold text-[var(--orange-deep)]">+ Customer baru</button>
                    </div>
                    <div id="existing-customer-fields">
                        <input class="form-field" id="customer-search" list="customer-options" placeholder="Cari nama atau nomor HP" autocomplete="off">
                        <datalist id="customer-options">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->name }} · {{ $customer->phone }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" id="customer_id" name="customer_id" value="{{ old('customer_id') }}">
                    </div>
                    <div id="new-customer-fields" class="hidden space-y-3">
                        <input class="form-field" name="customer[name]" placeholder="Nama customer" value="{{ old('customer.name') }}">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input class="form-field" name="customer[phone]" placeholder="Nomor HP" value="{{ old('customer.phone') }}">
                            <input class="form-field" name="customer[email]" type="email" placeholder="Email (opsional)" value="{{ old('customer.email') }}">
                        </div>
                        <input class="form-field" name="customer[address]" placeholder="Alamat (opsional)" value="{{ old('customer.address') }}">
                    </div>
                    <div id="existing-vehicle-fields" class="space-y-2">
                        <label class="form-label" for="vehicle-select">Kendaraan terdaftar</label>
                        <select class="form-field" id="vehicle-select"><option value="">Pilih customer terlebih dahulu</option></select>
                        <input type="hidden" id="vehicle_id" name="vehicle_id" value="{{ old('vehicle_id') }}">
                    </div>
                    <button type="button" id="new-vehicle-toggle" class="text-sm font-semibold text-[var(--orange-deep)]">+ Kendaraan baru</button>
                    <div id="new-vehicle-fields" class="hidden grid gap-3 sm:grid-cols-2">
                        <input class="form-field" name="vehicle[plate_number]" placeholder="Nomor polisi">
                        <input class="form-field" name="vehicle[brand]" placeholder="Merek">
                        <input class="form-field" name="vehicle[model]" placeholder="Model">
                        <input class="form-field" name="vehicle[current_km]" type="number" min="0" placeholder="KM saat ini">
                        <input class="form-field" name="vehicle[year]" type="number" min="1900" max="2100" placeholder="Tahun">
                        <input class="form-field" name="vehicle[color]" placeholder="Warna">
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="form-label" for="complaint">Keluhan customer</label>
                        <textarea class="form-field min-h-28" id="complaint" name="complaint" required>{{ old('complaint') }}</textarea>
                    </div>
                    <div>
                        <label class="form-label" for="inspection_result">Hasil pemeriksaan awal</label>
                        <textarea class="form-field min-h-28" id="inspection_result" name="inspection_result">{{ old('inspection_result') }}</textarea>
                    </div>
                    <div>
                        <label class="form-label" for="mechanic_id">Mekanik bertugas (opsional)</label>
                        <select class="form-field" id="mechanic_id" name="mechanic_id">
                            <option value="">Tugaskan dari halaman detail</option>
                            @foreach ($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}" @selected(old('mechanic_id') == $mechanic->id)>{{ $mechanic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3"><span class="grid size-8 place-items-center rounded-full bg-[var(--orange-light)] text-sm font-bold text-[var(--orange-deep)]">02</span><h2 class="text-base font-bold">Jasa servis</h2></div>
                <button type="button" class="btn-secondary text-sm" id="add-service">Tambah jasa</button>
            </div>
            <div id="service-rows" class="space-y-3"></div>
        </section>

        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3"><span class="grid size-8 place-items-center rounded-full bg-[var(--orange-light)] text-sm font-bold text-[var(--orange-deep)]">03</span><h2 class="text-base font-bold">Sparepart rencana</h2></div>
                <button type="button" class="btn-secondary text-sm" id="add-part">Tambah sparepart</button>
            </div>
            <div id="part-rows" class="space-y-3"></div>
        </section>

        <section class="sticky bottom-0 z-10 border border-[#cbd8d2] bg-white/95 p-4 shadow-[0_-8px_24px_rgba(23,35,33,0.06)] backdrop-blur sm:flex sm:items-center sm:justify-between sm:px-6">
            <div><p class="text-xs font-semibold uppercase text-[var(--muted)]">Estimasi biaya</p><output id="estimate" class="text-2xl font-bold text-[var(--orange-deep)]">Rp0</output><p class="text-xs text-[var(--muted)]">Total dihitung ulang dari harga master saat disimpan.</p></div>
            <button type="submit" class="btn-primary mt-3 w-full sm:mt-0 sm:w-auto">Simpan PKB</button>
        </section>
    </form>

    <script>
        const customers = @json($customerOptions);
        const services = @json($serviceOptions);
        const spareparts = @json($sparepartOptions);
        const serviceRows = document.querySelector('#service-rows');
        const partRows = document.querySelector('#part-rows');
        let serviceIndex = 0;
        let partIndex = 0;

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character]);
        }

        function optionMarkup(items, label, priceKey) {
            return `<option value="">Pilih ${label}</option>${items.map(item => `<option value="${item.id}" data-price="${item[priceKey]}">${escapeHtml(item.name)}</option>`).join('')}`;
        }

        function addService() {
            const row = document.createElement('div');
            row.className = 'grid gap-3 border border-[#e3eae6] bg-[#f8faf9] p-3 sm:grid-cols-[minmax(0,1fr)_130px_42px]';
            row.innerHTML = `<select class="form-field service-choice" name="services[${serviceIndex}][service_id]" required>${optionMarkup(services, 'jasa', 'price')}</select><input class="form-field service-qty" type="number" name="services[${serviceIndex}][qty]" value="1" min="0.001" step="0.001" aria-label="Jumlah jasa" required><button class="btn-secondary remove-row" type="button" aria-label="Hapus jasa">×</button>`;
            serviceRows.append(row);
            serviceIndex++;
            updateEstimate();
        }

        function addPart() {
            const row = document.createElement('div');
            row.className = 'grid gap-3 border border-[#e3eae6] bg-[#f8faf9] p-3 sm:grid-cols-[minmax(0,1fr)_130px_auto_42px]';
            row.innerHTML = `<select class="form-field part-choice" name="spareparts[${partIndex}][sparepart_id]">${optionMarkup(spareparts, 'sparepart', 'price')}</select><input class="form-field part-qty" type="number" name="spareparts[${partIndex}][qty_planned]" value="1" min="0.001" step="0.001" aria-label="Jumlah sparepart"><span class="stock-note self-center text-xs font-semibold text-[var(--muted)]">Pilih sparepart</span><button class="btn-secondary remove-row" type="button" aria-label="Hapus sparepart">×</button>`;
            partRows.append(row);
            partIndex++;
            updateEstimate();
        }

        function updateEstimate() {
            let total = 0;
            serviceRows.querySelectorAll('.service-choice').forEach(select => {
                total += Number(select.selectedOptions[0]?.dataset.price || 0) * Number(select.closest('div').querySelector('.service-qty').value || 0);
            });
            const choices = [...partRows.querySelectorAll('.part-choice')];
            choices.forEach(select => {
                const quantity = Number(select.closest('div').querySelector('.part-qty').value || 0);
                const item = spareparts.find(part => String(part.id) === select.value);
                if (!item) return;
                total += item.price * quantity;
                const planned = choices.reduce((sum, current) => current.value === select.value ? sum + Number(current.closest('div').querySelector('.part-qty').value || 0) : sum, 0);
                const note = select.closest('div').querySelector('.stock-note');
                note.textContent = planned > item.stock ? `Kurang ${Number(planned - item.stock).toLocaleString('id-ID')} ${item.unit}` : `Stok ${Number(item.stock).toLocaleString('id-ID')} ${item.unit}`;
                note.className = `stock-note self-center text-xs font-semibold ${planned > item.stock ? 'text-[var(--red)]' : 'text-[var(--orange-deep)]'}`;
            });
            document.querySelector('#estimate').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
        }

        const customerSearch = document.querySelector('#customer-search');
        const customerId = document.querySelector('#customer_id');
        const vehicleSelect = document.querySelector('#vehicle-select');
        const vehicleId = document.querySelector('#vehicle_id');

        function setFieldsDisabled(container, disabled) {
            container.querySelectorAll('input, select, textarea').forEach(field => field.disabled = disabled);
        }

        setFieldsDisabled(document.querySelector('#new-customer-fields'), true);
        setFieldsDisabled(document.querySelector('#new-vehicle-fields'), true);

        function syncCustomerFromSearch() {
            const customer = customers.find(item => `${item.name} · ${item.phone}` === customerSearch.value);
            customerId.value = customer?.id || '';
            vehicleSelect.innerHTML = '<option value="">Pilih kendaraan</option>' + (customer?.vehicles || []).map(vehicle => `<option value="${vehicle.id}">${escapeHtml(vehicle.plate_number)} · ${escapeHtml(vehicle.brand)} ${escapeHtml(vehicle.model)}</option>`).join('');
            vehicleId.value = '';
        }
        customerSearch.addEventListener('input', syncCustomerFromSearch);
        vehicleSelect.addEventListener('change', () => vehicleId.value = vehicleSelect.value);

        document.querySelector('#new-customer-toggle').addEventListener('click', event => {
            const useNew = document.querySelector('#new-customer-fields').classList.toggle('hidden') === false;
            setFieldsDisabled(document.querySelector('#new-customer-fields'), !useNew);
            document.querySelector('#existing-customer-fields').classList.toggle('hidden', useNew);
            customerId.disabled = useNew;
            customerId.value = '';
            if (!useNew) syncCustomerFromSearch();
            if (useNew) {
                vehicleSelect.value = '';
                vehicleId.value = '';
                document.querySelector('#existing-vehicle-fields').classList.add('hidden');
                document.querySelector('#new-vehicle-fields').classList.remove('hidden');
                setFieldsDisabled(document.querySelector('#new-vehicle-fields'), false);
                setFieldsDisabled(document.querySelector('#existing-vehicle-fields'), true);
                vehicleId.disabled = true;
                document.querySelector('#new-vehicle-toggle').disabled = true;
                document.querySelector('#new-vehicle-toggle').textContent = 'Kendaraan baru wajib untuk customer baru';
            } else {
                document.querySelector('#existing-vehicle-fields').classList.remove('hidden');
                setFieldsDisabled(document.querySelector('#existing-vehicle-fields'), false);
                document.querySelector('#new-vehicle-toggle').disabled = false;
                document.querySelector('#new-vehicle-toggle').textContent = '+ Kendaraan baru';
            }
            event.currentTarget.textContent = useNew ? 'Pilih customer terdaftar' : '+ Customer baru';
        });
        document.querySelector('#new-vehicle-toggle').addEventListener('click', event => {
            const useNew = document.querySelector('#new-vehicle-fields').classList.toggle('hidden') === false;
            setFieldsDisabled(document.querySelector('#new-vehicle-fields'), !useNew);
            document.querySelector('#existing-vehicle-fields').classList.toggle('hidden', useNew);
            setFieldsDisabled(document.querySelector('#existing-vehicle-fields'), useNew);
            vehicleId.disabled = useNew;
            vehicleId.value = '';
            event.currentTarget.textContent = useNew ? 'Pilih kendaraan terdaftar' : '+ Kendaraan baru';
        });

        document.querySelector('#add-service').addEventListener('click', addService);
        document.querySelector('#add-part').addEventListener('click', addPart);
        document.querySelector('#pkb-form').addEventListener('change', updateEstimate);
        document.querySelector('#pkb-form').addEventListener('input', updateEstimate);
        document.querySelector('#pkb-form').addEventListener('click', event => {
            if (event.target.classList.contains('remove-row')) {
                event.target.closest('div.grid').remove();
                updateEstimate();
            }
        });
        addService();
    </script>
@endsection
