<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail for changing an enquiry's PO after it is ready for reception (Integrity Check).
     */
    public function up(): void
    {
        Schema::create('enquiry_purchase_order_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_submission_request_id');
            $table->uuid('from_customer_purchase_order_id')->nullable()->index();
            $table->uuid('to_customer_purchase_order_id')->nullable()->index();
            $table->string('from_po_number')->nullable();
            $table->string('to_po_number')->nullable();
            $table->string('enquiry_status', 64)->nullable();
            $table->text('reason');
            $table->uuid('changed_by')->nullable()->index();
            $table->timestamps();

            $table->index(['sample_submission_request_id', 'created_at']);

            $table->foreign('sample_submission_request_id')
                ->references('id')
                ->on('sample_submission_requests')
                ->cascadeOnDelete();

            $table->foreign('from_customer_purchase_order_id')
                ->references('id')
                ->on('customer_purchase_orders')
                ->nullOnDelete();

            $table->foreign('to_customer_purchase_order_id')
                ->references('id')
                ->on('customer_purchase_orders')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiry_purchase_order_changes');
    }
};
