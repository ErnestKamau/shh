<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PO lines: one quantity allowance per package / test.
     * The *_qty balance columns are a cache of the ledger and are written only by
     * PurchaseOrderAllocationService inside the same locked transaction as the ledger insert.
     */
    public function up(): void
    {
        Schema::create('customer_purchase_order_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_purchase_order_id');
            $table->unsignedSmallInteger('line_no');
            $table->string('description', 500);
            $table->uuid('quotation_detail_id')->nullable()->index();
            $table->uuid('pricelist_item_id')->nullable()->index();
            $table->uuid('sample_type_id')->nullable()->index();
            $table->json('analysis_type_ids')->nullable();
            $table->boolean('is_package')->default(false);
            $table->decimal('unit_price_gross', 15, 2)->default(0);
            $table->integer('ordered_qty')->default(0);
            $table->integer('reserved_qty')->default(0);
            $table->integer('committed_qty')->default(0);
            $table->integer('invoiced_qty')->default(0);
            $table->integer('remaining_qty')->default(0);
            $table->unsignedInteger('notify_remaining_qty')->nullable();
            $table->timestamp('threshold_notified_at')->nullable();
            $table->timestamp('exhausted_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_purchase_order_id', 'line_no']);

            $table->foreign('customer_purchase_order_id')
                ->references('id')
                ->on('customer_purchase_orders')
                ->cascadeOnDelete();

            $table->foreign('quotation_detail_id')
                ->references('id')
                ->on('quotation_details')
                ->nullOnDelete();

            $table->foreign('pricelist_item_id')
                ->references('id')
                ->on('pricelist_items')
                ->nullOnDelete();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE customer_purchase_order_lines ADD CONSTRAINT cpo_lines_balances_non_negative CHECK (ordered_qty >= 0 AND reserved_qty >= 0 AND committed_qty >= 0 AND invoiced_qty >= 0 AND remaining_qty >= 0)');
            DB::statement('ALTER TABLE customer_purchase_order_lines ADD CONSTRAINT cpo_lines_invoiced_within_committed CHECK (invoiced_qty <= committed_qty)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_purchase_order_lines');
    }
};
