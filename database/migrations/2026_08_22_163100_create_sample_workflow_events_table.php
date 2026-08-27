<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sample_workflow_events')) {
            return;
        }

        Schema::create('sample_workflow_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type', 191);
            $table->uuid('subject_id');
            $table->uuid('submission_form_instance_id')->nullable()->index();
            $table->uuid('sample_header_id')->nullable()->index();
            $table->string('workflow_stage', 191)->nullable()->index();
            $table->string('event_type', 64)->index();
            $table->string('who_name', 191)->nullable();
            $table->uuid('who_user_id')->nullable()->index();
            $table->string('what', 255);
            $table->string('how', 191)->nullable();
            $table->string('why', 512)->nullable();
            $table->string('where', 191)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_workflow_events');
    }
};
