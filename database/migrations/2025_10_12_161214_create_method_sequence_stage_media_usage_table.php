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
            $table->id();
            $table->unsignedBigInteger('run_stage_data_id');
            $table->unsignedBigInteger('media_id');
            $table->string('media_name');
            $table->decimal('volume', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();
            
            $table->foreign('run_stage_data_id', 'fk_ms_media_stage')
                  ->references('id')
                  ->on('method_sequence_run_stage_data')
                  ->onDelete('cascade');
            
            $table->index('run_stage_data_id', 'idx_ms_media_stage');
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
