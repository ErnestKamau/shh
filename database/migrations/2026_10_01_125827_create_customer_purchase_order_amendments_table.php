<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audited PO amendments (top-ups, validity extensions, new lines, closure).
     */
    public function up(): void
    {
        Schema::create('customer_purchase_order_amendments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_purchase_order_id');
            $table->uuid('customer_purchase_order_line_id')->nullable()->index();
            $table->string('amendment_type', 30);
            $table->json('changes')->nullable();
            $table->text('reason')->nullable();
            $table->uuid('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['customer_purchase_order_id', 'created_at']);

            $table->foreign('customer_purchase_order_id')
                ->references('id')
                ->on('customer_purchase_orders')
                ->cascadeOnDelete();

            $table->foreign('customer_purchase_order_line_id')
                ->references('id')
                ->on('customer_purchase_order_lines')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_purchase_order_amendments');
    }
};
