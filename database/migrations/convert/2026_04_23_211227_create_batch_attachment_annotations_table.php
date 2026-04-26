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
        Schema::create('batch_attachment_annotations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('batch_attachment_id');
            $table->integer('page_number');
            $table->string('annotation_type', 50);
            $table->text('content');
            $table->decimal('x_position');
            $table->decimal('y_position');
            $table->decimal('width')->nullable();
            $table->decimal('height')->nullable();
            $table->longText('style_data')->nullable();
            $table->timestamps();

            $table->index(['batch_attachment_id', 'page_number'], 'baa_attachment_page_idx');
            $table->foreign(['batch_attachment_id'], 'fk_batch_attachment_annotations_batch_attachment_id_15e491e9')->references(['id'])->on('batch_attachments')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_attachment_annotations');
    }
};
