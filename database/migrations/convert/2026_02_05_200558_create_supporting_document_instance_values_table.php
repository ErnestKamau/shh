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
        Schema::create('supporting_document_instance_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supporting_document_instance_id')->index('idx_supporting_document_instance_values_supporting_doc_bc95753a');
            $table->uuid('supporting_document_element_id')->index('idx_supporting_document_instance_values_supporting_doc_acc03c82');
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['supporting_document_instance_id', 'supporting_document_element_id'], 'supporting_doc_instance_element_unique');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_document_instance_values');
    }
};
