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
        Schema::create('ticket_chat', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ticket_id')->index('idx_ticket_chat_ticket_id_96029bec');
            $table->uuid('user_id')->nullable()->index('idx_ticket_chat_user_id_967288a5');
            $table->text('message');
            $table->timestamp('read_at')->nullable()->index('idx_ticket_chat_read_at_f1bd32f8');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign(['ticket_id'], 'fk_ticket_chat_ticket_id_210db4dd')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_chat_user_id_b5e9069c')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_chat');
    }
};
