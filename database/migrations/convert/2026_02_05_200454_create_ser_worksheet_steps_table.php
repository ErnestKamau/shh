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
        if (Schema::hasTable('ser_worksheet_steps')) {
            return;
        }
        Schema::create('ser_worksheet_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('step');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('default_equipment_id')->nullable();
            $table->unsignedBigInteger('default_analyst_id')->nullable();
            $table->longText('default_measurand_ids')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ser_worksheet_steps');
    }
};
