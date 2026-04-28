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
        Schema::create('crm_customer_contacts', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('job_occupation')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('email');
            $table->string('telephone');
            $table->string('mobile')->nullable();
            $table->boolean('receive_price_list');
            $table->boolean('receive_invoice');
            $table->boolean('receive_report');
            $table->boolean('receive_feedback')->default(false);
            $table->boolean('can_receive_schedule_of_analysis')->default(false);
            $table->boolean('can_receive_payment_reminders')->default(false);
            $table->uuid('company_id')->index('idx_crm_customer_contacts_company_id_b8af522c');
            $table->uuid('crm_customer_id')->index('idx_crm_customer_contacts_crm_customer_id_adfd8739');
            $table->uuid('crm_company_unit_id')->nullable();
            $table->boolean('active');
            $table->timestamps();
            $table->boolean('can_login')->default(false);
            $table->string('other_customers', 500)->nullable();
            $table->boolean('can_submit_sample')->default(false);
            $table->string('signature')->nullable();
            $table->integer('title_id')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_customer_contacts');
    }
};
