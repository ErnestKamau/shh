<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmissionFormInstanceIdToSampleHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('submission_form_instance_id')->nullable()->after('id')->comment('ID of the submission form instance that created this sample');
            $table->index('submission_form_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->dropIndex(['submission_form_instance_id']);
            $table->dropColumn('submission_form_instance_id');
        });
    }
}
