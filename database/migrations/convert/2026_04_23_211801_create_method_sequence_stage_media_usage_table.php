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
        Schema::create('method_sequence_stage_media_usage', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_stage_data_id')->index('idx_ms_media_stage');
            $table->unsignedBigInteger('media_id');
            $table->string('media_name');
            $table->decimal('volume', 10)->nullable();
            $table->string('unit')->nullable();
            $table->string('batch_number')->nullable();
            $table->timestamps();
            $table->foreign(['run_stage_data_id'], 'fk_ms_media_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_stage_media_usage');
    }
};
