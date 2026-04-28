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
        Schema::create('sample_staging_rejection_log', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('sample_staging_id');
            $table->string('action');
            $table->integer('actioned_by');
            $table->text('comment')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_staging_rejection_log');
    }
};
