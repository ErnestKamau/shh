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

    public bool $reviewOnly = false;

    /** @var array<int, array{row_key: string, label: string, analysis_type_name: string|null}> */
    public array $parameters = [];

    /** @var array<int, array{sample_detail_code: string, sample_detail_id: string|null}> */
    public array $samples = [];

    /**
     * Nested map row_key → sample_code → { captured_result_id, result, reporting_symbol, remark }
     *
     * @var array<string, array<string, array{captured_result_id: string, result: string|null, reporting_symbol: string|null, remark: string|null}>>
     */
    public array $cells = [];

    /**
     * Per sample_detail_id: header_body, main_body, notes_body
     *
     * @var array<string, array{header_body: string, main_body: string, notes_body: string}>
     */
    public array $sampleComments = [];

    public int $captureStep = 1;

    public int $activeSampleIndex = 0;

    public string $message = '';

    public string $messageType = '';

    public bool $isPosted = false;

    public ?string $postedAtLabel = null;

    public ?string $postedByName = null;

    public int $commentsEditorKey = 0;

    public function mount(SampleHeader $batch, GroupedWorksheetHolder $holder, bool $reviewOnly = false): void
    {
        $this->batch = $batch;
        $this->holder = $holder;
        $this->reviewOnly = $reviewOnly;
        $this->loadMatrix();
        $this->loadPostingStatus();
    }

    public function loadMatrix(): void
    {
        $service = app(GroupedResultsCaptureService::class);
        $matrix = $service->loadMatrixWithDrafts($this->batch, $this->holder);

        $this->parameters = $matrix['parameters'];
        $this->samples = $matrix['samples'];
        $this->cells = $matrix['cells'];
        $this->sampleComments = $matrix['sample_comments'] ?? [];

        if ($this->activeSampleIndex >= count($this->samples)) {
            $this->activeSampleIndex = 0;
        }

        $this->loadPostingStatus();
    }

    public function loadPostingStatus(): void
    {
        $status = app(GroupedResultsCaptureService::class)->getPostingStatus($this->batch, $this->holder);

        $this->isPosted = $status['is_posted'];
        $this->postedAtLabel = $status['posted_at']?->format('M d, Y H:i');
        $this->postedByName = $status['posted_by_name'];
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 2) {
            return;
        }

        $this->captureStep = $step;
    }

    public function setActiveSampleIndex(int $index): void
    {
        if ($index < 0 || $index >= count($this->samples)) {
            return;
        }

        $this->activeSampleIndex = $index;
        $this->commentsEditorKey++;
    }

    /**
     * @param  array{header_body?: string|null, main_body?: string|null, notes_body?: string|null}  $comments
     */
    public function setSampleCommentsForSample(string $sampleDetailId, array $comments): void
    {
        $this->sampleComments[$sampleDetailId] = [
            'header_body' => $comments['header_body'] ?? '',
            'main_body' => $comments['main_body'] ?? '',
            'notes_body' => $comments['notes_body'] ?? '',
        ];
    }

    public function saveDraft(): void
    {
        try {
            app(GroupedResultsCaptureService::class)->saveDrafts(
                $this->batch,
                $this->holder,
                $this->buildCellPayload(),
                $this->buildSampleCommentsPayload()
            );
            $this->commentsEditorKey++;
            $this->setMessage('Results saved as draft.', 'success');
        } catch (\Throwable $e) {
            $this->setMessage('Failed to save draft: '.$e->getMessage(), 'error');
        }
    }

    public function saveAndPost(): void
    {
        $this->postResults('Results saved and posted successfully.');
    }

    public function postResults(?string $successMessage = null): void
    {
        try {
            app(GroupedResultsCaptureService::class)->postResults(
                $this->batch,
                $this->holder,
                $this->buildCellPayload(),
                $this->buildSampleCommentsPayload()
            );
            $this->loadMatrix();
            $this->loadPostingStatus();
            $this->commentsEditorKey++;
            $this->setMessage($successMessage ?? 'Results posted successfully.', 'success');
        } catch (\Throwable $e) {
            $this->setMessage('Failed to post results: '.$e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.worksheets.grouped-results-capture');
    }

    /**
     * @return array<string, array{result: string|null, reporting_symbol: string|null, remark: string|null}>
     */
    protected function buildCellPayload(): array
    {
        $payload = [];

        foreach ($this->cells as $rowCells) {
            foreach ($rowCells as $cell) {
                $payload[$cell['captured_result_id']] = [
                    'result' => $cell['result'] ?? null,
                    'reporting_symbol' => $cell['reporting_symbol'] ?? null,
                    'remark' => $cell['remark'] ?? null,
                ];
            }
        }

        return $payload;
    }

    /**
     * @return array<string, array{header_body: string, main_body: string, notes_body: string}>
     */
    protected function buildSampleCommentsPayload(): array
    {
        $payload = $this->sampleComments;

        foreach ($this->samples as $sample) {
            $sampleDetailId = $sample['sample_detail_id'] ?? null;
            if (! $sampleDetailId) {
                continue;
            }

            if (! isset($payload[$sampleDetailId])) {
                $payload[$sampleDetailId] = [
                    'header_body' => '',
                    'main_body' => '',
                    'notes_body' => '',
                ];
            }
        }

        return $payload;
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
