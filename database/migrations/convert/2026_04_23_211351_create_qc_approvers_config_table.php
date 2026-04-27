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
        Schema::create('qc_approvers_config', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('personnel_id');
            $table->uuid('created_by')->nullable()->index('idx_qc_approvers_config_created_by');
            $table->timestamps();
            $table->foreign(['created_by'], 'fk_qc_approvers_config_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_approvers_config');
    }
};
