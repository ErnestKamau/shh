<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplateElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificate_template_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_template_section_id');
            $table->enum('element_type', [
                'heading', 'paragraph', 'text', 'strong_text', 
                'image', 'data_field', 'table', 'spacer'
            ]);
            $table->text('content')->nullable(); // static content or rich text
            $table->json('properties')->nullable(); // element-specific properties
            $table->json('styling')->nullable(); // CSS-like styling options
            $table->integer('sort_order')->default(0);
            $table->boolean('is_conditional')->default(false);
            $table->json('conditional_logic')->nullable(); // show/hide conditions
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('certificate_template_section_id', 'ct_elements_section_fk')->references('id')->on('certificate_template_sections')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('certificate_template_section_id', 'cte_section_id_idx');
            $table->index('element_type', 'cte_element_type_idx');
            $table->index('sort_order', 'cte_sort_order_idx');
            $table->index(['certificate_template_section_id', 'sort_order'], 'cte_section_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificate_template_elements');
    }
}
