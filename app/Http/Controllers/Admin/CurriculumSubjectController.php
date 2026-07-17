<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurriculumSubjectRequest;
use App\Http\Requests\UpdateCurriculumSubjectRequest;
use App\Models\CurriculumSubject;
use App\Models\Major;
use App\Models\MajorYear;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurriculumSubjectController extends Controller
{
    public function index(Request $request): Response
    {
        $curriculumSubjects = CurriculumSubject::query()
            ->with([
                'major:id,name',
                'majorYear:id,major_id,year_number,name',
                'subject:id,code,name,credits',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('subject', function ($query) use ($search) {
                        $query->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })->orWhereHas('major', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/CurriculumSubjects/Index', [
            'curriculumSubjects' => $curriculumSubjects,
            'majors' => Major::orderBy('name')->get(['id', 'name']),
            'majorYears' => MajorYear::orderBy('major_id')
                ->orderBy('year_number')
                ->get(['id', 'major_id', 'year_number', 'name']),
            'subjects' => Subject::orderBy('code')->get(['id', 'code', 'name']),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function store(StoreCurriculumSubjectRequest $request): RedirectResponse
    {
        CurriculumSubject::create($request->validated());

        return redirect()->back()->with('success', 'Curriculum subject created successfully.');
    }

    public function update(UpdateCurriculumSubjectRequest $request, CurriculumSubject $curriculumSubject): RedirectResponse
    {
        $curriculumSubject->update($request->validated());

        return redirect()->back()->with('success', 'Curriculum subject updated successfully.');
    }

    public function destroy(CurriculumSubject $curriculumSubject): RedirectResponse
    {
        $curriculumSubject->delete();

        return redirect()->back()->with('success', 'Curriculum subject deleted successfully.');
    }
}
