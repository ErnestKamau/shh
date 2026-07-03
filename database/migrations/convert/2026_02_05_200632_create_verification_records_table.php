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
            $table->string('verification_number')->index('idx_verification_records_verification_number_3777ee42');
            $table->uuid('corrective_action_id')->index('idx_verification_records_corrective_action_id_55dc996c');
            $table->date('verification_date');
            $table->string('verified_by')->nullable();
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->uuid('effectiveness_result_id')->nullable()->index('idx_verification_records_effectiveness_result_id_16bb93f4');
            $table->string('effectiveness_result_name')->nullable();
            $table->text('verification_method')->nullable();
            $table->text('evidence_reviewed')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('requires_reopen')->default(false);
            $table->text('reopen_reason')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->uuid('closure_status_id')->nullable()->index('idx_verification_records_closure_status_id_996557e1');
            $table->string('closure_status_name')->nullable();
            $table->date('closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_verification_records_created_by_f2b5f504');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['verification_number']);
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
