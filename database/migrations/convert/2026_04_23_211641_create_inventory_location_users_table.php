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
            $table->uuid('user_id')->index('idx_inventory_location_users_user_id_0255e496');
            $table->uuid('inventory_location_id')->index('idx_inventory_location_users_inventory_location_id_f1d653e3');
            $table->timestamps();
            $table->foreign(['inventory_location_id'], 'fk_inventory_location_users_inventory_location_id_ef0dcd15')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_inventory_location_users_user_id_b839635a')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');


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
