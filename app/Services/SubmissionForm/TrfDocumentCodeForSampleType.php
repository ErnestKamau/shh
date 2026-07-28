<?php

namespace App\Services\SubmissionForm;

use App\SampleType;

/**
 * Maps a sample type record to the canonical TRF submission form document code.
 */
final class TrfDocumentCodeForSampleType
{
    public const FOOD = 'TRF-FOOD-019';

    public const FOOD_AND_FEED = 'TRF-FOOD-FEED-021';

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

        if ($this->isFoodAndFeed($sampleType)) {
            return self::FOOD_AND_FEED;
        }

        if ($this->isFood($sampleType)) {
            return self::FOOD;
        }

        if ($this->isWater($sampleType)) {
            return self::WATER;
        }

        return null;
    }

    public function isFoodAndFeed(SampleType $sampleType): bool
    {
        $name = trim((string) ($sampleType->name ?? ''));
        $code = trim((string) ($sampleType->code ?? ''));

        return $this->matchesFoodAndFeedLabel($name)
            || $this->matchesFoodAndFeedLabel($code);
    }

    public function isFood(SampleType $sampleType): bool
    {
        if ($this->isFoodAndFeed($sampleType)) {
            return false;
        }

        return stripos((string) ($sampleType->name ?? ''), 'Food') !== false
            || stripos((string) ($sampleType->code ?? ''), 'FOOD') !== false;
    }

    public function isWater(SampleType $sampleType): bool
    {
        if ($this->isWasteWater($sampleType)) {
            return false;
        }

        return stripos((string) ($sampleType->name ?? ''), 'Water') !== false
            || stripos((string) ($sampleType->code ?? ''), 'WTR') !== false;
    }

    public function isWasteWater(SampleType $sampleType): bool
    {
        return stripos((string) ($sampleType->name ?? ''), 'Waste Water') !== false
            || stripos((string) ($sampleType->code ?? ''), 'WWTR') !== false;
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
     * @return array{is_food: bool, is_food_and_feed: bool, is_water: bool, is_waste_water: bool}
     */
    public function classify(?SampleType $sampleType): array
    {
        if ($sampleType === null) {
            return [
                'is_food' => false,
                'is_food_and_feed' => false,
                'is_water' => false,
                'is_waste_water' => false,
            ];
        }

        $isFoodAndFeed = $this->isFoodAndFeed($sampleType);
        $isFood = ! $isFoodAndFeed && $this->isFood($sampleType);
        $isWasteWater = $this->isWasteWater($sampleType);
        $isWater = ! $isWasteWater && $this->isWater($sampleType);

        return [
            'is_food' => $isFood,
            'is_food_and_feed' => $isFoodAndFeed,
            'is_water' => $isWater,
            'is_waste_water' => $isWasteWater,
        ];
    }

    public function resolveReportVariant(?SampleType $sampleType): string
    {
        $classification = $this->classify($sampleType);

        if ($classification['is_food'] || $classification['is_food_and_feed']) {
            return 'food';
        }

        if ($classification['is_waste_water']) {
            return 'waste_water';
        }

        return 'water';
    }

    private function matchesFoodAndFeedLabel(string $value): bool
    {
        $normalized = mb_strtolower(trim($value));
        if ($normalized === '') {
            return false;
        }

        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return in_array($normalized, ['food and feed', 'food & feed'], true)
            || (bool) preg_match('/\bfood\b.*\bfeed\b/', $normalized);
    }
}
