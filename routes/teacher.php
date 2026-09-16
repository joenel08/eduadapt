<?php

use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\StudentController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\ClassDetailsController;
use App\Http\Controllers\Teacher\ContentLibraryController;
use App\Http\Controllers\Teacher\LearningMaterialController;
use App\Http\Controllers\Teacher\PreAssessmentController;
use App\Http\Controllers\Teacher\PostAssessmentController;
use App\Http\Controllers\Teacher\InterventionMaterialController;
use App\Http\Controllers\Teacher\InterventionVideoController;
use App\Http\Controllers\Teacher\InterventionQuizController;
use App\Http\Controllers\Teacher\InterventionController;
use App\Http\Controllers\Teacher\GlobalAnalyticsController;

// Dashboard & Classes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/students/{class}', [StudentController::class, 'index'])->name('class-students');
Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
Route::get('/classes', [ClassController::class, 'index'])->name('classes');
Route::get('/class/{class}/students', [ClassController::class, 'students'])->name('class-students');
// Route::get('/class/{class}', [ClassDetailsController::class, 'show'])->name('class-details.show');
Route::get('/class/{assignment}', [ClassDetailsController::class, 'show'])
    ->name('class-details.show');



Route::get('/global-analytics', [GlobalAnalyticsController::class, 'index'])
    ->name('global-analytics');

Route::put('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/picture', [DashboardController::class, 'updatePicture'])->name('profile.picture');
Route::put('/password/update', [DashboardController::class, 'updatePassword'])->name('password.update');

// ===================== CONTENT LIBRARY =====================
Route::prefix('content-library')->group(function () {

    // ---- Navigation ----
    Route::get('/', [ContentLibraryController::class, 'index'])->name('content-library');
    Route::get('/{grade}/{term}', [ContentLibraryController::class, 'subjects'])->name('content-library.subjects');
    Route::get('/{grade}/{term}/{subject}', [ContentLibraryController::class, 'weeks'])->name('content-library.weeks');
    Route::get('/{grade}/{term}/{subject}/{week}', [ContentLibraryController::class, 'content'])->name('content-library.content');

    // ---- RELEASES (outside week prefix) ----
    Route::get('/releases/{grade}/{term}/{subject}/{week}', [ContentLibraryController::class, 'getWeekReleases'])
        ->name('content-library.releases');
    Route::delete('/release/{id}', [ContentLibraryController::class, 'deleteRelease'])
        ->name('content-library.release.delete');

    // ---- AJAX endpoints (outside week prefix) ----
    Route::post('/save-content', [ContentLibraryController::class, 'saveContent'])->name('content-library.save');
    Route::delete('/delete-content', [ContentLibraryController::class, 'deleteContent'])->name('content-library.delete');

    Route::post('/upload-material', [LearningMaterialController::class, 'upload'])->name('content-library.upload-material');
    Route::get('/fetch/{grade}/{term}/{subject}/{week}', [LearningMaterialController::class, 'fetch'])->name('content-library.fetch');
    Route::delete('/delete/{id}', [LearningMaterialController::class, 'delete'])->name('content-library.delete');
    Route::post('/update/{id}', [LearningMaterialController::class, 'update'])->name('content-library.update');

    Route::post('/pre-assessment', [PreAssessmentController::class, 'store'])->name('content-library.pre-assessment.store'); // AJAX
    Route::get('/pre-assessment/fetch/{grade}/{term}/{subject}/{week}', [PreAssessmentController::class, 'fetch'])->name('content-library.pre-assessment.fetch');
    Route::delete('/pre-assessment/{id}', [PreAssessmentController::class, 'destroy'])->name('content-library.pre-assessment.delete');

    Route::post('/post-assessment', [PostAssessmentController::class, 'store'])->name('content-library.post-assessment.store');
    Route::get('/post-assessment/fetch/{grade}/{term}/{subject}/{week}', [PostAssessmentController::class, 'fetch'])->name('content-library.post-assessment.fetch');
    Route::delete('/post-assessment/{id}', [PostAssessmentController::class, 'destroy'])->name('content-library.post-assessment.delete');


    // ---- Intervention AJAX ----
    Route::prefix('intervention')->group(function () {
        Route::post('/material', [InterventionMaterialController::class, 'store'])
            ->name('content-library.intervention.material.store');
        Route::get('/material/fetch/{grade}/{term}/{subject}/{week}', [InterventionMaterialController::class, 'fetch'])
            ->name('content-library.intervention.material.fetch');

        Route::post('/video', [InterventionVideoController::class, 'store'])
            ->name('content-library.intervention.video.store');
        Route::post('/video/reorder', [InterventionVideoController::class, 'reorder'])
            ->name('content-library.intervention.video.reorder');
        Route::get('/video/fetch/{grade}/{term}/{subject}/{week}', [InterventionVideoController::class, 'fetch'])
            ->name('content-library.intervention.video.fetch');

        Route::post('/quiz', [InterventionQuizController::class, 'store'])
            ->name('content-library.intervention.quiz.store');
        Route::get('/quiz/fetch/{grade}/{term}/{subject}/{week}', [InterventionQuizController::class, 'fetch'])
            ->name('content-library.intervention.quiz.fetch');
    });

    // ---- Full‑page creation (nested week prefix) ----
    Route::prefix('{grade}/{term}/{subject}/{week}')->group(function () {
        // Learning Materials
        Route::get('/materials/create', [ContentLibraryController::class, 'materialsCreate'])
            ->name('content-library.materials.create');
        Route::post('/materials', [ContentLibraryController::class, 'materialsStore'])
            ->name('content-library.materials.store');

        // Pre‑Assessment
        Route::get('/pre-assessment/create', [PreAssessmentController::class, 'create'])
            ->name('content-library.pre-assessment.create');
        Route::post('/pre-assessment', [PreAssessmentController::class, 'storePage'])
            ->name('content-library.pre-assessment.store');

        // Post‑Assessment
        Route::get('/post-assessment/create', [PostAssessmentController::class, 'create'])
            ->name('content-library.post-assessment.create');
        Route::post('/post-assessment', [PostAssessmentController::class, 'storePage'])
            ->name('content-library.post-assessment.store');

        Route::put('/{type}/{id}', [ContentLibraryController::class, 'update'])
            ->name('content-library.update');

        // Intervention Materials
        Route::get('/intervention/materials/create', [InterventionMaterialController::class, 'createPage'])
            ->name('content-library.intervention.materials.create');
        Route::post('/intervention/materials', [InterventionMaterialController::class, 'storePage'])
            ->name('content-library.intervention.materials.store');

        // Intervention Videos
        Route::get('/intervention/videos/create', [InterventionVideoController::class, 'createPage'])
            ->name('content-library.intervention.videos.create');
        Route::post('/intervention/videos', [InterventionVideoController::class, 'storePage'])
            ->name('content-library.intervention.videos.store');

        // Intervention Quiz
        Route::get('/intervention/quiz/create', [InterventionQuizController::class, 'createPage'])
            ->name('content-library.intervention.quiz.create');
        Route::post('/intervention/quiz', [InterventionQuizController::class, 'storePage'])
            ->name('content-library.intervention.quiz.store');

        // View / Edit / Config (these stay inside the week prefix)
        Route::get('/view/{type}/{id}', [ContentLibraryController::class, 'view'])
            ->name('content-library.view');
        Route::get('/edit/{type}/{id}', [ContentLibraryController::class, 'edit'])
            ->name('content-library.edit');
        Route::put('/update/{type}/{id}', [ContentLibraryController::class, 'updatePage'])
            ->name('content-library.update');
        Route::get('/config/{type}/{id}', [ContentLibraryController::class, 'getConfig'])
            ->name('content-library.config.get');
        Route::post('/config', [ContentLibraryController::class, 'saveConfig'])
            ->name('content-library.config.save');
    });
});
