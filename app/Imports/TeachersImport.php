<?php

namespace App\Imports;

use App\Models\User;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Normalize keys: strip extra spaces, lowercase
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(trim(str_replace(' ', '_', $key)));
            $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
        }

        // If no employee_id, skip silently (footer/note rows, blanks)
        $employeeId = $normalized['employee_id'] ?? null;
        if (empty($employeeId)) {
            Log::info('TeachersImport: skipped row (no employee_id)', $normalized);
            return null;
        }

        // Skip existing
        if (TeacherProfile::where('employee_id', $employeeId)->exists()) {
            Log::info("TeachersImport: skipped {$employeeId} — already exists");
            return null;
        }

        // Create user
        $user = User::create([
            'login_id' => $employeeId,
            'password' => Hash::make($employeeId),
            'role'     => 'teacher',
        ]);

        // Create profile
        return new TeacherProfile([
            'user_id'     => $user->id,
            'employee_id' => $employeeId,
            'prefix_name' => $normalized['prefix_name'] ?? null,
            'first_name'  => $normalized['first_name']  ?? '',
            'middle_name' => $normalized['middle_name'] ?? null,
            'last_name'   => $normalized['last_name']   ?? '',
            'suffix_name' => $normalized['suffix_name'] ?? null,
            'contact_no'  => $normalized['contact_no']  ?? null,
            'address'     => $normalized['address']     ?? null,
        ]);
    }
}