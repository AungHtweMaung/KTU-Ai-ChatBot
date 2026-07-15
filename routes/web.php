<?php

use App\Http\Controllers\Admin\DepartmentController;
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

    Route::get('/admin/teachers', function () {
        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => [
                ['id' => 1, 'name' => 'John Doe', 'email' => '', 'department' => 'Computer Science'],
                ['id' => 2, 'name' => 'Jane Smith', 'email' => '', 'department' => 'Mathematics'],
                ['id' => 3, 'name' => 'Michael Johnson', 'email' => '', 'department' => 'Physics'],
            ],
        ]);
    })->name('admin.teachers');

    Route::resource('admin/departments', DepartmentController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.departments');
});

require __DIR__.'/auth.php';
