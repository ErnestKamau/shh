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
        if (Schema::hasTable('equipment_usage')) {
            return;
        }
        Schema::create('equipment_usage', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('operator');
            $table->integer('sample_header');
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
            $table->uuid('equipment_id')->nullable()->index('idx_equipment_usage_equipment_id_26022925');

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
