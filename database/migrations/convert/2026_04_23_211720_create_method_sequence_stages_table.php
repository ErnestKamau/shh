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
        Schema::create('method_sequence_stages', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('method_sequence_version_id')->index('idx_method_sequence_stages_method_sequence_version_id_54e17b4b');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->longText('equipment_ids')->nullable();
            $table->longText('media_ids')->nullable();
            $table->longText('control_ids')->nullable();
            $table->boolean('is_result_stage')->default(false)->index('idx_method_sequence_stages_is_result_stage_92fa455f');
            $table->boolean('fail_move_next_stage')->default(false);
            $table->boolean('is_end_stage')->default(false);
            $table->boolean('is_end_stage_if_pass')->default(false);
            $table->decimal('duration', 10)->nullable()->comment('Duration in hours');
            $table->decimal('move_to_next_stage_safe_duration', 10)->nullable()->comment('Safe duration in hours');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['method_sequence_version_id', 'order'], 'idx_method_sequence_stages_method_sequence_version_id_c9b5cab0');
            $table->foreign(['method_sequence_version_id'], 'fk_method_sequence_stages_method_sequence_version_id_556e02e9')->references(['id'])->on('method_sequence_versions')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_stages');
    }
};
