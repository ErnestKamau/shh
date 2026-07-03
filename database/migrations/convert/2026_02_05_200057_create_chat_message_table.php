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
        if (Schema::hasTable('chat_message')) {
            return;
        }
        Schema::create('chat_message', function (Blueprint $table) {
            $table->uuid('id');
            $table->text('message');
            $table->integer('from_user_id')->nullable();
            $table->integer('to_user_id')->nullable();
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_chat_message_company_id_789c9807');
            $table->smallInteger('active')->default(1);
            $table->uuid('conversation_id')->index('idx_chat_message_conversation_id_3c526e4f');
            $table->boolean('is_new')->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_message');
    }
};
