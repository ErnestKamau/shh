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
        Schema::create('document_types', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code', 50)->index('idx_document_types_code_29323977');
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable()->index('idx_document_types_parent_id_f760397e');
            $table->string('numbering_format', 200)->default('{TYPE_CODE}-{YEAR}-{SEQ}');
            $table->integer('amendment_limit')->default(5);
            $table->boolean('is_active')->default(true)->index('idx_document_types_is_active_855e02fc');
            $table->integer('sort_order')->default(0)->index('idx_document_types_sort_order_826df4f9');
            $table->uuid('created_by')->nullable()->index('document_types_created_by_foreign');
            $table->timestamps();

            $table->unique(['code']);
            $table->foreign(['created_by'], 'fk_document_types_created_by_a1df2b1f')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'], 'fk_document_types_parent_id_f5ed952b')->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('cascade');
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
