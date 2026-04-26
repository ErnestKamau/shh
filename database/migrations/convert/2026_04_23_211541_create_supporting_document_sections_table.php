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
        Schema::create('supporting_document_sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supporting_document_template_id')->index('idx_supporting_document_sections_supporting_document_t_4b644b56');
            $table->string('title')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['supporting_document_template_id', 'sort_order'], 'sdoc_sections_tpl_sort_idx');
            $table->foreign(['supporting_document_template_id'], 'fk_supporting_document_sections_supporting_document_te_c118c3bd')->references(['id'])->on('supporting_document_templates')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_document_sections');
    }
};
