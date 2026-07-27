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
        Schema::create('sampling_schedule_sample_plan_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sampling_schedule_id')->index();
            $table->uuid('changed_by')->nullable()->index();
            $table->unsignedInteger('number_of_samples_before')->nullable();
            $table->unsignedInteger('number_of_samples_after')->nullable();
            $table->json('sample_details_before')->nullable();
            $table->json('sample_details_after')->nullable();
            $table->json('display_before')->nullable();
            $table->json('display_after')->nullable();
            $table->timestamps();

            $table->foreign('sampling_schedule_id')
                ->references('id')
                ->on('sampling_schedules')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampling_schedule_sample_plan_histories');
    }
};
