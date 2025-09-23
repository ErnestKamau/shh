<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemedyFieldsToAnalysisElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->boolean('recommend_remedies')->default(false);
            $table->foreignId('remedy_header_id')->nullable()->constrained('remedy_headers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropForeign(['remedy_header_id']);
            $table->dropColumn(['recommend_remedies', 'remedy_header_id']);
        });
    }
}