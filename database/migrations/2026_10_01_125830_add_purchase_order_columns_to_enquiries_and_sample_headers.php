<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bind each enquiry to one customer PO, and record PO coverage / split lineage on jobs.
     */
    public function up(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table) {
            $table->uuid('customer_purchase_order_id')->nullable()->index();

            $table->foreign('customer_purchase_order_id')
                ->references('id')
                ->on('customer_purchase_orders')
                ->nullOnDelete();
        });

        Schema::table('sample_headers', function (Blueprint $table) {
            $table->uuid('customer_purchase_order_id')->nullable()->index();
            $table->string('po_status', 20)->nullable()->index();
            $table->uuid('split_from_sample_header_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->dropIndex(['customer_purchase_order_id']);
            $table->dropIndex(['po_status']);
            $table->dropIndex(['split_from_sample_header_id']);
            $table->dropColumn(['customer_purchase_order_id', 'po_status', 'split_from_sample_header_id']);
        });

        Schema::table('sample_submission_requests', function (Blueprint $table) {
            $table->dropForeign(['customer_purchase_order_id']);
            $table->dropIndex(['customer_purchase_order_id']);
            $table->dropColumn('customer_purchase_order_id');
        });
    }
};
