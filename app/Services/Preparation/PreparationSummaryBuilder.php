<?php

namespace App\Services\Preparation;

use App\LabCategoryItems;
use App\LabSubCategory;
use App\Models\SolutionPreparation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PreparationSummaryBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(SolutionPreparation $preparation): array
    {
        $preparation->loadMissing([
            'solution.reportingUnit',
            'solution.alternativeSolution',
            'preparer',
            'approver',
            'preparedUom',
            'sourcePreparation',
            'steps.ingredient.reagent',
            'steps.ingredient.unitMeasure',
        ]);

        $solution = $preparation->solution;
        $referenceQuantity = $this->resolveReferenceQuantity($solution, $preparation);
        $scaleFactor = $this->resolveScaleFactor($preparation, $referenceQuantity);

        return [
            'reference_quantity' => $referenceQuantity,
            'scale_factor' => $scaleFactor,
            'scale_label' => $this->scaleLabel($referenceQuantity, $scaleFactor),
            'ingredients' => $this->buildIngredientLines($preparation, $scaleFactor),
            'expiry' => $this->resolveExpiry($preparation, $solution),
            'yield_uom' => $preparation->preparedUom?->name
                ?? $solution?->reportingUnit?->name
                ?? '—',
        ];
    }

    protected function resolveReferenceQuantity(?LabSubCategory $solution, SolutionPreparation $preparation): ?float
    {
        $payload = $preparation->ingredient_payload;
        if (is_array($payload) && isset($payload['reference_quantity']) && is_numeric($payload['reference_quantity'])) {
            return (float) $payload['reference_quantity'];
        }

        $fromRate = $this->parseNumericRate($solution?->rate);
        if ($fromRate !== null && $fromRate > 0) {
            return $fromRate;
        }

        return null;
    }

    protected function parseNumericRate(?string $rate): ?float
    {
        if ($rate === null || trim($rate) === '') {
            return null;
        }

        if (is_numeric($rate)) {
            return (float) $rate;
        }

        if (preg_match('/([\d]+(?:\.\d+)?)/', $rate, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    protected function resolveScaleFactor(SolutionPreparation $preparation, ?float $referenceQuantity): float
    {
        $quantity = $preparation->quantity_prepared !== null
            ? (float) $preparation->quantity_prepared
            : null;

        if ($quantity === null || $quantity <= 0) {
            return 1.0;
        }

        if ($referenceQuantity === null || $referenceQuantity <= 0) {
            return 1.0;
        }

        return $quantity / $referenceQuantity;
    }

    protected function scaleLabel(?float $referenceQuantity, float $scaleFactor): string
    {
        if ($referenceQuantity === null || $referenceQuantity <= 0) {
            return 'Recipe amounts (configure solution rate for scaling)';
        }

        if (abs($scaleFactor - 1.0) < 0.0001) {
            return 'Matches standard batch size';
        }

        return sprintf('Scaled × %s from standard batch', rtrim(rtrim(number_format($scaleFactor, 4, '.', ''), '0'), '.'));

    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildIngredientLines(SolutionPreparation $preparation, float $scaleFactor): array
    {
        if (is_array($preparation->ingredient_payload) && ! empty($preparation->ingredient_payload['lines'])) {
            return collect($preparation->ingredient_payload['lines'])
                ->map(fn (array $line) => [
                    'id' => $line['id'] ?? null,
                    'name' => $line['name'] ?? 'Ingredient',
                    'recipe_amount' => isset($line['recipe_amount']) ? (float) $line['recipe_amount'] : null,
                    'calculated_amount' => isset($line['calculated_amount']) ? (float) $line['calculated_amount'] : null,
                    'unit' => $line['unit'] ?? '—',
                    'in_workflow' => (bool) ($line['in_workflow'] ?? false),
                ])
                ->values()
                ->all();
        }

        $stepIngredientIds = $preparation->steps
            ->filter(fn ($step) => $step->isRegularStep() && $step->ingredient_id)
            ->pluck('ingredient_id')
            ->map(fn ($id) => (string) $id)
            ->flip();

        $recipeItems = LabCategoryItems::query()
            ->where('sub_category_id', $preparation->solution_id)
            ->with(['reagent', 'unitMeasure'])
            ->orderBy('created_at')
            ->get();

        if ($recipeItems->isEmpty()) {
            return $this->ingredientsFromStepsOnly($preparation, $scaleFactor, $stepIngredientIds);
        }

        return $recipeItems->map(function (LabCategoryItems $item) use ($scaleFactor, $stepIngredientIds) {
            $recipeAmount = (float) ($item->amount_used ?? 0);

            return [
                'id' => $item->id,
                'name' => $item->reagent?->name ?? 'Reagent',
                'recipe_amount' => $recipeAmount,
                'calculated_amount' => round($recipeAmount * $scaleFactor, 4),
                'unit' => $item->unitMeasure?->name ?? '—',
                'in_workflow' => $stepIngredientIds->has((string) $item->id),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<string, int>  $stepIngredientIds
     * @return list<array<string, mixed>>
     */
    protected function ingredientsFromStepsOnly(
        SolutionPreparation $preparation,
        float $scaleFactor,
        Collection $stepIngredientIds
    ): array {
        $seen = [];

        return $preparation->steps
            ->filter(fn ($step) => $step->isRegularStep() && $step->ingredient)
            ->map(function ($step) use ($scaleFactor, &$seen) {
                $ingredient = $step->ingredient;
                if ($ingredient === null || isset($seen[(string) $ingredient->id])) {
                    return null;
                }
                $seen[(string) $ingredient->id] = true;
                $recipeAmount = (float) ($ingredient->amount_used ?? 0);

                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->reagent?->name ?? $step->step_name,
                    'recipe_amount' => $recipeAmount,
                    'calculated_amount' => round($recipeAmount * $scaleFactor, 4),
                    'unit' => $ingredient->unitMeasure?->name ?? '—',
                    'in_workflow' => true,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{date: string, hint: ?string, status: string}
     */
    protected function resolveExpiry(SolutionPreparation $preparation, ?LabSubCategory $solution): array
    {
        if (! $solution) {
            return [
                'date' => '—',
                'hint' => null,
                'status' => 'unknown',
            ];
        }

        $batchMatches = $preparation->batch_number
            && $solution->current_batch_number
            && (string) $preparation->batch_number === (string) $solution->current_batch_number;

        if ($batchMatches && $solution->batch_expiry_date) {
            $expiry = Carbon::parse($solution->batch_expiry_date);

            return [
                'date' => $expiry->format('M j, Y'),
                'hint' => $expiry->isPast() ? 'Batch expiry has passed' : $expiry->diffForHumans().' remaining',
                'status' => $expiry->isPast() ? 'expired' : ($expiry->diffInDays(now()) <= 14 ? 'soon' : 'ok'),
            ];
        }

        if ($preparation->is_new_batch && $preparation->isInProgress()) {
            return [
                'date' => 'Pending approval',
                'hint' => trim((string) ($solution->stability_notes ?? '')) ?: 'Expiry is recorded on the solution batch after approval.',
                'status' => 'pending',
            ];
        }

        if ($solution->batch_expiry_date) {
            $expiry = Carbon::parse($solution->batch_expiry_date);

            return [
                'date' => $expiry->format('M j, Y').' (current stock batch)',
                'hint' => $solution->stability_notes,
                'status' => $expiry->isPast() ? 'expired' : 'ok',
            ];
        }

        return [
            'date' => 'Not set',
            'hint' => $solution->stability_notes ?: 'Configure batch expiry on the solution when stock is updated.',
            'status' => 'unknown',
        ];
    }
}
