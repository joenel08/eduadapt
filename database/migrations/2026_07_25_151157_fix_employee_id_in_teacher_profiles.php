<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Drop the column if it exists (from failed migration)
        if (Schema::hasColumn('teacher_profiles', 'employee_id')) {
            Schema::table('teacher_profiles', function (Blueprint $table) {
                $table->dropColumn('employee_id');
            });
        }

        // 2. Add the column as nullable (no unique yet)
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('user_id');
        });

        // 3. Populate existing rows with a unique value
        $teachers = DB::table('teacher_profiles')->get();
        foreach ($teachers as $teacher) {
            // Use the user's login_id if available, or generate a unique ID
            $loginId = DB::table('users')->where('id', $teacher->user_id)->value('login_id');
            if ($loginId && !DB::table('teacher_profiles')->where('employee_id', $loginId)->exists()) {
                $employeeId = $loginId; // assume login_id is unique
            } else {
                $employeeId = 'TCH-' . str_pad($teacher->id, 4, '0', STR_PAD_LEFT);
            }
            DB::table('teacher_profiles')
                ->where('id', $teacher->id)
                ->update(['employee_id' => $employeeId]);
        }

        // 4. Make the column NOT NULL and add UNIQUE constraint
        // We use raw SQL to avoid Doctrine DBAL dependency
        DB::statement('ALTER TABLE teacher_profiles MODIFY employee_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE teacher_profiles ADD UNIQUE INDEX teacher_profiles_employee_id_unique (employee_id)');
    }

    public function down()
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropColumn('employee_id');
        });
    }
};