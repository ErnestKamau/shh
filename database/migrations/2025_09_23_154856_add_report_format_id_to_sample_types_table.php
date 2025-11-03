<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportFormatIdToSampleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_types', function (Blueprint $table) {
            $table->bigInteger('report_format_id')->nullable()->after('report_template_id');
            
            // Add foreign key constraint
            $table->foreign('report_format_id')->references('id')->on('report_formats')->onDelete('set null');
            
            // Add index for performance
            $table->index('report_format_id');
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
            $table->dropForeign(['report_format_id']);
            $table->dropIndex(['report_format_id']);
            $table->dropColumn('report_format_id');
        });
    }
}