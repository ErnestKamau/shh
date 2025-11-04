<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificate_template_element_holders', function (Blueprint $table) {
            // Add data_source field to specify which model to pull data from
            $table->enum('data_source', ['Company', 'CRMCustomer', 'SampleHeader', 'SampleDetails', 'CapturedResult'])
                ->nullable()
                ->after('holder_type');
            
            // Add field_mappings JSON field to store which fields from data_source to use
            $table->json('field_mappings')->nullable()->after('data_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_element_holders', function (Blueprint $table) {
            $table->dropColumn(['data_source', 'field_mappings']);
        });
    }
};
