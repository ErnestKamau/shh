<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPublicToComplaintNotesAndAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('complaintnotes', function (Blueprint $table) {
            $table->boolean('is_public')->default(0)->after('type');
        });

        Schema::table('complaintattachments', function (Blueprint $table) {
            $table->boolean('is_public')->default(0)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('complaintnotes', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });

        Schema::table('complaintattachments', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
}
