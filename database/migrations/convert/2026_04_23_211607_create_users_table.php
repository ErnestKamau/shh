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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('email')->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_users_company_id_08c8e4d2');
            $table->rememberToken();
            $table->timestamps();
            $table->integer('active')->nullable()->default(1);
            $table->integer('location_id')->default(3);
            $table->uuid('zone_id')->nullable()->index('idx_users_zone_id_f2368746');
            $table->integer('department_id')->nullable();
            $table->string('photo')->nullable();
            $table->integer('electronic signature')->nullable();
            $table->integer('position')->nullable();
            $table->string('education_level', 100)->nullable();
            $table->text('date_of_birth')->nullable();
            $table->date('employment_date')->nullable();
            $table->text('id_number')->nullable();
            $table->string('nssf', 100)->nullable();
            $table->string('nhif', 100)->nullable();
            $table->string('kra_pin', 100)->nullable();
            $table->text('first_name')->nullable();
            $table->text('middle_name')->nullable();
            $table->text('last_name')->nullable();
            $table->string('electronic_sig')->nullable();
            $table->text('designation')->nullable();
            $table->text('verify_code')->nullable();
            $table->text('verify_code_expires')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->unsignedInteger('two_factor_last_used_step')->nullable();
            $table->boolean('is_client')->default(false);
            $table->uuid('client_id')->nullable()->index('idx_users_client_id');
            $table->uuid('crm_contact_id')->nullable()->index('idx_users_crm_contact_id');
            $table->uuid('crmcontact_id')->nullable(); // redundant, maybe keeping as uuid?
            $table->string('license_type', 100)->default('shared_user');
            $table->uuid('supplier_id')->default(0)->index('idx_users_supplier_id_ab12961c');
            $table->boolean('is_online')->default(false);
            $table->text('phone')->nullable();
            $table->text('gender')->nullable();
            $table->boolean('is_support_staff')->nullable()->default(false);
            $table->string('lab_section_id')->nullable();
            $table->boolean('is_tablet')->nullable()->default(false);
            $table->string('lab_id', 100)->nullable()->index('idx_users_lab_id_a403074c');
            $table->dateTime('deleted_at')->nullable();
            $table->string('accounting_remember_token', 100)->nullable();
            $table->foreign(['company_id'], 'fk_users_company_id_e27b71e0')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_users_supplier_id_e9938e35')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['zone_id'], 'fk_users_zone_id_95643f3a')->references(['id'])->on('zones')->onUpdate('no action')->onDelete('set null');
            $table->foreign('client_id', 'fk_users_client_id')->references('id')->on('crm_customers')->onDelete('set null');
            $table->foreign('crm_contact_id', 'fk_users_crm_contact_id')->references('id')->on('crm_customer_contacts')->onDelete('set null');





            $table->primary(['id']);




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
