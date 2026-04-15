<?php

namespace App\Services\LiveData\Formatting;

use App\Services\LiveData\Contracts\FormatterInterface;

class MarkdownFormatter implements FormatterInterface
{
    public function formatCount(int $count, string $label, string $detail, ?string $since = null): string
    {
        $reply = "## {$label}\n\n"
            . "**{$count}**\n\n"
            . "{$detail}";

        if ($since) {
            $reply .= "\n\n*Calculation period: Since {$since}*";
        }

        return $reply;
    }

    public function buildTable(array $headers, array $rows): string
    {
        if (empty($rows)) return "No data available.";

        $headerStr = "| " . implode(" | ", $headers) . " |";
        $dividerStr = "| " . implode(" | ", array_fill(0, count($headers), "---")) . " |";
        
        $rowStrings = [];
        foreach ($rows as $row) {
            $rowStrings[] = "| " . implode(" | ", array_values((array)$row)) . " |";
        }

        return $headerStr . "\n" . $dividerStr . "\n" . implode("\n", $rowStrings);
    }

    public function formatTrend(float $current, float $previous, string $unit = ''): array
    {
        $delta = $current - $previous;
        $status = "stable";
        $trendStr = "";

        if ($previous > 0) {
            $percent = round((abs($delta) / $previous) * 100, 1);
            if ($delta < 0) {
                $trendStr = "📉 Improving: Decreased by **{$percent}%** ({$current}{$unit} vs {$previous}{$unit}).";
                $status = "improving";
            } elseif ($delta > 0) {
                $trendStr = "📈 Worsening: Increased by **{$percent}%** ({$current}{$unit} vs {$previous}{$unit}).";
                $status = "worsening";
            } else {
                $trendStr = "➡️ Stable: Remains at **{$current}{$unit}**.";
            }
        } else {
            $trendStr = "📊 Initial Baseline: Current value is **{$current}{$unit}**.";
        }

        return [
            'text' => $trendStr,
            'status' => $status,
            'delta' => $delta
        ];
    }
}
