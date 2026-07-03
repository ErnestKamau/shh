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
        if (Schema::hasTable('test_stages')) {
            return;
        }
        Schema::create('test_stages', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('stage_header_id')->index('idx_test_stages_stage_header_id_8a0329dd');
            $table->integer('order');
            $table->string('stage_name');
            $table->integer('duration_hours');
            $table->integer('next_day_if_positive')->nullable();
            $table->integer('next_day_if_negative')->nullable();
            $table->longText('media_required')->nullable();
            $table->longText('equipment_required')->nullable();
            $table->longText('controls_required')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_result_stage')->default(false);
            $table->timestamps();

            $table->unique(['stage_header_id', 'order']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_stages');
    }
};
