<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_permissions', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_id');
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->enum('permission_type', ['view', 'create', 'edit', 'review', 'approve']);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_id', 'sf_permissions_form_id_foreign')->references('id')->on('submission_forms')->onDelete('cascade');
            $table->foreign('role_id', 'sf_permissions_role_id_foreign')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id', 'sf_permissions_user_id_foreign')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['submission_form_id', 'permission_type'], 'sf_permissions_form_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_permissions');
    }
}