<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('standard_qc_scheme')) {
            Schema::create('standard_qc_scheme', function (Blueprint $table) {
                $table->uuid('standard_id');
                $table->uuid('qc_scheme_id');
                $table->timestamps();

                $table->primary(['standard_id', 'qc_scheme_id']);
                $table->index('qc_scheme_id');

                $table->foreign('standard_id')
                    ->references('id')
                    ->on('standards')
                    ->cascadeOnDelete();

                $table->foreign('qc_scheme_id')
                    ->references('id')
                    ->on('qc_scheme')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('method_qc_scheme')) {
            Schema::create('method_qc_scheme', function (Blueprint $table) {
                $table->uuid('method_id');
                $table->uuid('qc_scheme_id');
                $table->timestamps();

                $table->primary(['method_id', 'qc_scheme_id']);
                $table->index('qc_scheme_id');

                $table->foreign('method_id')
                    ->references('id')
                    ->on('analysis_methods')
                    ->cascadeOnDelete();

                $table->foreign('qc_scheme_id')
                    ->references('id')
                    ->on('qc_scheme')
                    ->cascadeOnDelete();
            });
        }

        $this->backfillStandardQcSchemes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_qc_scheme');
        Schema::dropIfExists('standard_qc_scheme');
    }

    /**
     * Copy legacy standards.qc_scheme_ids CSV (UUIDs and/or scheme codes) into the pivot.
     */
    private function backfillStandardQcSchemes(): void
    {
        if (! Schema::hasColumn('standards', 'qc_scheme_ids')) {
            return;
        }

        $schemeIdsByCode = DB::table('qc_scheme')
            ->whereNotNull('code')
            ->pluck('id', 'code')
            ->all();

        $schemeIdSet = DB::table('qc_scheme')->pluck('id')->flip()->all();
        $now = now();

        DB::table('standards')
            ->whereNotNull('qc_scheme_ids')
            ->where('qc_scheme_ids', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($standards) use ($schemeIdsByCode, $schemeIdSet, $now): void {
                $rows = [];

                foreach ($standards as $standard) {
                    $tokens = array_values(array_filter(array_map(
                        'trim',
                        explode(',', (string) $standard->qc_scheme_ids)
                    )));

                    foreach ($tokens as $token) {
                        $schemeId = null;

                        if (isset($schemeIdSet[$token])) {
                            $schemeId = $token;
                        } elseif (isset($schemeIdsByCode[$token])) {
                            $schemeId = $schemeIdsByCode[$token];
                        }

                        if ($schemeId === null) {
                            continue;
                        }

                        $rows[$standard->id.'|'.$schemeId] = [
                            'standard_id' => $standard->id,
                            'qc_scheme_id' => $schemeId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($rows === []) {
                    return;
                }

                DB::table('standard_qc_scheme')->insertOrIgnore(array_values($rows));
            });
    }
};
