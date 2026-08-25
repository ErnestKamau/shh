<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Known seed company UUIDs → stable business codes.
     *
     * @var array<string, string>
     */
    private const KNOWN_CODES = [
        '019dde3f-07d3-73d0-a0f2-a01ac58346b4' => 'AMSPEC-DXB',
        '019dde3f-07d3-73d0-a0f2-a01ac58346b5' => 'AMSPEC-RIO',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table): void {
            if (! Schema::hasColumn('companies', 'code')) {
                $table->string('code', 64)->nullable()->after('id');
            }
        });

        if (! Schema::hasColumn('companies', 'code')) {
            return;
        }

        foreach (self::KNOWN_CODES as $id => $code) {
            DB::table('companies')
                ->where('id', $id)
                ->where(function ($query): void {
                    $query->whereNull('code')->orWhere('code', '');
                })
                ->update(['code' => $code]);
        }

        if (! Schema::hasIndex('companies', 'companies_code_unique')) {
            Schema::table('companies', function (Blueprint $table): void {
                $table->unique('code');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('companies') || ! Schema::hasColumn('companies', 'code')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table): void {
            if (Schema::hasIndex('companies', 'companies_code_unique')) {
                $table->dropUnique(['code']);
            }
            $table->dropColumn('code');
        });
    }
};
