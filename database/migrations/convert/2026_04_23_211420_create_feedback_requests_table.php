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
            $table->uuid('company_id')->index('idx_feedback_requests_company_id_2adecf85');
            $table->unsignedBigInteger('customer_id')->nullable()->index('idx_feedback_requests_customer_id_976dfc5d');
            $table->uuid('contact_id')->nullable()->index('idx_feedback_requests_contact_id_c95ca77a');
            $table->string('token')->unique();
            $table->string('email');
            $table->uuid('feedback_id')->nullable()->index('idx_feedback_requests_feedback_id_6883fdd0');
            $table->enum('status', ['PENDING', 'SUBMITTED', 'EXPIRED'])->default('PENDING')->index('idx_feedback_requests_PENDING_6959d526');
            $table->timestamp('sent_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

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
