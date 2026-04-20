<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('analysis_methods') || Schema::hasColumn('analysis_methods', 'sample_header_id')) {
            return;
        }

        Schema::table('analysis_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('sample_header_id')->nullable()->after('validation_status');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('analysis_methods') || !Schema::hasColumn('analysis_methods', 'sample_header_id')) {
            return;
        }

        Schema::table('analysis_methods', function (Blueprint $table) {
            $table->dropColumn('sample_header_id');
        });
    }
};
