<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (!auth()->attempt($credentials, $remember)) {
            return back()->withErrors(['email' => __('auth.failed')])->withInput();
        }

        $user = auth()->user();

        if ($user->is_blocked) {
            auth()->logout();
            return back()->withErrors(['email' => __('auth.blocked')]);
        }

        $user->update(['last_login_at' => now()]);
        session(['locale' => $user->language, 'currency' => $user->currency]);

        // Show "Welcome to v1.1.0" popup once per browser (cookie-based)
        // Only set the session flag if the cookie hasn't been seen yet
        if (!$request->cookie('v110_seen')) {
            session(['show_update_popup' => true]);
        }

        // Redirect based on role
        if ($user->is_admin) {
            // Send admin the platform update email once per version per session
            $currentVersion = config('bybhub.version', '1.1.0');
            if (session('admin_update_email_sent') !== $currentVersion) {
                session(['admin_update_email_sent' => $currentVersion]);
                try {
                    app(\App\Services\EmailService::class)->sendAdminPlatformUpdate($user);
                } catch (\Throwable $e) { /* silent */ }
            }
            return redirect()->route('admin.dashboard');
        }

        if ($user->is_vendor && $user->vendor_status === 'active') {
            return redirect()->intended(route('vendor.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
    }
}
