<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Get role-specific statistics
        $userStats = $this->getUserStats($user);
        
        // Get recent activity logs
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('profile.edit', [
            'user' => $user,
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validated();
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('profile_photos', 'public');
        }
        
        // Check if email changed
        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }
        
        $user->fill($validated);
        $user->save();
        
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'updated_profile',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Updated profile information',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Current password is required.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.required' => 'New password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('profile.edit')
                ->withErrors($validator, 'updatePassword')
                ->withInput();
        }
        
        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'changed_password',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Changed password',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('profile.edit')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('profile.edit')
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = $request->user();
        
        // Store notification preferences (you can add columns to users table or create separate table)
        // For now, we'll just log it
        
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'updated_notification_preferences',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Updated notification preferences',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('profile.edit')
            ->with('success', 'Notification preferences updated successfully!');
    }

    /**
     * Delete the user's profile photo.
     */
    public function deletePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            
            $user->update(['profile_photo' => null]);
            
            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'deleted_profile_photo',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => 'Deleted profile photo',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return redirect()->route('profile.edit')
                ->with('success', 'Profile photo deleted successfully!');
        }
        
        return redirect()->route('profile.edit')
            ->with('info', 'No profile photo to delete.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Password is required to delete your account.',
            'password.current_password' => 'The password is incorrect.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('profile.edit')
                ->withErrors($validator, 'userDeletion')
                ->withInput();
        }
        
        $user = $request->user();
        
        // Log activity before deletion
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'deleted_account',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Deleted own account',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        Auth::logout();
        
        // Delete profile photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')
            ->with('success', 'Your account has been deleted successfully.');
    }
    
    /**
     * Get user statistics based on role
     */
    protected function getUserStats($user): array
    {
        $stats = [
            'member_since' => $user->created_at->format('d M Y'),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
            'total_actions' => ActivityLog::where('user_id', $user->id)->count(),
        ];
        
        if ($user->isTeacher()) {
            $stats['classes'] = $user->teachingClasses()->count();
            $stats['students'] = $user->teachingClasses()->withCount('enrollments')->get()->sum('enrollments_count');
            $stats['resources'] = $user->uploadedResources()->count();
        } elseif ($user->isParent()) {
            $stats['children'] = $user->children()->count();
        } elseif ($user->isSuperAdmin() || $user->isAdmin()) {
            $stats['users_created'] = ActivityLog::where('user_id', $user->id)
                ->where('action', 'created_user')
                ->count();
        }
        
        return $stats;
    }
}