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
        if (!Schema::hasTable('ticket_assignments')) {
            Schema::create('ticket_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id'); // complaints.id is unsigned
                $table->bigInteger('user_id'); // users.id is signed
                $table->date('assigned_date')->nullable();
                $table->bigInteger('assigned_by')->nullable(); // users.id is signed
                $table->text('notes')->nullable(); // Optional notes for this assignment
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('complaints')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
                
                // Ensure a developer can only be assigned once per ticket
                $table->unique(['ticket_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_assignments');
    }
};
