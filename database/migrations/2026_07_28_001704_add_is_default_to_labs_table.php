<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('labs')) {
            return;
        }

        Schema::table('labs', function (Blueprint $table): void {
            if (! Schema::hasColumn('labs', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_external');
            }
        });

        if (DB::connection()->getDriverName() === 'pgsql' && Schema::hasColumn('labs', 'is_default')) {
            DB::statement('DROP INDEX IF EXISTS labs_one_default_unique');
            DB::statement('CREATE UNIQUE INDEX labs_one_default_unique ON labs ((true)) WHERE is_default IS TRUE');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('labs')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS labs_one_default_unique');
        }

        Schema::table('labs', function (Blueprint $table): void {
            if (Schema::hasColumn('labs', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
