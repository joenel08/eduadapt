<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();
        return view('student.profile', compact('student', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();

        // Update user name
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->save();

        // Update student profile
        $student->first_name = $request->first_name;
        $student->last_name = $request->last_name;
        $student->lrn = $request->lrn;
        $student->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048',
        ]);

        $user = Auth::user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();

        // Delete old picture
        if ($student->profile_picture && Storage::disk('public')->exists($student->profile_picture)) {
            Storage::disk('public')->delete($student->profile_picture);
        }

        $path = $request->file('profile_picture')->store('student_profiles', 'public');
        $student->profile_picture = $path;
        $student->save();

        return back()->with('success', 'Profile picture updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }
}