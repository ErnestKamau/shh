<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('po_number')->nullable()->index();
            $table->boolean('po_skipped')->default(false);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->uuid('enquiry_id')->unique();
            $table->uuid('quotation_header_id')->nullable()->index();
            $table->uuid('customer_id')->nullable()->index();
            $table->uuid('uploaded_by')->nullable()->index();
            $table->timestamp('recorded_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('enquiry_id')
                ->references('id')
                ->on('sample_submission_requests')
                ->cascadeOnDelete();

            $table->foreign('quotation_header_id')
                ->references('id')
                ->on('quotation_headers')
                ->nullOnDelete();

            $table->foreign('customer_id')
                ->references('id')
                ->on('crm_customers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_purchase_orders');
    }
};
