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
        if (Schema::hasTable('solution_consumption_metrics')) {
            return;
        }
        Schema::create('solution_consumption_metrics', function (Blueprint $table) {
            $table->uuid('id');
            $table->bigInteger('solution_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_consumption', 10);
            $table->decimal('average_daily_consumption', 10);
            $table->decimal('peak_consumption', 10);
            $table->decimal('lowest_consumption', 10);
            $table->integer('consumption_days');
            $table->decimal('consumption_trend', 5)->nullable();
            $table->unsignedBigInteger('uom_id');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solution_consumption_metrics');
    }
};
