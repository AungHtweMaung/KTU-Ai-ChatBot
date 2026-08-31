<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Models\MajorYear;
use App\Models\Timetable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $timetables = Timetable::query()
            ->with('majorYear:id,name,major_id', 'majorYear.major:id,name')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('majorYear', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('major', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Timetable/Index', [
            'timetables' => $timetables,
            'classes' => MajorYear::query()
                ->with('major:id,name')
                ->orderBy('major_id')
                ->orderBy('year_number')
                ->get(['id', 'name', 'year_number', 'major_id']),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimetableRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['image_path'] = $request->file('image')->store('timetables', 'public');

        unset($data['image']);

        Timetable::create($data);

        return redirect()->back()->with('success', 'Timetable created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimetableRequest $request, Timetable $timetable): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($timetable->image_path) {
                Storage::disk('public')->delete($timetable->image_path);
            }
            $data['image_path'] = $request->file('image')->store('timetables', 'public');
        }

        unset($data['image']);

        $timetable->update($data);

        return redirect()->back()->with('success', 'Timetable updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable): RedirectResponse
    {
        if ($timetable->image_path) {
            Storage::disk('public')->delete($timetable->image_path);
        }

        $timetable->delete();

        return redirect()->back()->with('success', 'Timetable deleted successfully.');
    }
}
