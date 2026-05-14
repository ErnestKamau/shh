<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\SampleDetails;
use App\CapturedResult;
use App\BatchAttachment;
use App\Models\System\SystemConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class Attachments extends Component
{
    use WithPagination;

    protected ?bool $hasCapturedResultAttachmentColumn = null;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;
    public ?int $newAttachmentTypeId = null;
    public string $newAttachmentTypeName = '';
    public ?int $selectedAttachmentTypeId = null;

    protected $listeners = ['attachmentsUpdated' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getAttachmentsProperty()
    {
        $query = BatchAttachment::where('batch_id', $this->batch->id)
            ->with('annotations');

        if (Auth::user()->is_client == 1) {
            $query->where('is_internal', 0);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('uploader', function ($uploader) {
                        $uploader->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getAttachmentTypesProperty()
    {
        return SystemConfiguration::where('key', 'attachment_type')->get();
    }

    /**
     * Whether to show the "With Samples (Captured Results)" section.
     * True only when the selected attachment type value is exactly "Result Report".
     */
    public function getShowSamplesWithResultsSectionProperty(): bool
    {
        if (! $this->selectedAttachmentTypeId) {
            return false;
        }

        $config = SystemConfiguration::find($this->selectedAttachmentTypeId);

        if (! $config || ! is_string($config->value)) {
            return false;
        }

        return strtolower(trim($config->value)) === 'result report';
    }

    /**
     * Returns samples for this batch that have captured results (with a result value),
     * each with their results grouped by analyte_id.
     *
     * Shape per element:
     *   [
     *     'id'                 => int,
     *     'sample_code'        => string,
     *     'results_by_analyte' => [
     *       [
     *         'analyte_id'          => int,
     *         'analyte_code'        => string,
     *         'analyte_name'        => string,
     *         'captured_result_ids' => int[],
     *         'results'             => [['id'=>int,'result'=>mixed,'batch_attachment_id'=>int|null], ...],
     *       ],
     *       ...
     *     ],
     *   ]
     */
    public function getSamplesWithResultsProperty()
    {
        $hasAttachmentColumn = $this->hasCapturedResultAttachmentColumn();

        $samples = SampleDetails::where('sample_header_id', $this->batch->id)
            ->orderBy('id', 'asc')
            ->get();

        return $samples->map(function ($sample) use ($hasAttachmentColumn) {
            $query = CapturedResult::where('captured_results.sample_detail_id', $sample->id)
                ->where('captured_results.sample_header_id', $this->batch->id)
                ->whereNotNull('captured_results.result')
                ->join('analytes', 'analytes.id', '=', 'captured_results.analyte_id')
                ->orderBy('analytes.id', 'asc');

            if ($hasAttachmentColumn) {
                $query->selectRaw(
                    'captured_results.id,
                     captured_results.analyte_id,
                     captured_results.result,
                     captured_results.analyte_code,
                     captured_results.batch_attachment_id,
                     analytes.name as analyte_name'
                );
            } else {
                $query->selectRaw(
                    'captured_results.id,
                     captured_results.analyte_id,
                     captured_results.result,
                     captured_results.analyte_code,
                     null as batch_attachment_id,
                     analytes.name as analyte_name'
                );
            }

            $capturedResults = $query->get();

            $byAnalyte = $capturedResults->groupBy('analyte_id')->map(function ($group) {
                $first = $group->first();
                return [
                    'analyte_id'          => $first->analyte_id,
                    'analyte_code'        => $first->analyte_code,
                    'analyte_name'        => $first->analyte_name,
                    'captured_result_ids' => $group->pluck('id')->toArray(),
                    'results'             => $group->map(fn($r) => [
                        'id'                  => $r->id,
                        'result'              => $r->result,
                        'batch_attachment_id' => $r->batch_attachment_id,
                    ])->values()->toArray(),
                ];
            })->values()->toArray();

            return [
                'id'                 => $sample->id,
                'sample_code'        => $sample->sample_code,
                'results_by_analyte' => $byAnalyte,
            ];
        })->filter(fn($s) => count($s['results_by_analyte']) > 0)->values();
    }

    public function updatedSelectedAttachmentTypeId($value): void
    {
        Log::info('Attachments: selectedAttachmentTypeId updated', [
            'batch_id'                 => $this->batch->id,
            'selectedAttachmentTypeId' => $value,
        ]);
    }

    public function saveAttachmentType(): void
    {
        $name = trim($this->newAttachmentTypeName);

        if ($name === '') {
            session()->flash('error', 'Please enter a name for the attachment type.');
            return;
        }

        if (SystemConfiguration::where('key', 'attachment_type')->where('value', $name)->exists()) {
            session()->flash('error', 'Attachment Type already exists.');
            return;
        }

        $configType = SystemConfiguration::where('key', 'attachment_type_config_id')->first();
        if (! $configType) {
            session()->flash('error', 'Attachment Type Config not found.');
            return;
        }

        $config = new SystemConfiguration();
        $config->key = 'attachment_type';
        $config->value = $name;
        $config->configuration_type_id = $configType->id;
        $config->save();

        $this->newAttachmentTypeId = $config->id;
        $this->newAttachmentTypeName = '';

        Log::info('Attachments Livewire: attachment type created', [
            'batch_id'       => $this->batch->id,
            'new_type_id'    => $config->id,
            'new_type_value' => $config->value,
        ]);

        $this->dispatch('attachmentTypeSaved');
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = BatchAttachment::find($attachmentId);
        if ($attachment) {
            if ($this->hasCapturedResultAttachmentColumn()) {
                $linkedCapturedIds = CapturedResult::where('batch_attachment_id', $attachment->id)
                    ->pluck('id')
                    ->toArray();

                // Detach captured results linked to this attachment to avoid stale foreign references.
                if (!empty($linkedCapturedIds)) {
                    CapturedResult::whereIn('id', $linkedCapturedIds)
                        ->update(['batch_attachment_id' => null]);
                }
                // Keep attachment-based placeholders consistent once detached.
                if (!empty($linkedCapturedIds)) {
                    CapturedResult::whereIn('id', $linkedCapturedIds)
                        ->whereNull('batch_attachment_id')
                        ->where('result', 'as attached')
                        ->update(['result' => 'No attachment']);
                }
            }

            $relativePath = urldecode($attachment->attachment_url);
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                $cleanPath = ltrim($relativePath, '/');
                if (strpos($cleanPath, 'storage/') === 0) {
                    $storageInternalPath = substr($cleanPath, 8);
                    $fallbackPath = storage_path('app/' . $storageInternalPath);
                    if (file_exists($fallbackPath)) {
                        $filePath = $fallbackPath;
                    }
                }
            }

            if (file_exists($filePath)) {
                try {
                    unlink($filePath);
                    Log::info("Deleted attachment file: {$filePath}");
                } catch (\Exception $e) {
                    Log::error("Failed to delete attachment file: {$filePath}. Error: " . $e->getMessage());
                }
            }

            $attachment->delete();
            $this->dispatch('attachmentsUpdated');
            session()->flash('success', 'Attachment deleted successfully!');
        } else {
            session()->flash('error', 'Attachment not found.');
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.attachments', [
            'attachments'                   => $this->attachments,
            'attachmentTypes'               => $this->attachmentTypes,
            'showSamplesWithResultsSection' => $this->showSamplesWithResultsSection,
            'selectedAttachmentTypeId'      => $this->selectedAttachmentTypeId,
            'samplesWithResults'            => $this->samplesWithResults,
        ]);
    }

    protected function hasCapturedResultAttachmentColumn(): bool
    {
        if ($this->hasCapturedResultAttachmentColumn === null) {
            $this->hasCapturedResultAttachmentColumn = Schema::hasColumn('captured_results', 'batch_attachment_id');
        }

        return $this->hasCapturedResultAttachmentColumn;
    }
}
