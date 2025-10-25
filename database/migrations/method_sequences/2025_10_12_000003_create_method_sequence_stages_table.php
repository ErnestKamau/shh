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
            $table->id();
            $table->unsignedBigInteger('method_sequence_version_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->json('equipment_ids')->nullable();
            $table->json('media_ids')->nullable();
            $table->json('control_ids')->nullable();
            $table->boolean('is_result_stage')->default(false);
            $table->boolean('fail_move_next_stage')->default(false);
            $table->decimal('duration', 10, 2)->nullable()->comment('Duration in hours');
            $table->decimal('move_to_next_stage_safe_duration', 10, 2)->nullable()->comment('Safe duration in hours');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['method_sequence_version_id', 'order']);
            $table->index(['is_result_stage']);
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

