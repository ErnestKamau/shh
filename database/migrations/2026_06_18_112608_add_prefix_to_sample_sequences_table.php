<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_sequences')) {
            return;
        }

        Schema::table('sample_sequences', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_sequences', 'prefix')) {
                $table->char('prefix', 1)->default('')->after('batch_code');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sample_sequences DROP CONSTRAINT IF EXISTS unique_batch');
            DB::statement('ALTER TABLE sample_sequences DROP CONSTRAINT IF EXISTS sample_sequences_batch_code_unique');
        } elseif ($driver === 'mysql') {
            $indexes = collect(DB::select('SHOW INDEX FROM sample_sequences'))
                ->pluck('Key_name')
                ->unique();

            if ($indexes->contains('unique_batch')) {
                Schema::table('sample_sequences', function (Blueprint $table) {
                    $table->dropUnique('unique_batch');
                });
            }
        }

        Schema::table('sample_sequences', function (Blueprint $table) {
            $table->unique(['batch_code', 'prefix'], 'sample_sequences_job_prefix_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_sequences')) {
            return;
        }

        Schema::table('sample_sequences', function (Blueprint $table) {
            $table->dropUnique('sample_sequences_job_prefix_unique');
        });

        Schema::table('sample_sequences', function (Blueprint $table) {
            if (Schema::hasColumn('sample_sequences', 'prefix')) {
                $table->dropColumn('prefix');
            }

            $table->unique(['batch_code'], 'unique_batch');
        });
    }
};
