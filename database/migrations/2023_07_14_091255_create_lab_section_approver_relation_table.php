<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabSectionApproverRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_section_approver_relation', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('lab_section_id');
            $table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lab_section_approver_relation');
    }
}
