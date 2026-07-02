<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sample_submission_requests')) {
            Schema::table('sample_submission_requests', function (Blueprint $table): void {
                if (! Schema::hasColumn('sample_submission_requests', 'selected_source_quotation_header_id')) {
                    $table->uuid('selected_source_quotation_header_id')->nullable()->index();
                }
                if (! Schema::hasColumn('sample_submission_requests', 'quotation_source_mode')) {
                    $table->string('quotation_source_mode', 50)->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('quotation_headers')) {
            Schema::table('quotation_headers', function (Blueprint $table): void {
                if (! Schema::hasColumn('quotation_headers', 'source_quotation_header_id')) {
                    $table->uuid('source_quotation_header_id')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('customer_invoice')) {
            Schema::table('customer_invoice', function (Blueprint $table): void {
                if (! Schema::hasColumn('customer_invoice', 'quotation_header_id')) {
                    $table->uuid('quotation_header_id')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sample_submission_requests')) {
            Schema::table('sample_submission_requests', function (Blueprint $table): void {
                if (Schema::hasColumn('sample_submission_requests', 'selected_source_quotation_header_id')) {
                    $table->dropColumn('selected_source_quotation_header_id');
                }
                if (Schema::hasColumn('sample_submission_requests', 'quotation_source_mode')) {
                    $table->dropColumn('quotation_source_mode');
                }
            });
        }

        if (Schema::hasTable('quotation_headers')) {
            Schema::table('quotation_headers', function (Blueprint $table): void {
                if (Schema::hasColumn('quotation_headers', 'source_quotation_header_id')) {
                    $table->dropColumn('source_quotation_header_id');
                }
            });
        }

        if (Schema::hasTable('customer_invoice')) {
            Schema::table('customer_invoice', function (Blueprint $table): void {
                if (Schema::hasColumn('customer_invoice', 'quotation_header_id')) {
                    $table->dropColumn('quotation_header_id');
                }
            });
        }
    }
};
