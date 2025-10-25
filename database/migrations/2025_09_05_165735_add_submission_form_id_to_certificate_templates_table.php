<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmissionFormIdToCertificateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->bigInteger('submission_form_id')->after('footer_settings');
            
            // Add foreign key constraint
            $table->foreign('submission_form_id', 'ct_templates_submission_form_fk')
                  ->references('id')
                  ->on('submission_forms')
                  ->onDelete('cascade');
            
            // Add index for performance
            $table->index('submission_form_id', 'ct_templates_form_id_idx');
            $table->index(['submission_form_id', 'is_published', 'is_active'], 'ct_templates_form_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            // Drop foreign key and indexes first
            $table->dropForeign('ct_templates_submission_form_fk');
            $table->dropIndex('ct_templates_form_id_idx');
            $table->dropIndex('ct_templates_form_status_idx');
            
            // Drop the column
            $table->dropColumn('submission_form_id');
        });
    }
}
