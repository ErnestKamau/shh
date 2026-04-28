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
        Schema::create('inventory_location_users', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_inventory_location_users_user_id_3ac3d80d');
            $table->uuid('inventory_location_id')->index('idx_inventory_location_users_inventory_location_id_6b9b4258');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_location_users');
    }
};
