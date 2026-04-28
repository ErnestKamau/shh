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
            $table->uuid('ticket_id')->index('idx_ticket_chat_ticket_id_f5246447');
            $table->uuid('user_id')->nullable()->index('idx_ticket_chat_user_id_9e241422');
            $table->text('message');
            $table->timestamp('read_at')->nullable()->index('idx_ticket_chat_read_at_6e82dff9');
            $table->softDeletes();
            $table->timestamps();
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
