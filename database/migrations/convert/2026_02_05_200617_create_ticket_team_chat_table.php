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
        Schema::create('ticket_team_chat', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ticket_id')->index('idx_ticket_team_chat_ticket_id_da6d52e8');
            $table->uuid('user_id')->index('idx_ticket_team_chat_user_id_21527df6');
            $table->text('message');
            $table->longText('mentions')->nullable();
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
        Schema::dropIfExists('ticket_team_chat');
    }
};
