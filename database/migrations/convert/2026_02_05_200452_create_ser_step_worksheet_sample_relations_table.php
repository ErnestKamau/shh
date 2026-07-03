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
        Schema::create('ser_step_worksheet_sample_relations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ser_header_id')->index('idx_ser_step_worksheet_sample_relations_ser_header_id_b5e731fe');
            $table->uuid('ser_worksheet_step_id')->nullable()->index('idx_ser_step_worksheet_sample_relations_ser_worksheet_1765e071');
            $table->integer('step_number')->default(0);
            $table->unsignedInteger('measurand_id')->nullable();
            $table->uuid('equipment_id')->nullable()->index('idx_ser_step_worksheet_sample_relations_equipment_id_0866fc1c');
            $table->unsignedBigInteger('analyst_id')->nullable();
            $table->string('result_value')->nullable();
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ser_step_worksheet_sample_relations');
    }
};
