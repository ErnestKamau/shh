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
        if (Schema::hasTable('root_cause_analyses')) {
            return;
        }
        Schema::create('root_cause_analyses', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('non_conformance_id')->index('idx_root_cause_analyses_non_conformance_id_f0277637');
            $table->uuid('root_cause_method_id')->nullable()->index('idx_root_cause_analyses_root_cause_method_id_3aaab1ac');
            $table->string('method_name')->nullable();
            $table->text('analysis_data')->nullable();
            $table->text('root_cause_description');
            $table->text('contributing_factors')->nullable();
            $table->text('evidence_supporting_rca')->nullable();
            $table->uuid('status_id')->nullable()->index('idx_root_cause_analyses_status_id_e4f2de92');
            $table->string('status_name')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('approval_comments')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_root_cause_analyses_created_by_a11799b5');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('root_cause_analyses');
    }
};
