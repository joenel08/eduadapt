<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\SchoolYearController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;




Route::get('/', [DashboardController::class, 'index'])->name('dashboard');



Route::get('/school-years', [SchoolYearController::class, 'index'])->name('school-years');
Route::post('/school-years', [SchoolYearController::class, 'store'])->name('school-years.store');
Route::post('/school-years/{schoolYear}/set-active', [SchoolYearController::class, 'setActive'])->name('school-years.set-active');
Route::delete('/school-years/{schoolYear}', [SchoolYearController::class, 'destroy'])->name('school-years.destroy');

Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
// Classes
Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');

// Teacher Assignment
Route::post('/teacher-assign', [TeacherAssignmentController::class, 'assign'])->name('teacher-assign');
Route::delete('/teacher-assign/{assignment}', [TeacherAssignmentController::class, 'unassign'])->name('teacher-unassign');




// Master Data Upload
Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data');
Route::post('/master-data/upload-student', [MasterDataController::class, 'uploadStudent'])->name('master-data.upload-student');
Route::post('/master-data/upload-teacher', [MasterDataController::class, 'uploadTeacher'])->name('master-data.upload-teacher');
Route::delete('/master-data/student/{lrn}', [MasterDataController::class, 'deleteStudent'])->name('master-data.delete-student');
Route::delete('/master-data/teacher/{employeeId}', [MasterDataController::class, 'deleteTeacher'])->name('master-data.delete-teacher');

Route::get('/class-students/{class}', [ClassController::class, 'students'])->name('class-students');


// Student profile routes
Route::get('/student/{lrn}', [StudentController::class, 'show'])->name('student.show');
Route::get('/student/{lrn}/edit', [StudentController::class, 'edit'])->name('student.edit');
Route::put('/student/{lrn}', [StudentController::class, 'update'])->name('student.update');



