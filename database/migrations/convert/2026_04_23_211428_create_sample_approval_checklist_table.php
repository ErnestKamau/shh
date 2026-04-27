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
        Schema::create('sample_approval_checklist', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->text('checklist')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('active')->default(false);
            $table->dateTime('deleted_at')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_sample_approval_checklist_created_by');
            $table->string('workflow_stage', 100)->nullable();
            $table->foreign(['created_by'], 'fk_sample_approval_checklist_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_approval_checklist');
    }
};
