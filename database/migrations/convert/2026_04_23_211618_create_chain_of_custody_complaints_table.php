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
        Schema::create('chain_of_custody_complaints', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('complaint_id')->index('idx_chain_of_custody_complaints_complaint_id_976834f5');
            $table->string('action');
            $table->integer('action_taker_id');
            $table->string('comments')->nullable();
            $table->integer('workflow_stage');
            $table->dateTime('move_out_date')->nullable();
            $table->foreign(['complaint_id'], 'fk_chain_of_custody_complaints_complaint_id_781161b7')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chain_of_custody_complaints');
    }
};
