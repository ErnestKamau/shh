<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_acceptance_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 50)->default('awaiting_customer_sign');
            $table->uuid('submission_form_instance_id')->nullable()->index();
            $table->uuid('sample_submission_request_id')->nullable()->index();
            $table->uuid('crm_customer_id')->index();
            $table->uuid('pricelist_id')->nullable();
            $table->uuid('currency_id')->nullable();
            $table->uuid('sample_header_id')->nullable()->index();
            $table->uuid('invoice_id')->nullable()->index();
            $table->string('customer_name');
            $table->date('request_date')->nullable();
            $table->unsignedInteger('number_of_samples')->default(1);
            $table->string('mode_of_work', 20)->default('Normal');
            $table->date('date_of_sampling')->nullable();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->text('customer_certification_text')->nullable();
            $table->string('customer_signer_name')->nullable();
            $table->longText('customer_signature')->nullable();
            $table->timestamp('customer_signed_at')->nullable();
            $table->string('manager_signer_name')->nullable();
            $table->longText('manager_signature')->nullable();
            $table->timestamp('manager_signed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_acceptance_forms');
    }
};
