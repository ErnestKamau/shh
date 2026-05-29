<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_headers', 'file_no')) {
                $table->string('file_no', 50)->nullable()->after('batch_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            if (Schema::hasColumn('sample_headers', 'file_no')) {
                $table->dropColumn('file_no');
            }
        });
    }
};
