<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\PermissionController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\SystemSettingsController;
use App\Http\Controllers\SuperAdmin\StudentController;
use App\Http\Controllers\SuperAdmin\ClassController;
use App\Http\Controllers\SuperAdmin\ScheduleController;
use App\Http\Controllers\SuperAdmin\AttendanceController;
use App\Http\Controllers\SuperAdmin\HomeworkController;
use App\Http\Controllers\SuperAdmin\ProgressSheetController;
use App\Http\Controllers\SuperAdmin\LearningResourceController;
use App\Http\Controllers\SuperAdmin\SmsConfigurationController;
use App\Http\Controllers\SuperAdmin\SmsLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuperAdmin\ReportController;



/*
|--------------------------------------------------------------------------
| SuperAdmin Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by bootstrap/app.php and all of them will
| be assigned to the "superadmin" middleware group and "superadmin" prefix.
|
*/


    Route::middleware(['auth', 'role:superadmin'])->group(function () {

    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ========== SYSTEM MANAGEMENT ==========
    
    // User Management
    Route::resource('users', UserManagementController::class);
    Route::post('users/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('users.assignRole');
    Route::post('users/{user}/assign-permissions', [UserManagementController::class, 'assignPermissions'])->name('users.assignPermissions');
    Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggleStatus');
    
    // Roles
    Route::resource('roles', RoleController::class);
    
    // Permissions
    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/bulk-assign', [PermissionController::class, 'bulkAssign'])->name('permissions.bulk-assign');
    
    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    
    // System Settings
    Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SystemSettingsController::class, 'update'])->name('settings.update');
    
    // ========== ACADEMIC MANAGEMENT ==========
    
    // Students
    Route::resource('students', StudentController::class);
    
    // Classes
    Route::resource('classes', ClassController::class);
    Route::post('classes/{class}/enroll', [ClassController::class, 'enrollStudent'])->name('classes.enroll');
    Route::delete('classes/{class}/unenroll/{student}', [ClassController::class, 'unenrollStudent'])->name('classes.unenroll');
    
    // Schedules
    Route::resource('schedules', ScheduleController::class);
    
    // Attendance
    Route::resource('attendance', AttendanceController::class);
    
    // Homework
    Route::resource('homework', HomeworkController::class);
    
    // Progress Sheets
    Route::resource('progress-sheets', ProgressSheetController::class);
    
    // ========== RESOURCES ==========
    
    // Learning Resources
    Route::resource('resources', LearningResourceController::class);
    
    // ========== COMMUNICATION ==========
    
    // SMS Configuration
    Route::get('sms-config', [SmsConfigurationController::class, 'index'])->name('sms-config.index');
    Route::post('sms-config', [SmsConfigurationController::class, 'update'])->name('sms-config.update');
    Route::post('sms-config/test', [SmsConfigurationController::class, 'test'])->name('sms-config.test');
    
    // SMS Logs
    Route::get('sms-logs', [SmsLogController::class, 'index'])->name('sms-logs.index');
    Route::get('sms-logs/{smsLog}', [SmsLogController::class, 'show'])->name('sms-logs.show');
    
    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
    
    // ========== REPORTS ==========
    
    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/students', [ReportController::class, 'students'])->name('reports.students');
    Route::get('reports/classes', [ReportController::class, 'classes'])->name('reports.classes');
    Route::get('reports/homework', [ReportController::class, 'homework'])->name('reports.homework');
    Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
});