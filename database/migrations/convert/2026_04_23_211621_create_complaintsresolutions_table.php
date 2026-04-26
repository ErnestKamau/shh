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
        Schema::create('complaintsresolutions', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('car_no')->nullable();
            $table->text('action_taken');
            $table->longText('findings')->nullable();
            $table->longText('root_cause_analysis')->nullable();
            $table->longText('corrective_action_taken')->nullable();
            $table->longText('preventive_action')->nullable();
            $table->string('officer_responsible');
            $table->uuid('resolved_by_user_id')->nullable()->index('complaintsresolutions_resolved_by_user_id_foreign');
            $table->string('registered_by');
            $table->boolean('reject')->default(false);
            $table->boolean('approve')->default(false);
            $table->string('approved_by')->nullable();
            $table->uuid('complaint_id')->index('idx_complaintsresolutions_complaint_id_1e1eac04');
            $table->integer('workflow_stage');
            $table->string('edited_by')->nullable();
            $table->string('request_approve')->default('0');
            $table->text('cause_of_complaint')->nullable();
            $table->date('action_taken_date')->nullable();
            $table->unsignedBigInteger('action_taken_by')->nullable();
            $table->date('corrective_action_date')->nullable();
            $table->unsignedBigInteger('corrective_action_by')->nullable();
            $table->boolean('car_required')->default(false);
            $table->unsignedBigInteger('capa_approved_by')->nullable();
            $table->boolean('ncr_required')->default(false);
            $table->text('client_remarks')->nullable();
            $table->boolean('send_to_customer')->default(false);
            $table->timestamp('capa_approved_at')->nullable();
            $table->foreign(['resolved_by_user_id'], 'fk_complaintsresolutions_resolved_by_user_id_3d6a7e9b')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['complaint_id'], 'fk_complaintsresolutions_complaint_id_9de8d7d2')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaintsresolutions');
    }
};
