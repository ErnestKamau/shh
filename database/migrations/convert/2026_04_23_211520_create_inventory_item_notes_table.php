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
        Schema::create('inventory_item_notes', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_item_id')->index('idx_inventory_item_notes_inventory_item_id_f7242ace');
            $table->string('comments');
            $table->string('document', 512)->nullable();
            $table->timestamps();
            $table->string('title')->nullable();
            $table->foreign(['inventory_item_id'], 'fk_inventory_item_notes_inventory_item_id_eee48252')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');

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
