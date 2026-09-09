<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SchoolYear;

class DashboardController extends Controller
{
    public function index()
    {
        // Count users by role
        $studentCount = User::where('role', 'student')->count();
        $teacherCount = User::where('role', 'teacher')->count();
        $totalUsers = User::count();

        $schoolYears = SchoolYear::orderBy('year', 'desc')->get();
        $activeSchoolYear = SchoolYear::getActive();


        return view('admin.dashboard', compact(
            'studentCount',
            'teacherCount',
            'totalUsers',
            'schoolYears',
            'activeSchoolYear'
        ));
    }
}
