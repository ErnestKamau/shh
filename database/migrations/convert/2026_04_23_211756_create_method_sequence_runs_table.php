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
        Schema::create('method_sequence_runs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_header_id')->index('idx_method_sequence_runs_sample_header_id_b7c236b1');
            $table->uuid('method_sequence_id')->index('idx_method_sequence_runs_method_sequence_id_e11a601e');
            $table->unsignedBigInteger('analyst_id')->nullable();
            $table->date('run_date')->nullable();
            $table->integer('run_number')->index('idx_ms_runs_number');
            $table->string('run_name');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('started_by_user_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sample_header_id', 'method_sequence_id'], 'idx_ms_runs_batch_seq');
            $table->foreign(['method_sequence_id'], 'fk_method_sequence_runs_method_sequence_id_29d7cc91')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_method_sequence_runs_sample_header_id_863a6058')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_runs');
    }
};
