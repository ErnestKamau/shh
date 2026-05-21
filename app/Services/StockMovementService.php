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
            $targetUom = $solution->reporting_unit;
            $quantity = (float) $preparation->quantity_prepared;
            $fromUom = $preparation->uom_id ?? $targetUom;

            $converted = $this->convertAmount($quantity, $fromUom, $targetUom);

            $movement = new LabStockMovement();
            $movement->description = "Stock in from preparation {$preparation->preparation_number}";
            $movement->lab_sub_category_id = $solution->id;
            $movement->stock_type = 'stock_in';
            $movement->stock_in = $converted;
            $movement->stock_out = 0;
            $movement->uom_id = $targetUom;
            $movement->created_by = auth()->id();
            $movement->preparation_id = $preparation->id;
            $movement->batch_number = $preparation->batch_number;
            $movement->save();

            $solution->stock = ($solution->stock ?? 0) + $converted;
            if ($preparation->batch_number) {
                $solution->current_batch_number = $preparation->batch_number;
                $solution->batch_prepared_date = now()->toDateString();
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
}
