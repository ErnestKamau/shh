<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('store_to_cost_centers')) {
            return;
        }

        Schema::create('store_to_cost_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->index();
            $table->string('cost_center', 200)->index();
            $table->timestamps();

            $table->unique(['store_id', 'cost_center'], 'store_to_cost_centers_store_cc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_to_cost_centers');
    }
};
