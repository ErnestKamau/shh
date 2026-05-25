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
        Schema::table('sample_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sample_details', 'file_no')) {
                $table->string('file_no')->nullable()->after('sample_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            if (Schema::hasColumn('sample_details', 'file_no')) {
                $table->dropColumn('file_no');
            }
        });
    }
};
