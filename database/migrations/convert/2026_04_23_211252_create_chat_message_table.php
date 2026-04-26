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
        Schema::create('chat_message', function (Blueprint $table) {
            $table->uuid('id');
            $table->text('message');
            $table->integer('from_user_id')->nullable();
            $table->integer('to_user_id')->nullable();
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_chat_message_company_id_d7756029');
            $table->smallInteger('active')->default(1);
            $table->uuid('conversation_id')->index('idx_chat_message_conversation_id_bfbda43c');
            $table->boolean('is_new')->default(false);
            $table->foreign(['company_id'], 'fk_chat_message_company_id_b892717e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['conversation_id'], 'fk_chat_message_conversation_id_2f3ae54c')->references(['id'])->on('conversation')->onUpdate('no action')->onDelete('cascade');


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
