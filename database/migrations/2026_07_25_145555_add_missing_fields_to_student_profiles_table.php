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
        Schema::table('student_profiles', function (Blueprint $table) {
            // Add all fields from the Excel sample
            $table->date('birth_date')->nullable()->after('suffix_name');
            $table->enum('sex', ['M', 'F'])->nullable()->after('birth_date');
            $table->string('mother_tongue')->nullable()->after('sex');
            $table->string('ip_ethnic_group')->nullable()->after('mother_tongue');
            $table->string('religion')->nullable()->after('ip_ethnic_group');

            // Address parts
            $table->string('address_house')->nullable()->after('religion');
            $table->string('address_barangay')->nullable()->after('address_house');
            $table->string('address_municipality')->nullable()->after('address_barangay');
            $table->string('address_province')->nullable()->after('address_municipality');

            // Parents/guardians
            $table->string('father_name')->nullable()->after('address_province');
            $table->string('mother_maiden_name')->nullable()->after('father_name');
            $table->string('guardian_name')->nullable()->after('mother_maiden_name');
            $table->string('guardian_relationship')->nullable()->after('guardian_name');

            // Contact and other
            $table->string('contact_number')->nullable()->after('guardian_relationship');
            $table->string('learning_modality')->nullable()->after('contact_number');
            $table->text('remarks')->nullable()->after('learning_modality');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            //
        });
    }
};
