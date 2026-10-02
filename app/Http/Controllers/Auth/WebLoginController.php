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
            if (request()->user()->hasPermission('pkb.view_own') && ! request()->user()->hasPermission('pkb.view_all')) {
                return redirect()->route('mechanic.tasks');
            }

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
        $user = $request->user();
        $defaultRoute = $user->hasPermission('pkb.view_own') && ! $user->hasPermission('pkb.view_all')
            ? route('mechanic.tasks')
            : route('admin.pkb.index');

        return redirect()->intended($defaultRoute);
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
