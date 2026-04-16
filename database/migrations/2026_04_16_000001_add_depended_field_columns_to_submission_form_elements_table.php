<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDependedFieldColumnsToSubmissionFormElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_form_elements', function (Blueprint $table) {
            $table->string('depends_on_type', 100)->nullable()->after('is_mapped')
                ->comment('Element type of the select field this element depends on (e.g. client_select)');
            $table->string('depends_on_field', 255)->nullable()->after('depends_on_type')
                ->comment('The name attribute of the select element to listen to');
            $table->string('source_table', 255)->nullable()->after('depends_on_field')
                ->comment('DB table to read the value from (e.g. crm_customers)');
            $table->string('source_field', 255)->nullable()->after('source_table')
                ->comment('Column in source_table to read (e.g. postal_address)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_form_elements', function (Blueprint $table) {
            $table->dropColumn(['depends_on_type', 'depends_on_field', 'source_table', 'source_field']);
        });
    }
}
