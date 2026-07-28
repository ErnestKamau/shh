<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tax_regime') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('tax_regime', 'registered_by')) {
            return;
        }

        $isUuid = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'tax_regime')
            ->where('column_name', 'registered_by')
            ->where('udt_name', 'uuid')
            ->exists();

        if ($isUuid) {
            return;
        }

        // Legacy integer user IDs cannot map to UUID users; clear them before the type change.
        DB::statement(
            'ALTER TABLE tax_regime ALTER COLUMN registered_by TYPE uuid USING NULL'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('tax_regime') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('tax_regime', 'registered_by')) {
            return;
        }

        DB::statement(
            'ALTER TABLE tax_regime ALTER COLUMN registered_by TYPE integer USING NULL'
        );
    }
};
