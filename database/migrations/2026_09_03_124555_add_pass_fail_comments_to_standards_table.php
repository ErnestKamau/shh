<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('standards')) {
            return;
        }

        Schema::table('standards', function (Blueprint $table): void {
            if (! Schema::hasColumn('standards', 'pass_comment')) {
                $table->text('pass_comment')->nullable()->after('qc_scheme_ids');
            }
            if (! Schema::hasColumn('standards', 'fail_comment')) {
                $table->text('fail_comment')->nullable()->after('pass_comment');
            }
        });

        if (! Schema::hasTable('standards_analytes')) {
            return;
        }

        // Copy the first non-empty analyte-level comments up to the parent standard.
        $rows = DB::table('standards_analytes')
            ->select('standard_id', 'pass_comment', 'fail_comment')
            ->where(function ($query): void {
                $query->whereNotNull('pass_comment')->where('pass_comment', '!=', '')
                    ->orWhere(function ($inner): void {
                        $inner->whereNotNull('fail_comment')->where('fail_comment', '!=', '');
                    });
            })
            ->orderBy('id')
            ->get();

        $byStandard = [];
        foreach ($rows as $row) {
            $standardId = (string) $row->standard_id;
            if (! isset($byStandard[$standardId])) {
                $byStandard[$standardId] = [
                    'pass_comment' => null,
                    'fail_comment' => null,
                ];
            }
            if ($byStandard[$standardId]['pass_comment'] === null && filled($row->pass_comment)) {
                $byStandard[$standardId]['pass_comment'] = $row->pass_comment;
            }
            if ($byStandard[$standardId]['fail_comment'] === null && filled($row->fail_comment)) {
                $byStandard[$standardId]['fail_comment'] = $row->fail_comment;
            }
        }

        foreach ($byStandard as $standardId => $comments) {
            DB::table('standards')
                ->where('id', $standardId)
                ->where(function ($query): void {
                    $query->whereNull('pass_comment')
                        ->orWhere('pass_comment', '')
                        ->orWhereNull('fail_comment')
                        ->orWhere('fail_comment', '');
                })
                ->update([
                    'pass_comment' => $comments['pass_comment'],
                    'fail_comment' => $comments['fail_comment'],
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('standards')) {
            return;
        }

        Schema::table('standards', function (Blueprint $table): void {
            foreach (['pass_comment', 'fail_comment'] as $column) {
                if (Schema::hasColumn('standards', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
