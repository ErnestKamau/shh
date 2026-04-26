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
        Schema::create('audit_checklist_audit', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('audit_id');
            $table->uuid('audit_checklist_id')->index('audit_checklist_audit_audit_checklist_id_foreign');
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->unique(['audit_id', 'audit_checklist_id']);
            $table->foreign(['audit_checklist_id'], 'fk_audit_checklist_audit_audit_checklist_id_ca5c2996')->references(['id'])->on('audit_checklists')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['audit_id'], 'fk_audit_checklist_audit_audit_id_5ad9b77a')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('cascade');
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
