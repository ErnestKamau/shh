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
            $table->id();
            $table->integer('company_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->bigInteger('contact_id')->nullable()->index();
            $table->string('token')->unique();
            $table->string('email');
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->enum('status', ['PENDING', 'SUBMITTED', 'EXPIRED'])->default('PENDING')->index();
            $table->timestamp('sent_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Foreign keys
            // $table->foreign('customer_id')->references('id')->on('crm_customers')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('crm_customer_contacts')->onDelete('cascade');
            $table->foreign('feedback_id')->references('id')->on('customerfeedbacks')->onDelete('set null');
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
