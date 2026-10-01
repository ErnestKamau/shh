<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-sample Test Report PDFs, each reachable through a public QR token.
     */
    public function up(): void
    {
        Schema::create('test_report_documents', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();
            $table->string('batch_id');
            $table->string('sample_detail_id');
            $table->unsignedSmallInteger('revision_no');
            $table->string('language', 10)->default('en');
            $table->string('report_number')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_official')->default(true);
            $table->uuid('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['batch_id', 'sample_detail_id', 'revision_no', 'language'],
                'test_report_documents_sample_revision_lang_unique'
            );
            $table->index('batch_id');
            $table->index('sample_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_report_documents');
    }
};
