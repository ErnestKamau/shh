<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_header_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stage_header_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('stage_header_id')->references('id')->on('stage_headers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('stage_header_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_header_runs');
    }
};
