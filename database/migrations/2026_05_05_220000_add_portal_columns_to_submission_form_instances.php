<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('submission_form_instances', 'portal_account_id')) {
                $table->uuid('portal_account_id')->nullable()->after('submitted_by');
            }

            if (!Schema::hasColumn('submission_form_instances', 'crm_customer_id')) {
                $table->uuid('crm_customer_id')->nullable()->after('portal_account_id');
            }

            if (!Schema::hasColumn('submission_form_instances', 'target_record_type')) {
                $table->string('target_record_type', 100)->nullable()->after('crm_customer_id');
            }

            if (!Schema::hasColumn('submission_form_instances', 'target_record_id')) {
                $table->unsignedBigInteger('target_record_id')->nullable()->after('target_record_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            $table->dropColumn(['portal_account_id', 'crm_customer_id', 'target_record_type', 'target_record_id']);
        });
    }
};
