<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\HomeworkController;
use App\Http\Controllers\Teacher\ProgressSheetController;
use App\Http\Controllers\Teacher\LearningResourceController;

    Route::middleware(['auth', 'role:teacher', 'check.status'])->group(function () {

    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ========== ACADEMIC MANAGEMENT ==========
    
    // Classes (Only classes assigned to this teacher)
    Route::get('classes', [ClassController::class, 'index'])->name('classes.index');
    Route::get('classes/{class}', [ClassController::class, 'show'])->name('classes.show');
    
    // Attendance (Only for teacher's classes)
    Route::resource('attendance', AttendanceController::class);
    Route::post('attendance/bulk-mark', [AttendanceController::class, 'bulkMark'])->name('attendance.bulk-mark');
    
    // Homework (Only for teacher's classes)
    Route::resource('homework', HomeworkController::class);
    Route::post('homework/{homework}/grade', [HomeworkController::class, 'grade'])->name('homework.grade');
    Route::get('homework/{homework}/submissions', [HomeworkController::class, 'submissions'])->name('homework.submissions');
    
    // Progress Sheets (Only for teacher's classes)
    Route::get('progress-sheets/get-students', [ProgressSheetController::class, 'getStudents'])
    ->name('progress-sheets.get-students');
    Route::resource('progress-sheets', ProgressSheetController::class);
    Route::post('progress-sheets/{progressSheet}/notes', [ProgressSheetController::class, 'addNote'])->name('progress-sheets.add-note');
    // ========== RESOURCES ==========
    
    // Learning Resources (Teacher can upload and view)
    Route::resource('resources', LearningResourceController::class);
    Route::get('resources/{resource}/download', [LearningResourceController::class, 'download'])
        ->name('resources.download');
});