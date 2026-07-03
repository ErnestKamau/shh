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
        if (Schema::hasTable('user_roles')) {
            return;
        }
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('role_id')->index('idx_user_roles_role_id_d1697941');
            $table->uuid('user_id')->index('idx_user_roles_user_id_3dabe24d');
            $table->timestamps();

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
