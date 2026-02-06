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
        Schema::create('procedure_worksheets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('procedure_worksheet_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_worksheet_id')->constrained('procedure_worksheets')->onDelete('cascade');
            $table->string('step');
            $table->boolean('is_active')->default(true);
            $table->foreignId('default_equipment_id')->nullable()->constrained('equipment');
            $table->foreignId('default_analyst_id')->nullable()->constrained('users');
            $table->json('default_measurand_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_worksheet_steps');
        Schema::dropIfExists('procedure_worksheets');
    }
};
