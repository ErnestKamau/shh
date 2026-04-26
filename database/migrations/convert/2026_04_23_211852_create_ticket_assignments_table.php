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
            $table->uuid('user_id')->index('ticket_assignments_user_id_foreign');
            $table->date('assigned_date')->nullable();
            $table->uuid('assigned_by')->nullable()->index('ticket_assignments_assigned_by_foreign');
            $table->text('notes')->nullable();
            $table->integer('tat_value');
            $table->string('tat_unit', 12);
            $table->timestamps();

            $table->unique(['ticket_id', 'user_id']);
            $table->foreign(['assigned_by'], 'fk_ticket_assignments_assigned_by_2ab2e2a6')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ticket_id'], 'fk_ticket_assignments_ticket_id_6360d46f')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_assignments_user_id_1c68edcb')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
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
