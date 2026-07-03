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
        if (Schema::hasTable('submission_form_permissions')) {
            return;
        }
        Schema::create('submission_form_permissions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_id');
            $table->uuid('role_id')->nullable()->index('idx_submission_form_permissions_role_id_993b02e2');
            $table->uuid('user_id')->nullable()->index('idx_submission_form_permissions_user_id_859feb49');
            $table->enum('permission_type', ['view', 'create', 'edit', 'review', 'approve']);
            $table->timestamps();

            $table->index(['submission_form_id', 'permission_type'], 'idx_submission_form_permissions_submission_form_id_per_e2b3588a');
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
