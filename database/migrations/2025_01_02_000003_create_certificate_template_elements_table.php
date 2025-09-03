<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplateElementsTable extends Migration
{
    public function up()
    {
        Schema::create('certificate_template_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_template_section_id');
            $table->enum('element_type', [
                'heading', 'paragraph', 'text', 'strong_text', 
                'image', 'data_field', 'table', 'spacer'
            ]);
            $table->text('content')->nullable();
            $table->json('properties')->nullable();
            $table->json('styling')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_conditional')->default(false);
            $table->json('conditional_logic')->nullable();
            $table->timestamps();

            $table->foreign('certificate_template_section_id', 'cert_elements_section_fk')->references('id')->on('certificate_template_sections')->onDelete('cascade');
            $table->index(['certificate_template_section_id', 'sort_order'], 'cert_elements_section_sort_idx');
            $table->index(['element_type'], 'cert_elements_type_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificate_template_elements');
    }
}