<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_request_report_revisions', function (Blueprint $table) {
            if (! Schema::hasColumn('test_request_report_revisions', 'report_url')) {
                $table->string('report_url')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('test_request_report_revisions', 'report_online_url')) {
                $table->text('report_online_url')->nullable()->after('report_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_request_report_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('test_request_report_revisions', 'report_online_url')) {
                $table->dropColumn('report_online_url');
            }
            if (Schema::hasColumn('test_request_report_revisions', 'report_url')) {
                $table->dropColumn('report_url');
            }
        });
    }
};
