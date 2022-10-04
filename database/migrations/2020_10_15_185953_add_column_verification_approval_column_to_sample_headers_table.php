<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnVerificationApprovalColumnToSampleHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->date('verification_email_date')->nullable();
            $table->date('approval_email_date')->nullable();
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
            //
        });
    }
}
