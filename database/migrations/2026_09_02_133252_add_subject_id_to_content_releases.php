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
    Schema::table('content_releases', function (Blueprint $table) {
        $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_releases', function (Blueprint $table) {
            //
        });
    }
};
