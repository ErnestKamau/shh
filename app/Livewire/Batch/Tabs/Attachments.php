<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\SampleDetails;
use App\CapturedResult;
use App\BatchAttachment;
use App\Models\System\SystemConfiguration;
use App\Models\RequestWorkflowForm;
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

    // Case File Review Form state
    public bool $showCaseFileModal = false;
    public array $caseFileForm = [];

    // GCLA 02 Form Language Selection Modal
    public bool $showGclaLanguageModal = false;

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

    public function getReportAttachmentsProperty()
    {
        $reportTitles = ['Certificate of Analysis', 'Analysis Report', 'Case File', 'COA', 'GCLA 02'];
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
        
        if (!$hasCaseFileAttachment) {
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
            'SRO',
            'Disclaimer'
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
        $caseFile = \App\Models\CaseFileReviewForm::where('batch_id', $this->batch->id)->first();
        
        if ($caseFile) {
            $this->caseFileForm = $caseFile->toArray();
        } else {
            // Default initialization
            $this->caseFileForm = [
                'batch_id' => $this->batch->id,
                'lab_no' => $this->batch->batch_code,
                'client' => $this->batch->client->name ?? '',
                'no_of_samples' => $this->batch->sample_details()->count(),
                'date_in' => now()->format('Y-m-d'),
                
                // Booleans defaults
                'sample_condition_sealed' => false,
                'sample_condition_labelled' => false,
                'screening_sample_type_blood' => false,
                'screening_sample_type_object_with_blood' => false,
                'screening_sample_type_semen' => false,
                'screening_sample_type_object_with_semen' => false,
                'extraction_method_chelex' => false,
                'extraction_method_prepfiler' => false,
                'quantification_no_of_cycles_40' => false,
                'quantification_kit_used_quant_trio' => false,
                'pcr_no_of_cycles_28' => false,
                'pcr_no_of_cycles_29' => false,
                'pcr_no_of_cycles_30' => false,
                'pcr_no_of_cycles_32' => false,
                'pcr_kit_used_identifiler_plus' => false,
                'pcr_kit_used_globalfiler' => false,
                'pcr_kit_used_yfiler_plus' => false,
                'injection_instrument_3500' => false,
                'reporting_reviewed' => false,
                'reporting_corrected' => false,
                'reporting_attachment_real_time_data' => false,
                'reporting_attachment_converge' => false,
                'reporting_attachment_statistical_analysis' => false,
                'manager_review_technical' => false,
                'manager_review_administrative' => false,
                'manager_comments_verified' => false,
                'manager_comments_not_verified' => false,
            ];
        }

        $this->showCaseFileModal = true;
    }

    public function closeCaseFileModal()
    {
        $this->showCaseFileModal = false;
        $this->caseFileForm = [];
    }

    public function saveCaseFile()
    {
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
        
        // This alerts the browser if needed, or simply re-renders
        return redirect()->route('view-case-file-pdf', \App\Models\CaseFileReviewForm::where('batch_id', $this->batch->id)->value('id'));
    }

    public function openGclaLanguageModal()
    {
        $this->showGclaLanguageModal = true;
    }

    public function closeGclaLanguageModal()
    {
        $this->showGclaLanguageModal = false;
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

    public function generateGCLA02Form($language)
    {
        $this->closeGclaLanguageModal();

        $batch = SampleHeader::with(['customer', 'sample_type'])->find($this->batch->id);

        $samplesData = [];
        $samples = SampleDetails::where('sample_header_id', $batch->id)->get();
        $allCapturedResults = CapturedResult::where('captured_results.sample_header_id', $batch->id)
                                ->join('analytes', 'analytes.id', '=', 'captured_results.analyte_id')
                                ->leftJoin('analysis_methods', 'analysis_methods.id', '=', 'captured_results.method_id')
                                ->leftJoin('reporting_units', 'reporting_units.id', '=', 'captured_results.reporting_unit_id')
                                ->select('captured_results.*', 'analytes.name as analyte_name', 'analysis_methods.name as method_name', 'reporting_units.name as unit_name')
                                ->get()
                                ->groupBy('sample_detail_id');

        $testsRequested = [];
        $sampleDescParts = [];

        foreach ($samples as $sample) {
            $sampleResults = $allCapturedResults->get($sample->id, collect());
            
            $mappedResults = $sampleResults->map(function($res) use (&$testsRequested) {
                if ($res->analyte_name) {
                    $testsRequested[] = $res->analyte_name;
                }
                return [
                    'analyte' => $res->analyte_name ?? $res->analyte_code,
                    'value' => $res->result ?? 'N/A',
                    'unit' => $res->unit_name ?? '',
                    'method' => $res->method_name ?? 'N/A'
                ];
            })->toArray();

            $desc = $sample->comments ?? $sample->sample_condition_name ?? 'N/A';
            if (!in_array($desc, $sampleDescParts) && $desc !== 'N/A') {
                $sampleDescParts[] = $desc;
            }

            $samplesData[] = [
                'sample_code' => $sample->sample_code,
                'appearance' => $desc,
                'results' => $mappedResults
            ];
        }

        $testsRequestedStr = !empty($testsRequested) ? implode(', ', array_unique($testsRequested)) : 'N/A';
        $sampleDescStr = !empty($sampleDescParts) ? implode(', ', $sampleDescParts) : 'N/A';
        
        $customer = $batch->customer;
        
        // Approvers
        $analystData = null;
        $verifierData = null;
        $approverData = null;
        
        $approvers = \App\BatchLabSectionApprover::where('batch_id', $batch->id)->get();
        
        foreach ($approvers as $appr) {
            $apprUser = $appr->getApproverDetails();
            if ($apprUser) {
                $sig = null;
                if ($apprUser->signature && file_exists(public_path($apprUser->signature))) {
                    $sig = $this->resolveImageAsDataUri(public_path($apprUser->signature));
                }
                
                $data = [
                    'name' => $apprUser->name,
                    'signature' => $sig,
                    'title' => $apprUser->designation ?? ''
                ];
                
                if (strtolower($appr->batch_status) === 'sample verification' || $appr->is_approver == 1) {
                    $verifierData = $data;
                } else if (strtolower($appr->batch_status) === 'sample approval') {
                    $approverData = $data;
                } else {
                    $analystData = $data;
                }
            }
        }

        // If no analyst found via approvers table, try to get from user who completed analysis
        if (!$analystData) {
            $user = Auth::user();
            $sig = null;
            if ($user->signature && file_exists(public_path($user->signature))) {
                $sig = $this->resolveImageAsDataUri(public_path($user->signature));
            }
            $analystData = [
                'name' => $user->name,
                'signature' => $sig,
                'title' => $user->designation ?? ''
            ];
        }

        // Get comments from ReportHeaderDetail if exists
        $reportDetail = \App\ReportHeaderDetail::where('sample_header_id', $batch->id)->first();
        $comments = $reportDetail ? $reportDetail->main_body : '';

        // Logos
        $gclaLogoPath = base_path('gclalogo.png');
        $gclaLogo = file_exists($gclaLogoPath) ? $this->resolveImageAsDataUri($gclaLogoPath) : null;
        
        $coatOfArmsPath = base_path('tanzanialogo.jpeg');
        $coatOfArms = file_exists($coatOfArmsPath) ? $this->resolveImageAsDataUri($coatOfArmsPath) : null;

        $data = [
            'language' => $language,
            'batch' => $batch,
            'customer' => $customer,
            'samples_data' => $samplesData,
            'processing_date' => date('d/m/Y'),
            'receipt_date' => $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : '',
            'tests_requested' => $testsRequestedStr,
            'sample_description' => $sampleDescStr,
            'nb_notes' => '',
            'comments' => $comments,
            'analyst' => $analystData,
            'verifier' => $verifierData,
            'approver' => $approverData,
            'gcla_logo' => $gclaLogo,
            'coat_of_arms' => $coatOfArms
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('batch.attachments.gcla-02-form-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $customer_name = preg_replace('/[^A-Za-z0-9]/', '', $customer->name ?? 'Client');
        $batch_code = preg_replace('/[^A-Za-z0-9]/', '', $batch->batch_code);
        $filename = 'GCLA02-' . $customer_name . '-' . $batch_code . '-' . date("d-M-Y-H-i-s") . '.pdf';

        $storagePath = 'reports/' . $customer_name;
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($storagePath)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($storagePath);
        }

        $fullPath = $storagePath . '/' . $filename;
        \Illuminate\Support\Facades\Storage::disk('public')->put($fullPath, $pdf->output());

        // Create Attachment Record
        $attTypeConfig = \App\Models\System\SystemConfiguration::where('key', 'attachment_type')->where('value', 'Report')->first();

        $attachment = new BatchAttachment();
        $attachment->batch_id = $batch->id;
        $attachment->uploaded_by = Auth::id() ?? 1;
        $attachment->title = 'GCLA 02 Form (' . strtoupper($language) . ')';
        $attachment->attachment_type = $attTypeConfig ? $attTypeConfig->id : null;
        $attachment->attachment_url = '/storage/' . $fullPath;
        $attachment->is_internal = 0;
        $attachment->save();

        session()->flash('success', 'GCLA 02 Form generated successfully.');
        $this->dispatch('attachmentsUpdated');
    }
}

