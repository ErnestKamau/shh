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
            $table->id();
            $table->unsignedBigInteger('supporting_document_instance_id');
            $table->unsignedBigInteger('supporting_document_element_id');
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['supporting_document_instance_id', 'supporting_document_element_id'], 'supporting_doc_instance_element_unique');
            $table->foreign('supporting_document_instance_id', 'sdoc_values_instance_fk')
                ->references('id')
                ->on('supporting_document_instances')
                ->cascadeOnDelete();
            $table->foreign('supporting_document_element_id', 'sdoc_values_element_fk')
                ->references('id')
                ->on('supporting_document_elements')
                ->cascadeOnDelete();
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
