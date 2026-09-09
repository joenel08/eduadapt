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
        Schema::create('intervention_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('grade_level');
            $table->string('term');
            $table->string('week');
            $table->enum('level', ['basic', 'standard', 'advanced']);
            $table->string('file_name');
            $table->string('file_path')->nullable();
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
        Schema::dropIfExists('intervention_materials');
    }
};
