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
        Schema::create('sample_submission_requests', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_header_id')->nullable()->index('idx_sample_submission_requests_sample_header_id_6c689408');
            $table->uuid('crm_customer_id')->nullable()->index('idx_sample_submission_requests_crm_customer_id_998cd9e1');
            $table->unsignedBigInteger('crm_contact_id')->nullable()->index('idx_sample_submission_requests_crm_contact_id_268e459d');
            $table->string('submitting_agency')->nullable();
            $table->string('submitting_officer_full_name')->nullable();
            $table->string('submitting_officer_title')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('working_station')->nullable();
            $table->string('office_telephone_no')->nullable();
            $table->string('mobile_telephone_no')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('case_no')->nullable()->index('idx_sample_submission_requests_case_no_89b04559');
            $table->string('offence')->nullable();
            $table->date('date_of_seizure')->nullable();
            $table->string('seizure_region')->nullable();
            $table->string('seizure_district')->nullable();
            $table->string('seizure_ward')->nullable();
            $table->string('seizure_village_street')->nullable();
            $table->string('submitted_by_full_name')->nullable();
            $table->string('submitted_by_title')->nullable();
            $table->string('submitted_by_signature')->nullable();
            $table->date('submitted_by_date')->nullable();
            $table->string('submitted_by_time')->nullable();
            $table->string('received_by_full_name')->nullable();
            $table->string('received_by_title')->nullable();
            $table->string('received_by_signature')->nullable();
            $table->date('received_by_date')->nullable();
            $table->string('received_by_time')->nullable();
            $table->string('status')->nullable()->index('idx_sample_submission_requests_status_315bab5c');
            $table->timestamps();
            $table->foreign(['crm_customer_id'], 'fk_sample_submission_requests_crm_customer_id_c9e674aa')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_id'], 'fk_sample_submission_requests_sample_header_id_85abbada')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_submission_requests');
    }
};
