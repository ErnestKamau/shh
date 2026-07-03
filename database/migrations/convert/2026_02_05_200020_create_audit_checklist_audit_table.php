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
        if (Schema::hasTable('audit_checklist_audit')) {
            return;
        }
        Schema::create('audit_checklist_audit', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('audit_id');
            $table->uuid('audit_checklist_id')->index('idx_audit_checklist_audit_audit_checklist_id_ed4c5a29');
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->unique(['audit_id', 'audit_checklist_id']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_audit');
    }
};
