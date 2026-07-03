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
        if (Schema::hasTable('method_reagents')) {
            return;
        }
        Schema::create('method_reagents', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_sub_category_id')->index('idx_method_reagents_inventory_sub_category_id_a8e8bb6e');
            $table->integer('method_id');
            $table->string('reporting_unit');
            $table->double('quantity');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_reagents');
    }
};
