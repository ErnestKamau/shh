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
        Schema::create('ser_worksheet_steps', function (Blueprint $table) {
            $table->id();
            $table->string('step');
            $table->boolean('is_active')->default(true);
            $table->foreignId('default_equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('default_analyst_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('default_measurand_ids')->nullable();
            $table->timestamps();
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
