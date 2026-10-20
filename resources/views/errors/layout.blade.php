<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kesalahan' }} &middot; TEFA Automotive</title>
    @vite(['resources/css/app.css'])
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--paper);
        }
        .error-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            padding: 2.5rem 2rem;
            max-width: 28rem;
            width: 100%;
            text-align: center;
        }
        .error-code {
            font-size: 4.5rem;
            font-weight: 800;
            color: var(--orange);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 1rem;
        }
        .error-message {
            color: var(--muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .error-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.15s, color 0.15s;
        }
        .btn-primary {
            background: var(--orange);
            color: white;
        }
        .btn-primary:hover {
            background: var(--orange-deep);
        }
        .btn-secondary {
            border: 1px solid var(--line);
            background: white;
            color: var(--ink);
        }
        .btn-secondary:hover {
            background: var(--paper);
        }
    </style>
</head>
<body class="min-h-screen antialiased bg-[var(--paper)] text-[var(--ink)]">
    <div class="error-container">
        <div class="error-card">
            <div class="error-code">@yield('code')</div>
            <h1 class="error-title">@yield('title')</h1>
            <p class="error-message">@yield('message')</p>
            <div class="error-actions">
                <a href="{{ route('admin.pkb.index') }}" class="btn btn-primary">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>