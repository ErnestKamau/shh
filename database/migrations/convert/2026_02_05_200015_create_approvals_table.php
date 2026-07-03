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
        Schema::create('approvals', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->string('for');
            $table->string('stage');
            $table->uuid('role_id')->index('idx_approvals_role_id_768a89db');
            $table->integer('level');
            $table->timestamps();
            $table->uuid('inventory_location_id')->nullable()->index('idx_approvals_inventory_location_id_d7c90ac4');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
