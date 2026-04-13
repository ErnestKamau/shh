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
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            
            // --- MISSING COLUMN ADDED HERE ---
            // We use 'bigInteger' (not unsigned) to match your customers table ID exactly.
            $table->bigInteger('customer_id')->nullable()->after('id');
            
            // Add the foreign key constraint (CRM uses crm_customers table)
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('crm_customers')
                  ->nullOnDelete(); 
            // ---------------------------------

            // Service Details
            $table->string('service_type')->nullable();
            $table->string('service_type_other')->nullable()->after('service_type');
            $table->string('service_reference_no')->nullable()->after('service_type_other');
            $table->string('equipment_sample_id')->nullable()->after('service_reference_no');
            $table->date('results_issued_date')->nullable()->after('equipment_sample_id');

            // Ratings (1-4 scale)
            $table->unsignedTinyInteger('rating_communication')->nullable()->after('results_issued_date');
            $table->unsignedTinyInteger('rating_turnaround')->nullable()->after('rating_communication');
            $table->unsignedTinyInteger('rating_technical')->nullable()->after('rating_turnaround');
            $table->unsignedTinyInteger('rating_accuracy')->nullable()->after('rating_technical');
            $table->unsignedTinyInteger('rating_reports')->nullable()->after('rating_accuracy');
            $table->unsignedTinyInteger('rating_professionalism')->nullable()->after('rating_reports');
            $table->unsignedTinyInteger('rating_handling')->nullable()->after('rating_professionalism');
            $table->unsignedTinyInteger('rating_overall')->nullable()->after('rating_handling');

            // Impartiality & Confidentiality
            $table->string('iso_impartiality')->nullable()->after('rating_overall');
            $table->string('iso_confidentiality')->nullable()->after('iso_impartiality');
            $table->text('iso_concerns_description')->nullable()->after('iso_confidentiality');

            // Complaints
            $table->boolean('has_issues')->default(false)->after('iso_concerns_description');
            $table->text('issue_description')->nullable()->after('has_issues');
            $table->string('reported_previously')->nullable()->after('issue_description');

            // Improvement
            $table->text('suggestions')->nullable()->after('reported_previously');

            // Future Engagement
            $table->string('will_use_again')->nullable()->after('suggestions');
            $table->string('will_recommend')->nullable()->after('will_use_again');

            // Consent
            $table->boolean('consent_contact')->default(false)->after('will_recommend');
            $table->string('preferred_contact_method')->nullable()->after('consent_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            // Drop the foreign key and column first
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            $table->dropColumn([
                'service_type', 'service_type_other', 'service_reference_no', 'equipment_sample_id', 'results_issued_date',
                'rating_communication', 'rating_turnaround', 'rating_technical', 'rating_accuracy',
                'rating_reports', 'rating_professionalism', 'rating_handling', 'rating_overall',
                'iso_impartiality', 'iso_confidentiality', 'iso_concerns_description',
                'has_issues', 'issue_description', 'reported_previously',
                'suggestions',
                'will_use_again', 'will_recommend',
                'consent_contact', 'preferred_contact_method'
            ]);
        });
    }
};