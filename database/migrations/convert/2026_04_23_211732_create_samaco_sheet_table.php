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
        Schema::create('samaco_sheet', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('equipment_id')->index('idx_samaco_sheet_equipment_id_0efc246b');
            $table->integer('year');
            $table->integer('week');
            $table->boolean('complete')->default(false);
            $table->foreign(['equipment_id'], 'fk_samaco_sheet_equipment_id_21279139')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samaco_sheet');
    }
};
