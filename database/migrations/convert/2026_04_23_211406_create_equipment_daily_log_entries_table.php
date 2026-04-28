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
        Schema::create('equipment_daily_log_entries', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('equipment_id');
            $table->uuid('company_id')->index('idx_equipment_daily_log_entries_company_id_0d26791e');
            $table->date('log_date');
            $table->unsignedTinyInteger('slot_number');
            $table->string('recorded_value')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'log_date'], 'idx_equipment_daily_log_entries_company_id_log_date_cf6a40b7');
            $table->unique(['equipment_id', 'log_date', 'slot_number'], 'unique_equipment_log_slot');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_daily_log_entries');
    }
};
