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
        Schema::create('ticket_assignments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ticket_id');
            $table->uuid('user_id')->index('idx_ticket_assignments_user_id_ecdee943');
            $table->date('assigned_date')->nullable();
            $table->uuid('assigned_by')->nullable()->index('idx_ticket_assignments_assigned_by_d32c4758');
            $table->text('notes')->nullable();
            $table->integer('tat_value');
            $table->string('tat_unit', 12);
            $table->timestamps();

            $table->unique(['ticket_id', 'user_id']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_assignments');
    }
};
