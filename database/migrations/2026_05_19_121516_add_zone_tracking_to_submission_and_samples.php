<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (! Schema::hasColumn('submission_form_instances', 'zone_id')) {
                $table->uuid('zone_id')->nullable()->after('crm_customer_id');
                $table->index('zone_id', 'idx_submission_form_instances_zone_id');
            }
            if (! Schema::hasColumn('submission_form_instances', 'processing_zone_id')) {
                $table->uuid('processing_zone_id')->nullable()->after('zone_id');
                $table->index('processing_zone_id', 'idx_submission_form_instances_processing_zone_id');
            }
        });

        Schema::table('sample_headers', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_headers', 'zone_id')) {
                $table->uuid('zone_id')->nullable()->after('crm_customer_id');
                $table->index('zone_id', 'idx_sample_headers_zone_id');
            }
            if (! Schema::hasColumn('sample_headers', 'processing_zone_id')) {
                $table->uuid('processing_zone_id')->nullable()->after('zone_id');
                $table->index('processing_zone_id', 'idx_sample_headers_processing_zone_id');
            }
            if (! Schema::hasColumn('sample_headers', 'reporting_zone_id')) {
                $table->uuid('reporting_zone_id')->nullable()->after('processing_zone_id');
                $table->index('reporting_zone_id', 'idx_sample_headers_reporting_zone_id');
            }
        });

        Schema::table('sample_details', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_details', 'processing_zone_id')) {
                $table->uuid('processing_zone_id')->nullable()->after('sample_header_id');
                $table->index('processing_zone_id', 'idx_sample_details_processing_zone_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            if (Schema::hasColumn('sample_details', 'processing_zone_id')) {
                $table->dropIndex('idx_sample_details_processing_zone_id');
                $table->dropColumn('processing_zone_id');
            }
        });

        Schema::table('sample_headers', function (Blueprint $table) {
            foreach (['reporting_zone_id', 'processing_zone_id', 'zone_id'] as $column) {
                if (Schema::hasColumn('sample_headers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('submission_form_instances', function (Blueprint $table) {
            foreach (['processing_zone_id', 'zone_id'] as $column) {
                if (Schema::hasColumn('submission_form_instances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
