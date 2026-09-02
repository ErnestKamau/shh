<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pricelist_items') || ! Schema::hasColumn('pricelist_items', 'analysis_id')) {
            return;
        }

        if ($this->analysisIdIsNullable()) {
            return;
        }

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $table->uuid('analysis_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pricelist_items') || ! Schema::hasColumn('pricelist_items', 'analysis_id')) {
            return;
        }

        if (! $this->analysisIdIsNullable()) {
            return;
        }

        DB::table('pricelist_items')
            ->whereNull('analysis_id')
            ->delete();

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $table->uuid('analysis_id')->nullable(false)->change();
        });
    }

    private function analysisIdIsNullable(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $row = DB::selectOne(
                'select is_nullable from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                ['pricelist_items', 'analysis_id']
            );

            return $row && strtoupper((string) ($row->is_nullable ?? '')) === 'YES';
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $row = DB::selectOne(
                'select is_nullable from information_schema.columns where table_schema = database() and table_name = ? and column_name = ?',
                ['pricelist_items', 'analysis_id']
            );

            return $row && strtoupper((string) ($row->is_nullable ?? '')) === 'YES';
        }

        if ($driver === 'sqlite') {
            foreach (DB::select('pragma table_info(pricelist_items)') as $col) {
                if (($col->name ?? '') === 'analysis_id') {
                    return (int) ($col->notnull ?? 1) === 0;
                }
            }
        }

        return false;
    }
};
