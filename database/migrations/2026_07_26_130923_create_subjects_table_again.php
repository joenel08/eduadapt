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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('grade_level'); // "Grade 5", "Grade 6"
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['grade_level', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subjects_table_again');
    }
};
