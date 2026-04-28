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
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('user_type')->nullable();
            $table->uuid('user_id')->nullable()->index('idx_audits_user_id_cdae85a5');
            $table->string('event');
            $table->string('auditable_type');
            $table->bigInteger('auditable_id');
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id'], 'idx_audits_auditable_type_auditable_id_d708ee5a');
            $table->index(['user_id', 'user_type'], 'idx_audits_user_id_user_type_ee1c5754');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
