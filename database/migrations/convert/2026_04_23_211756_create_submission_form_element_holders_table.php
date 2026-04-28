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
        Schema::create('submission_form_element_holders', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_section_id')->index('idx_submission_form_element_holders_submission_form_se_45c0e696');
            $table->enum('holder_type', ['field', 'text']);
            $table->integer('max_elements')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['submission_form_section_id', 'sort_order'], 'idx_submission_form_element_holders_submission_form_se_6f2a533c');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_element_holders');
    }
};
