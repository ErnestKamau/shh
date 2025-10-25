<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificate_template_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_template_id');
            $table->unsignedBigInteger('parent_section_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_collapsible')->default(false);
            $table->json('styling_options')->nullable(); // background, borders, spacing
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('certificate_template_id', 'ct_sections_template_fk')->references('id')->on('certificate_templates')->onDelete('cascade');
            $table->foreign('parent_section_id', 'ct_sections_parent_fk')->references('id')->on('certificate_template_sections')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('certificate_template_id');
            $table->index('parent_section_id');
            $table->index('sort_order');
            $table->index(['certificate_template_id', 'sort_order'], 'ct_sections_template_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificate_template_sections');
    }
}
