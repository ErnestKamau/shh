<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pricelist_items') || !Schema::hasColumn('pricelist_items', 'analysis_id')) {
            return;
        }

        if ($this->analysisIdColumnIsUuidLike()) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (!in_array($driver, ['pgsql', 'mysql', 'mariadb', 'sqlite'], true)) {
            return;
        }

        if (Schema::hasColumn('pricelist_items', 'analysis_id_uuid_temp')) {
            return;
        }

        Schema::table('pricelist_items', function (Blueprint $table) use ($driver): void {
            if (in_array($driver, ['pgsql', 'mysql', 'mariadb'], true)) {
                $table->uuid('analysis_id_uuid_temp')->nullable()->after('pricelist_id');
            } else {
                $table->uuid('analysis_id_uuid_temp')->nullable();
            }
        });

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $table->dropColumn('analysis_id');
        });

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $table->renameColumn('analysis_id_uuid_temp', 'analysis_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pricelist_items') || !Schema::hasColumn('pricelist_items', 'analysis_id')) {
            return;
        }

        if (!$this->analysisIdColumnIsUuidLike()) {
            return;
        }

        if (Schema::hasColumn('pricelist_items', 'analysis_id_int_temp')) {
            return;
        }

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['pgsql', 'mysql', 'mariadb'], true)) {
                $table->unsignedInteger('analysis_id_int_temp')->nullable()->after('pricelist_id');
            } else {
                $table->unsignedInteger('analysis_id_int_temp')->nullable();
            }
        });

        DB::table('pricelist_items')->update(['analysis_id_int_temp' => null]);

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $table->dropColumn('analysis_id');
        });

        Schema::table('pricelist_items', function (Blueprint $table): void {
            $table->renameColumn('analysis_id_int_temp', 'analysis_id');
        });
    }

    private function analysisIdColumnIsUuidLike(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $row = DB::selectOne(
                'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                ['pricelist_items', 'analysis_id']
            );

            return $row && ($row->data_type === 'uuid');
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $row = DB::selectOne(
                'select data_type, character_maximum_length from information_schema.columns where table_schema = database() and table_name = ? and column_name = ?',
                ['pricelist_items', 'analysis_id']
            );

            if (!$row) {
                return false;
            }

            $type = strtolower((string) ($row->data_type ?? ''));

            return $type === 'char' && (int) ($row->character_maximum_length ?? 0) === 36;
        }

        if ($driver === 'sqlite') {
            foreach (DB::select('pragma table_info(pricelist_items)') as $col) {
                if (($col->name ?? '') !== 'analysis_id') {
                    continue;
                }
                $type = strtolower((string) ($col->type ?? ''));

                return !str_contains($type, 'int');
            }

            return false;
        }

        return false;
    }
};
