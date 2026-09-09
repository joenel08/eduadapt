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
        Schema::create('student_class_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade'); // use 'class_id'
            $table->timestamps();
            $table->unique(['student_profile_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_class');
    }
};
