<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Customer POs become customer-level documents (validity, status, invoicing mode).
     * enquiry_id stays as a legacy single-enquiry link but is no longer unique.
     */
    public function up(): void
    {
        Schema::table('customer_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['enquiry_id']);
            $table->dropUnique(['enquiry_id']);
        });

        Schema::table('customer_purchase_orders', function (Blueprint $table) {
            $table->uuid('enquiry_id')->nullable()->change();
            $table->index('enquiry_id');
            $table->foreign('enquiry_id')
                ->references('id')
                ->on('sample_submission_requests')
                ->nullOnDelete();

            $table->string('po_type', 20)->default('single')->after('po_skipped');
            $table->string('status', 20)->default('active')->after('po_type')->index();
            $table->uuid('currency_id')->nullable()->after('customer_id')->index();
            $table->date('valid_from')->nullable()->after('currency_id');
            $table->date('valid_to')->nullable()->after('valid_from')->index();
            $table->string('invoicing_mode', 20)->default('per_job')->after('valid_to');
            $table->string('invoicing_period', 20)->nullable()->after('invoicing_mode');
            $table->unsignedSmallInteger('expiry_notice_days')->default(30)->after('invoicing_period');
            $table->timestamp('expiry_notified_at')->nullable()->after('expiry_notice_days');
            $table->timestamp('closed_at')->nullable()->after('expiry_notified_at');
            $table->text('notes')->nullable()->after('closed_at');

            $table->index(['customer_id', 'po_number']);

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropIndex(['customer_id', 'po_number']);
            $table->dropColumn([
                'po_type',
                'status',
                'currency_id',
                'valid_from',
                'valid_to',
                'invoicing_mode',
                'invoicing_period',
                'expiry_notice_days',
                'expiry_notified_at',
                'closed_at',
                'notes',
            ]);

            $table->dropForeign(['enquiry_id']);
            $table->dropIndex(['enquiry_id']);
        });

        Schema::table('customer_purchase_orders', function (Blueprint $table) {
            $table->uuid('enquiry_id')->nullable(false)->change();
            $table->unique('enquiry_id');
            $table->foreign('enquiry_id')
                ->references('id')
                ->on('sample_submission_requests')
                ->cascadeOnDelete();
        });
    }
};
