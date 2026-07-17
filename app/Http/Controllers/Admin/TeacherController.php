<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $teachers = Teacher::query()
            ->with('department:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhereHas('department', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('teachers', 'public');
        }

        unset($data['image']);

        Teacher::create($data);

        return redirect()->back()->with('success', 'Teacher created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($teacher->image_path) {
                Storage::disk('public')->delete($teacher->image_path);
            }
            $data['image_path'] = $request->file('image')->store('teachers', 'public');
        }

        unset($data['image']);

        $teacher->update($data);

        return redirect()->back()->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        if ($teacher->image_path) {
            Storage::disk('public')->delete($teacher->image_path);
        }

        $teacher->delete();

        return redirect()->back()->with('success', 'Teacher deleted successfully.');
    }
}
