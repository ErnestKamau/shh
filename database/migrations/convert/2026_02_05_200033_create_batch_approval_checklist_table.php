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
        if (Schema::hasTable('batch_approval_checklist')) {
            return;
        }
        Schema::create('batch_approval_checklist', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('approval_id')->index('idx_batch_approval_checklist_approval_id_7821a4c8');
            $table->integer('checklist_id');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_approval_checklist');
    }
};
