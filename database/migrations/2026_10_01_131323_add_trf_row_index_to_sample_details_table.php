<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which TRF sample row (0-based) a job sample was created from.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sample_details', 'trf_row_index')) {
            return;
        }

        Schema::table('sample_details', function (Blueprint $table) {
            $table->unsignedSmallInteger('trf_row_index')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('sample_details', 'trf_row_index')) {
            return;
        }

        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropColumn('trf_row_index');
        });
    }
};
