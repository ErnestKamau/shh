<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_import_batches', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('upserted_summary');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_import_batches', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};
