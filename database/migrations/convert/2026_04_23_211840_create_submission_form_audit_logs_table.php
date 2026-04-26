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
        Schema::create('submission_form_audit_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id')->index('idx_submission_form_audit_logs_submission_form_instanc_dc55276c');
            $table->uuid('user_id')->index('idx_submission_form_audit_logs_user_id_2461c896');
            $table->string('action')->index('idx_submission_form_audit_logs_action_a6f2493a');
            $table->longText('field_changes')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_submission_form_audit_logs_created_at_a3e0dc09');
            $table->timestamp('updated_at')->nullable();
            $table->foreign(['submission_form_instance_id'], 'fk_submission_form_audit_logs_submission_form_instance_6021b7dc')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_submission_form_audit_logs_user_id_36d8a7c1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_audit_logs');
    }
};
