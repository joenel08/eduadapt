<?php

namespace App\Imports;

use App\Models\User;
use App\Models\TeacherProfile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;

class TeachersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['employee_id'])) return null;

        // Skip duplicates
        if (TeacherProfile::where('employee_id', $row['employee_id'])->exists()) {
            return null;
        }

        $fullName = trim(
            ($row['prefix_name'] ?? '') . ' ' .
                $row['first_name'] . ' ' .
                ($row['middle_name'] ?? '') . ' ' .
                $row['last_name'] . ' ' .
                ($row['suffix_name'] ?? '')
        );



        $user = User::create([
            'name'     => $fullName,
            'password' => Hash::make($row['employee_id']),
            'role'     => 'teacher',
            'status' => 'approved',
        ]);

        return new TeacherProfile([
            'user_id'      => $user->id,
            'employee_id'  => $row['employee_id'],
            'prefix_name'  => $row['prefix_name']  ?? null,
            'first_name'   => $row['first_name'],
            'middle_name'  => $row['middle_name']  ?? null,
            'last_name'    => $row['last_name'],
            'suffix_name'  => $row['suffix_name']  ?? null,
            'contact_no'   => $row['contact_no']   ?? null,
            'address'      => $row['address']      ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required',
            'first_name'  => 'required',
            'last_name'   => 'required',
        ];
    }
}
