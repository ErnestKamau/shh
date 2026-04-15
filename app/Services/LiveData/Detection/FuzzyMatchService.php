<?php

namespace App\Services\LiveData\Detection;

/**
 * Fuzzy matching service for typo correction in intent detection.
 */
class FuzzyMatchService
{
    private const FUZZY_THRESHOLD = 0.85;

    public function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower($str1);
        $str2 = mb_strtolower($str2);

        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) return 1.0;

        $distance = levenshtein($str1, $str2);
        return 1.0 - ($distance / $maxLen);
    }

    public function findBestMatch(string $searchKeyword, array $candidates): ?string
    {
        $bestCandidate = null;
        $bestSimilarity = 0;

        foreach ($candidates as $candidate) {
            $similarity = $this->calculateSimilarity($searchKeyword, $candidate);

            if ($similarity > self::FUZZY_THRESHOLD && $similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestCandidate = $candidate;
            }
        }

        return $bestCandidate;
    }
}
