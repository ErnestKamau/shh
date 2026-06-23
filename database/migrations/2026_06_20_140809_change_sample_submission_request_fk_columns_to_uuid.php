<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->dropColumn(['sample_type_id', 'zone_id', 'matrix_id']);
        });

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->uuid('sample_type_id')->nullable()->index();
            $table->uuid('zone_id')->nullable()->index();
            $table->uuid('matrix_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->dropColumn(['sample_type_id', 'zone_id', 'matrix_id']);
        });

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->unsignedBigInteger('sample_type_id')->nullable()->index();
            $table->unsignedBigInteger('zone_id')->nullable()->index();
            $table->unsignedBigInteger('matrix_id')->nullable()->index();
        });
    }
};
