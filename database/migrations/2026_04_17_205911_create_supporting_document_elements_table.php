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
        Schema::create('supporting_document_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supporting_document_section_id');

            $table->string('element_type', 50);
            $table->string('label')->nullable();
            $table->string('name', 150)->nullable();
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable();
            $table->json('conditional_logic')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['supporting_document_section_id', 'sort_order'], 'sdoc_elements_section_sort_idx');
            $table->index(['supporting_document_section_id', 'name'], 'sdoc_elements_section_name_idx');
            $table->foreign('supporting_document_section_id', 'sdoc_elements_section_fk')
                ->references('id')
                ->on('supporting_document_sections')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_document_elements');
    }
};
