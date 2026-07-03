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
        if (Schema::hasTable('procedure_worksheet_steps')) {
            return;
        }
        Schema::create('procedure_worksheet_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('idx_procedure_worksheet_steps_procedure_worksheet_id_47b0758b');
            $table->string('step');
            $table->unsignedInteger('order')->nullable()->default(0);
            $table->boolean('is_active')->default(true);
            $table->longText('default_equipment_id')->nullable();
            $table->longText('default_analyst_id')->nullable();
            $table->longText('default_measurand_ids')->nullable();
            $table->timestamps();
            $table->string('value_type', 20)->default('text');
            $table->text('default_value')->nullable();
            $table->longText('default_measurand_values')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_worksheet_steps');
    }
};
