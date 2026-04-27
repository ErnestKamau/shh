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
        Schema::create('entity_attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->string('type');
            $table->string('file');
            $table->string('description');
            $table->string('model');
            $table->integer('model_id');
            $table->uuid('created_by')->nullable()->index('idx_entity_attachments_created_by');
            $table->timestamps();
            $table->string('mime', 100)->nullable();
            $table->string('size', 100)->nullable();
            $table->foreign(['created_by'], 'fk_entity_attachments_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_attachments');
    }
};
