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
            $table->uuid('approval_id')->index('idx_entity_approvals_approval_id_ca707ec1');
            $table->string('model');
            $table->integer('model_id');
            $table->uuid('user_id')->nullable()->index('idx_entity_approvals_user_id_2e935c28');
            $table->dateTime('approved_at')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
            $table->string('description', 1024)->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_entity_approvals_inventory_location_id_2f5945f7');

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
