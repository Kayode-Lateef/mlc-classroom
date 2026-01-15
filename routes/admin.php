<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\HomeworkController;
use App\Http\Controllers\Admin\ProgressSheetController;
use App\Http\Controllers\Admin\LearningResourceController;
use App\Http\Controllers\Admin\NotificationController;
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

Route::middleware(['auth', 'role:admin', 'check.status'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ========== USER MANAGEMENT ==========
    
    // Users (Cannot manage superadmins)
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::resource('users', UserController::class);
    
    // ========== ACADEMIC MANAGEMENT ==========
    
    // Students
    Route::resource('students', StudentController::class);
    // Add this to your admin routes
    Route::post('students/check-duplicate', [StudentController::class, 'checkDuplicate'])
    ->name('students.check-duplicate');
    
    // Classes
    Route::resource('classes', ClassController::class);
    Route::post('classes/{class}/enroll', [ClassController::class, 'enrollStudent'])->name('classes.enroll');
    Route::delete('classes/{class}/unenroll/{student}', [ClassController::class, 'unenrollStudent'])->name('classes.unenroll');
    
    // Schedules
    Route::resource('schedules', ScheduleController::class);
    
    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/{date}/{classId}/{scheduleId}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('attendance/{date}/{classId}/{scheduleId}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('attendance/{date}/{classId}/{scheduleId}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('attendance/{date}/{classId}/{scheduleId}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
        
    // Homework
    Route::get('homework/{homework}/download', [HomeworkController::class, 'download'])
        ->name('homework.download');
    Route::post('homework/submissions/{submission}/grade', [HomeworkController::class, 'gradeSubmission'])
        ->name('homework.submissions.grade');

    Route::resource('homework', HomeworkController::class);
        
    // Progress Sheets
    Route::get('progress-sheets/get-students', [ProgressSheetController::class, 'getStudents'])
        ->name('progress-sheets.get-students');
    Route::resource('progress-sheets', ProgressSheetController::class);
        
    // ========== RESOURCES ==========
    
    // Learning Resources
    Route::get('resources/{resource}/download', [LearningResourceController::class, 'download'])
        ->name('resources.download');
    Route::resource('resources', LearningResourceController::class);
        
    // ========== COMMUNICATION ==========
    
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
    
    // ========== SETTINGS ==========
    
    // Settings (Admin-level settings, not system settings)
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});