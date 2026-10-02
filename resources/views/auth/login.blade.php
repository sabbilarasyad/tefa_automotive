<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Masuk · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="grid min-h-screen place-items-center px-4 py-10">
        <main class="w-full max-w-md border border-[#dce5e1] bg-white p-7 shadow-sm sm:p-9">
            <div class="mb-8 flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-md bg-[#126b56] font-black text-white">TA</span>
                <div><p class="font-bold">TEFA Automotive</p><p class="text-sm text-[#687671]">Masuk ke sistem bengkel</p></div>
            </div>
            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="form-label" for="username">Username</label>
                    <input class="form-field" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
                    @error('username')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="password">Password</label>
                    <input class="form-field" id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
                </div>
                <button class="btn-primary w-full" type="submit">Masuk</button>
            </form>
        </main>
    </body>
</html>