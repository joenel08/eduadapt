<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'students');

        $students = StudentProfile::with(['user', 'studentClassRecords.class'])
            ->when($request->search, function ($query) use ($request) {
                $q = $request->search;
                $query->where(function ($w) use ($q) {
                    $w->where('first_name', 'like', "%{$q}%")
                      ->orWhere('middle_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('lrn', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->get();

        $teachers = TeacherProfile::with(['user', 'teacherClassAssignments.class', 'teacherClassAssignments.subject'])
            ->when($request->search, function ($query) use ($request) {
                $q = $request->search;
                $query->where(function ($w) use ($q) {
                    $w->where('first_name', 'like', "%{$q}%")
                      ->orWhere('middle_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('employee_id', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->get();

        return view('admin.user-management', compact('students', 'teachers', 'tab'));
    }

    public function updateStudent(Request $request, $id)
    {
        $student = StudentProfile::findOrFail($id);
        $user = $student->user;

        $data = $request->validate([
            'lrn'         => ['required', 'string', 'max:255', Rule::unique('student_profiles', 'lrn')->ignore($student->id)],
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'suffix_name' => 'nullable|string|max:255',
            'birth_date'  => 'nullable|date',
            'sex'         => 'nullable|string|max:10',
            'contact_no'  => 'nullable|string|max:255',
            'address'     => 'nullable|string',
        ]);

        $student->update($data);

        // Keep user.name in sync
        $user->update([
            'name' => trim(
                $data['first_name'] . ' ' .
                ($data['middle_name'] ?? '') . ' ' .
                $data['last_name'] . ' ' .
                ($data['suffix_name'] ?? '')
            ),
        ]);

        return response()->json(['success' => true, 'message' => 'Student updated successfully.']);
    }

    public function updateTeacher(Request $request, $id)
    {
        $teacher = TeacherProfile::findOrFail($id);
        $user = $teacher->user;

        $data = $request->validate([
            'employee_id' => ['required', 'string', 'max:255', Rule::unique('teacher_profiles', 'employee_id')->ignore($teacher->id)],
            'prefix_name' => 'nullable|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'suffix_name' => 'nullable|string|max:255',
            'contact_no'  => 'nullable|string|max:255',
            'address'     => 'nullable|string',
        ]);

        $teacher->update($data);

        $user->update([
            'name' => trim(
                ($data['prefix_name'] ?? '') . ' ' .
                $data['first_name'] . ' ' .
                ($data['middle_name'] ?? '') . ' ' .
                $data['last_name'] . ' ' .
                ($data['suffix_name'] ?? '')
            ),
        ]);

        return response()->json(['success' => true, 'message' => 'Teacher updated successfully.']);
    }

    public function resetStudentPassword($id)
    {
        $student = StudentProfile::findOrFail($id);
        $user = $student->user;

        $user->update([
            'password' => Hash::make($student->lrn),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset to default (LRN).',
        ]);
    }

    public function resetTeacherPassword($id)
    {
        $teacher = TeacherProfile::findOrFail($id);
        $user = $teacher->user;

        $user->update([
            'password' => Hash::make($teacher->employee_id),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset to default (Employee ID).',
        ]);
    }
}