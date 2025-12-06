<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by bootstrap/app.php and all of them will
| be assigned to the "admin" middleware group and "admin" prefix.
|
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // User Management
    Route::resource('users', UserController::class);
    
    // Student Management
    Route::resource('students', StudentController::class);
    
    // Class Management
    Route::resource('classes', ClassController::class);
    Route::post('classes/{class}/enroll', [ClassController::class, 'enrollStudent'])
        ->name('classes.enroll');
    Route::delete('classes/{class}/students/{student}', [ClassController::class, 'unenrollStudent'])
        ->name('classes.unenroll');
    
    // Schedule Management
    // Route::resource('schedules', ScheduleController::class);
    
    // // Reports
    // Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    // Route::post('reports/attendance', [ReportController::class, 'attendanceReport'])
    //     ->name('reports.attendance');
    // Route::post('reports/homework', [ReportController::class, 'homeworkReport'])
    //     ->name('reports.homework');
    // Route::post('reports/sms', [ReportController::class, 'smsReport'])
    //     ->name('reports.sms');
    
    // // Settings
    // Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    // Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    // Route::get('settings/sms', [SettingsController::class, 'sms'])->name('settings.sms');
    // Route::put('settings/sms', [SettingsController::class, 'updateSms'])->name('settings.sms.update');
    
    // // Activity Logs
    // Route::get('activity-logs', [DashboardController::class, 'activityLogs'])
    //     ->name('activity-logs');
    
    // // SMS Logs
    // Route::get('sms-logs', [DashboardController::class, 'smsLogs'])
    //     ->name('sms-logs');
});