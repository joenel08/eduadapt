<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showAdminLoginForm()
    {
        return view('admin.login');
    }

    public function showTeacherLoginForm()
    {
        return view('teacher.login');
    }

    public function showStudentLoginForm()
    {
        return view('student.login');
    }

    // Generic login method – we pass the expected role
    protected function login(Request $request, $expectedRole, $redirectTo)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('login_id', 'password');
        $credentials['disabled'] = false; // only allow active accounts

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            // Check role matches
            if ($user->role !== $expectedRole) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'login_id' => 'Invalid credentials for this portal.',
                ]);
            }
            return redirect()->intended($redirectTo);
        }

        throw ValidationException::withMessages([
            'login_id' => 'The provided credentials do not match our records.',
        ]);
    }

    public function adminLogin(Request $request)
    {
        return $this->login($request, 'admin', route('admin.dashboard'));
    }

    public function teacherLogin(Request $request)
    {
        return $this->login($request, 'teacher', route('teacher.dashboard'));
    }

    public function studentLogin(Request $request)
    {
        return $this->login($request, 'student', route('student.dashboard'));
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}