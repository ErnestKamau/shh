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
            $table->uuid('user_id')->index('idx_audit_team_members_user_id_9346a5ac');
            $table->uuid('role_id')->nullable()->index('idx_audit_team_members_role_id_206011e5');
            $table->string('role_name')->nullable();
            $table->text('responsibilities')->nullable();
            $table->timestamps();

            $table->unique(['audit_id', 'user_id']);

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
