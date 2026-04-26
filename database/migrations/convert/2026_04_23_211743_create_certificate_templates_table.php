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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false)->index('ct_is_published_idx');
            $table->boolean('is_active')->default(true)->index('ct_is_active_idx');
            $table->string('version')->default('1.0');
            $table->longText('page_settings')->nullable();
            $table->longText('header_settings')->nullable();
            $table->longText('footer_settings')->nullable();
            $table->uuid('submission_form_id')->index('ct_submission_form_id_idx');
            $table->uuid('created_by')->index('ct_created_by_idx');
            $table->timestamps();

            $table->index(['submission_form_id', 'is_published', 'is_active'], 'ct_form_pub_active_idx');
            $table->index(['is_published', 'is_active'], 'ct_published_active_idx');
            $table->foreign(['created_by'], 'ct_templates_created_by_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['submission_form_id'], 'ct_templates_submission_form_fk')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
