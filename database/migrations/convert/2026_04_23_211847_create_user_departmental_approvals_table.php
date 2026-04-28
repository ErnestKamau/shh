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
            $table->uuid('role_id')->index('idx_user_departmental_approvals_role_id_9b7dd362');
            $table->uuid('user_id')->index('idx_user_departmental_approvals_user_id_6f0effa3');
            $table->integer('department_id');
            $table->timestamps();

            $table->index(['role_id', 'user_id', 'department_id'], 'idx_user_departmental_approvals_role_id_user_id_depart_f288e5ef');

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
