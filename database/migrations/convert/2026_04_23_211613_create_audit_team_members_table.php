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
        Schema::create('audit_team_members', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('audit_id');
            $table->uuid('user_id')->index('idx_audit_team_members_user_id_58ee1f66');
            $table->uuid('role_id')->nullable()->index('audit_team_members_role_id_foreign');
            $table->string('role_name')->nullable();
            $table->text('responsibilities')->nullable();
            $table->timestamps();

            $table->unique(['audit_id', 'user_id']);
            $table->foreign(['audit_id'], 'fk_audit_team_members_audit_id_e79981bc')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'fk_audit_team_members_role_id_8db54710')->references(['id'])->on('audit_team_roles')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_audit_team_members_user_id_9f975f5e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_team_members');
    }
};
