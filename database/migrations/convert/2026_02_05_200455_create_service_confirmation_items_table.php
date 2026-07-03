<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('service_confirmation_items')) {
            return;
        }
        Schema::create('service_confirmation_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('service_confirmation_id');
            $table->integer('sap_id');
            $table->string('type_code');
            $table->string('proccessing_type_code')->nullable();
            $table->string('description');
            $table->string('uuid');
            $table->string('confirmation_duration')->nullable();
            $table->string('service_performer_item_party_id')->nullable();
            $table->string('service_performer_item_party_name')->nullable();
            $table->string('start_time_point')->nullable();
            $table->string('end_time_point')->nullable();
            $table->string('time_zone_code')->nullable();
            $table->string('product_id')->nullable();
            $table->string('product_id_internal')->nullable();
            $table->string('service_product_description')->nullable();
            $table->string('schedule_line_quantity')->nullable();
            $table->string('schedule_line_unit_code')->nullable();
            $table->string('net_amount_value')->nullable();
            $table->string('net_amount_currency_code')->nullable();
            $table->string('net_price_value')->nullable();
            $table->string('net_price_currency_code')->nullable();
            $table->string('base_quantity')->nullable();
            $table->string('base_quantity_unit_code')->nullable();
            $table->string('base_quantity_type_code')->nullable();
            $table->string('document_reference_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('mobile_id')->nullable();
            $table->string('internal_comment', 100)->nullable();
            $table->string('internal_comment_typecode', 100)->nullable();
            $table->text('customer_comment')->nullable();
            $table->string('customer_comment_typecode')->nullable();
            $table->string('authorised_by')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_confirmation_items');
    }
};
