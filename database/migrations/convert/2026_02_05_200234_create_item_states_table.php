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
        Schema::create('item_states', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->integer('uom');
            $table->integer('material_type_id');
            $table->timestamps(6);
            $table->string('location_id', 100)->nullable();
            $table->integer('is_default')->default(0);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_states');
    }
};
