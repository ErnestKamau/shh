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
        Schema::create('equipment_operators', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_equipment_operators_user_id_89aa96da');
            $table->uuid('equipment_id')->index('idx_equipment_operators_equipment_id_2ecabc3c');
            $table->timestamps();
            $table->foreign(['equipment_id'], 'fk_equipment_operators_equipment_id_2dbbbdb8')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_equipment_operators_user_id_2b6edac5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');


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
