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
        Schema::create('submission_form_permissions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_id');
            $table->uuid('role_id')->nullable()->index('sf_permissions_role_id_foreign');
            $table->uuid('user_id')->nullable()->index('sf_permissions_user_id_foreign');
            $table->enum('permission_type', ['view', 'create', 'edit', 'review', 'approve']);
            $table->timestamps();

            $table->index(['submission_form_id', 'permission_type'], 'sf_permissions_form_type_idx');
            $table->foreign(['submission_form_id'], 'sf_permissions_form_id_foreign')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'sf_permissions_role_id_foreign')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'sf_permissions_user_id_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_permissions');
    }
};
