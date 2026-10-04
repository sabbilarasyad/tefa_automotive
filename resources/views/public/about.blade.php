@extends('layouts.public')

@section('title', 'Tentang Kami')

@section('content')
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="text-center max-w-3xl mx-auto mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-[var(--color-text)]">Tentang TEFA Automotive</h1>
                <p class="mt-4 text-lg text-[var(--color-muted)]">Bengkel profesional yang berkomitmen memberikan layanan servis kendaraan terbaik</p>
            </header>

            <div class="grid gap-12 lg:grid-cols-2 items-center">
                <div>
                    <h2 class="text-2xl font-bold text-[var(--color-text)]">Sejarah Kami</h2>
                    <div class="mt-6 space-y-4 text-[var(--color-muted)]">
                        <p>TEFA Automotive didirikan pada tahun 2010 dengan visi menjadi bengkel terpercaya yang memberikan layanan servis kendaraan berkualitas tinggi dengan harga yang bersaing.</p>
                        <p>Sejak awal, kami berfokus pada tiga hal utama: keahlian teknis, transparansi harga, dan kepuasan pelanggan. Komitmen ini telah membuat kami dipercaya oleh ratusan pelanggan setia di wilayah Jakarta dan sekitarnya.</p>
                        <p>Kami terus mengembangkan kompetensi tim melalui pelatihan rutin dan investasi pada peralatan diagnostik terkini untuk memastikan setiap kendaraan yang masuk ke bengkel kami mendapat penanganan yang tepat dan efisien.</p>
                    </div>
                </div>
                <div class="bg-[var(--color-primary)] rounded-2xl p-8 lg:p-12 text-center text-white">
                    <div class="grid gap-8 sm:grid-cols-3">
                        <div>
                            <div class="text-4xl font-bold">14+</div>
                            <div class="text-sm opacity-90">Tahun Pengalaman</div>
                        </div>
                        <div class="border-l border-white/20 pl-8 sm:border-l-0 sm:border-t sm:border-white/20 sm:pt-8 sm:pl-0">
                            <div class="text-4xl font-bold">500+</div>
                            <div class="text-sm opacity-90">Pelanggan Setia</div>
                        </div>
                        <div class="border-l border-white/20 pl-8 sm:border-l-0 sm:border-t sm:border-white/20 sm:pt-8 sm:pl-0">
                            <div class="text-4xl font-bold">15+</div>
                            <div class="text-sm opacity-90">Mekanik Profesional</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16">
                <h2 class="text-2xl font-bold text-[var(--color-text)] text-center mb-10">Visi & Misi</h2>
                <div class="grid gap-8 md:grid-cols-2">
                    <article class="bg-white p-6 rounded-xl border border-[var(--color-border)]">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="size-12 rounded-lg bg-[var(--color-primary-light)] flex items-center justify-center">
                                <span class="text-xl">🎯</span>
                            </div>
                            <h3 class="text-xl font-semibold text-[var(--color-text)]">Visi</h3>
                        </div>
                        <p class="text-[var(--color-muted)]">Menjadi bengkel pilihan utama di Indonesia yang dikenal karena keahlian teknis, integritas, dan pelayanan pelanggan yang luar biasa.</p>
                    </article>
                    <article class="bg-white p-6 rounded-xl border border-[var(--color-border)]">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="size-12 rounded-lg bg-[var(--color-accent-bg)] flex items-center justify-center">
                                <span class="text-xl">📋</span>
                            </div>
                            <h3 class="text-xl font-semibold text-[var(--color-text)]">Misi</h3>
                        </div>
                        <ul class="space-y-2 text-[var(--color-muted)]">
                            <li class="flex items-center gap-2"><span class="text-[var(--color-primary)]">✓</span> Memberikan layanan servis berkualitas dengan harga transparan</li>
                            <li class="flex items-center gap-2"><span class="text-[var(--color-primary)]">✓</span> Menggunakan sparepart original dan peralatan standar pabrik</li>
                            <li class="flex items-center gap-2"><span class="text-[var(--color-primary)]">✓</span> Membangun hubungan jangka panjang berbasis kepercayaan</li>
                            <li class="flex items-center gap-2"><span class="text-[var(--color-primary)]">✓</span> Mengembangkan kompetensi tim secara berkelanjutan</li>
                        </ul>
                    </article>
                </div>
            </div>

            <div class="mt-16">
                <h2 class="text-2xl font-bold text-[var(--color-text)] text-center mb-10">Nilai-Nilai Kami</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['icon' => '🤝', 'title' => 'Integritas', 'desc' => 'Jujur dan transparan dalam setiap transaksi'],
                        ['icon' => '🏆', 'title' => 'Kualitas', 'desc' => 'Standar tertinggi pada setiap pekerjaan'],
                        ['icon' => '❤️', 'title' => 'Peduli', 'desc' => 'Mengutamakan kepuasan dan keamanan pelanggan'],
                        ['icon' => '🚀', 'title' => 'Inovasi', 'desc' => 'Terus berkembang dengan teknologi terbaru'],
                    ] as $value)
                        <div class="text-center p-6 bg-white rounded-xl border border-[var(--color-border)]">
                            <div class="text-3xl mb-3">{{ $value['icon'] }}</div>
                            <h3 class="font-semibold text-[var(--color-text)]">{{ $value['title'] }}</h3>
                            <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $value['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection