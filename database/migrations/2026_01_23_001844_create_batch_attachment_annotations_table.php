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
            $table->id();
            $table->unsignedBigInteger('batch_attachment_id');
            $table->integer('page_number');
            $table->string('annotation_type', 50); // 'text' or 'image'
            $table->text('content'); // Text content or image path/base64
            $table->decimal('x_position', 8, 2);
            $table->decimal('y_position', 8, 2);
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->json('style_data')->nullable(); // Font size, color, etc.
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('batch_attachment_id')
                  ->references('id')
                  ->on('batch_attachments')
                  ->onDelete('cascade');
            
            // Index for faster queries (custom short name to avoid MySQL 64-char limit)
            $table->index(['batch_attachment_id', 'page_number'], 'baa_attachment_page_idx');
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
