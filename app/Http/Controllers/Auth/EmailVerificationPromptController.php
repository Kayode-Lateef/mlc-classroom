<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // If already verified, redirect to role-based dashboard
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        // Show verification prompt
        return view('auth.verify-email');
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('superadmin.dashboard'));
        } elseif ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->isTeacher()) {
            return redirect()->intended(route('teacher.dashboard'));
        } elseif ($user->isParent()) {
            return redirect()->intended(route('parent.dashboard'));
        }

        return redirect()->route('login');
    }
}