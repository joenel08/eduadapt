<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix_name')->nullable();
            $table->string('grade_level');
            $table->string('contact_no')->nullable();
            $table->text('address')->nullable();
            // LRN could be here, but we already have login_id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};