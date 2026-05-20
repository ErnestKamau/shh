<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, int|null>
     */
    private array $stringColumns = [
        'user_type' => 255,
        'event' => 255,
        'auditable_type' => 255,
        'auditable_id' => 255,
        'tags' => 255,
        'user_agent' => 1023,
        'url' => 255,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audits')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            foreach ($this->stringColumns as $column => $length) {
                if (! Schema::hasColumn('audits', $column)) {
                    continue;
                }

                DB::statement("ALTER TABLE audits ALTER COLUMN {$column} TYPE TEXT USING {$column}::text");
            }

            return;
        }

        foreach ($this->stringColumns as $column => $length) {
            if (! Schema::hasColumn('audits', $column)) {
                continue;
            }

            DB::statement("ALTER TABLE audits MODIFY {$column} TEXT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('audits')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            foreach ($this->stringColumns as $column => $length) {
                if (! Schema::hasColumn('audits', $column)) {
                    continue;
                }

                DB::statement("ALTER TABLE audits ALTER COLUMN {$column} TYPE VARCHAR({$length}) USING LEFT({$column}, {$length})");
            }

            return;
        }

        foreach ($this->stringColumns as $column => $length) {
            if (! Schema::hasColumn('audits', $column)) {
                continue;
            }

            DB::statement("ALTER TABLE audits MODIFY {$column} VARCHAR({$length}) NULL");
        }
    }
};
