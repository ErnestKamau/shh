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
        if (Schema::hasTable('customerfeedbacks')) {
            return;
        }
        Schema::create('customerfeedbacks', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('customer_id')->nullable()->index('idx_customerfeedbacks_customer_id_b832bc14');
            $table->uuid('contact_id')->nullable()->index('idx_customerfeedbacks_contact_id_18e191d9');
            $table->string('contact_position')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('usage_duration')->nullable();
            $table->string('doc_ref')->nullable();
            $table->string('doc_version')->nullable();
            $table->timestamps();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('feedback');
            $table->string('received_from');
            $table->string('registered_by');
            $table->string('user_type');
            $table->dateTime('date');
            $table->string('edited_by')->nullable();
            $table->boolean('status')->default(false);
            $table->string('delivery_status')->default('pending')->comment('pending, processing, sent, failed');
            $table->boolean('is_submitted')->default(true);
            $table->integer('client_id')->default(0);
            $table->string('code', 100)->nullable();
            $table->string('service_type')->nullable();
            $table->string('service_type_other')->nullable();
            $table->string('service_reference_no')->nullable();
            $table->string('equipment_sample_id')->nullable();
            $table->date('results_issued_date')->nullable();
            $table->unsignedTinyInteger('rating_communication')->nullable();
            $table->unsignedTinyInteger('rating_turnaround')->nullable();
            $table->unsignedTinyInteger('rating_technical')->nullable();
            $table->unsignedTinyInteger('rating_accuracy')->nullable();
            $table->unsignedTinyInteger('rating_reports')->nullable();
            $table->unsignedTinyInteger('rating_professionalism')->nullable();
            $table->unsignedTinyInteger('rating_handling')->nullable();
            $table->unsignedTinyInteger('rating_overall')->nullable();
            $table->string('iso_impartiality')->nullable();
            $table->string('iso_confidentiality')->nullable();
            $table->text('iso_concerns_description')->nullable();
            $table->boolean('has_issues')->default(false);
            $table->text('specific_feedback')->nullable();
            $table->text('issue_description')->nullable();
            $table->string('reported_previously')->nullable();
            $table->text('suggestions')->nullable();
            $table->string('will_use_again')->nullable();
            $table->string('will_recommend')->nullable();
            $table->boolean('consent_contact')->default(false);
            $table->string('preferred_contact_method')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customerfeedbacks');
    }
};
