<?php

/**
 * Hatchery Hygiene standards CFU Scores/ 16cm (Squared) — range-based entries.
 *
 * @return list<array{low: int, high: int|null, value: string, interpretation: string}>
 */
return [
    ['low' => 100, 'high' => null, 'value' => '0', 'interpretation' => 'Unacceptable'],
    ['low' => 76, 'high' => 100, 'value' => '1', 'interpretation' => 'Very Poor'],
    ['low' => 51, 'high' => 75, 'value' => '2', 'interpretation' => 'Poor'],
    ['low' => 26, 'high' => 50, 'value' => '3', 'interpretation' => 'Moderate'],
    ['low' => 6, 'high' => 25, 'value' => '4', 'interpretation' => 'Good'],
    ['low' => 0, 'high' => 5, 'value' => '5', 'interpretation' => 'Excellent'],
];
