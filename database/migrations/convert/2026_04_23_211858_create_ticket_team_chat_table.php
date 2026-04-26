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
            $table->uuid('ticket_id')->index('idx_ticket_team_chat_ticket_id_2bb23c12');
            $table->uuid('user_id')->index('idx_ticket_team_chat_user_id_66465631');
            $table->text('message');
            $table->longText('mentions')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign(['ticket_id'], 'fk_ticket_team_chat_ticket_id_a741762d')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_team_chat_user_id_5d0780f7')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
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
