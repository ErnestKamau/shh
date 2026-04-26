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
        Schema::create('verification_records', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('verification_number')->index('idx_verification_records_verification_number_e7828b7c');
            $table->uuid('corrective_action_id')->index('verification_records_corrective_action_id_foreign');
            $table->date('verification_date');
            $table->string('verified_by')->nullable();
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->uuid('effectiveness_result_id')->nullable()->index('verification_records_effectiveness_result_id_foreign');
            $table->string('effectiveness_result_name')->nullable();
            $table->text('verification_method')->nullable();
            $table->text('evidence_reviewed')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('requires_reopen')->default(false);
            $table->text('reopen_reason')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->uuid('closure_status_id')->nullable()->index('verification_records_closure_status_id_foreign');
            $table->string('closure_status_name')->nullable();
            $table->date('closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['verification_number']);
            $table->foreign(['closure_status_id'], 'fk_verification_records_closure_status_id_17665657')->references(['id'])->on('verification_closure_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['corrective_action_id'], 'fk_verification_records_corrective_action_id_1d120db9')->references(['id'])->on('corrective_actions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['effectiveness_result_id'], 'fk_verification_records_effectiveness_result_id_9eb622b5')->references(['id'])->on('verification_results')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_records');
    }
};
