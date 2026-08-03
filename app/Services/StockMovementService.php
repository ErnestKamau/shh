<?php

namespace App\Services;

use App\LabStockMovement;
use App\LabSubCategory;
use App\Models\SolutionPreparation;
use App\UnitOfMeasureConversion;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function createPreparationStockMovement(SolutionPreparation $preparation): ?LabStockMovement
    {
        if ($preparation->stockMovements()->exists()) {
            return $preparation->stockMovements()->first();
        }

        $solution = $preparation->solution;
        if (! $solution || ! $preparation->quantity_prepared) {
            return null;
        }

        return DB::transaction(function () use ($preparation, $solution) {
            $targetUomId = $solution->reporting_unit;
            $quantity = (float) $preparation->quantity_prepared;
            $fromUomId = $preparation->uom_id ?? $targetUomId;

            if (! $targetUomId) {
                return null;
            }

            $converted = $this->convertAmount($quantity, $fromUomId, $targetUomId);

            $movement = new LabStockMovement();
            $movement->description = "Stock in from preparation {$preparation->preparation_number}";
            $movement->lab_sub_category_id = $solution->id;
            $movement->stock_type = 'stock_in';
            $movement->stock_in = $converted;
            $movement->stock_out = 0;
            $movement->uom_id = $targetUomId;
            $movement->created_by = auth()->id();
            $movement->preparation_id = $preparation->id;
            $movement->batch_number = $preparation->batch_number;
            $movement->save();

            $solution->stock = ($solution->stock ?? 0) + $converted;
            if ($preparation->batch_number) {
                $solution->current_batch_number = $preparation->batch_number;
                $solution->batch_prepared_date = now()->toDateString();
                $solution->batch_expiry_date = $preparation->expiry_date?->toDateString();
                $solution->batch_status = 'active';
            }
            $solution->save();

            return $movement;
        });
    }

    public function convertAmount(float $amount, ?string $fromUomId, ?string $toUomId): float
    {
        if (! $fromUomId || ! $toUomId || $fromUomId === $toUomId) {
            return $amount;
        }

        $conversion = UnitOfMeasureConversion::where('uom1', $toUomId)
            ->where('uom2', $fromUomId)
            ->first();

        if ($conversion && (float) $conversion->conversion_ratio > 0) {
            return $amount / (float) $conversion->conversion_ratio;
        }

        $inverse = UnitOfMeasureConversion::where('uom1', $fromUomId)
            ->where('uom2', $toUomId)
            ->first();

        if ($inverse && (float) $inverse->conversion_ratio > 0) {
            return $amount * (float) $inverse->conversion_ratio;
        }

        return $amount;
    }

    /**
     * Deduct stock for all reagent_input columns in a procedure matrix section.
     *
     * Safe to call multiple times — idempotency is enforced via source_type/source_id on
     * LabStockMovement and the stock_deducted_at flag on ProcedureSectionInputValue rows.
     *
     * @param  \App\Models\Procedures\ProcedureWorksheet  $worksheet
     * @param  string  $batchId       Sample header ID
     * @param  string  $sectionKey    e.g. 'enrichment'
     * @param  int     $sampleCount   Number of samples — used when multiply_by_sample_count is true
     * @param  array<string, mixed>  $sectionConfig  The section's layout_settings JSON fragment
     */
    public function deductForMatrixSection(
        \App\Models\Procedures\ProcedureWorksheet $worksheet,
        string $batchId,
        string $sectionKey,
        int $sampleCount,
        array $sectionConfig
    ): void {
        $rows = $sectionConfig['rows'] ?? [];
        $columns = $sectionConfig['columns'] ?? [];

        // Collect reagent_input columns that have a lab_sub_category_id (or per-row map).
        $reagentColumns = array_filter($columns, fn ($col) =>
            ($col['type'] ?? '') === 'reagent_input'
            && (! empty($col['lab_sub_category_id']) || ! empty($col['lab_sub_category_id_by_row']))
        );

        if (empty($reagentColumns)) {
            return;
        }

        DB::transaction(function () use ($worksheet, $batchId, $sectionKey, $sampleCount, $rows, $reagentColumns) {
            foreach ($rows as $row) {
                $rowKey = $row['key'];

                foreach ($reagentColumns as $col) {
                    $colKey = $col['key'];
                    $subCategoryId = $col['lab_sub_category_id_by_row'][$rowKey]
                        ?? $col['lab_sub_category_id']
                        ?? null;

                    if (! $subCategoryId) {
                        continue;
                    }

                    $multiplyBySampleCount = (bool) ($col['multiply_by_sample_count'] ?? false);

                    // Unique source ID for idempotency guard.
                    $sourceId = implode(':', [
                        $worksheet->id, $batchId, $sectionKey, $rowKey, $colKey,
                    ]);

                    // Skip if already deducted.
                    $alreadyDeducted = \App\LabStockMovement::query()
                        ->where('source_type', 'procedure_matrix')
                        ->where('source_id', $sourceId)
                        ->exists();

                    if ($alreadyDeducted) {
                        continue;
                    }

                    // Load the stored shared input value for this cell.
                    $inputRow = \App\Models\Procedures\ProcedureSectionInputValue::query()
                        ->where('procedure_worksheet_id', $worksheet->id)
                        ->where('batch_id', $batchId)
                        ->where('section_key', $sectionKey)
                        ->where('row_key', $rowKey)
                        ->where('column_key', $colKey)
                        ->whereNull('captured_result_id')
                        ->first();

                    if (! $inputRow || ! filled($inputRow->value)) {
                        continue;
                    }

                    $rawQty = (float) $inputRow->value;
                    if ($rawQty <= 0) {
                        continue;
                    }

                    $subCategory = \App\LabSubCategory::find($subCategoryId);
                    if (! $subCategory) {
                        continue;
                    }

                    $targetUomId = $subCategory->reporting_unit;
                    $fromUomId = $inputRow->uom_id ?? $targetUomId;
                    $convertedQty = $this->convertAmount($rawQty, $fromUomId, $targetUomId);

                    if ($multiplyBySampleCount && $sampleCount > 1) {
                        $convertedQty *= $sampleCount;
                    }

                    // Create the stock-out movement.
                    \App\LabStockMovement::create([
                        'description' => "Matrix deduction: {$worksheet->name} / {$sectionKey} / {$rowKey} / {$col['label']}",
                        'lab_sub_category_id' => $subCategoryId,
                        'stock_type' => 'stock_out',
                        'stock_in' => 0,
                        'stock_out' => $convertedQty,
                        'uom_id' => $targetUomId,
                        'created_by' => auth()->id(),
                        'source_type' => 'procedure_matrix',
                        'source_id' => $sourceId,
                    ]);

                    // Reduce stock on the subcategory.
                    $subCategory->decrement('stock', $convertedQty);

                    // Mark the input row as deducted.
                    $inputRow->update(['stock_deducted_at' => now()]);
                }
            }
        });
    }

    /**
     * Reverse stock deductions for a matrix section (called when a phase is re-opened).
     */
    public function reverseMatrixSectionDeductions(
        \App\Models\Procedures\ProcedureWorksheet $worksheet,
        string $batchId,
        string $sectionKey
    ): void {
        $sourcePrefix = implode(':', [$worksheet->id, $batchId, $sectionKey]);

        $movements = \App\LabStockMovement::query()
            ->where('source_type', 'procedure_matrix')
            ->where('source_id', 'like', $sourcePrefix.'%')
            ->get();

        if ($movements->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($movements, $worksheet, $batchId, $sectionKey) {
            foreach ($movements as $movement) {
                /** @var \App\LabSubCategory|null $sub */
                $sub = \App\LabSubCategory::find($movement->lab_sub_category_id);
                if ($sub) {
                    $sub->increment('stock', (float) $movement->stock_out);
                }

                $movement->delete();
            }

            // Clear stock_deducted_at flags for this section.
            \App\Models\Procedures\ProcedureSectionInputValue::query()
                ->where('procedure_worksheet_id', $worksheet->id)
                ->where('batch_id', $batchId)
                ->where('section_key', $sectionKey)
                ->whereNotNull('stock_deducted_at')
                ->update(['stock_deducted_at' => null]);
        });
    }
}
