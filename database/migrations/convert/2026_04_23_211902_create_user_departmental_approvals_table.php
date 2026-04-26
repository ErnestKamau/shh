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
        Schema::create('user_departmental_approvals', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('role_id')->index('idx_user_departmental_approvals_role_id_a1658819');
            $table->uuid('user_id')->index('idx_user_departmental_approvals_user_id_7f89fc92');
            $table->integer('department_id');
            $table->timestamps();

            $table->index(['role_id', 'user_id', 'department_id'], 'user_departmental_approvals_role_id_idx');
            $table->foreign(['role_id'], 'fk_user_departmental_approvals_role_id_a09303d7')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_user_departmental_approvals_user_id_3394ca6d')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_departmental_approvals');
    }
};
