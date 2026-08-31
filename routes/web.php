<?php

use App\Http\Controllers\Admin\CurriculumSubjectController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\MajorYearController;
use App\Http\Controllers\Admin\RegistrationFeeController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'appName' => config('app.name'),
    ]);
});

// Post-login landing: admins go to the admin dashboard, everyone else to chat.
Route::get('/dashboard', function () {
    return request()->user()?->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('chat');
})->middleware(['auth'])->name('dashboard');

// Chat UI is intentionally public so guest visitors can hold a conversation
// (identified by a UUID stored in localStorage). Authenticated users are
// automatically linked to their account via $request->user().
Route::get('/chat', function () {
    return Inertia::render('Chat/Index');
})->name('chat');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::get('/chat/conversations', [ConversationController::class, 'index'])->name('chat.conversations.index');
Route::get('/chat/conversations/{conversation}', [ConversationController::class, 'show'])->name('chat.conversations.show');
Route::patch('/chat/conversations/{conversation}', [ConversationController::class, 'update'])->name('chat.conversations.update');
Route::delete('/chat/conversations/{conversation}', [ConversationController::class, 'destroy'])->name('chat.conversations.destroy');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin panel — requires an authenticated user whose is_admin flag is true.
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        // Daily user-question counts for the last 7 calendar days.
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));

        return Inertia::render('Admin/Dashboard', [
            'statistics' => [
                'teachers' => \App\Models\Teacher::count(),
                'subjects' => \App\Models\Subject::count(),
                'departments' => \App\Models\Department::count(),
                'majors' => \App\Models\Major::count(),
                'daily' => [
                    'labels' => $days->map(fn ($d) => $d->format('D'))->all(),
                    'data' => $days->map(fn ($d) => \App\Models\Message::where('role', 'user')
                        ->whereDate('created_at', $d->toDateString())
                        ->count())->all(),
                ],
            ],
            'announcements' => [],
            'events' => \App\Models\Event::query()
                ->where('is_published', true)
                ->orderByDesc('starts_at')
                ->take(5)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'start_date' => optional($e->starts_at)->format('M j, Y'),
                ]),
            'recentQuestions' => \App\Models\Message::query()
                ->where('role', 'user')
                ->latest()
                ->take(6)
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'question' => $m->content,
                ]),
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

    Route::resource('admin/timetable', TimetableController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['timetable' => 'timetable'])
        ->names('admin.timetable');

    Route::resource('admin/faqs', FaqController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['faqs' => 'faq'])
        ->names('admin.faqs');

    Route::resource('admin/events', EventController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.events');

    Route::resource('admin/fees', RegistrationFeeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['fees' => 'fee'])
        ->names('admin.fees');
});

require __DIR__.'/auth.php';
