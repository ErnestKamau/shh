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
            $table->id();
            $table->unsignedBigInteger('supporting_document_template_id');
            $table->string('title')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['supporting_document_template_id', 'sort_order'], 'sdoc_sections_tpl_sort_idx');
            $table->foreign('supporting_document_template_id', 'sdoc_sections_template_fk')
                ->references('id')
                ->on('supporting_document_templates')
                ->cascadeOnDelete();
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
