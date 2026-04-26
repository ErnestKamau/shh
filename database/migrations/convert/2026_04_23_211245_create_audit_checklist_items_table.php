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
        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('audit_checklist_id')->index('audit_checklist_items_audit_checklist_id_foreign');
            $table->string('item_number');
            $table->string('iso_clause')->nullable();
            $table->text('requirement');
            $table->text('guidance')->nullable();
            $table->text('evidence_required')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['audit_checklist_id'], 'fk_audit_checklist_items_audit_checklist_id_35b1b989')->references(['id'])->on('audit_checklists')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_items');
    }
};
