<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   public function up()
    {
        DB::statement("ALTER TABLE pre_assessments MODIFY exam_type ENUM('multipleChoice','trueFalse','matchingType','mixed') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE pre_assessments MODIFY exam_type ENUM('multipleChoice','trueFalse','matchingType') NOT NULL");
    }
};
