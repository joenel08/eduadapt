<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('content_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->onDelete('cascade');
            $table->string('content_type'); // learning_material, pre_assessment, etc.
            $table->unsignedBigInteger('content_id');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->datetime('release_date')->nullable();
            $table->datetime('due_date')->nullable();
            $table->timestamps();

            $table->unique(['content_type', 'content_id', 'class_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_releases');
    }
};