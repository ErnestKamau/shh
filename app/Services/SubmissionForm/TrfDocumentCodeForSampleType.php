<?php

namespace App\Services\SubmissionForm;

use App\SampleType;

/**
 * Maps a sample type record to the canonical TRF submission form document code.
 */
final class TrfDocumentCodeForSampleType
{
    public const FOOD = 'TRF-FOOD-019';

    public const WATER = 'TRF-WATER-020';

    public const WASTE_WATER = 'TRF-WASTE-036';

    public function resolve(?SampleType $sampleType): ?string
    {
        if ($sampleType === null) {
            return null;
        }

        if ($this->isWasteWater($sampleType)) {
            return self::WASTE_WATER;
        }

        if ($this->isFood($sampleType)) {
            return self::FOOD;
        }

        if ($this->isWater($sampleType)) {
            return self::WATER;
        }

        return null;
    }

    public function isFood(SampleType $sampleType): bool
    {
        return stripos($sampleType->name, 'Food') !== false
            || stripos($sampleType->code, 'FOOD') !== false;
    }

    public function isWater(SampleType $sampleType): bool
    {
        if ($this->isWasteWater($sampleType)) {
            return false;
        }

        return stripos($sampleType->name, 'Water') !== false
            || stripos($sampleType->code, 'WTR') !== false;
    }

    public function isWasteWater(SampleType $sampleType): bool
    {
        return stripos($sampleType->name, 'Waste Water') !== false
            || stripos($sampleType->code, 'WWTR') !== false;
    }

    /**
     * @return list<string>
     */
    public function foodSampleTypeLabels(): array
    {
        return ['Raw', 'Cooked', 'Ready To Eat'];
    }

    public function isFoodSampleTypeLabel(string $value): bool
    {
        return in_array(trim($value), $this->foodSampleTypeLabels(), true);
    }

    /**
     * @return array{is_food: bool, is_water: bool, is_waste_water: bool}
     */
    public function classify(?SampleType $sampleType): array
    {
        if ($sampleType === null) {
            return ['is_food' => false, 'is_water' => false, 'is_waste_water' => false];
        }

        $isFood = $this->isFood($sampleType);
        $isWasteWater = $this->isWasteWater($sampleType);
        $isWater = ! $isWasteWater && $this->isWater($sampleType);

        return [
            'is_food' => $isFood,
            'is_water' => $isWater,
            'is_waste_water' => $isWasteWater,
        ];
    }

    public function resolveReportVariant(?SampleType $sampleType): string
    {
        $classification = $this->classify($sampleType);

        if ($classification['is_food']) {
            return 'food';
        }

        if ($classification['is_waste_water']) {
            return 'waste_water';
        }

        return 'water';
    }
}
