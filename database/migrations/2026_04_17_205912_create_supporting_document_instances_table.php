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
        Schema::create('supporting_document_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supporting_document_template_id');
            $table->unsignedInteger('template_version');

            $table->unsignedBigInteger('sample_header_id')->index();
            $table->string('status', 30)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['sample_header_id', 'status'], 'sdoc_instances_sample_status_idx');
            $table->foreign('supporting_document_template_id', 'sdoc_instances_template_fk')
                ->references('id')
                ->on('supporting_document_templates')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_document_instances');
    }
};
