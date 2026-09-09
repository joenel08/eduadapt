<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained('teacher_profiles');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->string('grade_level');
            $table->string('term');
            $table->string('week');
            $table->enum('type', ['learning_material'])->default('learning_material');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_items');
    }
};
