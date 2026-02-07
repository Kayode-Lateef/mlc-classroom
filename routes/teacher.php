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
    // Route::resource('attendance', AttendanceController::class);
    // Route::post('attendance/bulk-mark', [AttendanceController::class, 'bulkMark'])->name('attendance.bulk-mark');
    // Attendance Management
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    
    // Session-specific routes with 3 parameters
    Route::get('attendance/{date}/{classId}/{scheduleId}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('attendance/{date}/{classId}/{scheduleId}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('attendance/{date}/{classId}/{scheduleId}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('attendance/{date}/{classId}/{scheduleId}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    
    // Homework (Only for teacher's classes)
    Route::get('homework/{homework}/download', [HomeworkController::class, 'download'])
        ->name('homework.download');
    Route::post('homework/{homework}/grade', [HomeworkController::class, 'grade'])
        ->name('homework.grade');
    Route::get('homework/{homework}/submissions', [HomeworkController::class, 'submissions'])
        ->name('homework.submissions');
    Route::post('homework/{homework}/mark-submitted', [HomeworkController::class, 'markAsSubmitted'])
        ->name('homework.mark-submitted');
    Route::post('homework/{homework}/bulk-mark-submitted', [HomeworkController::class, 'bulkMarkAsSubmitted'])
        ->name('homework.bulk-mark-submitted');
    Route::post('homework/{homework}/bulk-grade', [HomeworkController::class, 'bulkGrade'])
        ->name('homework.bulk-grade');
    Route::post('/homework/{homework}/grade-topics', [HomeworkController::class, 'gradeTopics'])
        ->name('homework.grade-topics');
    Route::post('/homework/{homework}/update-submitted-date', [HomeworkController::class, 'updateSubmittedDate'])
        ->name('homework.update-submitted-date');
    
    Route::resource('homework', HomeworkController::class);
    
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