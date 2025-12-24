<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Validation\ValidationException;

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
        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            // ✅ CHECK 1: Account Status (suspended, inactive, banned)
            if (isset($user->status)) {
                $messages = [
                    'suspended' => 'Your account has been suspended. Please contact an administrator.',
                    'inactive' => 'Your account is inactive. Please contact an administrator to activate your account.',
                    'banned' => 'Your account has been permanently banned. Please contact support.',
                ];
                
                if (isset($messages[$user->status])) {
                    throw ValidationException::withMessages([
                        'email' => [$messages[$user->status]],
                    ]);
                }
            }
            
            // ✅ CHECK 2: Email Verification
            if (!$user->email_verified_at) {
                throw ValidationException::withMessages([
                    'email' => ['Your email address is not verified. Please verify your email or contact an administrator.'],
                ]);
            }
        }
        
        // All checks passed - proceed with authentication
        $request->authenticate();
        $request->session()->regenerate();
        
        // Get authenticated user  
        $user = auth()->user();
        
        // Redirect based on role
        $redirectRoute = match($user->role) {
            'superadmin' => 'superadmin.dashboard',
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent' => 'parent.dashboard',
            default => 'login',
        };
        
        return redirect()->intended(route($redirectRoute));
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
