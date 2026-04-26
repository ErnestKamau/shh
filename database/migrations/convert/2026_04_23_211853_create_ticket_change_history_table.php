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
            $table->uuid('id');
            $table->uuid('ticket_id')->index('idx_ticket_change_history_ticket_id_9ac0bd0e');
            $table->uuid('user_id')->nullable()->index('idx_ticket_change_history_user_id_4bcb151a');
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->enum('change_type', ['created', 'updated', 'assigned', 'escalated', 'status_changed'])->default('updated')->index('idx_ticket_change_history_updated_1fea85e8');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent()->index('idx_ticket_change_history_created_at_92ca0061');
            $table->foreign(['ticket_id'], 'fk_ticket_change_history_ticket_id_34778629')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_change_history_user_id_6b8da7bf')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
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
