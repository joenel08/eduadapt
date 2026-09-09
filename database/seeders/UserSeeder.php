<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdminProfile;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== ADMIN ====================
        $admin = User::create([
            'login_id' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'disabled' => false,
        ]);

        AdminProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'Admin User',
            'email' => 'admin@eduadapt.edu.ph',
        ]);

        // ==================== TEACHERS ====================
        $teachers = [
            [
                'login_id' => 'TCH-001',
                'prefix_name' => 'Ms.',
                'first_name' => 'Grace',
                'middle_name' => 'R.',
                'last_name' => 'Santos',
                'suffix_name' => null,
                'contact_no' => '09171234567',
                'address' => '123 Teacher St., Quezon City',
            ],
            [
                'login_id' => 'TCH-002',
                'prefix_name' => 'Mr.',
                'first_name' => 'Mark',
                'middle_name' => 'V.',
                'last_name' => 'Villegas',
                'suffix_name' => 'Jr.',
                'contact_no' => '09181234567',
                'address' => '456 Educator Ave., Manila',
            ],
            [
                'login_id' => 'TCH-003',
                'prefix_name' => 'Dr.',
                'first_name' => 'Maria',
                'middle_name' => 'L.',
                'last_name' => 'Cruz',
                'suffix_name' => null,
                'contact_no' => '09191234567',
                'address' => '789 Academic Rd., Pasig',
            ],
        ];

        foreach ($teachers as $teacherData) {
            $user = User::create([
                'login_id' => $teacherData['login_id'],
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'disabled' => false,
            ]);

            TeacherProfile::create([
                'user_id' => $user->id,
                'prefix_name' => $teacherData['prefix_name'],
                'first_name' => $teacherData['first_name'],
                'middle_name' => $teacherData['middle_name'],
                'last_name' => $teacherData['last_name'],
                'suffix_name' => $teacherData['suffix_name'],
                'contact_no' => $teacherData['contact_no'],
                'address' => $teacherData['address'],
            ]);
        }

        // ==================== STUDENTS ====================
        $students = [
            [
                'login_id' => '202312345678',
                'first_name' => 'Alyssa',
                'middle_name' => 'M.',
                'last_name' => 'Mercado',
                'suffix_name' => null,
                'grade_level' => 'Grade 5',
                'contact_no' => '09201234567',
                'address' => '123 Student Lane, Mandaluyong',
            ],
            [
                'login_id' => '202312345679',
                'first_name' => 'Josiah',
                'middle_name' => 'D.',
                'last_name' => 'Del Rosario',
                'suffix_name' => 'III',
                'grade_level' => 'Grade 5',
                'contact_no' => '09211234567',
                'address' => '456 Learner St., Quezon City',
            ],
            [
                'login_id' => '202312345680',
                'first_name' => 'Maya',
                'middle_name' => 'C.',
                'last_name' => 'Cruz',
                'suffix_name' => null,
                'grade_level' => 'Grade 6',
                'contact_no' => '09221234567',
                'address' => '789 Knowledge Ave., Pasig',
            ],
            [
                'login_id' => '202312345681',
                'first_name' => 'Ethan',
                'middle_name' => 'R.',
                'last_name' => 'Garcia',
                'suffix_name' => 'II',
                'grade_level' => 'Grade 6',
                'contact_no' => '09231234567',
                'address' => '101 Wisdom St., Taguig',
            ],
            [
                'login_id' => '202312345682',
                'first_name' => 'Sophia',
                'middle_name' => 'T.',
                'last_name' => 'Reyes',
                'suffix_name' => null,
                'grade_level' => 'Grade 5',
                'contact_no' => '09241234567',
                'address' => '202 Brilliance Rd., Makati',
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::create([
                'login_id' => $studentData['login_id'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'disabled' => false,
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'first_name' => $studentData['first_name'],
                'middle_name' => $studentData['middle_name'],
                'last_name' => $studentData['last_name'],
                'suffix_name' => $studentData['suffix_name'],
                'grade_level' => $studentData['grade_level'],
                'contact_no' => $studentData['contact_no'],
                'address' => $studentData['address'],
            ]);
        }
    }
}