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
            $table->uuid('ticket_id')->index('idx_ticket_change_history_ticket_id_c097a7f6');
            $table->uuid('user_id')->nullable()->index('idx_ticket_change_history_user_id_3ff8792b');
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->enum('change_type', ['created', 'updated', 'assigned', 'escalated', 'status_changed'])->default('updated')->index('idx_ticket_change_history_updated_37b7e1be');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent()->index('idx_ticket_change_history_created_at_f491092d');
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
