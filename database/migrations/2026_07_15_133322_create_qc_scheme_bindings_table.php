<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('qc_scheme_bindings')) {
            Schema::create('qc_scheme_bindings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('standard_id')->nullable()->index();
                $table->uuid('method_id')->nullable()->index();
                $table->uuid('qc_scheme_id')->index();
                $table->string('mode', 20)->default('override');
                $table->integer('priority')->default(100);
                $table->jsonb('conditions')->nullable();
                $table->timestamps();

                $table->foreign('standard_id')
                    ->references('id')
                    ->on('standards')
                    ->cascadeOnDelete();

                $table->foreign('method_id')
                    ->references('id')
                    ->on('analysis_methods')
                    ->cascadeOnDelete();

                $table->foreign('qc_scheme_id')
                    ->references('id')
                    ->on('qc_scheme')
                    ->cascadeOnDelete();
            });

            DB::statement('
                ALTER TABLE qc_scheme_bindings
                ADD CONSTRAINT qc_scheme_bindings_scope_check
                CHECK (standard_id IS NOT NULL OR method_id IS NOT NULL)
            ');

            DB::statement('
                CREATE UNIQUE INDEX qc_scheme_bindings_standard_scheme_priority_unique
                ON qc_scheme_bindings (standard_id, qc_scheme_id, priority)
                WHERE standard_id IS NOT NULL
            ');

            DB::statement('
                CREATE UNIQUE INDEX qc_scheme_bindings_method_scheme_priority_unique
                ON qc_scheme_bindings (method_id, qc_scheme_id, priority)
                WHERE method_id IS NOT NULL
            ');
        }

        $this->backfillFromStandardQcScheme();
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_scheme_bindings');
    }

    private function backfillFromStandardQcScheme(): void
    {
        if (! Schema::hasTable('standard_qc_scheme')) {
            return;
        }

        $now = now();

        DB::table('standard_qc_scheme')
            ->orderBy('standard_id')
            ->chunk(200, function ($rows) use ($now): void {
                $inserts = [];

                foreach ($rows as $row) {
                    $inserts[] = [
                        'id' => (string) Str::uuid(),
                        'standard_id' => $row->standard_id,
                        'method_id' => null,
                        'qc_scheme_id' => $row->qc_scheme_id,
                        'mode' => 'override',
                        'priority' => 100,
                        'conditions' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($inserts !== []) {
                    DB::table('qc_scheme_bindings')->insertOrIgnore($inserts);
                }
            });
    }
};
