<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_shelf_life_conditions')) {
            Schema::create('sample_shelf_life_conditions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('sample_detail_id')->unique();
                $table->string('study_type')->nullable();
                $table->string('accelerated_temperature')->nullable();
                $table->unsignedInteger('study_duration_value')->nullable();
                $table->string('study_duration_unit')->default('weeks');
                $table->string('relative_humidity')->nullable();
                $table->string('evaluation_type')->nullable();
                $table->string('sampling_frequency')->nullable();
                $table->string('declared_shelf_life')->nullable();
                $table->string('storage_condition')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('sample_detail_id')
                    ->references('id')
                    ->on('sample_details')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_shelf_life_conditions');
    }
};
