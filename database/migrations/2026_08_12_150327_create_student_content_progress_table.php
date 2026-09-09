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
       Schema::create('student_content_progress', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
    $table->foreignId('content_item_id')->constrained('content_items')->onDelete('cascade');
    $table->enum('status', ['pending', 'viewed', 'completed'])->default('pending');
    $table->timestamp('viewed_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    $table->unique(['student_profile_id', 'content_item_id']);
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_content_progress');
    }
};
