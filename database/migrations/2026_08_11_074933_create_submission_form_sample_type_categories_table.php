<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_form_sample_type_categories', function (Blueprint $table) {
            $table->uuid('submission_form_id');
            $table->unsignedInteger('sample_type_category_id');
            $table->timestamps();

            $table->unique(
                ['submission_form_id', 'sample_type_category_id'],
                'sf_stc_unique'
            );
            $table->index('submission_form_id', 'sf_stc_sf_id_index');
            $table->index('sample_type_category_id', 'sf_stc_stc_id_index');

            $table->foreign('submission_form_id', 'sf_stc_sf_id_foreign')
                ->references('id')
                ->on('submission_forms')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign('sample_type_category_id', 'sf_stc_stc_id_foreign')
                ->references('id')
                ->on('sample_type_categories')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_form_sample_type_categories');
    }
};
