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
            $table->id();
            $table->unsignedBigInteger('ticket_id'); // complaint_id (references complaints.id which is unsigned)
            $table->bigInteger('user_id'); // references users.id which is signed bigint
            $table->text('message');
            $table->json('mentions')->nullable(); // Array of user_ids mentioned with @
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('complaints')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('ticket_id');
            $table->index('user_id');
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
