<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_maintenance_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // annual | preventive | register
            $table->string('name');
            $table->date('program_date');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft | active
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance_programs');
    }
};
