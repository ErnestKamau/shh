<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pricelist_items')
            && ! Schema::hasColumn('pricelist_items', 'is_package')) {
            Schema::table('pricelist_items', function (Blueprint $table): void {
                $table->boolean('is_package')->default(false)->index();
            });
        }

        if (! Schema::hasTable('pricelist_item_elements')) {
            Schema::create('pricelist_item_elements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('pricelist_item_id')->index();
                $table->uuid('analysis_element_id')->index();
                $table->timestamps();

                $table->unique(['pricelist_item_id', 'analysis_element_id'], 'pricelist_item_elements_item_element_unique');

                $table->foreign('pricelist_item_id')
                    ->references('id')->on('pricelist_items')
                    ->cascadeOnDelete();

                $table->foreign('analysis_element_id')
                    ->references('id')->on('analysis_elements')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('quotation_details')
            && ! Schema::hasColumn('quotation_details', 'is_package')) {
            Schema::table('quotation_details', function (Blueprint $table): void {
                $table->boolean('is_package')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pricelist_item_elements');

        if (Schema::hasTable('pricelist_items')
            && Schema::hasColumn('pricelist_items', 'is_package')) {
            Schema::table('pricelist_items', function (Blueprint $table): void {
                $table->dropColumn('is_package');
            });
        }

        if (Schema::hasTable('quotation_details')
            && Schema::hasColumn('quotation_details', 'is_package')) {
            Schema::table('quotation_details', function (Blueprint $table): void {
                $table->dropColumn('is_package');
            });
        }
    }
};
