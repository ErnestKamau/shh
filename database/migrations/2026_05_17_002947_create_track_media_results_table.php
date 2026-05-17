<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_media_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('track_id');
            $table->uuid('media_id');
            $table->string('result');
            $table->uuid('analyst_id')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->foreign('track_id')->references('id')->on('sample_captured_test_stages_track')->cascadeOnDelete();
            $table->foreign('media_id')->references('id')->on('lab_sub_category')->cascadeOnDelete();
            $table->foreign('analyst_id')->references('id')->on('users')->nullOnDelete();

            $table->index('track_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_media_results');
    }
};
