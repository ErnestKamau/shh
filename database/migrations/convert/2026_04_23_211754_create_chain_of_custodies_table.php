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
        Schema::create('chain_of_custodies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('workflow_stage');
            $table->integer('tracking_stage_id');
            $table->integer('moved_in_by');
            $table->integer('moved_out_by')->nullable();
            $table->dateTime('moved_out_date')->nullable();
            $table->uuid('sample_header_id')->index('idx_chain_of_custodies_sample_header_id_1f0cd2ab');
            $table->string('comments', 1024)->nullable();
            $table->timestamps();
            $table->foreign(['sample_header_id'], 'fk_chain_of_custodies_sample_header_id_addc66de')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');

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
