<?php

namespace App\Livewire\Batch\Tabs;

use App\Livewire\Batch\Concerns\InteractsWithCaseFileReviewForm;
use App\SampleHeader;
use App\SampleDetails;
use App\CapturedResult;
use App\BatchAttachment;
use App\Models\System\SystemConfiguration;
use App\Models\RequestWorkflowForm;
use App\Services\Sampleworkflow\BatchWorkflowDocumentAttachmentService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class Attachments extends Component
{
    use InteractsWithCaseFileReviewForm;
    use WithPagination;

    protected ?bool $hasCapturedResultAttachmentColumn = null;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;
    public ?int $newAttachmentTypeId = null;
    public string $newAttachmentTypeName = '';
    public ?int $selectedAttachmentTypeId = null;

    // Case File Review Form state
    public bool $showCaseFileModal = false;
    public array $caseFileForm = [];

    protected $listeners = ['attachmentsUpdated' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
        $this->syncMissingWorkflowDocuments();
    }

    private function syncMissingWorkflowDocuments(): void
    {
        $requiredTitles = [
            BatchWorkflowDocumentAttachmentService::QUOTATION_TITLE,
            TestRequestFormPdfService::ATTACHMENT_TITLE,
        ];

        $existingTitles = BatchAttachment::query()
            ->where('batch_id', $this->batch->id)
            ->whereIn('title', $requiredTitles)
            ->pluck('title')
            ->all();

        if (count(array_intersect($requiredTitles, $existingTitles)) === count($requiredTitles)) {
            return;
        }

        if (empty($this->batch->quote_id) && empty($this->batch->submission_form_instance_id)) {
            return;
        }

        app(BatchWorkflowDocumentAttachmentService::class)
            ->attachForAcceptedBatch($this->batch, Auth::id() ? (string) Auth::id() : null);
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

        $configurationTypeId = app(\App\Services\System\AttachmentTypeResolver::class)
            ->resolveAttachmentConfigurationTypeId();
        if ($configurationTypeId === null) {
            session()->flash('error', 'Attachment Types configuration is not set up in System Configuration.');
            return;
        }

        $config = new SystemConfiguration();
        $config->key = 'attachment_type';
        $config->value = $name;
        $config->configuration_type_id = $configurationTypeId;
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

    public function syncWorkflowDocuments(): void
    {
        app(BatchWorkflowDocumentAttachmentService::class)
            ->attachForAcceptedBatch($this->batch, Auth::id() ? (string) Auth::id() : null);

        $this->dispatch('attachmentsUpdated');
        session()->flash('success', 'Workflow documents synced to this batch.');
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

    public function getAcceptanceFormProperty()
    {
        // Check for attached Acceptance Form via BatchAttachment
        $attachment = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', 'like', '%Acceptance%')
            ->first();

        if ($attachment) {
            return (object) [
                'id' => $attachment->id,
                'is_batch_attachment' => true,
                'submitted_at' => $attachment->created_at,
                'attachment_url' => $attachment->attachment_url,
            ];
        }

        // Fallback to workflow form
        $form = RequestWorkflowForm::where('sample_header_id', $this->batch->id)
            ->where('form_type', 'laboratory_analysis_acceptance')
            ->first();

        if ($form) {
            $url = $form->pdf_path ? (str_starts_with($form->pdf_path, 'http') ? $form->pdf_path : url('storage/' . $form->pdf_path)) : '#';
            return (object) [
                'id' => $form->id,
                'is_batch_attachment' => false,
                'submitted_at' => $form->created_at,
                'attachment_url' => $url,
            ];
        }

        $analysisForm = \App\Models\Sampleworkflow\AnalysisAcceptanceForm::where('sample_header_id', $this->batch->id)->first();
        if ($analysisForm) {
            return (object) [
                'id' => $analysisForm->id,
                'is_batch_attachment' => false,
                'submitted_at' => $analysisForm->created_at,
                'attachment_url' => route('view-acceptance-pdf', $analysisForm->id),
            ];
        }

        return null;
    }

    public function getRejectionFormProperty()
    {
        $attachment = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', 'like', '%Rejection%')
            ->first();

        if ($attachment) {
            return (object) [
                'id' => $attachment->id,
                'is_batch_attachment' => true,
                'submitted_at' => $attachment->created_at,
                'attachment_url' => $attachment->attachment_url,
            ];
        }

        $form = RequestWorkflowForm::where('sample_header_id', $this->batch->id)
            ->where('form_type', 'sample_rejection')
            ->first();

        if ($form) {
            $url = $form->pdf_path ? (str_starts_with($form->pdf_path, 'http') ? $form->pdf_path : url('storage/' . $form->pdf_path)) : '#';
            return (object) [
                'id' => $form->id,
                'is_batch_attachment' => false,
                'submitted_at' => $form->created_at,
                'attachment_url' => $url,
            ];
        }

        return null;
    }

    public function getReceiptNotificationProperty()
    {
        $attachment = BatchAttachment::where('batch_id', $this->batch->id)
            ->where(function ($q) {
                $q->where('title', 'like', '%Receipt Notification%')
                  ->orWhere('title', 'like', '%Sample Receipt%');
            })
            ->first();
            
        if ($attachment) return $attachment;
        
        $form = RequestWorkflowForm::where('sample_header_id', $this->batch->id)
            ->where('form_type', 'sample_receipt_notification')
            ->first();

        if ($form) {
            $url = $form->pdf_path ? (str_starts_with($form->pdf_path, 'http') ? $form->pdf_path : url('storage/' . $form->pdf_path)) : '#';
            return (object) [
                'id' => $form->id,
                'is_batch_attachment' => false,
                'created_at' => $form->created_at,
                'attachment_url' => $url,
            ];
        }

        $analysisForm = \App\Models\Sampleworkflow\AnalysisAcceptanceForm::where('sample_header_id', $this->batch->id)
            ->whereNotNull('receipt_notification_payload')
            ->first();
            
        if ($analysisForm) {
            return (object) [
                'id' => $analysisForm->id,
                'is_batch_attachment' => false,
                'created_at' => $analysisForm->created_at,
                'attachment_url' => route('view-receipt-notification-pdf', $analysisForm->id),
            ];
        }
        
        return null;
    }

    public function getCustomerAttachmentsProperty()
    {
        return $this->batch->submissionFormInstance ? $this->batch->submissionFormInstance->customAttachments : collect();
    }

    public function getQuotationDocumentProperty()
    {
        $documentService = app(BatchWorkflowDocumentAttachmentService::class);

        $attachment = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', BatchWorkflowDocumentAttachmentService::QUOTATION_TITLE)
            ->orderByDesc('created_at')
            ->first();

        $quotation = $documentService->resolveQuotationForBatch($this->batch);

        if ($attachment) {
            $attachmentUrl = $attachment->attachment_url;
            if ($quotation !== null && $this->isLegacyQuotationStorageUrl($attachmentUrl)) {
                $attachmentUrl = $documentService->resolveQuotationPublicUrl($quotation, $this->batch);
            }

            return (object) [
                'id' => $attachment->id,
                'title' => $attachment->title,
                'submitted_at' => $attachment->created_at,
                'attachment_url' => $attachmentUrl,
            ];
        }

        if ($quotation === null) {
            return null;
        }

        return (object) [
            'id' => $quotation->id,
            'title' => BatchWorkflowDocumentAttachmentService::QUOTATION_TITLE,
            'submitted_at' => $quotation->updated_at ?? $quotation->created_at,
            'attachment_url' => $documentService->resolveQuotationPublicUrl($quotation, $this->batch),
            'quote_number' => $quotation->quote_number,
        ];
    }

    private function isLegacyQuotationStorageUrl(?string $url): bool
    {
        $url = trim((string) $url);

        return $url !== '' && str_starts_with($url, '/quotations/');
    }

    public function getTestRequestFormDocumentProperty()
    {
        $attachment = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', TestRequestFormPdfService::ATTACHMENT_TITLE)
            ->orderByDesc('created_at')
            ->first();

        if ($attachment) {
            return (object) [
                'id' => $attachment->id,
                'title' => $attachment->title,
                'submitted_at' => $attachment->created_at,
                'attachment_url' => $attachment->attachment_url,
            ];
        }

        $instance = app(BatchWorkflowDocumentAttachmentService::class)->resolveSubmissionFormInstanceForBatch($this->batch);
        if ($instance === null) {
            return null;
        }

        $storagePath = app(TestRequestFormPdfService::class)->resolveStoragePath($instance);
        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($storagePath)) {
            return (object) [
                'id' => $instance->id,
                'title' => TestRequestFormPdfService::ATTACHMENT_TITLE,
                'submitted_at' => $instance->updated_at ?? $instance->created_at,
                'attachment_url' => route('submission-forms.trf-pdf', [
                    'submissionForm' => $instance->submission_form_id,
                    'instance' => $instance->id,
                ]),
                'form_number' => $instance->form_number,
            ];
        }

        return (object) [
            'id' => $instance->id,
            'title' => TestRequestFormPdfService::ATTACHMENT_TITLE,
            'submitted_at' => $instance->updated_at ?? $instance->created_at,
            'attachment_url' => app(TestRequestFormPdfService::class)->resolvePublicUrl($instance),
            'form_number' => $instance->form_number,
        ];
    }

    public function getReportAttachmentsProperty()
    {
        $reportTitles = ['Certificate of Analysis', 'Analysis Report', 'Case File', 'COA', 'GCLA 02', 'DCEA 009'];
        $reports = $this->attachments->filter(function($a) use ($reportTitles) {
            $name = str_replace('_', ' ', strtolower($a->title));
            $type = str_replace('_', ' ', strtolower($a->attachtypename ?? ''));
            foreach($reportTitles as $title) {
                if (stripos($name, strtolower($title)) !== false || stripos($type, strtolower($title)) !== false) return true;
            }
            return false;
        })->values();

        // Check if there is an existing CaseFileReviewForm
        $hasCaseFileAttachment = $reports->contains(function($a) {
            return stripos(str_replace('_', ' ', strtolower($a->title)), 'case file') !== false;
        });
        
        if (!$hasCaseFileAttachment && $this->batch->hasDnaLab()) {
            $caseForm = \App\Models\CaseFileReviewForm::where('batch_id', $this->batch->id)->first();
            if ($caseForm) {
                $reports->push((object)[
                    'id' => 'cf_' . $caseForm->id,
                    'title' => 'Case File Review Form',
                    'attachtypename' => 'Case File',
                    'created_at' => $caseForm->created_at ?? now(),
                    'uploaduser' => 'System',
                    'attachment_url' => route('view-case-file-pdf', $caseForm->id),
                    'is_mock' => true,
                ]);
            }
        }

        return $reports;
    }

    public function getSampleAttachmentsProperty()
    {
        $excludedTitles = [
            'Receipt Notification', 
            'Sample Receipt',
            'Certificate of Analysis', 
            'Analysis Report', 
            'Case File', 
            'Acceptance Form', 
            'Sample Rejection', 
            'COA',
            'GCLA 02',
            'DCEA 009',
            'SRO',
            'Disclaimer',
            'Quotation',
            'Test Request Form',
        ];
        return $this->attachments->filter(function($a) use ($excludedTitles) {
            $name = str_replace('_', ' ', strtolower($a->title));
            $type = str_replace('_', ' ', strtolower($a->attachtypename ?? ''));
            foreach($excludedTitles as $title) {
                if (stripos($name, strtolower($title)) !== false || stripos($type, strtolower($title)) !== false) return false;
            }
            return true;
        });
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.attachments', [
            'attachments'                   => $this->attachments,
            'attachmentTypes'               => $this->attachmentTypes,
            'showSamplesWithResultsSection' => $this->showSamplesWithResultsSection,
            'selectedAttachmentTypeId'      => $this->selectedAttachmentTypeId,
            'samplesWithResults'            => $this->samplesWithResults,
            'acceptanceForm'                => $this->acceptanceForm,
            'rejectionForm'                 => $this->rejectionForm,
            'receiptNotification'           => $this->receiptNotification,
            'quotationDocument'             => $this->quotationDocument,
            'testRequestFormDocument'       => $this->testRequestFormDocument,
            'customerAttachments'           => $this->customerAttachments,
            'reportAttachments'             => $this->reportAttachments,
            'sampleAttachments'             => $this->sampleAttachments,
        ]);
    }

    protected function hasCapturedResultAttachmentColumn(): bool
    {
        if ($this->hasCapturedResultAttachmentColumn === null) {
            $this->hasCapturedResultAttachmentColumn = Schema::hasColumn('captured_results', 'batch_attachment_id');
        }

        return $this->hasCapturedResultAttachmentColumn;
    }

    public function openCaseFileModal()
    {
        if (!$this->batch->hasDnaLab()) {
            session()->flash('error', 'Case File Review Form is only available for DNA laboratories.');
            return;
        }

        $this->initializeCaseFileFormData();
        $this->showCaseFileModal = true;
    }

    public function closeCaseFileModal()
    {
        $this->showCaseFileModal = false;
        $this->caseFileForm = [];
    }

    public function saveCaseFile()
    {
        if (!$this->batch->hasDnaLab()) {
            session()->flash('error', 'Case File Review Form is only available for DNA laboratories.');
            return;
        }

        // Simple required validation, mostly boolean and nullable string fields so validation is light
        $this->validate([
            'caseFileForm.lab_no' => 'required',
        ]);

        $data = $this->caseFileForm;
        $data['batch_id'] = $this->batch->id;
        
        \App\Models\CaseFileReviewForm::updateOrCreate(
            ['batch_id' => $this->batch->id],
            $data
        );

        $this->closeCaseFileModal();
        
        // Use Livewire dispatch to notify success or just let it refresh
        $this->dispatch('attachmentsUpdated');
        
        $pdfId = \App\Models\CaseFileReviewForm::where('batch_id', $this->batch->id)->value('id');
        $this->dispatch('open-new-tab', url: route('view-case-file-pdf', $pdfId));
    }

    protected function assignCaseFileFormArray(array $data): void
    {
        $this->caseFileForm = $data;
    }

    protected function caseFileFormArray(): array
    {
        return $this->caseFileForm;
    }

    public function regenerateAcceptanceForm()
    {
        // 1. Check if there is an AnalysisAcceptanceForm first
        $analysisForm = \App\Models\Sampleworkflow\AnalysisAcceptanceForm::where('sample_header_id', $this->batch->id)->first();
        if ($analysisForm) {
            $pdfService = app(\App\Services\Sampleworkflow\AcceptanceFormPdfService::class);
            $url = $pdfService->generatePdfAndStoreAttachment($analysisForm);
            
            // If the attachment is already in BatchAttachment, update its URL just in case
            $attachment = BatchAttachment::where('batch_id', $this->batch->id)
                ->where('title', 'like', '%Acceptance%')
                ->first();
            if ($attachment && $url) {
                $attachment->attachment_url = $url;
                $attachment->save();
            }
            
            session()->flash('success', 'Laboratory Analysis Acceptance Form PDF regenerated successfully!');
            $this->dispatch('attachmentsUpdated');
            return;
        }

        // 2. If no AnalysisAcceptanceForm exists, but a draft json exists in public storage
        $draftPath = 'batch-attachments/laboratory-analysis-acceptance-batch-' . $this->batch->id . '.json';
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($draftPath)) {
            $decoded = json_decode((string) \Illuminate\Support\Facades\Storage::disk('public')->get($draftPath), true);
            if (is_array($decoded)) {
                $form = $decoded['form'] ?? [];
                $parameters = $decoded['parameters'] ?? [];
                
                $pdfFilename = 'laboratory-analysis-acceptance-batch-' . $this->batch->id . '.pdf';
                $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
                $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);

                $acceptedParameters = array_values(array_filter($parameters, static fn ($row) => (bool) ($row['selected'] ?? false)));
                $rejectedParameters = array_values(array_filter($parameters, static fn ($row) => ! ((bool) ($row['selected'] ?? false))));
                $acceptedTotal = round(array_sum(array_map(static fn ($row) => (float) ($row['price'] ?? 0), $acceptedParameters)), 2);

                $pdf = app('dompdf.wrapper');
                $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
                
                $logoSrc = $this->resolveLogoAsDataUri();

                $pdf->loadView('batch.attachments.laboratory-analysis-acceptance-pdf', [
                    'batch' => $this->batch,
                    'form' => $form,
                    'requestedParameters' => $parameters,
                    'acceptedParameters' => $acceptedParameters,
                    'rejectedParameters' => $rejectedParameters,
                    'acceptedTotal' => $acceptedTotal,
                    'logoSrc' => $logoSrc,
                ]);

                \Illuminate\Support\Facades\Storage::disk('public')->put($pdfStoragePath, $pdf->output());

                $attachmentTypeId = app(\App\Services\System\AttachmentTypeResolver::class)
                    ->resolveOrCreateAttachmentTypeId('Laboratory Analysis Acceptance Form');
                $title = 'Laboratory Analysis Acceptance Form';

                $attachment = BatchAttachment::where('batch_id', $this->batch->id)
                    ->where('title', $title)
                    ->orderByDesc('created_at')
                    ->first();

                if (! $attachment) {
                    $attachment = new BatchAttachment();
                    $attachment->batch_id = $this->batch->id;
                    $attachment->uploaded_by = Auth::id();
                    $attachment->title = $title;
                    $attachment->is_internal = 0;
                    $attachment->show_on_coa = 0;
                }

                $attachment->attachment_type = $attachmentTypeId;
                $attachment->attachment_url = $pdfPublicUrl;
                $attachment->save();

                session()->flash('success', 'Laboratory Analysis Acceptance Form PDF regenerated successfully!');
                $this->dispatch('attachmentsUpdated');
                return;
            }
        }

        session()->flash('error', 'Could not find source form data to regenerate PDF.');
    }

    public function regenerateReceiptNotification()
    {
        $service = app(\App\Services\Sampleworkflow\SampleReceiptNotificationService::class);
        $acceptance = $service->findAcceptanceFormForBatch($this->batch);
        
        if ($acceptance instanceof \App\Models\Sampleworkflow\AnalysisAcceptanceForm && $acceptance->receipt_notification_payload) {
            $formData = is_array($acceptance->receipt_notification_payload)
                ? $acceptance->receipt_notification_payload
                : (json_decode((string) $acceptance->receipt_notification_payload, true) ?: []);
            $service->generatePdfAndStoreAttachment($this->batch, $formData, Auth::id());
            
            session()->flash('success', 'Sample Receipt Notification PDF regenerated successfully!');
            $this->dispatch('attachmentsUpdated');
            return;
        }

        // Try fallback payload from system
        $formData = $service->resolveFormStateForBatch($this->batch);
        if (!empty($formData)) {
            $service->generatePdfAndStoreAttachment($this->batch, $formData, Auth::id());
            session()->flash('success', 'Sample Receipt Notification PDF regenerated successfully!');
            $this->dispatch('attachmentsUpdated');
            return;
        }

        session()->flash('error', 'Could not resolve Receipt Notification data to regenerate PDF.');
    }

    private function resolveLogoAsDataUri(): string
    {
        $company = getActiveCompany();

        if ($company && !empty($company->logo)) {
            $path = $company->logo;

            // Strip URL prefix if stored as a full URL
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $path = parse_url($path, PHP_URL_PATH) ?? $path;
            }
            $path = ltrim($path, '/');
            $filename = basename($path);

            if ($filename !== '') {
                // 1) Public storage disk (storage/app/public/...)
                $relative = preg_replace('#^storage/#', '', $path);
                if ($relative !== $path) {
                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
                    if (file_exists($fullPath)) {
                        return $this->imagePathToDataUri($fullPath);
                    }
                }

                // 2) App convention: storage/app/companies/<filename>
                $fullPath = storage_path('app/companies/' . $filename);
                if (file_exists($fullPath)) {
                    return $this->imagePathToDataUri($fullPath);
                }

                // 3) The company logo field may also be a public/ relative path
                if (file_exists(public_path($path))) {
                    return $this->imagePathToDataUri(public_path($path));
                }

                // 4) public/ ltrim fallback
                if (file_exists(public_path(ltrim($path, '/')))) {
                    return $this->imagePathToDataUri(public_path(ltrim($path, '/')));
                }
            }
        }

        // Fallback: default logo
        $defaultLogo = public_path('images/logo.png');
        if (file_exists($defaultLogo)) {
            return $this->imagePathToDataUri($defaultLogo);
        }

        $defaultReportLogo = public_path('images/logo-report.png');
        if (file_exists($defaultReportLogo)) {
            return $this->imagePathToDataUri($defaultReportLogo);
        }

        return '';
    }

    private function imagePathToDataUri(string $absolutePath): string
    {
        if ($absolutePath === '' || !is_readable($absolutePath)) {
            return '';
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return '';
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'        => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'        => 'image/gif',
            'webp'       => 'image/webp',
            'svg'        => 'image/svg+xml',
            default      => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function resolveImageAsDataUri(string $absolutePath): string
    {
        if ($absolutePath === '' || !is_readable($absolutePath)) {
            return $absolutePath;
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return $absolutePath;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'        => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'        => 'image/gif',
            'webp'       => 'image/webp',
            'svg'        => 'image/svg+xml',
            default      => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

}

