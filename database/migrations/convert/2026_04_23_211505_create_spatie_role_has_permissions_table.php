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
        Schema::create('spatie_role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id')->index('spatie_role_has_permissions_role_id_foreign');
            $table->foreign(['permission_id'], 'fk_spatie_role_has_permissions_permission_id_c9388e9a')->references(['id'])->on('spatie_permissions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'fk_spatie_role_has_permissions_role_id_8803c21a')->references(['id'])->on('spatie_roles')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spatie_role_has_permissions');
    }
};
