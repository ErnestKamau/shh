<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCertificateTemplateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop tables in reverse order of creation to avoid foreign key constraints
        Schema::dropIfExists('certificate_template_elements');
        Schema::dropIfExists('certificate_template_element_holders');
        Schema::dropIfExists('certificate_template_sections');
        Schema::dropIfExists('certificate_template_permissions');
        Schema::dropIfExists('certificate_template_data_bindings');
        Schema::dropIfExists('certificate_templates');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration only drops tables, so we don't need to implement down()
        // If you need to recreate the tables, you would need to run the original migrations
    }
}