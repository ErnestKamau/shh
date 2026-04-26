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
        Schema::create('submission_form_sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_id')->index('idx_submission_form_sections_submission_form_id_dc40f3fc');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('section_type')->default('regular');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['submission_form_id', 'sort_order'], 'idx_submission_form_sections_submission_form_id_sort_o_0f4fc438');
            $table->foreign(['submission_form_id'], 'fk_submission_form_sections_submission_form_id_0630182a')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_sections');
    }
};
