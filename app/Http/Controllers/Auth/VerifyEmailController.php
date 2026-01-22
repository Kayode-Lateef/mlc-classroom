<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     * This handles both logged-in and logged-out verification scenarios.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Verify the hash matches
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')
                ->with('error', 'Invalid verification link. Please request a new verification email.');
        }

        // Check if the signature is valid (expiration check)
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')
                ->with('error', 'This verification link has expired. Please request a new verification email.');
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            // If user is logged in, redirect to dashboard
            if (Auth::check()) {
                return $this->redirectBasedOnRole($user, true);
            }
            
            // If not logged in, redirect to login with message
            return redirect()->route('login')
                ->with('info', 'Your email is already verified. Please log in.');
        }

        // Mark as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // If user is already logged in, redirect to dashboard
        if (Auth::check() && Auth::id() === $user->id) {
            return $this->redirectBasedOnRole($user, true);
        }

        // If not logged in, log them in automatically then redirect to dashboard
        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user, true);
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    protected function redirectBasedOnRole($user, $verified = false)
    {
        $verifiedParam = $verified ? '?verified=1' : '';

        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('superadmin.dashboard') . $verifiedParam)
                ->with('success', 'Email verified successfully! Welcome to MLC Classroom.');
        } elseif ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard') . $verifiedParam)
                ->with('success', 'Email verified successfully! Welcome to MLC Classroom.');
        } elseif ($user->isTeacher()) {
            return redirect()->intended(route('teacher.dashboard') . $verifiedParam)
                ->with('success', 'Email verified successfully! Welcome to MLC Classroom.');
        } elseif ($user->isParent()) {
            return redirect()->intended(route('parent.dashboard') . $verifiedParam)
                ->with('success', 'Email verified successfully! Welcome to MLC Classroom.');
        }

        // Fallback to login if role is not recognized
        return redirect()->route('login')
            ->with('success', 'Email verified successfully! Please log in.');
    }
}