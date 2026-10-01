<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Insert-only PO ledger. Every balance on a PO line is the sum of its entries;
     * reversals are new rows with a negative quantity, never updates or deletes.
     * Document references are plain indexed UUIDs (no FKs) so history survives purges.
     */
    public function up(): void
    {
        Schema::create('customer_purchase_order_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_purchase_order_id');
            $table->uuid('customer_purchase_order_line_id');
            $table->string('entry_type', 20);
            $table->integer('quantity');
            $table->uuid('enquiry_id')->nullable()->index();
            $table->uuid('sample_header_id')->nullable()->index();
            $table->uuid('invoice_id')->nullable()->index();
            $table->uuid('credit_note_id')->nullable()->index();
            $table->uuid('amendment_id')->nullable()->index();
            $table->string('reason', 500)->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['customer_purchase_order_line_id', 'entry_type'], 'cpo_ledger_line_type_index');
            $table->index('customer_purchase_order_id', 'cpo_ledger_po_index');

            $table->foreign('customer_purchase_order_id', 'cpo_ledger_po_foreign')
                ->references('id')
                ->on('customer_purchase_orders')
                ->cascadeOnDelete();

            $table->foreign('customer_purchase_order_line_id', 'cpo_ledger_line_foreign')
                ->references('id')
                ->on('customer_purchase_order_lines')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_purchase_order_ledger_entries');
    }
};
