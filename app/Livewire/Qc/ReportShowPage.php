<?php

namespace App\Livewire\Qc;

use App\Models\QcModule\QCProcessedResults;
use Livewire\Component;

class ReportShowPage extends Component
{
    public string $resultId;

    public function mount(string $resultId): void
    {
        $this->resultId = $resultId;
    }

    public function render()
    {
        $result = QCProcessedResults::query()
            ->with(['method', 'sampletype', 'analysistype', 'analyte', 'results'])
            ->findOrFail($this->resultId);

        $resultMap = $result->getresultsarr();
        $labels = array_keys($resultMap);
        $data = array_map(static fn ($value) => (float) $value, array_values($resultMap));

        $mean = (float) ($result->robust_mean ?? 0);
        $median = (float) ($result->robust_median ?? 0);
        $sd = (float) ($result->robust_standard_deviation ?? 0);

        return view('livewire.qc.report-show-page', [
            'result' => $result,
            'labels' => $labels,
            'data' => $data,
            'mean' => $mean,
            'median' => $median,
            'sd' => $sd,
            'innerUpperLimit' => $mean + $sd,
            'outerUpperLimit' => $mean + (2 * $sd),
            'innerLowerLimit' => $mean - $sd,
            'outerLowerLimit' => $mean - (2 * $sd),
        ]);
    }
}
