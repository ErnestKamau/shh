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
        Schema::create('supporting_document_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('document_code', 100)->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_supporting_document_templates_company_id_b2527536');
            $table->uuid('created_by')->nullable()->index('idx_supporting_document_templates_created_by_94f166bf');
            $table->timestamps();

            $table->index(['is_published', 'is_active'], 'idx_supporting_document_templates_is_published_is_acti_f1203eac');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_document_templates');
    }
};
