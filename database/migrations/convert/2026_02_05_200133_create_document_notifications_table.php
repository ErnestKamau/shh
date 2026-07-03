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
        if (Schema::hasTable('document_notifications')) {
            return;
        }
        Schema::create('document_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_document_notifications_user_id_dcc582af');
            $table->uuid('document_id')->nullable()->index('idx_document_notifications_document_id_992fe55e');
            $table->string('notification_type');
            $table->string('title');
            $table->text('message');
            $table->longText('metadata')->nullable();
            $table->boolean('is_read')->default(false)->index('idx_document_notifications_is_read_e244d1b7');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_document_notifications_created_at_d57d4f2f');
            $table->timestamp('updated_at')->nullable();
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
