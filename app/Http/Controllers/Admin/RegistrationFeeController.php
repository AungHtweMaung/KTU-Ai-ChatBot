<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationFeeRequest;
use App\Http\Requests\UpdateRegistrationFeeRequest;
use App\Models\Major;
use App\Models\RegistrationFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $fees = RegistrationFee::query()
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

        return Inertia::render('Admin/Fees/Index', [
            'fees' => $fees,
            'majors' => Major::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRegistrationFeeRequest $request): RedirectResponse
    {
        RegistrationFee::create($request->validated());

        return redirect()->back()->with('success', 'Registration fee created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRegistrationFeeRequest $request, RegistrationFee $fee): RedirectResponse
    {
        $fee->update($request->validated());

        return redirect()->back()->with('success', 'Registration fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegistrationFee $fee): RedirectResponse
    {
        $fee->delete();

        return redirect()->back()->with('success', 'Registration fee deleted successfully.');
    }
}
