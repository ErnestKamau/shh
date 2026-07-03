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
        Schema::create('submission_forms', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name')->index('idx_submission_forms_name_54fce041');
            $table->string('document_code')->nullable();
            $table->text('description')->nullable();
            $table->string('naming_convention_prefix', 50)->default('SF');
            $table->string('naming_convention_format', 100)->default('{prefix}/{year}/{sequence}');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('start_submission_number')->nullable()->default(1);
            $table->string('version', 10)->default('1.0');
            $table->date('issue_date')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_submission_forms_created_by_619b053b');
            $table->timestamps();
            $table->string('print_template_name')->nullable();

            $table->index(['is_published', 'is_active'], 'idx_submission_forms_is_published_is_active_633d298f');
            $table->unique(['name']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_forms');
    }
};
