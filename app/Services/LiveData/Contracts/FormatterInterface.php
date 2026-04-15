<?php

namespace App\Services\LiveData\Contracts;

interface FormatterInterface
{
    /**
     * Format a single metric result.
     */
    public function formatCount(int $count, string $label, string $detail, ?string $since = null): string;

    /**
     * Build a structured table for UI/Markdown.
     */
    public function buildTable(array $headers, array $rows): string;

    /**
     * Format a trend comparison.
     */
    public function formatTrend(float $current, float $previous, string $unit = ''): array;
}
