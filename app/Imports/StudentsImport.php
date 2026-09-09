<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\StudentClassRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Throwable;

class StudentsImport implements ToCollection
{
    protected $classId;
    protected $errors = [];
    protected $successCount = 0;

    public function __construct($classId)
    {
        $this->classId = $classId;
    }

    public function collection(Collection $rows)
    {
        // 1. Find header row (where first column is 'LRN')
        $headerRowIndex = null;
        foreach ($rows as $index => $row) {
            $first = trim($row[0] ?? '');
            if (strtoupper($first) === 'LRN') {
                $headerRowIndex = $index;
                break;
            }
        }

        if ($headerRowIndex === null) {
            throw new \Exception('LRN header not found.');
        }

        Log::info('Student Import: Header found at row ' . $headerRowIndex);

        // 2. Process data rows (after header)
        for ($i = $headerRowIndex + 1; $i < $rows->count(); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (empty($row[0]) && empty($row[2]) && empty($row[6])) {
                continue;
            }

            // ---- LRN (column A, index 0) ----
            $lrn = trim($row[0] ?? '');
            if (empty($lrn) || !is_numeric($lrn) || strlen($lrn) != 12) {
                continue; // skip invalid rows (footers, summary rows)
            }

            try {
                // ---- Full Name ----
                $firstName = '';
                $lastName = '';
                $middleName = '';
                $suffix = '';

                // Option 1: Try comma-separated format in column C (index 2)
                $fullName = trim($row[2] ?? '');
                if (!empty($fullName)) {
                    // Check if it contains a comma (like "LAST, FIRST MIDDLE")
                    if (strpos($fullName, ',') !== false) {
                        $parts = array_map('trim', explode(',', $fullName));
                        if (count($parts) >= 2) {
                            $lastName = $parts[0];
                            $firstName = $parts[1];
                            $middleName = $parts[2] ?? '';
                        }
                    } else {
                        // No comma – split by spaces
                        $nameParts = explode(' ', $fullName);
                        if (count($nameParts) >= 2) {
                            $lastName = array_pop($nameParts);
                            $firstName = implode(' ', $nameParts);
                        } else {
                            $firstName = $fullName;
                            $lastName = '';
                        }
                    }
                }

                // Option 2: If still empty, try split columns C-F (indices 2, 3, 4, 5)
                if (empty($firstName) || empty($lastName)) {
                    $lastName = trim($row[2] ?? '');
                    $firstName = trim($row[3] ?? '');
                    $middleName = trim($row[4] ?? '');
                    $suffix = trim($row[5] ?? '');
                }

                // If still empty, log error and skip
                if (empty($firstName) || empty($lastName)) {
                    $this->errors[] = "Row " . ($i + 1) . ": Missing name for LRN $lrn (read: '$fullName')";
                    continue;
                }

                // ---- Sex (column G, index 6) ----
                $sex = trim($row[6] ?? '');
                if (!in_array($sex, ['M', 'F', ''])) {
                    $sex = null;
                }

                // ---- Birth Date (columns H-I, indices 7-8) ----
                $birthDate = null;
                if (!empty($row[7])) {
                    try {
                        $birthDate = date('Y-m-d', strtotime($row[7]));
                    } catch (\Exception $e) {
                        $birthDate = null;
                    }
                }

                // ---- Optional fields ----
                $motherTongue = trim($row[11] ?? '');
                $ipEthnic = trim($row[13] ?? '');
                $religion = trim($row[14] ?? '');
                $addressHouse = trim($row[15] ?? '');
                $addressBarangay = trim($row[17] ?? '');
                $addressMunicipality = trim($row[20] ?? '');
                $addressProvince = trim($row[22] ?? '');
                $fatherName = trim($row[27] ?? '');
                $motherMaiden = trim($row[31] ?? '');
                $guardianName = trim($row[36] ?? '');
                $guardianRelationship = trim($row[40] ?? '');
                $contactNumber = trim($row[41] ?? '');
                $learningModality = trim($row[43] ?? '');
                $remarks = trim($row[44] ?? '');

                // ---- Create or find student profile ----
                $profile = StudentProfile::where('lrn', $lrn)->first();

                if (!$profile) {
                    $user = User::where('login_id', $lrn)->first();
                    if (!$user) {
                        $user = User::create([
                            'login_id' => $lrn,
                            'password' => Hash::make($lrn),
                            'role' => 'student',
                        ]);
                    }

                    $profile = StudentProfile::create([
                        'user_id' => $user->id,
                        'lrn' => $lrn,
                        'first_name' => $firstName,
                        'middle_name' => $middleName ?: null,
                        'last_name' => $lastName,
                        'suffix_name' => $suffix ?: null,
                        'birth_date' => $birthDate,
                        'sex' => $sex,
                        'mother_tongue' => $motherTongue ?: null,
                        'ip_ethnic_group' => $ipEthnic ?: null,
                        'religion' => $religion ?: null,
                        'address_house' => $addressHouse ?: null,
                        'address_barangay' => $addressBarangay ?: null,
                        'address_municipality' => $addressMunicipality ?: null,
                        'address_province' => $addressProvince ?: null,
                        'father_name' => $fatherName ?: null,
                        'mother_maiden_name' => $motherMaiden ?: null,
                        'guardian_name' => $guardianName ?: null,
                        'guardian_relationship' => $guardianRelationship ?: null,
                        'contact_number' => $contactNumber ?: null,
                        'learning_modality' => $learningModality ?: null,
                        'remarks' => $remarks ?: null,
                    ]);
                }

                // ---- Enroll in class ----
                StudentClassRecord::firstOrCreate([
                    'student_profile_id' => $profile->id,
                    'class_id' => $this->classId,
                ]);

                $this->successCount++;

            } catch (Throwable $e) {
                $errorMsg = "Row " . ($i + 1) . " (LRN: $lrn): " . $e->getMessage();
                Log::error($errorMsg);
                $this->errors[] = $errorMsg;
            }
        }

        Log::info("Student Import completed: {$this->successCount} rows imported.");
        if (!empty($this->errors)) {
            Log::warning("Student Import had errors: " . implode('; ', $this->errors));
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}