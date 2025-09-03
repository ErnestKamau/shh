<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTemplatePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('certificate_template_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_template_id');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('role_id')->nullable();
            $table->enum('permission_type', ['view', 'edit', 'publish', 'generate']);
            $table->timestamps();

            $table->foreign('certificate_template_id')->references('id')->on('certificate_templates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Note: role_id foreign key depends on your role system structure
            $table->index(['certificate_template_id', 'permission_type'], 'cert_perms_template_type_idx');
            $table->index(['user_id'], 'cert_perms_user_idx');
            $table->index(['role_id'], 'cert_perms_role_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificate_template_permissions');
    }
}