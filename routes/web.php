<?php

use App\Http\Controllers\Admin\CurriculumSubjectController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\MajorYearController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'appName' => config('app.name'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'statistics' => [
                'teachers' => 125,
                'subjects' => 350,
                'departments' => 12,
                'students' => 5000,
                'daily' => [
                    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'data' => [120, 150, 130, 180, 200, 170, 220],
                ],
            ],
            'announcements' => [],
            'events' => [],
            'recentQuestions' => [],
        ]);
    })->name('admin.dashboard');

    Route::resource('admin/departments', DepartmentController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.departments');

    Route::resource('admin/majors', MajorController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.majors');

    Route::resource('admin/teachers', TeacherController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.teachers');

    Route::resource('admin/academic-years', MajorYearController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['academic-years' => 'academic_year'])
        ->names('admin.academic-years');

    Route::resource('admin/subjects', SubjectController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.subjects');

    Route::resource('admin/curriculum-subjects', CurriculumSubjectController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['curriculum-subjects' => 'curriculum_subject'])
        ->names('admin.curriculum-subjects');

    Route::resource('admin/teacher-assignments', TeacherAssignmentController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['teacher-assignments' => 'teacher_assignment'])
        ->names('admin.teacher-assignments');
});

require __DIR__.'/auth.php';
