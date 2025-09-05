<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificate_template_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_template_id');
            $table->bigInteger('submission_form_instance_id');
            $table->bigInteger('generated_by');
            $table->string('file_path');
            $table->timestamp('generated_at');
            $table->enum('status', ['pending', 'generating', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('certificate_template_id', 'ct_reports_template_fk')->references('id')->on('certificate_templates')->onDelete('cascade');
            $table->foreign('submission_form_instance_id', 'ct_reports_instance_fk')->references('id')->on('submission_form_instances')->onDelete('cascade');
            $table->foreign('generated_by', 'ct_reports_generated_by_fk')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('certificate_template_id');
            $table->index('submission_form_instance_id');
            $table->index('generated_by');
            $table->index('generated_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificate_template_reports');
    }
}
