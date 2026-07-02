<?php

namespace App\Livewire\Qc;

use App\Models\QcModule\Data\QcResults;
use App\Models\QcModule\QCProcessedResults;
use Livewire\Component;
use Livewire\WithPagination;

class ProcessingPage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public int $perPage = 50;

    public function processResults(): void
    {
        $unProcessedAnalyteIds = QcResults::query()
            ->where('is_qc_processed', 0)
            ->pluck('analyte_processed_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($unProcessedAnalyteIds === []) {
            session()->flash('success', 'No unprocessed QC results were found.');

            return;
        }

        $unprocessed = collect($unProcessedAnalyteIds)
            ->map(static fn ($id) => QCProcessedResults::query()->find($id))
            ->filter();

        foreach ($unprocessed as $record) {
            $rawResults = QcResults::query()
                ->where('analyte_processed_id', $record->id)
                ->pluck('result')
                ->filter(fn ($value) => is_numeric($value))
                ->map(fn ($value) => (float) $value)
                ->values()
                ->toArray();

            $statistics = $this->calculateRobustCv($rawResults);
            if ($statistics === null) {
                continue;
            }

            $record->robust_standard_deviation = $statistics['rSD'];
            $record->robust_median = $statistics['median'];
            $record->robust_mean = $statistics['mean'];
            $record->robust_cv = $statistics['rCV'];
            $record->robust_cv_percentage = $statistics['percent_rCV'];
            $record->save();
        }

        QcResults::query()->where('is_qc_processed', 0)->update(['is_qc_processed' => 1]);

        session()->flash('success', 'Unprocessed QC results have been processed successfully.');
        $this->resetPage();
    }

    private function calculateMean(array $values): float
    {
        if ($values === []) {
            return 0;
        }

        return array_sum($values) / count($values);
    }

    private function calculateMedian(array $values): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }

        sort($values);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    private function calculateRobustSd(array $values): ?float
    {
        $count = count($values);
        if ($count <= 1) {
            return 0.0;
        }

        $median = $this->calculateMedian($values);
        if ($median === null) {
            return null;
        }

        $deviations = array_map(fn ($value) => abs($value - $median), $values);
        $mad = $this->calculateMedian($deviations);

        return $mad !== null ? $mad * 1.4826 : null;
    }

    private function calculateRobustCv(array $values): ?array
    {
        if ($values === []) {
            return null;
        }

        $median = $this->calculateMedian($values);
        if ($median === null || $median == 0.0) {
            return [
                'mean' => 0,
                'median' => $median,
                'rSD' => 0.0,
                'rCV' => null,
                'percent_rCV' => null,
            ];
        }

        $rSd = $this->calculateRobustSd($values) ?? 0.0;
        $mean = $this->calculateMean($values);
        $rCv = $rSd / $median;

        return [
            'mean' => $mean,
            'median' => $median,
            'rSD' => $rSd,
            'rCV' => $rCv,
            'percent_rCV' => $rCv * 100,
        ];
    }

    public function render()
    {
        $results = QcResults::query()
            ->where('is_qc_processed', 0)
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.qc.processing-page', [
            'results' => $results,
        ]);
    }
}
