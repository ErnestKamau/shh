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
        Schema::create('batch_attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('uploaded_by');
            $table->string('attachment_url');
            $table->uuid('batch_id')->index('idx_batch_attachments_batch_id');
            $table->string('title', 500)->nullable();
            $table->boolean('is_internal')->default(false);
            $table->integer('attachment_type')->nullable();
            $table->boolean('show_on_coa')->nullable()->default(false);
            $table->foreign(['batch_id'], 'fk_batch_attachments_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_attachments');
    }
};
