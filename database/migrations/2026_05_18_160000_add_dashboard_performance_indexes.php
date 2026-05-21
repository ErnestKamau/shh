<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submission_form_instances')) {
            Schema::table('submission_form_instances', function (Blueprint $table) {
                $table->index(['crm_customer_id', 'status'], 'idx_sfi_customer_status');
                $table->index(['crm_customer_id', 'submitted_at'], 'idx_sfi_customer_submitted');
            });
        }

        if (Schema::hasTable('customer_notifications')) {
            Schema::table('customer_notifications', function (Blueprint $table) {
                $table->index(['customer_id', 'read_at', 'created_at'], 'idx_cn_customer_read_created');
            });
        }

        if (Schema::hasTable('customer_invoice')) {
            Schema::table('customer_invoice', function (Blueprint $table) {
                $table->index(['customer_id', 'deleted_at'], 'idx_ci_customer_deleted');
            });
        }

        if (Schema::hasTable('sample_headers')) {
            Schema::table('sample_headers', function (Blueprint $table) {
                $table->index(['crm_customer_id', 'status'], 'idx_sh_customer_status');
            });
        }

        if (Schema::hasTable('complaints')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->index(['client_id', 'is_closed'], 'idx_complaints_client_closed');
            });
        }

        if (Schema::hasTable('customerfeedbacks')) {
            Schema::table('customerfeedbacks', function (Blueprint $table) {
                $table->index(['customer_id', 'is_submitted', 'submitted_at'], 'idx_cf_customer_submitted');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('submission_form_instances')) {
            Schema::table('submission_form_instances', function (Blueprint $table) {
                $table->dropIndex('idx_sfi_customer_status');
                $table->dropIndex('idx_sfi_customer_submitted');
            });
        }

        if (Schema::hasTable('customer_notifications')) {
            Schema::table('customer_notifications', function (Blueprint $table) {
                $table->dropIndex('idx_cn_customer_read_created');
            });
        }

        if (Schema::hasTable('customer_invoice')) {
            Schema::table('customer_invoice', function (Blueprint $table) {
                $table->dropIndex('idx_ci_customer_deleted');
            });
        }

        if (Schema::hasTable('sample_headers')) {
            Schema::table('sample_headers', function (Blueprint $table) {
                $table->dropIndex('idx_sh_customer_status');
            });
        }

        if (Schema::hasTable('complaints')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->dropIndex('idx_complaints_client_closed');
            });
        }

        if (Schema::hasTable('customerfeedbacks')) {
            Schema::table('customerfeedbacks', function (Blueprint $table) {
                $table->dropIndex('idx_cf_customer_submitted');
            });
        }
    }
};
