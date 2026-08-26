<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lab_section_worksheet_sequences')) {
            Schema::create('lab_section_worksheet_sequences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('lab_section_id');
                $table->unsignedSmallInteger('calendar_year');
                $table->unsignedInteger('last_sequence')->default(0);
                $table->timestamps();

                $table->unique(['lab_section_id', 'calendar_year'], 'lab_section_worksheet_seq_unique');
                $table->index('lab_section_id');
            });
        }

        if (! Schema::hasTable('lab_section_worksheets')) {
            Schema::create('lab_section_worksheets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('worksheet_number', 64)->unique();
                $table->uuid('lab_section_id')->index();
                $table->unsignedSmallInteger('calendar_year');
                $table->unsignedInteger('sequence');
                $table->string('context', 32)->default('integrity');
                $table->uuid('submission_form_instance_id')->nullable()->index();
                $table->uuid('sample_header_id')->nullable()->index();
                $table->uuid('generated_by')->nullable()->index();
                $table->json('assigned_analyst_ids')->nullable();
                $table->json('test_snapshot')->nullable();
                $table->string('pdf_path', 512)->nullable();
                $table->string('excel_path', 512)->nullable();
                $table->timestamp('issued_at')->nullable();
                $table->timestamp('downloaded_at')->nullable();
                $table->timestamp('imported_at')->nullable();
                $table->string('status', 32)->default('issued');
                $table->timestamps();

                $table->index(['lab_section_id', 'calendar_year']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_section_worksheets');
        Schema::dropIfExists('lab_section_worksheet_sequences');
    }
};
