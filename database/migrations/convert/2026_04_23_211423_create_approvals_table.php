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
            $table->uuid('role_id')->index('role_id');
            $table->integer('level');
            $table->timestamps();
            $table->uuid('inventory_location_id')->nullable()->index('inventory_location_id');
            $table->foreign(['inventory_location_id'], 'fk_approvals_inventory_location_id_f6594aa9')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['role_id'], 'fk_approvals_role_id_a3ff9e9b')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');


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
