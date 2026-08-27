<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers_rating_criterias')) {
            return;
        }

        Schema::table('suppliers_rating_criterias', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers_rating_criterias', 'reason')) {
                $table->text('reason')->nullable();
            }

            if (! Schema::hasColumn('suppliers_rating_criterias', 'request_id')) {
                $table->uuid('request_id')->nullable();
            }
        });

        $this->convertIntegerFkToUuid('suppliers_rating_criterias', 'rating_by');
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers_rating_criterias')) {
            return;
        }

        Schema::table('suppliers_rating_criterias', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers_rating_criterias', 'reason')) {
                $table->dropColumn('reason');
            }

            if (Schema::hasColumn('suppliers_rating_criterias', 'request_id')) {
                $table->dropColumn('request_id');
            }
        });
    }

    private function convertIntegerFkToUuid(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $type = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );

        if (! $type || $type->data_type === 'uuid') {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("
            ALTER TABLE {$table}
            ALTER COLUMN {$column} TYPE uuid
            USING CASE
                WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                    THEN {$column}::text::uuid
                ELSE NULL
            END
        ");
    }
};
