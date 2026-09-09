<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ClassController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\ProfileController;



Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

Route::get('/classes', [ClassController::class, 'index'])->name('classes');

// Class details
Route::get('/class/{classId}/{subjectId?}', [ClassController::class, 'show'])->name('class.details');

// AJAX endpoints
Route::post('/content/view', [ClassController::class, 'markViewed'])->name('content.view');
Route::post('/assessment/submit', [ClassController::class, 'submitAssessment'])->name('assessment.submit');

Route::post('lock-materials', [ClassController::class, 'lockMaterials'])->name('lock.materials');
Route::get('/content/progress', [ClassController::class, 'getProgress'])->name('content.progress');


Route::post('/complete-lesson', [ClassController::class, 'completeLesson'])->name('complete.lesson');
Route::post('/lesson/complete', [ClassController::class, 'completeLesson'])->name('lesson.complete');


Route::get('/intervention/progress', [ClassController::class, 'getInterventionProgress'])->name('intervention.progress');

Route::post('/intervention/complete', [ClassController::class, 'completeIntervention'])
    ->name('intervention.complete');

Route::get('/progress', [ProgressController::class, 'index'])
    ->name('progress');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');