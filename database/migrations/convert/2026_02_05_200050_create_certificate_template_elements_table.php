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
        if (Schema::hasTable('certificate_template_elements')) {
            return;
        }
        Schema::create('certificate_template_elements', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('certificate_template_section_id')->index('idx_certificate_template_elements_certificate_template_f9c41ee2');
            $table->uuid('certificate_template_element_holder_id')->nullable()->index('idx_certificate_template_elements_certificate_template_579633ac');
            $table->enum('element_type', ['heading', 'paragraph', 'text', 'strong_text', 'image', 'data_field', 'table', 'spacer', 'button', 'divider', 'list', 'icon', 'custom_html', 'input_text', 'input_email', 'input_number', 'input_date', 'textarea', 'select'])->nullable()->index('idx_certificate_template_elements_element_type_4cdf34db');
            $table->text('content')->nullable();
            $table->longText('properties')->nullable();
            $table->longText('styling')->nullable();
            $table->json('css_config')->nullable();
            $table->json('data_config')->nullable();
            $table->string('parent_cell_id')->nullable();
            $table->integer('sort_order')->default(0)->index('idx_certificate_template_elements_sort_order_9562f631');
            $table->decimal('position_x', 10)->nullable();
            $table->decimal('position_y', 10)->nullable();
            $table->decimal('width', 10)->nullable();
            $table->decimal('height', 10)->nullable();
            $table->decimal('position_x_percent', 10, 4)->nullable();
            $table->decimal('position_y_percent', 10, 4)->nullable();
            $table->decimal('width_percent', 10, 4)->nullable();
            $table->decimal('height_percent', 10, 4)->nullable();
            $table->integer('z_index')->default(1);
            $table->boolean('is_conditional')->default(false);
            $table->longText('conditional_logic')->nullable();
            $table->timestamps();

            $table->index(['certificate_template_section_id', 'sort_order'], 'idx_certificate_template_elements_certificate_template_17fc6e15');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_template_elements');
    }
};
