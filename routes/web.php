<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\NotificationBellController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    // Redirect to login if not authenticated
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    // Redirect to appropriate dashboard based on role
    return redirect()->route(auth()->user()->role . '.dashboard');
});

// Breeze authentication routes (login, register, etc.)
require __DIR__.'/auth.php';

// Role-based dashboard redirects after login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Redirect to role-specific dashboard
        switch ($user->role) {
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'parent':
                return redirect()->route('parent.dashboard');
            default:
                auth()->logout();
                return redirect()->route('login')
                    ->with('error', 'Invalid user role.');
        }
    })->name('dashboard');
});


// Profile Routes (Updated - Replace existing profile routes with these)
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    // View profile
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    
    // Update profile information
    Route::patch('/update', [ProfileController::class, 'updateProfile'])->name('update');
    
    // Update password
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    
    // Update notification preferences
    Route::patch('/notifications', [ProfileController::class, 'updateNotifications'])->name('notifications.update');
    
    // Delete profile photo
    Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('photo.delete');
    
    // Delete account
    Route::delete('/destroy', [ProfileController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Notification Bell API Routes (All Authenticated Users)
|--------------------------------------------------------------------------
| These routes handle AJAX requests for the notification bell in the navbar
*/
Route::middleware(['auth'])->prefix('notifications')->group(function () {
    // Get unread notifications for bell
    Route::get('/unread', [NotificationBellController::class, 'getUnread'])->name('notifications.unread');
    
    // Get unread count
    Route::get('/count', [NotificationBellController::class, 'getCount'])->name('notifications.count');
    
    // Mark single notification as read
    Route::post('/{id}/read', [NotificationBellController::class, 'markAsRead'])->name('notifications.mark-read');
    
    // Mark all notifications as read
    Route::post('/mark-all-read', [NotificationBellController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Get all notifications (paginated)
    Route::get('/all', [NotificationBellController::class, 'getAll'])->name('notifications.all');
});