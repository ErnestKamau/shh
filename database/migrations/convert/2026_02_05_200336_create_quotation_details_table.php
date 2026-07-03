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
        Schema::create('quotation_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('quotation_header_id')->index('idx_quotation_details_quotation_header_id_55d2b7e7');
            $table->uuid('invoicable_item_id')->nullable()->index('idx_quotation_details_invoicable_item_id_21e09693');
            $table->uuid('analyte_id')->nullable()->index('idx_quotation_details_analyte_id_96d33915');
            $table->integer('quantity');
            $table->string('part_no', 100)->nullable();
            $table->double('unit_price');
            $table->double('tax')->nullable();
            $table->string('sample_type', 100)->nullable();
            $table->string('item_name', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('photo_url', 1000)->nullable();
            $table->text('subcontracted_analytes')->nullable();
            $table->text('accredited_analytes')->nullable();
            $table->text('default_analytes')->nullable();
            $table->text('sub_acc_analytes')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_details');
    }
};
