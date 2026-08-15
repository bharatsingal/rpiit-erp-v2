<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfferingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

// Public landing page. Signed-in users get a link through to the ERP.
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'attempt'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    Route::get('/batches/{batch}/timetable', [TimetableController::class, 'show'])->name('timetable.show');
    Route::post('/batches/{batch}/timetable', [TimetableController::class, 'store'])->name('timetable.store');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');

    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');

    Route::get('/offerings', [OfferingController::class, 'index'])->name('offerings.index');
    Route::post('/offerings', [OfferingController::class, 'store'])->name('offerings.store');
    Route::get('/offerings/terms/{batch}', [OfferingController::class, 'termsFor'])->name('offerings.terms');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{offering}', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/{offering}', [AttendanceController::class, 'store'])->name('attendance.store');
});
