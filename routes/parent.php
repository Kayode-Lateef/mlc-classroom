<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parent\DashboardController;
use App\Http\Controllers\Parent\StudentController;
use App\Http\Controllers\Parent\AttendanceController;
use App\Http\Controllers\Parent\HomeworkController;
use App\Http\Controllers\Parent\ProgressController;
use App\Http\Controllers\Parent\ResourceController;

    Route::middleware(['auth', 'role:parent', 'check.status'])->group(function () {

    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ========== MY CHILDREN ==========
    
    // Students (Only parent's children)
    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');

    
    // ========== ACADEMIC ==========
    
    // Attendance (View only for parent's children)
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/{student}', [AttendanceController::class, 'show'])->name('attendance.show');
    
    // Homework (View and submit for parent's children)
    Route::get('homework', [HomeworkController::class, 'index'])->name('homework.index');
    Route::get('homework/{homework}', [HomeworkController::class, 'show'])->name('homework.show');
    Route::post('homework/{homework}/submit', [HomeworkController::class, 'submit'])->name('homework.submit');
        Route::get('homework/{homework}/download', [HomeworkController::class, 'download'])
    ->name('homework.download');
    
    
    // Progress Reports (View only for parent's children)
    Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');
    Route::get('progress/{student}', [ProgressController::class, 'show'])->name('progress.show');
    
    // ========== RESOURCES ==========
    
    // Learning Resources (View only)
    Route::get('resources', [ResourceController::class, 'index'])->name('resources.index');
    Route::get('resources/{resource}', [ResourceController::class, 'show'])->name('resources.show');
    Route::get('resources/{resource}/download', [ResourceController::class, 'download'])->name('resources.download');
});