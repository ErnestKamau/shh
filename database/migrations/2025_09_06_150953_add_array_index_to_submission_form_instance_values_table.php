<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArrayIndexToSubmissionFormInstanceValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_form_instance_values', function (Blueprint $table) {
            $table->integer('array_index')->nullable()->after('submission_form_element_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_form_instance_values', function (Blueprint $table) {
            $table->dropColumn('array_index');
        });
    }
}
