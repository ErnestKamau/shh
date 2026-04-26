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
        Schema::create('equipment_usage', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('operator');
            $table->integer('sample_header');
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
            $table->uuid('equipment_id')->nullable()->index('idx_equipment_usage_equipment_id_47f9418e');
            $table->foreign(['equipment_id'], 'fk_equipment_usage_equipment_id_a560c5d6')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_usage');
    }
};
