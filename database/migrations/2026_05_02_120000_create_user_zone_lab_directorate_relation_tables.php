<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('user_zone_relation')) {
            Schema::create('user_zone_relation', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('zone_id')->constrained('zones')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'zone_id']);
            });
        }

        if (!Schema::hasTable('user_directorate_relation')) {
            Schema::create('user_directorate_relation', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('directorate_id')->constrained('directorates')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'directorate_id']);
            });
        }

        if (!Schema::hasTable('user_lab_relation')) {
            Schema::create('user_lab_relation', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('lab_id')->constrained('labs')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'lab_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lab_relation');
        Schema::dropIfExists('user_directorate_relation');
        Schema::dropIfExists('user_zone_relation');
    }
};
