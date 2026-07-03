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
        if (Schema::hasTable('document_types')) {
            return;
        }
        Schema::create('document_types', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code', 50)->index('idx_document_types_code_848e9ef2');
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable()->index('idx_document_types_parent_id_ae847cf6');
            $table->string('numbering_format', 200)->default('{TYPE_CODE}-{YEAR}-{SEQ}');
            $table->integer('amendment_limit')->default(5);
            $table->boolean('is_active')->default(true)->index('idx_document_types_is_active_d66f20f1');
            $table->integer('sort_order')->default(0)->index('idx_document_types_sort_order_58ab0442');
            $table->uuid('created_by')->nullable()->index('idx_document_types_created_by_e10c5465');
            $table->timestamps();

            $table->unique(['code']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
