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
        Schema::create('equipment_replacement_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->integer('start_year');
            $table->integer('end_year');
            $table->timestamps();
        });

        Schema::create('equipment_replacement_plan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_replacement_plan_id');
            $table->uuid('equipment_id')->nullable();
            $table->string('equipment_name');
            $table->string('scheduled_year');
            $table->string('location')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('equipment_replacement_plan_id', 'eq_repl_plan_id_foreign')
                ->references('id')
                ->on('equipment_replacement_plans')
                ->onDelete('cascade');

            $table->foreign('equipment_id', 'eq_repl_item_equipment_id_foreign')
                ->references('id')
                ->on('equipment')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_replacement_plan_items');
        Schema::dropIfExists('equipment_replacement_plans');
    }
};
