<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('prefix_name')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->text('address')->nullable();
            // employee_id can be stored here, but we already have login_id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};