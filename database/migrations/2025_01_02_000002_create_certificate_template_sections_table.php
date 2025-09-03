<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplateSectionsTable extends Migration
{
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
            $table->json('styling_options')->nullable();
            $table->timestamps();

            $table->foreign('certificate_template_id')->references('id')->on('certificate_templates')->onDelete('cascade');
            $table->foreign('parent_section_id')->references('id')->on('certificate_template_sections')->onDelete('cascade');
            $table->index(['certificate_template_id', 'sort_order'], 'cert_sections_template_sort_idx');
            $table->index(['parent_section_id'], 'cert_sections_parent_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificate_template_sections');
    }
}