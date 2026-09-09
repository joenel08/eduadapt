<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_class_records', function (Blueprint $table) {
            $table->boolean('materials_locked')->default(false)->after('class_id');
        });
    }

    public function down()
    {
        Schema::table('student_class_records', function (Blueprint $table) {
            $table->dropColumn('materials_locked');
        });
    }
};