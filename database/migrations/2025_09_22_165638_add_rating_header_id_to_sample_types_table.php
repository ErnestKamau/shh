<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatingHeaderIdToSampleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_types', function (Blueprint $table) {
            $table->foreignId('rating_header_id')->nullable()->constrained('rating_headers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_types', function (Blueprint $table) {
            $table->dropForeign(['rating_header_id']);
            $table->dropColumn('rating_header_id');
        });
    }
}