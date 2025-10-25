<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldMappingToSubmissionFormElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_form_elements', function (Blueprint $table) {
            $table->string('mapping_table')->nullable()->after('validation_rules')->comment('Target table: sample_headers or sample_details');
            $table->string('mapping_field')->nullable()->after('mapping_table')->comment('Target field name in the mapping table');
            $table->boolean('is_mapped')->default(false)->after('mapping_field')->comment('Whether this element is mapped to a database field');
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
            $table->dropColumn(['mapping_table', 'mapping_field', 'is_mapped']);
        });
    }
}