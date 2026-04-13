<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns from detailed-fields migration (iso_impartiality, etc.)
     * when the original migration could not run fully (e.g. customer_id already existed).
     */
    public function up(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            if (!Schema::hasColumn('customerfeedbacks', 'service_type')) {
                $table->string('service_type')->nullable()->after('code');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'service_type_other')) {
                $table->string('service_type_other')->nullable()->after('service_type');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'service_reference_no')) {
                $table->string('service_reference_no')->nullable()->after('service_type_other');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'equipment_sample_id')) {
                $table->string('equipment_sample_id')->nullable()->after('service_reference_no');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'results_issued_date')) {
                $table->date('results_issued_date')->nullable()->after('equipment_sample_id');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_communication')) {
                $table->unsignedTinyInteger('rating_communication')->nullable()->after('results_issued_date');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_turnaround')) {
                $table->unsignedTinyInteger('rating_turnaround')->nullable()->after('rating_communication');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_technical')) {
                $table->unsignedTinyInteger('rating_technical')->nullable()->after('rating_turnaround');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_accuracy')) {
                $table->unsignedTinyInteger('rating_accuracy')->nullable()->after('rating_technical');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_reports')) {
                $table->unsignedTinyInteger('rating_reports')->nullable()->after('rating_accuracy');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_professionalism')) {
                $table->unsignedTinyInteger('rating_professionalism')->nullable()->after('rating_reports');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_handling')) {
                $table->unsignedTinyInteger('rating_handling')->nullable()->after('rating_professionalism');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'rating_overall')) {
                $table->unsignedTinyInteger('rating_overall')->nullable()->after('rating_handling');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'iso_impartiality')) {
                $table->string('iso_impartiality')->nullable()->after('rating_overall');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'iso_confidentiality')) {
                $table->string('iso_confidentiality')->nullable()->after('iso_impartiality');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'iso_concerns_description')) {
                $table->text('iso_concerns_description')->nullable()->after('iso_confidentiality');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'has_issues')) {
                $table->boolean('has_issues')->default(false)->after('iso_concerns_description');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'issue_description')) {
                $table->text('issue_description')->nullable()->after('has_issues');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'reported_previously')) {
                $table->string('reported_previously')->nullable()->after('issue_description');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'suggestions')) {
                $table->text('suggestions')->nullable()->after('reported_previously');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'will_use_again')) {
                $table->string('will_use_again')->nullable()->after('suggestions');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'will_recommend')) {
                $table->string('will_recommend')->nullable()->after('will_use_again');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'consent_contact')) {
                $table->boolean('consent_contact')->default(false)->after('will_recommend');
            }
            if (!Schema::hasColumn('customerfeedbacks', 'preferred_contact_method')) {
                $table->string('preferred_contact_method')->nullable()->after('consent_contact');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $columns = [
                'service_type', 'service_type_other', 'service_reference_no', 'equipment_sample_id',
                'results_issued_date', 'rating_communication', 'rating_turnaround', 'rating_technical',
                'rating_accuracy', 'rating_reports', 'rating_professionalism', 'rating_handling', 'rating_overall',
                'iso_impartiality', 'iso_confidentiality', 'iso_concerns_description',
                'has_issues', 'issue_description', 'reported_previously', 'suggestions',
                'will_use_again', 'will_recommend', 'consent_contact', 'preferred_contact_method',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('customerfeedbacks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
