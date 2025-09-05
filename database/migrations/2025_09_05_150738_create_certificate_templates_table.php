<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('version')->default('1.0');
            $table->json('page_settings')->nullable(); // size, orientation, margins
            $table->json('header_settings')->nullable(); // logo, header content
            $table->json('footer_settings')->nullable(); // footer content, page numbers
            $table->bigInteger('submission_form_id'); // Link to submission form
            $table->bigInteger('created_by');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('submission_form_id', 'ct_templates_submission_form_fk')->references('id')->on('submission_forms')->onDelete('cascade');
            $table->foreign('created_by', 'ct_templates_created_by_fk')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('submission_form_id');
            $table->index('is_published');
            $table->index('is_active');
            $table->index('created_by');
            $table->index(['is_published', 'is_active']);
            $table->index(['submission_form_id', 'is_published', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificate_templates');
    }
}
