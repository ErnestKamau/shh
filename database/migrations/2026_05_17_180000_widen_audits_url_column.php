<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audits') || ! Schema::hasColumn('audits', 'url')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audits ALTER COLUMN url TYPE TEXT USING url::text');

            return;
        }

        DB::statement('ALTER TABLE audits MODIFY url TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('audits') || ! Schema::hasColumn('audits', 'url')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audits ALTER COLUMN url TYPE VARCHAR(255) USING LEFT(url, 255)');

            return;
        }

        DB::statement('ALTER TABLE audits MODIFY url VARCHAR(255) NULL');
    }
};
