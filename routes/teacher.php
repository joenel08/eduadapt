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
use App\Http\Controllers\Teacher\StudentProgressController;
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

    // -----------------------------------------------------------------
    // 1. NAVIGATION (server-driven pages)
    // -----------------------------------------------------------------
    Route::get('/',                                       [ContentLibraryController::class, 'index'])->name('content-library');
    Route::get('/{grade}/{term}',                         [ContentLibraryController::class, 'subjects'])->name('content-library.subjects');
    Route::get('/{grade}/{term}/{subject}',               [ContentLibraryController::class, 'weeks'])->name('content-library.weeks');
    Route::get('/{grade}/{term}/{subject}/{week}',        [ContentLibraryController::class, 'content'])->name('content-library.content');

    // -----------------------------------------------------------------
    // 2. FIXED-SEGMENT ROUTES  (must come BEFORE the wildcard {grade} routes below)
    // -----------------------------------------------------------------

    // ---- Releases ----
    Route::get('/releases/{grade}/{term}/{subject}/{week}', [ContentLibraryController::class, 'getWeekReleases'])
        ->name('content-library.releases');
    Route::delete('/release/{id}', [ContentLibraryController::class, 'deleteRelease'])
        ->name('content-library.release.delete');

    // ---- AJAX: legacy generic endpoints ----
    Route::post('/save-content',   [ContentLibraryController::class, 'saveContent'])->name('content-library.save');

    // ---- Learning materials (AJAX + delete) ----
    Route::post('/upload-material', [LearningMaterialController::class, 'upload'])->name('content-library.upload-material');
    Route::get('/fetch/{grade}/{term}/{subject}/{week}', [LearningMaterialController::class, 'fetch'])->name('content-library.fetch');
    Route::post('/update/{id}',     [LearningMaterialController::class, 'update'])->name('content-library.update-material');
    Route::delete('/materials/{id}', [ContentLibraryController::class, 'deleteLearningMaterial'])->name('materials.delete');

    // ---- Pre-assessment (AJAX) ----
    Route::post('/pre-assessment', [PreAssessmentController::class, 'store'])->name('content-library.pre-assessment.store-ajax');
    Route::get('/pre-assessment/fetch/{grade}/{term}/{subject}/{week}', [PreAssessmentController::class, 'fetch'])->name('content-library.pre-assessment.fetch');
    Route::delete('/pre-assessment/{id}', [ContentLibraryController::class, 'deletePreAssessment'])->name('pre-assessment.delete');

    // ---- Post-assessment (AJAX) ----
    Route::post('/post-assessment', [PostAssessmentController::class, 'store'])->name('content-library.post-assessment.store-ajax');
    Route::get('/post-assessment/fetch/{grade}/{term}/{subject}/{week}', [PostAssessmentController::class, 'fetch'])->name('content-library.post-assessment.fetch');
    Route::delete('/post-assessment/{id}', [ContentLibraryController::class, 'deletePostAssessment'])->name('post-assessment.delete');

    // ---- Intervention (AJAX + delete) ----
    Route::prefix('intervention')->group(function () {

        // Materials
        Route::post('/material', [InterventionMaterialController::class, 'store'])
            ->name('content-library.intervention.material.store');
        Route::get('/material/fetch/{grade}/{term}/{subject}/{week}', [InterventionMaterialController::class, 'fetch'])
            ->name('content-library.intervention.material.fetch');

        // Videos
        Route::post('/video', [InterventionVideoController::class, 'store'])
            ->name('content-library.intervention.video.store');
        Route::post('/video/reorder', [InterventionVideoController::class, 'reorder'])
            ->name('content-library.intervention.video.reorder');
        Route::get('/video/fetch/{grade}/{term}/{subject}/{week}', [InterventionVideoController::class, 'fetch'])
            ->name('content-library.intervention.video.fetch');

        // Quiz
        Route::post('/quiz', [InterventionQuizController::class, 'store'])
            ->name('content-library.intervention.quiz.store');
        Route::get('/quiz/fetch/{grade}/{term}/{subject}/{week}', [InterventionQuizController::class, 'fetch'])
            ->name('content-library.intervention.quiz.fetch');
    });

    // ---- DELETEs for intervention subtypes (single set, unique names) ----
    Route::delete('/intervention/material/{id}', [ContentLibraryController::class, 'deleteInterventionMaterial'])->name('intervention.material.delete');
    Route::delete('/intervention/video/{id}',    [ContentLibraryController::class, 'deleteInterventionVideo'])->name('intervention.video.delete');
    Route::delete('/intervention/quiz/{id}',     [InterventionQuizController::class, 'destroy'])->name('intervention.quiz.delete');

    // -----------------------------------------------------------------
    // 3. WEEK-SCOPED ROUTES  (wildcard prefix — MUST be last)
    // -----------------------------------------------------------------
    Route::prefix('{grade}/{term}/{subject}/{week}')->group(function () {

        // ---- Learning Materials ----
        Route::get('/materials/create', [ContentLibraryController::class, 'materialsCreate'])->name('content-library.materials.create');
        Route::post('/materials',       [ContentLibraryController::class, 'materialsStore'])->name('content-library.materials.store');

        // ---- Pre-Assessment ----
        Route::get('/pre-assessment/create', [PreAssessmentController::class, 'create'])->name('content-library.pre-assessment.create');
        Route::post('/pre-assessment',       [PreAssessmentController::class, 'storePage'])->name('content-library.pre-assessment.store');

        // ---- Post-Assessment ----
        Route::get('/post-assessment/create', [PostAssessmentController::class, 'create'])->name('content-library.post-assessment.create');
        Route::post('/post-assessment',       [PostAssessmentController::class, 'storePage'])->name('content-library.post-assessment.store');

        // ---- Intervention: create pages + store ----
        Route::get('/intervention/materials/create', [InterventionMaterialController::class, 'createPage'])->name('content-library.intervention.materials.create');
        Route::post('/intervention/materials',       [InterventionMaterialController::class, 'storePage'])->name('content-library.intervention.materials.store');

        Route::get('/intervention/videos/create',    [InterventionVideoController::class, 'createPage'])->name('content-library.intervention.videos.create');
        Route::post('/intervention/videos',          [InterventionVideoController::class, 'storePage'])->name('content-library.intervention.videos.store');

        Route::get('/intervention/quiz/create',      [InterventionQuizController::class, 'createPage'])->name('content-library.intervention.quiz.create');
        Route::post('/intervention/quiz',            [InterventionQuizController::class, 'storePage'])->name('content-library.intervention.quiz.store-page');
        Route::post('/intervention/quiz', [InterventionQuizController::class, 'storePage'])
            ->name('content-library.intervention.quiz.store');
        // ---- View / Edit / Update (single route per verb+name) ----
        Route::get('/view/{type}/{id}',   [ContentLibraryController::class, 'view'])->name('content-library.view');
        Route::get('/edit/{type}/{id}',   [ContentLibraryController::class, 'edit'])->name('content-library.edit');
        Route::put('/update/{type}/{id}', [ContentLibraryController::class, 'updatePage'])->name('content-library.update');

        // ---- Release config ----
        Route::get('/config/{type}/{id}', [ContentLibraryController::class, 'getConfig'])->name('content-library.config.get');
        Route::post('/config',            [ContentLibraryController::class, 'saveConfig'])->name('content-library.config.save');
    });
});


Route::get(
    '/class/{classId}/student/{studentId}/progress',
    [StudentProgressController::class, 'show']
)
    ->name('student-progress');
Route::delete(
    '/class/{classId}/student/{studentId}/remove',
    [ClassDetailsController::class, 'removeStudent']
)
    ->name('class.remove-student');
