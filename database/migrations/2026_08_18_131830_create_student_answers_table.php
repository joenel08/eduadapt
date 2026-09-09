<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->onDelete('cascade');
            $table->enum('exam_type', ['pre', 'post', 'intervention']);
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('grade_level');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('week'); // e.g., 'Week 1'
            $table->json('answers')->nullable();
            $table->decimal('score', 5, 2)->nullable(); // e.g., 85.00
            $table->string('video_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_answers');
    }
};