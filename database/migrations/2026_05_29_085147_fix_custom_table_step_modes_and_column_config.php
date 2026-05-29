<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set table_mode = 'static' for any custom_table steps that have static rows
     * defined but table_mode = NULL, and fix blank dataset_config on columns
     * that the admin configured with a YES/NO choice but whose config was never saved.
     */
    public function up(): void
    {
        // 1. Find custom_table steps with null table_mode that have static rows
        $stepIds = DB::table('procedure_step_table_static_rows')
            ->join('procedure_worksheet_steps', 'procedure_worksheet_steps.id', '=', 'procedure_step_table_static_rows.procedure_worksheet_step_id')
            ->where('procedure_worksheet_steps.value_type', 'custom_table')
            ->whereNull('procedure_worksheet_steps.table_mode')
            ->distinct()
            ->pluck('procedure_worksheet_steps.id');

        if ($stepIds->isNotEmpty()) {
            DB::table('procedure_worksheet_steps')
                ->whereIn('id', $stepIds)
                ->update(['table_mode' => 'static']);
        }

        // 2. Fix the "Tests Selection" column (dataset_config is empty) that belongs
        //    to a step whose other column is "Types of Test" (fixed). We identify it
        //    by column label + belonging to a custom_table static step.
        $yesNoColumns = DB::table('procedure_step_table_columns')
            ->join('procedure_worksheet_steps', 'procedure_worksheet_steps.id', '=', 'procedure_step_table_columns.procedure_worksheet_step_id')
            ->where('procedure_worksheet_steps.value_type', 'custom_table')
            ->where('procedure_worksheet_steps.table_mode', 'static')
            ->where('procedure_step_table_columns.label', 'Tests Selection')
            ->where(function ($q) {
                $q->whereNull('procedure_step_table_columns.dataset_config')
                  ->orWhereRaw("procedure_step_table_columns.dataset_config::text IN ('', '[]', '{}', 'null')");
            })
            ->pluck('procedure_step_table_columns.id');

        if ($yesNoColumns->isNotEmpty()) {
            DB::table('procedure_step_table_columns')
                ->whereIn('id', $yesNoColumns)
                ->update([
                    'dataset_config' => json_encode([
                        'column_mode'    => 'choices',
                        'choice_control' => 'radio',
                        'static_options' => ['Yes', 'No'],
                    ]),
                ]);
        }

        // 3. Delete any empty instances for these steps so that static rows get
        //    re-materialized on the next page load (instances with zero rows).
        if ($stepIds->isNotEmpty()) {
            $instanceIds = DB::table('sample_procedure_step_table_instances')
                ->whereIn('procedure_worksheet_step_id', $stepIds)
                ->pluck('id');

            foreach ($instanceIds as $instanceId) {
                $hasRows = DB::table('sample_procedure_step_table_rows')
                    ->where('instance_id', $instanceId)
                    ->exists();

                if (! $hasRows) {
                    DB::table('sample_procedure_step_table_instances')
                        ->where('id', $instanceId)
                        ->delete();
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally left blank — this is a data-fix migration.
    }
};
