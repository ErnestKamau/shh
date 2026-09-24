<?php

namespace App\Livewire\Batch\Tabs;

use App\CapturedResult;
use App\SampleHeader;
use App\Services\StandardLimitDisplayService;
use Illuminate\Support\Collection;
use Livewire\Component;

class RawResults extends Component
{
    public SampleHeader $batch;

    protected $listeners = ['resultsUpdated' => '$refresh'];

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function render(): \Illuminate\View\View
    {
        $limitDisplay = app(StandardLimitDisplayService::class);

        $rawResults = CapturedResult::with(['sample', 'analysis_type', 'operator'])
            ->where('sample_header_id', $this->batch->id)
            ->orderBy('sample_detail_id', 'ASC')
            ->orderBy('parameters_order', 'ASC')
            ->get()
            ->each(function (CapturedResult $result) use ($limitDisplay): void {
                $result->setAttribute(
                    'standard_limit_display',
                    $limitDisplay->forCapturedResult($result)
                );
            });

        /** @var Collection<string, Collection<int, CapturedResult>> $resultsBySample */
        $resultsBySample = $rawResults
            ->groupBy(function (CapturedResult $result): string {
                return (string) ($result->sample->sample_code ?? $result->sample_detail_code ?? 'Unknown');
            })
            ->map(function (Collection $sampleResults): Collection {
                // One row per analyte for this sample; prefer the filled capture if duplicates exist.
                return $sampleResults
                    ->groupBy(function (CapturedResult $result): string {
                        return implode('|', [
                            (string) ($result->analyte_id ?? ''),
                            (string) ($result->analysis_type_id ?? ''),
                            (string) ($result->analyte_code ?? ''),
                        ]);
                    })
                    ->map(function (Collection $matches): CapturedResult {
                        return $matches->first(function (CapturedResult $row): bool {
                            return $row->result !== null && $row->result !== '';
                        }) ?? $matches->first();
                    })
                    ->values()
                    ->sortBy(function (CapturedResult $result): string {
                        return sprintf(
                            '%06d-%s-%s',
                            (int) ($result->parameters_order ?? 0),
                            (string) ($result->analysis_type->name ?? ''),
                            (string) ($result->analyte_code ?? '')
                        );
                    })
                    ->values();
            });

        return view('livewire.batch.tabs.raw-results', [
            'resultsBySample' => $resultsBySample,
        ]);
    }
}
