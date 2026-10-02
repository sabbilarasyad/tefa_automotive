<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.pkb.index');
        }

        return view('auth.login');
    }

    public function store(WebLoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->validated() + ['is_active' => true])) {
            return back()->withErrors([
                'username' => 'Username atau password salah, atau akun tidak aktif.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.pkb.index'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
