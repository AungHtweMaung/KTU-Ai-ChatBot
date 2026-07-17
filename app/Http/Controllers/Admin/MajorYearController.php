<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMajorYearRequest;
use App\Http\Requests\UpdateMajorYearRequest;
use App\Models\Major;
use App\Models\MajorYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MajorYearController extends Controller
{
    public function index(Request $request): Response
    {
        $academicYears = MajorYear::query()
            ->with('major:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('major', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => $academicYears,
            'majors' => Major::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function store(StoreMajorYearRequest $request): RedirectResponse
    {
        MajorYear::create($request->validated());

        return redirect()->back()->with('success', 'Academic year created successfully.');
    }

    public function update(UpdateMajorYearRequest $request, MajorYear $academicYear): RedirectResponse
    {
        $academicYear->update($request->validated());

        return redirect()->back()->with('success', 'Academic year updated successfully.');
    }

    public function destroy(MajorYear $academicYear): RedirectResponse
    {
        $academicYear->delete();

        return redirect()->back()->with('success', 'Academic year deleted successfully.');
    }
}
