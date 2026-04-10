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
        Schema::create('ticket_change_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id'); // complaint_id (references complaints.id which is unsigned)
            $table->bigInteger('user_id'); // references users.id which is signed bigint
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->enum('change_type', ['created', 'updated', 'assigned', 'escalated', 'status_changed'])
                ->default('updated');
            $table->timestamp('created_at');
            
            $table->foreign('ticket_id')->references('id')->on('complaints')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('change_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_change_history');
    }
};
