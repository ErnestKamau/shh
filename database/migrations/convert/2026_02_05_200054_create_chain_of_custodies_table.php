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
        if (Schema::hasTable('chain_of_custodies')) {
            return;
        }
        Schema::create('chain_of_custodies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('workflow_stage');
            $table->integer('tracking_stage_id');
            $table->integer('moved_in_by');
            $table->integer('moved_out_by')->nullable();
            $table->dateTime('moved_out_date')->nullable();
            $table->uuid('sample_header_id')->index('idx_chain_of_custodies_sample_header_id_32485d79');
            $table->string('comments', 1024)->nullable();
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chain_of_custodies');
    }
};
