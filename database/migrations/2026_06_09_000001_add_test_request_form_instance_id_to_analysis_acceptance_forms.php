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
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('analysis_acceptance_forms', 'test_request_form_instance_id')) {
                $table->uuid('test_request_form_instance_id')->nullable()->after('submission_form_instance_id');
                $table->foreign('test_request_form_instance_id')->references('id')->on('test_request_form_instances')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            if (Schema::hasColumn('analysis_acceptance_forms', 'test_request_form_instance_id')) {
                $table->dropForeign(['test_request_form_instance_id']);
                $table->dropColumn('test_request_form_instance_id');
            }
        });
    }
};
