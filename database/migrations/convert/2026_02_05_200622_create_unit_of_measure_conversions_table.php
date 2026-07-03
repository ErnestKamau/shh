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
        Schema::create('unit_of_measure_conversions', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('uom1');
            $table->integer('uom2');
            $table->double('conversion');
            $table->timestamps();
            $table->integer('material_type_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->text('description')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_of_measure_conversions');
    }
};
