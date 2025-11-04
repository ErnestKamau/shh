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
            // Company Information Fields
            $table->string('company_name')->nullable()->after('holder_type');
            $table->string('company_email')->nullable()->after('company_name');
            $table->string('company_website')->nullable()->after('company_email');
            $table->string('company_phone')->nullable()->after('company_website');
            $table->string('company_logo')->nullable()->after('company_phone');
            
            // Document QA Details
            $table->string('form_number')->nullable()->after('company_logo');
            $table->date('publish_date')->nullable()->after('form_number');
            $table->string('qa_other_details')->nullable()->after('publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_element_holders', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'company_email',
                'company_website',
                'company_phone',
                'company_logo',
                'form_number',
                'publish_date',
                'qa_other_details'
            ]);
        });
    }
};
