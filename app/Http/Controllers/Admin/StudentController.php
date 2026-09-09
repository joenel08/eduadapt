<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // Show student profile (view)
    public function show($lrn)
    {
        $student = StudentProfile::with('user', 'classRecords.class')
            ->where('lrn', $lrn)
            ->firstOrFail();
            
        return view('admin.student-profile', compact('student'));
    }

    // Show edit form
    public function edit($lrn)
    {
        $student = StudentProfile::where('lrn', $lrn)->firstOrFail();
        return view('admin.student-edit', compact('student'));
    }

    // Update student
    public function update(Request $request, $lrn)
    {
        $student = StudentProfile::where('lrn', $lrn)->firstOrFail();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix_name' => 'nullable|string|max:50',
            'sex' => 'nullable|in:M,F',
            'birth_date' => 'nullable|date',
            'mother_tongue' => 'nullable|string|max:255',
            'ip_ethnic_group' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'address_house' => 'nullable|string|max:255',
            'address_barangay' => 'nullable|string|max:255',
            'address_municipality' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_maiden_name' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relationship' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'learning_modality' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $student->update($validated);

        return redirect()->route('admin.student.show', $lrn)
            ->with('success', 'Student profile updated successfully.');
    }


}