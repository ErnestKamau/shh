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
        if (Schema::hasTable('inventory_item_notes')) {
            return;
        }
        Schema::create('inventory_item_notes', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_item_id')->index('idx_inventory_item_notes_inventory_item_id_6cd32d92');
            $table->string('comments');
            $table->string('document', 512)->nullable();
            $table->timestamps();
            $table->string('title')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_item_notes');
    }
};
