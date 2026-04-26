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
        Schema::create('entity_approvals', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('approval_id')->index('idx_entity_approvals_approval_id_9b9703f5');
            $table->string('model');
            $table->integer('model_id');
            $table->uuid('user_id')->nullable()->index('idx_entity_approvals_user_id_2b6d6124');
            $table->dateTime('approved_at')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
            $table->string('description', 1024)->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_entity_approvals_inventory_location_id_361ba1b9');
            $table->foreign(['approval_id'], 'fk_entity_approvals_approval_id_cc2f5119')->references(['id'])->on('approvals')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_location_id'], 'fk_entity_approvals_inventory_location_id_ae45f704')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_entity_approvals_user_id_2217f2a2')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_approvals');
    }
};
