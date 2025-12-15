<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposal_approvals', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('disposal_id');
            $table->integer('step');
            $table->bigInteger('approver_id');
            $table->enum('decision', ['approve', 'reject'])->nullable();
            $table->text('remarks')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('disposal_id')->references('id')->on('equipment_disposals')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('disposal_id');
            $table->index('step');
            $table->index('approver_id');
            $table->index('decision');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_approvals');
    }
}

