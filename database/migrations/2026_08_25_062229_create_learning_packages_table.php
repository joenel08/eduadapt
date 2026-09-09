<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('learning_packages', function (Blueprint $table) {

            $table->id();

            $table->foreignId('teacher_profile_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('grade_level');

            $table->string('term');

            $table->string('subject');

            $table->string('week');

            $table->json('assigned_class_ids')
                ->nullable();

            $table->date('release_date')
                ->nullable();

            $table->date('due_date')
                ->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'archived'
            ])->default('draft');

            $table->timestamps();

            /*
             * Give the unique index a short name.
             * MySQL has a 64-character identifier limit.
             */
            $table->unique(
                [
                    'teacher_profile_id',
                    'grade_level',
                    'term',
                    'subject',
                    'week'
                ],
                'lp_teacher_grade_term_subject_week_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('learning_packages');
    }
};