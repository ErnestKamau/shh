<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('directorate_zone')) {
            return;
        }

        Schema::create('directorate_zone', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignUuid('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['directorate_id', 'zone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directorate_zone');
    }
};
