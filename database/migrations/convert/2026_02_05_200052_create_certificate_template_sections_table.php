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
        if (Schema::hasTable('certificate_template_sections')) {
            return;
        }
        Schema::create('certificate_template_sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('certificate_template_id')->index('idx_certificate_template_sections_certificate_template_9387d201');
            $table->uuid('parent_section_id')->nullable()->index('idx_certificate_template_sections_parent_section_id_481e4638');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0)->index('idx_certificate_template_sections_sort_order_e8866446');
            $table->boolean('is_collapsible')->default(false);
            $table->longText('styling_options')->nullable();
            $table->json('layout_structure')->nullable();
            $table->json('css_config')->nullable();
            $table->json('data_config')->nullable();
            $table->timestamps();
            $table->integer('height')->default(400);

            $table->index(['certificate_template_id', 'sort_order'], 'idx_certificate_template_sections_certificate_template_5f01bbda');
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
