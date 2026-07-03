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
        if (Schema::hasTable('equipment_operators')) {
            return;
        }
        Schema::create('equipment_operators', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_equipment_operators_user_id_35bf2089');
            $table->uuid('equipment_id')->index('idx_equipment_operators_equipment_id_4afa041f');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_operators');
    }
};
