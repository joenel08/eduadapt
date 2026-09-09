<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained('teacher_profiles');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->string('grade_level');
            $table->string('term');
            $table->string('week');
            $table->enum('exam_type', ['multipleChoice', 'trueFalse', 'matchingType']);
            $table->enum('input_method', ['upload', 'manual']);
            $table->json('questions');          // Full question data (choices, correct answers, etc.)
            $table->json('settings')->nullable(); // timer, shuffle, due_date, etc.
            $table->string('file_name')->nullable(); // original uploaded file name
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_assessments');
    }
};