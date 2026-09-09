<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the landing page with the login form.
     */
    public function showLanding()
    {
        return view('landing');
    }

    /**
     * Unified login – automatically detects the user's role.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'login_id' => $request->login_id,
            'password' => $request->password,
            'disabled' => false, // only allow active accounts
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Check if the user has a valid role
            $role = $user->role;

            // Redirect based on role
            $redirectTo = match ($role) {
                'admin'   => route('admin.dashboard'),
                'teacher' => route('teacher.dashboard'),
                'student' => route('student.dashboard'),
                default   => '/', // fallback
            };

            return redirect()->intended($redirectTo);
        }

        throw ValidationException::withMessages([
            'login_id' => 'The provided credentials do not match our records.',
        ]);
    }

    // ----- Separate methods (kept for backward compatibility) -----
    public function showAdminLoginForm()
    {
        return redirect()->route('landing');
    }

    public function showTeacherLoginForm()
    {
        return redirect()->route('landing');
    }

    public function showStudentLoginForm()
    {
        return redirect()->route('landing');
    }

    public function adminLogin(Request $request)
    {
        return $this->login($request);
    }

    public function teacherLogin(Request $request)
    {
        return $this->login($request);
    }

    public function studentLogin(Request $request)
    {
        return $this->login($request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}