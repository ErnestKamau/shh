<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_headers')) {
            return;
        }

        Schema::table('sample_headers', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_headers', 'is_technical')) {
                $table->boolean('is_technical')->default(false)->after('is_qc_batch')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_headers') || ! Schema::hasColumn('sample_headers', 'is_technical')) {
            return;
        }

        Schema::table('sample_headers', function (Blueprint $table): void {
            $table->dropColumn('is_technical');
        });
    }
};
