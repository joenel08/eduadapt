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
        Schema::create('classes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
    $table->string('grade_level'); // "Grade 5", "Grade 6"
    $table->string('section_name'); // "DIAMOND"
    $table->timestamps();

    $table->unique(['school_year_id', 'grade_level', 'section_name']);
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('section_names');
    }
};
