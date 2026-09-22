<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_import_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('scope', 40);
            $table->string('scope_key');
            $table->unsignedInteger('version');
            $table->string('status', 40)->default('applied');
            $table->string('file_path');
            $table->string('pre_apply_snapshot_path')->nullable();
            $table->json('change_summary')->nullable();
            $table->uuid('bulk_import_batch_id')->nullable();
            $table->uuid('sample_header_id')->nullable();
            $table->uuid('lab_section_worksheet_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['scope', 'scope_key', 'version']);
            $table->index(['scope', 'scope_key', 'status']);
            $table->index(['sample_header_id', 'created_at']);
            $table->index(['bulk_import_batch_id']);

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('bulk_import_batch_id')->references('id')->on('bulk_import_batches')->nullOnDelete();
            $table->foreign('sample_header_id')->references('id')->on('sample_headers')->nullOnDelete();
            $table->foreign('lab_section_worksheet_id')->references('id')->on('lab_section_worksheets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_import_versions');
    }
};
