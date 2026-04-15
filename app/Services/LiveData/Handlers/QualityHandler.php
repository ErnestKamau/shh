<?php

namespace App\Services\LiveData\Handlers;

use Illuminate\Support\Facades\DB;
use App\Services\LiveData\Contracts\LiveDataHandlerInterface;
use App\Services\LiveData\Contracts\FormatterInterface;
use App\Services\LiveData\DTOs\LiveDataResult;

class QualityHandler implements LiveDataHandlerInterface
{
    public function __construct(protected FormatterInterface $formatter) {}

    public function supports(string $intent): bool
    {
        return str_starts_with($intent, 'qc_') || str_starts_with($intent, 'board_qc_') || $intent === 'capa_pending';
    }

    public function handle(string $intent, ?string $question = null): LiveDataResult
    {
        $data = match ($intent) {
            'board_qc_kpi_summary' => $this->boardQcKpiSummary(),
            'capa_pending'         => $this->capaPending(),
            default => ['reply' => "Unsupported quality intent: {$intent}", 'value' => null]
        };

        return LiveDataResult::fromArray($intent, $data);
    }

    private function boardQcKpiSummary(): array
    {
        $stats = DB::table('sample_headers')
            ->where('isactive', 1)
            ->whereNotNull('approval_date')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completions
            ')
            ->first();

        $passRate = $stats->total > 0 ? round(($stats->completions / $stats->total) * 100, 1) : 0;

        return [
            'reply' => $this->formatter->formatCount(
                $passRate,
                'Overall QC Pass Rate (Batch Level)',
                "Based on {$stats->total} total active batches.",
                '%'
            ),
            'value' => $passRate
        ];
    }

    private function capaPending(): array
    {
        $open = DB::table('corrective_actions')
            ->whereNull('deleted_at')
            ->whereRaw("LOWER(COALESCE(status_name, '')) NOT IN ('closed', 'completed', 'cancelled')")
            ->count();

        return [
            'reply' => $this->formatter->formatCount(
                $open,
                'Pending CAPA Actions',
                "Corrective actions currently open and requiring resolution."
            ),
            'value' => $open
        ];
    }
}
