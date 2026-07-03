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
        if (Schema::hasTable('sample_interlab_log')) {
            return;
        }
        Schema::create('sample_interlab_log', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('sample_id');
            $table->integer('to_lab_section_id')->nullable();
            $table->integer('from_lab_section_id')->nullable();
            $table->string('quantity')->nullable();
            $table->integer('submited_by');
            $table->dateTime('date_submitted');
            $table->integer('received_by')->nullable();
            $table->dateTime('date_received')->nullable();
            $table->text('remarks')->nullable();
            $table->date('expected_date')->nullable();
            $table->boolean('status')->nullable()->default(false);
            $table->date('prelim_date')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_interlab_log');
    }
};
