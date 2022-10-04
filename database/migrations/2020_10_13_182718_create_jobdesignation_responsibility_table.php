<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobdesignationResponsibilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_designation_responsibility', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('job_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(0);
            $table->integer('edited_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobdesignation_responsibility');
    }
}
