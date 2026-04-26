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
        Schema::create('certificate_template_sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('certificate_template_id')->index('idx_certificate_template_sections_certificate_template_901c76d3');
            $table->uuid('parent_section_id')->nullable()->index('idx_certificate_template_sections_parent_section_id_5c367b0b');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0)->index('idx_certificate_template_sections_sort_order_9293407f');
            $table->boolean('is_collapsible')->default(false);
            $table->longText('styling_options')->nullable();
            $table->json('layout_structure')->nullable();
            $table->json('css_config')->nullable();
            $table->json('data_config')->nullable();
            $table->timestamps();
            $table->integer('height')->default(400);

            $table->index(['certificate_template_id', 'sort_order'], 'ct_sections_template_sort_idx');
            $table->foreign(['parent_section_id'], 'ct_sections_parent_fk')->references(['id'])->on('certificate_template_sections')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['certificate_template_id'], 'ct_sections_template_fk')->references(['id'])->on('certificate_templates')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_template_sections');
    }
};
