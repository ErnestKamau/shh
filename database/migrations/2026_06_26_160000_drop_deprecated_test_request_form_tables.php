<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — drops legacy TRF capture tables after SFI cutover.
 * See docs/deprecation/TRF_LAYER_MANIFEST.md
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sample_submission_requests', 'test_request_form_instance_id')) {
            Schema::table('sample_submission_requests', function (Blueprint $table): void {
                $table->dropForeign(['test_request_form_instance_id']);
                $table->dropColumn('test_request_form_instance_id');
            });
        }

        if (Schema::hasColumn('analysis_acceptance_forms', 'test_request_form_instance_id')) {
            Schema::table('analysis_acceptance_forms', function (Blueprint $table): void {
                $table->dropForeign(['test_request_form_instance_id']);
                $table->dropColumn('test_request_form_instance_id');
            });
        }

        if (Schema::hasColumn('submission_form_instances', 'test_request_form_instance_id')) {
            Schema::table('submission_form_instances', function (Blueprint $table): void {
                $table->dropForeign(['test_request_form_instance_id']);
                $table->dropColumn('test_request_form_instance_id');
            });
        }

        Schema::dropIfExists('test_request_form_instances');
        Schema::dropIfExists('test_request_forms');
    }

    public function down(): void
    {
        // Intentionally empty — legacy tables are not restored.
    }
};
