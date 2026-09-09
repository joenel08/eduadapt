<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            // Add lrn column if it doesn't exist
            if (!Schema::hasColumn('student_profiles', 'lrn')) {
                $table->string('lrn')->unique()->after('user_id');
            }
            
            // Ensure sex column allows only 'M' or 'F', but default null
            // You may need to drop and re-add if data is invalid
            // For now, just make it nullable and accept any string
            $table->string('sex', 10)->nullable()->change();
            
            // Add contact_number if missing
            if (!Schema::hasColumn('student_profiles', 'contact_number')) {
                $table->string('contact_number')->nullable();
            }
        });
    }

    public function down(): void
    {
        // rollback logic if needed
    }
};