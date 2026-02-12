<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // ======================================================
        // CHECK 1: Account Status (suspended, banned, inactive)
        // ======================================================
        if (in_array($user->status, ['suspended', 'banned', 'inactive'])) {
            auth()->logout();
            
            $messages = [
                'suspended' => 'Your account has been suspended. Please contact administration.',
                'banned'    => 'Your account has been banned. Please contact administration.',
                'inactive'  => 'Your account is inactive. Please contact administration.',
            ];
            
            return redirect()->route('login')
                ->with('error', $messages[$user->status] ?? 'Your account is not active.');
        }

        // ======================================================
        // CHECK 2: Maintenance Mode (WIRED to System Settings)
        //
        // When SuperAdmin enables maintenance_mode in Settings:
        // - SuperAdmin & Admin: access normally
        // - Teacher & Parent: see maintenance page
        // - Logout route always accessible
        // ======================================================
        if ($this->isMaintenanceMode() && !$this->canBypassMaintenance($user)) {
            // Always allow logout so users aren't trapped
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            $message = SystemSetting::get(
                'maintenance_message',
                'The system is currently undergoing maintenance. Please check back later.'
            );

            // Use the errors/503 view if it exists, otherwise use the standalone maintenance view
            $view = view()->exists('errors.503') ? 'errors.503' : 'maintenance';

            return response()->view($view, [
                'message' => $message,
            ], 503);
        }

        return $next($request);
    }

    /**
     * Check if the system is in maintenance mode.
     * 
     * IMPORTANT: getAllCached() returns raw DB strings.
     * The string "false" is truthy in PHP, so we MUST use filter_var().
     * This is the same bug that affected checkboxes in the settings view.
     */
    protected function isMaintenanceMode(): bool
    {
        try {
            $value = SystemSetting::get('maintenance_mode', false);
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            // If settings table doesn't exist yet, don't block anything
            return false;
        }
    }

    /**
     * SuperAdmins and Admins bypass maintenance mode.
     */
    protected function canBypassMaintenance($user): bool
    {
        return in_array($user->role, ['superadmin', 'admin']);
    }
}