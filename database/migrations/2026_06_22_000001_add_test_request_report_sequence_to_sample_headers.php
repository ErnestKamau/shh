<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->unsignedSmallInteger('test_request_report_sequence')->default(0)->after('batch_report_url');
        });
    }

    public function down(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->dropColumn('test_request_report_sequence');
        });
    }
};
