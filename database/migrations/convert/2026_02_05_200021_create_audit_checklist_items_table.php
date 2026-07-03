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
        if (Schema::hasTable('audit_checklist_items')) {
            return;
        }
        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('audit_checklist_id')->index('idx_audit_checklist_items_audit_checklist_id_7ab38f1b');
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
