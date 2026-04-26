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
        Schema::create('feedback_requests', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('company_id')->index('idx_feedback_requests_company_id_07e77a91');
            $table->unsignedBigInteger('customer_id')->nullable()->index('idx_feedback_requests_customer_id_3fa2446f');
            $table->uuid('contact_id')->nullable()->index('idx_feedback_requests_contact_id_d64c437b');
            $table->string('token')->unique();
            $table->string('email');
            $table->uuid('feedback_id')->nullable()->index('feedback_requests_feedback_id_foreign');
            $table->enum('status', ['PENDING', 'SUBMITTED', 'EXPIRED'])->default('PENDING')->index('idx_feedback_requests_PENDING_1a44cfa7');
            $table->timestamp('sent_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at')->default('0000-00-00 00:00:00');
            $table->timestamps();
            $table->foreign(['contact_id'], 'fk_feedback_requests_contact_id_5227f2e9')->references(['id'])->on('crm_customer_contacts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['feedback_id'], 'fk_feedback_requests_feedback_id_1799cc3e')->references(['id'])->on('customerfeedbacks')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_feedback_requests_company_id_4a70275d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_requests');
    }
};
