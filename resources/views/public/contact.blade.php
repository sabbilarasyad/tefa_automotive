@extends('layouts.public')

@section('title', 'Kontak')

@section('content')
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="text-center max-w-3xl mx-auto mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-[var(--ink)]">Hubungi Kami</h1>
                <p class="mt-4 text-lg text-[var(--muted)]">Kami siap membantu kebutuhan servis kendaraan Anda</p>
            </header>

            <div class="grid gap-12 lg:grid-cols-2">
                <!-- Contact Info -->
                <div>
                    <h2 class="text-2xl font-bold text-[var(--ink)] mb-6">Informasi Kontak</h2>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-lg bg-[var(--orange-light)] flex items-center justify-center shrink-0">
                                <svg class="size-6 text-[var(--orange)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[var(--ink)]">Alamat</h3>
                                <p class="text-[var(--muted)]">{{ config('tefa.address') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-lg bg-[var(--orange-light)] flex items-center justify-center shrink-0">
                                <svg class="size-6 text-[var(--orange)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[var(--ink)]">Telepon</h3>
                                <p class="text-[var(--muted)]">{{ config('tefa.phone') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-lg bg-[var(--orange-light)] flex items-center justify-center shrink-0">
                                <svg class="size-6 text-[var(--orange)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[var(--ink)]">Email</h3>
                                <p class="text-[var(--muted)]">{{ config('tefa.email') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-lg bg-[var(--orange-light)] flex items-center justify-center shrink-0">
                                <svg class="size-6 text-[var(--orange)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[var(--ink)]">Jam Operasional</h3>
                                <p class="text-[var(--muted)]">{{ config('tefa.business_hours') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-[var(--paper)] rounded-2xl p-6 sm:p-8 border border-[var(--line)]">
                    <h2 class="text-2xl font-bold text-[var(--ink)] mb-6">Kirim Pesan</h2>

                    @if (session('success'))
                        <div class="mb-6 p-4 bg-[var(--success)]/10 border border-[var(--success)] text-[var(--success)] rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.contact.store') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label class="form-label" for="name">Nama Lengkap</label>
                            <input class="form-field" id="name" name="name" type="text" required maxlength="150" autocomplete="name">
                            @error('name')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="email">Email</label>
                            <input class="form-field" id="email" name="email" type="email" required maxlength="150" autocomplete="email">
                            @error('email')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="phone">Telepon (Opsional)</label>
                            <input class="form-field" id="phone" name="phone" type="tel" maxlength="30" autocomplete="tel">
                            @error('phone')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="subject">Subjek</label>
                            <input class="form-field" id="subject" name="subject" type="text" required maxlength="200" autocomplete="off">
                            @error('subject')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="message">Pesan</label>
                            <textarea class="form-field" id="message" name="message" rows="5" required maxlength="2000" autocomplete="off"></textarea>
                            @error('message')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn-primary w-full sm:w-auto">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection