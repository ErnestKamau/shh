<?php

namespace App\Livewire\Worksheets;

use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\SampleHeader;
use App\Services\GroupedWorksheets\GroupedResultsCaptureService;
use Livewire\Component;

class GroupedResultsCapture extends Component
{
    public SampleHeader $batch;

    public GroupedWorksheetHolder $holder;

    /** @var array<int, array{row_key: string, label: string, analysis_type_name: string|null}> */
    public array $parameters = [];

    /** @var array<int, array{sample_detail_code: string, sample_detail_id: string|null}> */
    public array $samples = [];

    /**
     * Nested map row_key → sample_code → { captured_result_id, result, reporting_symbol }
     *
     * @var array<string, array<string, array{captured_result_id: string, result: string|null, reporting_symbol: string|null}>>
     */
    public array $cells = [];

    public string $message = '';

    public string $messageType = '';

    public function mount(SampleHeader $batch, GroupedWorksheetHolder $holder): void
    {
        $this->batch = $batch;
        $this->holder = $holder;
        $this->loadMatrix();
    }

    public function loadMatrix(): void
    {
        $service = app(GroupedResultsCaptureService::class);
        $capturedResults = $service->loadCapturedResults($this->batch, $this->holder);
        $matrix = $service->buildMatrix($capturedResults);

        $this->parameters = $matrix['parameters'];
        $this->samples = $matrix['samples'];
        $this->cells = $matrix['cells'];
    }

    public function saveResults(): void
    {
        $payload = [];

        foreach ($this->cells as $rowCells) {
            foreach ($rowCells as $cell) {
                $payload[$cell['captured_result_id']] = [
                    'result' => $cell['result'] ?? null,
                    'reporting_symbol' => $cell['reporting_symbol'] ?? null,
                ];
            }
        }

        try {
            app(GroupedResultsCaptureService::class)->saveCellResults($payload);
            $this->setMessage('Results saved successfully.', 'success');
        } catch (\Throwable $e) {
            $this->setMessage('Failed to save results: '.$e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.worksheets.grouped-results-capture');
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
