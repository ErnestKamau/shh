<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            // Make submitted_by nullable to support portal submissions where
            // a direct LIMS user ID may not exist.
            $table->bigInteger('submitted_by')->nullable()->change();

            // Track which portal account and CRM customer submitted this instance.
            if (!Schema::hasColumn('submission_form_instances', 'portal_account_id')) {
                $table->uuid('portal_account_id')
                    ->nullable()
                    ->after('reviewed_by')
                    ->comment('UUID of the portal account that created this instance');
            }

            if (!Schema::hasColumn('submission_form_instances', 'crm_customer_id')) {
                $table->unsignedBigInteger('crm_customer_id')
                    ->nullable()
                    ->after('portal_account_id')
                    ->comment('CRM customer ID scoped from the portal account');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_instances', 'crm_customer_id')) {
                $table->dropColumn('crm_customer_id');
            }

            if (Schema::hasColumn('submission_form_instances', 'portal_account_id')) {
                $table->dropColumn('portal_account_id');
            }

            // Revert submitted_by to non-nullable (data must be clean first)
            $table->bigInteger('submitted_by')->nullable(false)->change();
        });
    }
};
