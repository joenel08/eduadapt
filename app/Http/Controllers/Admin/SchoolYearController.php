<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::orderBy('year', 'desc')->get();
        return view('admin.school-years', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $request->validate(['year' => 'required|unique:school_years']);
        SchoolYear::create(['year' => $request->year]);
        return back()->with('success', 'School year added.');
    }

    public function setActive(SchoolYear $schoolYear)
    {
        // Deactivate all others
        SchoolYear::where('is_active', true)->update(['is_active' => false]);
        $schoolYear->update(['is_active' => true]);
        return back()->with('success', 'Active school year updated.');
    }

    public function destroy(SchoolYear $schoolYear)
    {
        if ($schoolYear->classes()->count() > 0) {
            return back()->with('error', 'Cannot delete: has classes.');
        }
        $schoolYear->delete();
        return back()->with('success', 'School year deleted.');
    }
}