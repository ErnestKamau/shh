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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('role_id')->index('idx_user_roles_role_id_5852e650');
            $table->uuid('user_id')->index('idx_user_roles_user_id_e57d22b6');
            $table->timestamps();
            $table->foreign(['role_id'], 'fk_user_roles_role_id_6896079e')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_user_roles_user_id_2aa2451a')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
