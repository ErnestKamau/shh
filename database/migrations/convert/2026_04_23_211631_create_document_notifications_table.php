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
        Schema::create('document_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_document_notifications_user_id_07b07d0f');
            $table->uuid('document_id')->nullable()->index('idx_document_notifications_document_id_7d1e22f4');
            $table->string('notification_type');
            $table->string('title');
            $table->text('message');
            $table->longText('metadata')->nullable();
            $table->boolean('is_read')->default(false)->index('idx_document_notifications_is_read_af5eac26');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_document_notifications_created_at_6a1ceaf6');
            $table->timestamp('updated_at')->nullable();
            $table->foreign(['document_id'], 'fk_document_notifications_document_id_5687ddfe')->references(['id'])->on('documents')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_document_notifications_user_id_c868cf66')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_notifications');
    }
};
