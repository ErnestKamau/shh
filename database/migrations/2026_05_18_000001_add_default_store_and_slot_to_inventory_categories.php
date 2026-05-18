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
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->uuid('default_store_id')->nullable();
            $table->uuid('default_slot_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->dropColumn(['default_store_id', 'default_slot_id']);
        });
    }
};
