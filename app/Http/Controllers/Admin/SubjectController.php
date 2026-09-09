<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('grade_level')->orderBy('name')->get();
        return view('admin.subjects', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|in:Grade 5,Grade 6',
            'name' => 'required|unique:subjects,name,NULL,id,grade_level,'.$request->grade_level,
        ]);
        Subject::create($request->all());
        return back()->with('success', 'Subject added.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->teacherAssignments()->exists()) {
            return back()->with('error', 'Cannot delete subject with existing teacher assignments.');
        }
        $subject->delete();
        return back()->with('success', 'Subject deleted.');
    }
}