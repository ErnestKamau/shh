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
        Schema::create('root_cause_analyses', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('non_conformance_id')->index('root_cause_analyses_non_conformance_id_foreign');
            $table->uuid('root_cause_method_id')->nullable()->index('root_cause_analyses_root_cause_method_id_foreign');
            $table->string('method_name')->nullable();
            $table->text('analysis_data')->nullable();
            $table->text('root_cause_description');
            $table->text('contributing_factors')->nullable();
            $table->text('evidence_supporting_rca')->nullable();
            $table->uuid('status_id')->nullable()->index('root_cause_analyses_status_id_foreign');
            $table->string('status_name')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('approval_comments')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_root_cause_analyses_created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['non_conformance_id'], 'fk_root_cause_analyses_non_conformance_id_e1a6c6d0')->references(['id'])->on('non_conformances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['root_cause_method_id'], 'fk_root_cause_analyses_root_cause_method_id_152078fc')->references(['id'])->on('root_cause_methods')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_root_cause_analyses_status_id_5ff24697')->references(['id'])->on('rca_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_root_cause_analyses_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
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
