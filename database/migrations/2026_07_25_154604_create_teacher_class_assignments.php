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
        Schema::create('teacher_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained('teacher_profiles')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['teacher_profile_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_class_assignments');
    }
};
