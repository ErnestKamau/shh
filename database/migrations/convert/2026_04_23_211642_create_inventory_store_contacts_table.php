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
        Schema::create('inventory_store_contacts', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_inventory_store_contacts_user_id_6b7c4942');
            $table->integer('store');
            $table->timestamps(6);
            $table->foreign(['user_id'], 'fk_inventory_store_contacts_user_id_fa496f66')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_store_contacts');
    }
};
