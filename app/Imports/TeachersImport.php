<?php
namespace App\Imports;

use App\Models\User;
use App\Models\TeacherProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $employeeId = trim($row['employee_id'] ?? '');
            if (empty($employeeId)) continue;

            $teacher = TeacherProfile::where('employee_id', $employeeId)->first();

            if (!$teacher) {
                $user = User::create([
                    'login_id' => $employeeId,
                    'password' => Hash::make($employeeId),
                    'role' => 'teacher',
                    'status' => 'approved',
                ]);

                TeacherProfile::create([
                    'user_id' => $user->id,
                    'employee_id' => $employeeId,
                    'prefix_name' => $row['prefix_name'] ?? null,
                    'first_name' => $row['first_name'] ?? '',
                    'middle_name' => $row['middle_name'] ?? null,
                    'last_name' => $row['last_name'] ?? '',
                    'suffix_name' => $row['suffix_name'] ?? null,
                    'contact_no' => $row['contact_no'] ?? null,
                    'address' => $row['address'] ?? null,
                ]);
            }
        }
    }
}