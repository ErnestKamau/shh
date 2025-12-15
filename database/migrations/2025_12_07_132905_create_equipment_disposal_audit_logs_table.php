<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposal_audit_logs', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('disposal_id');
            $table->enum('action', [
                'created',
                'updated',
                'submitted',
                'approved',
                'rejected',
                'executed',
                'closed',
                'file_uploaded',
                'file_deleted',
                'signature_added'
            ]);
            $table->bigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('disposal_id')->references('id')->on('equipment_disposals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('disposal_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_audit_logs');
    }
}

