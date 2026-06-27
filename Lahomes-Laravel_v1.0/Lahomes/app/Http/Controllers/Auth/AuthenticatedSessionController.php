<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // 🛠️ ৪০৪ এরর ফিক্স: রোল অনুযায়ী সঠিক ড্যাশবোর্ডে রিডাইরেক্ট লজিক
        return match ($request->user()->role) {
            'admin'    => redirect()->intended(route('admin.dashboard')),
            'agent'    => redirect()->intended(route('agent.dashboard')),
            default    => redirect()->intended(route('dashboard')), // customer এর জন্য
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // 🛠️ লগআউট করার পর সরাসরি লগইন পেজে বা মেইন হোমপেজে রিডাইরেক্ট
        return redirect()->route('login');
    }
}
