<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// ==============================================
// PUBLIC ROUTES (LANDING & LOGIN)
// ==============================================

// Landing page (now serves as the single login page)
Route::get('/', [LoginController::class, 'showLanding'])->name('home');

// Unified login POST – handles all roles
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Separate login GET routes – redirect to landing page (keeps old URLs working)
Route::get('/admin/login', function () {
    return redirect('/');
})->name('admin.login');

Route::get('/teacher/login', function () {
    return redirect('/');
})->name('teacher.login');

Route::get('/student/login', function () {
    return redirect('/');
})->name('student.login');

// Separate login POST routes – still work, but they call the same unified method
Route::post('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login.submit');
Route::post('/teacher/login', [LoginController::class, 'teacherLogin'])->name('teacher.login.submit');
Route::post('/student/login', [LoginController::class, 'studentLogin'])->name('student.login.submit');

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==============================================
// PROTECTED DASHBOARDS
// ==============================================

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    require __DIR__ . '/admin.php';
});

// Teacher routes
Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    require __DIR__ . '/teacher.php';
});

// Student routes
Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    require __DIR__ . '/student.php';
});

// ==============================================
// OPCACHE (optional)
// ==============================================
Route::get('/clear-opcache', function () {
    if (function_exists('opcache_reset')) {
        opcache_reset();
        return 'Opcache cleared';
    }
    return 'Opcache not enabled';
});