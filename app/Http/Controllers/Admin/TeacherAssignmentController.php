<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherAssignmentRequest;
use App\Http\Requests\UpdateTeacherAssignmentRequest;
use App\Models\CurriculumSubject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $assignments = TeacherAssignment::query()
            ->with([
                'teacher:id,name,email',
                'curriculumSubject.major:id,name',
                'curriculumSubject.majorYear:id,year_number,name',
                'curriculumSubject.subject:id,code,name',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('school_year', 'like', "%{$search}%")
                        ->orWhereHas('teacher', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('curriculumSubject.subject', function ($query) use ($search) {
                            $query->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $curriculumSubjects = CurriculumSubject::query()
            ->with([
                'major:id,name',
                'majorYear:id,year_number',
                'subject:id,code,name',
            ])
            ->orderBy('major_id')
            ->get()
            ->map(fn ($cs) => [
                'id' => $cs->id,
                'label' => sprintf(
                    '%s / Year %d / Sem %d — %s: %s',
                    $cs->major?->name ?? '?',
                    $cs->majorYear?->year_number ?? '?',
                    $cs->semester,
                    $cs->subject?->code ?? '?',
                    $cs->subject?->name ?? '?',
                ),
            ]);

        return Inertia::render('Admin/TeacherAssignments/Index', [
            'assignments' => $assignments,
            'teachers' => Teacher::orderBy('name')->get(['id', 'name']),
            'curriculumSubjects' => $curriculumSubjects,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function store(StoreTeacherAssignmentRequest $request): RedirectResponse
    {
        TeacherAssignment::create($request->validated());

        return redirect()->back()->with('success', 'Teacher assignment created successfully.');
    }

    public function update(UpdateTeacherAssignmentRequest $request, TeacherAssignment $teacherAssignment): RedirectResponse
    {
        $teacherAssignment->update($request->validated());

        return redirect()->back()->with('success', 'Teacher assignment updated successfully.');
    }

    public function destroy(TeacherAssignment $teacherAssignment): RedirectResponse
    {
        $teacherAssignment->delete();

        return redirect()->back()->with('success', 'Teacher assignment deleted successfully.');
    }
}
