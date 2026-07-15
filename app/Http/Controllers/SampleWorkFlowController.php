<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesStageHeaderMethodSequences;
use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\BatchAmmendment;
use App\BatchAttachment;
use App\BatchComment;
use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\CapturedResultView;
use App\Company;
use App\Country;
use App\Http\Controllers\System\SystemNotifications;
use App\InterLabLog;
use App\InterLabLogView;
use App\InventorySubCategories;
use App\Invoice;
use App\InvoiceDetails;
use App\InvoicePaymentDetail;
use App\JobDescription;
use App\Lab;
use App\LabSectionApproverRelationShip;
use App\Models\CRM\CompanyProduct;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\Equipments\Equipment;
use App\Models\Lab\TatCaptured;
use App\Models\Lab\TatCapturedView;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\Configurations\QcTypes;
use App\Models\SampleSubmissionRequest;
use App\Models\RequestWorkflowForm;
use App\Models\SubmissionFormInstance;
use App\Models\SupportingDocumentInstance;
use App\Models\SupportingDocumentTemplate;
use App\Services\Billing\InvoiceNumberGenerator;
use App\Services\SupportingDocumentInstanceFormService;
use App\Services\SampleCreationService;
use App\Models\System\SystemConfiguration;
use App\Services\ResultRemarkService;
use App\Services\Qc\QcBatchCompletionService;
use App\Services\Sampleworkflow\CapturedResultCaptureService;
use App\Services\Sampleworkflow\ProcessedResultSyncService;
use App\Services\StandardLimitDisplayService;
use App\Services\SubmissionFormPdfService;
use App\ModulePreConfigs;
use App\Pricelist;
use App\PricelistCustomer;
use App\PricelistItem;
use App\QuotationDetails;
use App\Result;
use App\ReportingUnit;
use App\SampleAnalysisDates;
use App\SampleAnalysisStage;
use App\SampleAnalysisTypeRelation;
use App\SampleAnalysisTypeRelationView;
use App\SampleCondition;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use App\SamplesCategory;
use App\SampleType;
use App\SchoolContacts;
use App\StandardAnalytes;
use App\Standards;
use App\StandardValue;
use App\TaxRegime;
use App\User;
use App\ZohoCustomers;
use App\ZohoPricelist;
use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\BatchAttachmentAnnotation;
use setasign\Fpdi\TcpdfFpdi;
use Illuminate\Support\Facades\Log;
use App\Models\Procedures\ProcedureWorksheet;
use App\Services\ProcedureWorksheetPdfService;
use App\Http\Requests\StoreSampleSubmissionRequest;
use App\Services\WorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class SampleWorkFlowController extends Controller
{
    use HandlesStageHeaderMethodSequences;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(
        private readonly SupportingDocumentInstanceFormService $supportingDocumentInstanceFormService,
        private readonly SampleCreationService $sampleCreationService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request, $status = false)
    {
        if (!$status) {
            $status = getSampleWorflowStages()[0];
        }

        return view('livewire.layout.sample-workflow', [
            'status' => $status,
            'initialFilters' => $request->all(),
        ]);
    }

    public function submissionRequestsIndex(Request $request): \Illuminate\View\View
    {
        $tab = $request->get('tab', 'requests');
        $query = SampleSubmissionRequest::query()
            ->with([
                'batch:id,batch_code,status',
                'customer:id,name',
                'contact:id,first_name,middle_name,last_name,email',
                'currentQuotation:id,sample_submission_request_id,quote_number,status',
            ])
            ->withCount(['exhibits', 'suspects', 'requestedAnalyses'])
            ->orderByDesc('id');

        if (trim((string) $request->get('q', '')) !== '') {
            $search = trim((string) $request->get('q'));
            $query->where(function ($q) use ($search) {
                $q->where('case_no', 'like', "%{$search}%")
                    ->orWhere('offence', 'like', "%{$search}%")
                    ->orWhere('submitting_officer_full_name', 'like', "%{$search}%")
                    ->orWhere('submitting_agency', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tab filtering
        if ($tab === 'requests') {
            $query->whereIn('status', ['Submitted', 'Samples Reception', 'Samples Request Review']);
        } elseif ($tab === 'received') {
            $query->whereIn('status', ['Samples In Lab', 'Completed', 'Sample Approval', 'Sample Verification', 'Reports In Payment', 'Reports for Collection']);
        }

        // Calculate totals for status cards
        $totals = [
            'requested' => (clone $query)->where('status', 'Submitted')->count(),
            'portal' => (clone $query)->where('status', 'Portal Samples Submitted')->count(),
            'review' => (clone $query)->where('status', 'Samples Request Review')->count(),
            'delivery' => (clone $query)->where('status', 'Samples Reception')->count(),
        ];

        $requests = $query->paginate(15)->withQueryString();

        $customers = CRMCustomer::where('active', 1)->orderBy('name')->get();
        $contacts = collect();

        $supportingDocumentTemplates = SupportingDocumentTemplate::query()
            ->where('is_published', true)
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'document_code', 'title', 'subtitle', 'version', 'description']);

        return view('layouts.lab.sample-workflow.submission-requests.index', compact('requests', 'customers', 'contacts', 'supportingDocumentTemplates', 'tab', 'totals'));
    }

    public function createSampleSubmissionRequest(): \Illuminate\View\View
    {
        $customers = \App\Models\CRM\CRMCustomer::where('active', 1)->orderBy('name')->get();
        $contacts = \App\Models\CRM\CustomerContact::orderBy('first_name')->orderBy('last_name')->get();

        return view('layouts.lab.sample-workflow.submission-requests.create', compact('customers', 'contacts'));
    }

    public function showSampleSubmissionRequest(\App\Models\SampleSubmissionRequest $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        if ($request->isCommercialEnquiry() && ! $request->submission_form_instance_id) {
            app(\App\Services\Commercial\PortalEnquiryFormInstanceSyncService::class)->syncFromEnquiry($request);
            $request->refresh();
        }

        $request->load([
            'batch',
            'customer',
            'contact',
            'suspects',
            'exhibits',
            'requestedAnalyses',
            'supportingDocumentTemplates',
            'supportingDocumentInstances.template',
            'workflowForms',
            'submissionFormInstance.submissionForm',
        ]);

        $linkedInstance = $request->resolveLinkedFormInstance();

        if ($linkedInstance?->submissionForm) {
            return redirect()->route('submission-forms.instances.show', [
                $linkedInstance->submissionForm,
                $linkedInstance,
            ]);
        }

        if ($request->batch && ! request()->boolean('details')) {
            return redirect()->route('view-batch-details', [
                'batch' => $request->batch->id,
                'client' => 0,
                'portal' => 0,
                'status' => 'Samples En-Route',
            ]);
        }

        return view('layouts.lab.sample-workflow.submission-requests.show', [
            'request' => $request,
        ]);
    }

    public function approveSampleSubmissionBookingDate(\Illuminate\Http\Request $httpRequest, SampleSubmissionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $request->loadMissing('batch');

        if (! $request->batch) {
            return redirect()->back()->with('error', 'This submission request is not linked to a batch.');
        }

        $batch = $request->batch;
        $approvedDate = $batch->date_expected ?: now()->toDateString();

        $batch->date_expected = $approvedDate;
        if ($batch->status === 'Samples Request Review' || $batch->status === 'Samples Reception') {
            $batch->status = 'Samples En-Route';
        }
        $batch->save();

        $request->booking_date_status = 'approved';
        $request->booking_date_reviewed_at = now();
        $request->status = 'booking_date_approved';
        $request->save();

        return redirect()->back()->with('success', 'Lab booking date approved successfully.');
    }

    public function rescheduleSampleSubmissionBookingDate(\Illuminate\Http\Request $httpRequest, SampleSubmissionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $httpRequest->validate([
            'date_expected' => ['required', 'date'],
        ]);

        $request->loadMissing('batch');

        if (! $request->batch) {
            return redirect()->back()->with('error', 'This submission request is not linked to a batch.');
        }

        $batch = $request->batch;
        $batch->date_expected = $validated['date_expected'];
        if ($batch->status === 'Samples Request Review' || $batch->status === 'Samples Reception') {
            $batch->status = 'Samples En-Route';
        }
        $batch->save();

        $request->booking_date_status = 'rescheduled';
        $request->booking_date_reviewed_at = now();
        $request->status = 'booking_date_rescheduled';
        $request->save();

        return redirect()->back()->with('success', 'Lab booking date moved to the new date successfully.');
    }

    public function getSubmissionRequestCustomerContacts(int $customer): \Illuminate\Http\JsonResponse
    {
        $contacts = \App\Models\CRM\CustomerContact::query()
            ->where('crm_customer_id', $customer)
            ->where('active', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'email', 'job_occupation']);

        return response()->json($contacts);
    }

    public function storeSampleSubmissionRequest(StoreSampleSubmissionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        $submissionRequest = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            /** @var \App\Models\SampleSubmissionRequest $submissionRequest */
            $submissionRequest = SampleSubmissionRequest::create([
                'crm_customer_id' => $data['crm_customer_id'],
                'crm_contact_id' => $data['crm_contact_id'] ?? null,
                'submitting_agency' => $data['submitting_agency'] ?? null,
                'submitting_officer_full_name' => $data['submitting_officer_full_name'] ?? null,
                'submitting_officer_title' => $data['submitting_officer_title'] ?? null,
                'physical_address' => $data['physical_address'] ?? null,
                'region' => $data['region'] ?? null,
                'district' => $data['district'] ?? null,
                'working_station' => $data['working_station'] ?? null,
                'office_telephone_no' => $data['office_telephone_no'] ?? null,
                'mobile_telephone_no' => $data['mobile_telephone_no'] ?? null,
                'fax' => $data['fax'] ?? null,
                'email' => $data['email'] ?? null,
                'case_no' => $data['case_no'] ?? null,
                'offence' => $data['offence'] ?? null,
                'date_of_seizure' => $data['date_of_seizure'] ?? null,
                'seizure_region' => $data['seizure_region'] ?? null,
                'seizure_district' => $data['seizure_district'] ?? null,
                'seizure_ward' => $data['seizure_ward'] ?? null,
                'seizure_village_street' => $data['seizure_village_street'] ?? null,
                'submitted_by_full_name' => $data['submitted_by_full_name'] ?? null,
                'submitted_by_title' => $data['submitted_by_title'] ?? null,
                'submitted_by_signature' => $data['submitted_by_signature'] ?? null,
                'submitted_by_date' => $data['submitted_by_date'] ?? null,
                'submitted_by_time' => $data['submitted_by_time'] ?? null,
                'received_by_full_name' => $data['received_by_full_name'] ?? null,
                'received_by_title' => $data['received_by_title'] ?? null,
                'received_by_signature' => $data['received_by_signature'] ?? null,
                'received_by_date' => $data['received_by_date'] ?? null,
                'received_by_time' => $data['received_by_time'] ?? null,
                'status' => 'Submitted',
            ]);

            foreach (($data['exhibits'] ?? []) as $row) {
                $hasAnyValue = collect($row)->filter(function ($value) {
                    return $value !== null && $value !== '';
                })->isNotEmpty();

                if (!$hasAnyValue) {
                    continue;
                }

                $submissionRequest->exhibits()->create([
                    'serial_number' => $row['serial_number'] ?? null,
                    'number_of_items' => $row['number_of_items'] ?? null,
                    'item_description' => $row['item_description'] ?? null,
                    'suspected_item' => $row['suspected_item'] ?? null,
                ]);
            }

            foreach (($data['suspects'] ?? []) as $row) {
                $hasAnyValue = collect($row)->filter(function ($value) {
                    return $value !== null && $value !== '';
                })->isNotEmpty();

                if (!$hasAnyValue) {
                    continue;
                }

                $submissionRequest->suspects()->create([
                    'serial_number' => $row['serial_number'] ?? null,
                    'first_name' => $row['first_name'] ?? null,
                    'middle_name' => $row['middle_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'sex' => $row['sex'] ?? null,
                    'date_of_birth' => $row['date_of_birth'] ?? null,
                    'nationality' => $row['nationality'] ?? null,
                    'id_passport_number' => $row['id_passport_number'] ?? null,
                ]);
            }

            if (! empty($data['supporting_document_template_ids'])) {
                $submissionRequest->supportingDocumentTemplates()->sync($data['supporting_document_template_ids']);
                $headerId = (int) ($submissionRequest->sample_header_id ?? 0);

                foreach ($data['supporting_document_template_ids'] as $templateId) {
                    $templateId = (int) $templateId;
                    $template = SupportingDocumentTemplate::query()->whereKey($templateId)->first();

                    SupportingDocumentInstance::query()->firstOrCreate(
                        [
                            'sample_submission_request_id' => $submissionRequest->id,
                            'supporting_document_template_id' => $templateId,
                        ],
                        [
                            'template_version' => (int) ($template?->version ?? 1),
                            'sample_header_id' => $headerId,
                            'status' => 'draft',
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            } else {
                $submissionRequest->supportingDocumentTemplates()->sync([]);
            }

            return $submissionRequest;
        });

        return redirect()
            ->route('sample-submission-requests.show', ['request' => $submissionRequest, 'details' => 1])
            ->with('success', 'Submission request created successfully. Request #' . $submissionRequest->id);
    }

    public function editSampleSubmissionSupportingDocument(
        SampleSubmissionRequest $request,
        SupportingDocumentInstance $instance,
    ): \Illuminate\View\View {
        if ((int) $instance->sample_submission_request_id !== (int) $request->id) {
            abort(404);
        }

        $instance->load(['template.sections.elements', 'values']);

        return view('layouts.lab.sample-workflow.submission-requests.supporting-document-fill', [
            'submissionRequest' => $request,
            'instance' => $instance,
            'readOnly' => $instance->status === 'submitted',
        ]);
    }

    public function updateSampleSubmissionSupportingDocument(
        Request $httpRequest,
        SampleSubmissionRequest $request,
        SupportingDocumentInstance $instance,
    ): \Illuminate\Http\RedirectResponse {
        if ((int) $instance->sample_submission_request_id !== (int) $request->id) {
            abort(404);
        }

        if ($instance->status === 'submitted') {
            return redirect()
                ->route('sample-submission-requests.supporting-documents.edit', [$request, $instance])
                ->with('error', 'This supporting document has already been submitted and cannot be changed.');
        }

        $validated = $httpRequest->validate([
            'action' => ['required', 'in:draft,submit'],
            'values' => ['present', 'array'],
        ]);

        $instance->loadMissing(['template.sections.elements']);
        $elements = $instance->template->sections->flatMap(function ($section) {
            return $section->elements;
        });

        if ($validated['action'] === 'submit') {
            $httpRequest->validate($this->supportingDocumentInstanceFormService->buildSubmitRules($elements));
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($instance, $elements, $validated) {
            $this->supportingDocumentInstanceFormService->syncValuesFromInput(
                $instance,
                $elements,
                $validated['values']
            );

            if ($validated['action'] === 'submit') {
                $instance->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }
        });

        $message = $validated['action'] === 'submit'
            ? 'Supporting document submitted.'
            : 'Supporting document saved as draft.';

        return redirect()
            ->route('sample-submission-requests.show', ['request' => $request, 'details' => 1])
            ->with('success', $message);
    }

    public function print_labels(Request $request)
    {
        // return response()->json(getSampleWorkFLowTotals(), 200);
        $ids = (array) $request->sample_code;

        if (count($ids) == 0) {
            return redirect()->back()->with('error', 'No Samples selected');
        }

        $labels = [];

        foreach ($ids as $batch_code) {
            $batch = SampleHeader::with(['receivingofficer'])->where('batch_code', $batch_code)->first();
            if ($batch === null) {
                continue;
            }

            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';

            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
            if ($stage !== null) {
                $custodyDetails = [
                    'batch_id' => $batch->id,
                    'comments' => $request->comments ?? '',
                    'current' => [
                        'status' => $batch->status,
                        'tracking_stage' => $batch->sample_tracking_stage,
                    ],
                    'target' => [
                        'status' => $samWk,
                        'tracking_stage' => $stage->id,
                    ],
                ];
                $this->updateChainofCustody($custodyDetails);
            }

            $samples = $batch->all_samples();

            // echo json_encode($batch);
            // return;
            $client = $batch->client;

            // Get target date for the batch
            $targetDate = $batch->get_date('Target Date');
            $targetDateFormatted = $targetDate ? date('Y-m-d', strtotime($targetDate->date)) : 'N/A';

            foreach ($samples as $sample) {
                // return response()->json(SampleAnalysisTypeRelationView::where('sample_detail_id',$sample->id)->pluck('analysis_type_name')->toArray(),200);
                $analysis = $sample->analysis();
                $data = [];
                !isset($data['ref_no']) ? $data['ref_no'] = $sample->sample_code : $data;

                // Add client name from batch
                !isset($data['client_name']) ? $data['client_name'] = $client->name ?? 'N/A' : $data;

                // Add sample point name from sample detail
                $samplePoint = $sample->sample_point;
                !isset($data['sample_point']) ? $data['sample_point'] = (isset($samplePoint->name) ? $samplePoint->name : 'N/A') : $data;

                // Add sample code (separate from ref_no which is already there)
                !isset($data['sample_code']) ? $data['sample_code'] = $sample->sample_code : $data;

                // Add analysis types (using getAnalysisRelation method)
                !isset($data['analysis_types']) ? $data['analysis_types'] = $sample->getAnalysisRelation() ?: 'N/A' : $data;

                // Add target date
                !isset($data['target_date']) ? $data['target_date'] = $targetDateFormatted : $data;

                // Keep legacy fields for reference (commented out)
                // !isset($data['Markings']) ? $data['Markings']  = $sample->comments : $data;
                // !isset($data['date_received']) ? $data['date_received'] = $batch->receipt_date : $data;
                // !isset($data['test']) ? $data['test'] = implode(', ', $sample->analyteNames() ?? []) : $data;
                // !isset($data['received_by']) ? $data['received_by'] = $batch->receivingofficer->name : $data;
                // !isset($data['sample_type']) ? $data['sample_type'] = getSampleTypeByID($batch->sample_type_id)->name : $data;
                // !isset($data['time']) ? $data['time'] = $batch->radio_active_levels : $data;

                // !isset($data['Date Expected']) ? $data['Date Expected'] = date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : $data;
                // !isset($data['Disposal Date']) ? $data['Disposal Date'] = $sample->disposal_date : $data;


                $labels[] = $data;
            }
            // return response()->json($labels,200);
            if ($stage !== null) {
                $batch->sample_tracking_stage = $stage->id;
                $batch->save();
            }
        }

        // return response()->json($labels, 200);

        return view('layouts.lab.sample-workflow.labels', compact('labels'));
    }

    public function add_batch_info(Request $request, $batch)
    {
        Log::info(json_encode($request->all(), JSON_PRETTY_PRINT));
        if (isset($request->is_qc_batch)) {
            $qc_customer_id = SystemConfiguration::where('key', 'qc_customer_id')->first();
            if (!isset($qc_customer_id->id)) {
                return redirect()->back()->with('error', 'Kindly set company QC Customer first');
            }
            $selectedCustomer = CRMCustomer::find($qc_customer_id->value);
            if (isset($request->repeat_samples_id) && $request->repeat_samples_id != '') {
                $repeat_samples = SampleDetails::whereIn('id', $request->repeat_samples_id)->get();
            }
        } else {
            $selectedCustomer = CRMCustomer::find($request->crm_customer_id);
        }
        $selectedSampleType = SampleType::find($request->sample_type_id);
        $batch_config = SystemConfiguration::where('key', 'batch_code_config')->first();
        if (!isset($batch_config->id)) {
            return redirect()->back()->with('error', 'Kindly add batch_code_config configuration');
        }

        $cust_code = str_split($selectedCustomer->code);
        $code = [];
        $loop = 0;
        $cont = [];
        foreach ($cust_code as $cc) {
            if ((int) $cc > 0) {
                array_push($cont, $loop);
            } elseif (is_string($cc) && $cc != '0') {
                array_push($code, $cc);
            }
            ++$loop;
        }
        $tt = sizeof($cust_code) - 1;

        $ranges = range($cont[0], $tt);
        $values = [];
        if (sizeof($cont) < 2) {
            array_push($values, '0');
            array_push($values, $cust_code[$cont[0]]);
        } else {
            foreach ($ranges as $r) {
                array_push($values, $cust_code[$r]);
            }
        }

        $cP = 'BA' . $batch_config->value . implode('', $values) . $selectedSampleType->code;
        $isNew = false;
        $isInReception = false;
        $header = SampleHeader::find($batch) ?? new SampleHeader();
        if (!isset($header->batch_code)) {
            $isNew = true;
            $isInReception = true;
            // return response()->json($cP);
            $config_batch_no = SystemConfiguration::where('key', 'batch_start_no')->first();
            if (!isset($config_batch_no->id)) {
                return redirect()->back()->with('error', 'Kindly set the start batch no');
            }
            $last_id = isset(SampleHeader::latest('id')->first()->id) ? SampleHeader::latest('id')->first()->id : 0;
            $batch_no_s = $config_batch_no->value + $last_id + 1;
            $final_no = '';
            if (strlen(strval($batch_no_s)) < 4) {
                $zerosss = str_repeat('0', 4 - strlen(strval($batch_no_s)));
                $final_no = $zerosss . '' . strval($batch_no_s);
            } else {
                $final_no = strval($batch_no_s);
            }
            $header->batch_code = $cP . '' . $final_no;
        }

        if (isset($header->status) && in_array($header->status, ['Samples Reception', 'Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports for Collection', 'Reports In Payment', 'Completed'])) {
            $isInReception = true;
        }

        if (!isset($request->is_bl_save)) {
            $header->receipt_date = $request->receipt_date;
            $header->date_collected = $request->date_collected;
            $header->batch_scope = $request->batch_scope;
            if ($request->has('customer_survey')) {
                $header->customer_survey = $request->customer_survey;
            }
            $header->is_qc_batch = isset($request->is_qc_batch);
            $header->qc_type_id = $request->qc_type_id;
            $header->qc_scheme_id = $request->qc_scheme_id;
            $header->repeat_batch_id = isset($request->repeat_sample_id) ? $request->repeat_batch_id : 0;
            $header->repeat_sample_id = isset($request->repeat_sample_id) && $request->repeat_sample_id > 0 ? $request->repeat_sample_id : $header->repeat_sample_id;
            $header->begin_proccess = isset($request->is_qc_batch) ? 1 : 0;
            if ($request->has('quote_no')) {
                $header->quote_no = $request->quote_no;
            }
            $header->lab_capable = isset($request->lab_capable) ? 1 : 0;
            $header->can_be_subcontracted = isset($request->can_be_subcontracted) ? 1 : 0;
            $header->batch_subcontracted_client_approval = isset($request->batch_subcontracted_client_approval) ? 1 : 0;
            $header->client_instruction_clear = isset($request->client_instruction_clear) ? 1 : 0;
            $header->batch_instructions = $request->batch_instructions;
            $header->reason_for_submission = $request->reason_for_submission;
            $header->sampling_method_id = $request->sampling_method_id;
            $header->require_mu = $request->require_mu;
            $header->payment_done_by = $request->payment_done_by;
            $header->condition_quality_sample = $request->condition_quality_sample;
            // $header->invoice_amount = $request->invoice_amount;
            if ($request->filled('lab_id')) {
                $header->lab_id = $request->lab_id;
                $sectionIds = SampleAnalysisStage::query()
                    ->where('lab_id', $request->lab_id)
                    ->where('active', 1)
                    ->where('is_system', 0)
                    ->pluck('id')
                    ->all();
                $header->lab_section_ids = implode(',', $sectionIds);
            } else {
                $header->lab_section_ids = implode(',', $request->lab_section_ids ?? []);
            }
            if (strtolower((string) $request->batch_scope) === 'express') {
                $header->priority = 'Express';
            } elseif (strtolower((string) $request->batch_scope) === 'normal') {
                $header->priority = 'Normal';
            }
            $header->crm_contact_id = $request->crm_contact_id;
            $header->schedule_customer_email = $request->customer_email;

            if ($isInReception) {
                $header->sample_type_id = $request->sample_type_id;
                if (isset($request->is_qc_batch)) {
                    $qc_customer_id = SystemConfiguration::where('key', 'qc_customer_id')->first();
                    $qc_customer_unit = SystemConfiguration::where('key', 'qc_customer_unit')->first();

                    $header->crm_customer_id = $qc_customer_id->value;

                    $header->crm_unit_name = $qc_customer_unit->value;
                    $header->qc_type_id = $request->qc_type_id;
                    $header->qc_scheme_id = $request->qc_scheme_id;
                    $header->repeat_sample_id = implode(',', $request->repeat_samples_id) ?? '';
                } else {
                    $header->crm_customer_id = $request->crm_customer_id;

                    $header->crm_unit_id = $request->crm_unit_name;

                    $customer = getCrmCustomerByID($request->crm_customer_id);
                    $account = SystemConfiguration::find($customer->account_status);
                    if (isset($account->id)) {
                        $header->current_account_status = $account->key;
                        if ($account->key == 'Suspended') {
                            return redirect()->back()->with('error', 'The customer is currently suspended!');
                        }
                    } else {
                        $header->current_account_status = 'N/a';
                    }
                }
            }

            $header->description = $request->description;
            $header->document_number = $request->document_number;
            $header->importer_address = $request->importer_address;
            $header->date_expected = $request->date_expected;
            $header->quote_id = $request->quote_id;
            $header->sampling_method_id = $request->sampling_method_id;
            $header->radio_active_levels = $request->radio_active_levels;
            $header->receiving_officer_name = $request->receive_by;
            $header->receiving_officer = $request->receive_by;
            $header->sampling_officer_name = $request->sample_by;
            $header->reference_number = $request->reference_number ?? 'n/a';
            $header->is_routine = $request->is_routine ?? 0;
            $header->routine_frequency = isset($request->is_routine) ? $request->routine_frequency : 0;
            if ($isNew) {
                if ($request->has('is_client_order')) {
                    $header->status = 'Samples En-Route';
                } else {
                    $header->status = 'Samples Reception';
                }
            }
        }


        $header->sampling_method_id = $request->sampling_method_id;
        $header->submit_by = $request->submit_by;
        $header->radio_active_levels = $request->radio_active_levels;
        $header->kra_office_ref = $request->kra_office_ref;
        if ($request->has('use_of_goods')) {
            $header->use_of_goods = $request->use_of_goods;
        }
        // $header->how_sample_was_obtained = $request->how_sample_was_obtained;
        // $header->declared_commodity_code = $request->declared_commodity_code;
        // $header->declared_amount = $request->declared_amount ?? 0;
        // $header->net_quantity_and_unit_of_quantity = $request->net_quantity_and_unit_of_quantity;
        // $header->sample_appearance_description = $request->sample_appearance_description;
        // $header->kra_office_station = $request->kra_office_station;
        // $header->where_sample_was_obtained = $request->where_sample_was_obtained;
        // $header->importer_address = $request->importer_address;
        if (isset($request->sampled_by_company_personnel)) {
            $header->sampled_by_company_personnel = 1;
        } else {
            $header->sampled_by_company_personnel = 0;
        }
        if (isset($request->agreement)) {
            $header->user_agreement = 1;
        } else {
            $header->user_agreement = 0;
        }

        if ($isNew) {
            $stage = SampleAnalysisStage::where('sample_workflow', $header->status)->orderBy('level', 'asc')->first();
            $header->sample_tracking_stage = $stage->id ?? 0;
        }
        $header->save();

        Log::info("-------------------------------------");

        if (isset($request->repeat_samples_id) && $request->repeat_samples_id != '') {
            $sample_point = SystemConfiguration::where('key', 'qc_sample_point_id')->first();
            foreach ($repeat_samples as $old_sample) {
                $new_sample = $old_sample->replicate();
                $lab = Lab::find($old_sample->lab_id);
                $code = SampleDetails::orderBy('id', 'DESC')->first();
                $last_sample = isset($code->id) ? $code->sample_no : $lab->start_sample_no;

                // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                // return response()->json($request->sample_details['lab_id'][$k]);
                $sample_number = intval($last_sample) + 1;
                // $sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

                $sample_code = 'S' . date('Y') . $lab->code . $selectedSampleType->code . sprintf('%0' . '4' . 'd', $sample_number);
                $sample_no = sprintf('%0' . '4' . 'd', $sample_number);
                $report_number = 'LR/' . $selectedSampleType->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%0' . '4' . 'd', $sample_number);
                $new_sample->fill([
                    "sample_header_id" => $header->id,
                    "sample_code" => $sample_code,
                    "sample_no" => $sample_no,
                    "report_number" => $report_number,
                    "sample_point_id" => $sample_point->value,
                ]);
                $new_sample->save();
                $this->createDetailAnalysisRelation($header->id, $new_sample->id, explode(',', $new_sample->analysis_type_id));
                $captured_results = CapturedResult::where('sample_detail_id', $old_sample->id)->get();
                foreach ($captured_results as $c_result) {
                    $new_captured = $c_result->replicate();
                    $new_captured->fill([
                        "sample_detail_id" => $new_sample->id,
                        "sample_header_id" => $header->id,
                        "sample_detail_code" => $new_sample->sample_code,
                        "result" => null,
                        "remark" => null,
                        "repeat_captured_id" => $c_result->id,
                    ]);
                    $new_captured->save();
                    $result = Result::where('captured_result_id', $c_result->id)->first();
                    if (!$result) {
                        $result = new Result();
                        $result->captured_result_id = $c_result->id;
                        $result->sample_detail_code = $c_result->sample_detail_code;
                        $result->sample_detail_id = $c_result->sample_detail_id;
                        $result->sample_header_id = $c_result->sample_header_id;
                        $result->analyte_id = $c_result->analyte_id;
                        $result->analyte_code = $c_result->analyte_code;
                        $result->analysis_type_id = $c_result->analysis_type_id;
                        $result->lab_section_id = $c_result->lab_section_id;
                        $result->parameters_order = $c_result->parameters_order ?? 0;
                        $result->remark_is_manual = $c_result->remark_is_manual ?? false;
                        $result->has_no_result_capture = $c_result->has_no_result_capture ?? false;
                        $result->save();
                    }
                    $new_result = $result->replicate();
                    $new_result->fill([
                        "captured_result_id" => $new_captured->id,
                        "sample_detail_id" => $new_sample->id,
                        "sample_header_id" => $header->id,
                        "sample_detail_code" => $new_sample->sample_code,
                        "result" => null,
                        "remark" => null,
                        "repeat_results_id" => $result->id,
                    ]);
                    $new_result->save();
                }
            }
        }
        if (isset($request->is_qc_batch) && $request->repeat_samples_id != '') {
            $batch_a_types = SampleAnalysisTypeRelationView::where('batch_id', $header->id)->pluck('analysis_type_id')->toArray();
            $analysis_time = AnalysisType::whereIn('id', $batch_a_types)->max('reporting_time');
            $analytes_ids = CapturedResult::where('sample_header_id', $header->id)->pluck('analyte_id')->toArray();
            $element_time = AnalysisElements::whereIn('analysis_type_id', $batch_a_types)->whereIn('analyte_id', $analytes_ids)->max('reporting_time');
            $maxReportingTime = $analysis_time > $element_time ? $analysis_time : $element_time;
        } else {
            $maxReportingTime = 0;
        }
        $targetDateStr = 'Target Date';
        $targetDate = \App\SampleDate::where('sample_header_id', $header->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
        $targetDate->name = $targetDateStr;
        $targetDate->sample_header_id = $header->id;
        $targetDate->date = \Carbon\Carbon::parse($header->receipt_date)->addDays($maxReportingTime);
        $targetDate->save();

        if ($isNew) {
            $custodyDetails = [
                'batch_id' => $header->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $header->status,
                    'tracking_stage' => $header->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $header->status,
                    'tracking_stage' => $header->sample_tracking_stage,
                ],
            ];
            $this->updateChainofCustody($custodyDetails);
        }

        $route_obj = ['batch' => $header->id, 'client' => 0, 'portal' => 0, 'status' => $header->status];

        if ($request->has('is_client_order')) {
            $route_obj['client'] = $request->crm_customer_id;
        } else {
            // Set Login Date: use submission form instance creation date if available, otherwise use batch creation date
            $loginDate = $header->submissionFormInstance 
                ? $header->submissionFormInstance->created_at 
                : $header->created_at;
            $header->set_date('Login Date', $loginDate, true);
        }

        return redirect()->route('view-batch-details', $route_obj)->within('success', 'Batch Info added.');
    }

    public function add_batch_samples(Request $request, $batch)
    {
        // return response()->json($request->all(), 200);
        $SampleHeader = SampleHeader::with(['sample_type'])->find($batch);

        $selectedCustomer = CRMCustomer::find($SampleHeader->crm_customer_id);
        $selectedSampleType = SampleType::find($SampleHeader->sample_type_id);

        $configuration = SystemConfiguration::where('key', 'sample_code_naming')->first();
        if (isset($configuration->id)) {
            $codePrefix = $configuration->value . '-';
        } else {
            return redirect()->back()->with('error', 'Setup the naming convention of samples in system configurations');
        }

        $samplesRequiringStorage = ['samples' => [], 'store_ids' => []];

        // return response()->json($request->all(), 200);
        $maxReportingTime = 0;
        $currentAnalysisSample = [];
        $duplicate_samples = [];
        $duplicate_samples_ids = [];
        $duplicateDataSampleIds = [];

        foreach ($request->sample_details['sample_code'] as $k => $v) {
            $detailId = $request->sample_details['detail_header'][$k];

            $detail = $detailId && $detailId != 'undefined' ? SampleDetails::find($detailId) : new SampleDetails();
            $detail->sample_header_id = $SampleHeader->id;

            if (!isset($detail->sample_code)) {
                // $config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
                $lab = Lab::find($request->sample_details['lab_id'][$k]);
                $code = SampleDetails::orderBy('id', 'DESC')->first();
                $last_sample = isset($code->id) ? $code->sample_no : $lab->start_sample_no;

                // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                // return response()->json($request->sample_details['lab_id'][$k]);
                $sample_number = intval($last_sample) + 1;
                // $sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

                $detail->sample_code = 'S' . date('Y') . $lab->code . $SampleHeader->sample_type->code . sprintf('%0' . '4' . 'd', $sample_number);
                $detail->sample_no = sprintf('%0' . '4' . 'd', $sample_number);
                $detail->report_number = 'LR/' . $SampleHeader->sample_type->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%0' . '4' . 'd', $sample_number);
            }

            if (isset($detail->id) && $SampleHeader->status == 'Samples Reception') {
                $current_analysis = explode(',', $detail->analysis_type_id);
                $currentAnalysisSample[$detail->sample_code] = $current_analysis;
                $updated_analysis = $request->sample_details['sample_analysis'][$k];
                // return response()->json($updated_analysis);
                foreach ($current_analysis as $ca) {
                    if (!in_array($ca, $updated_analysis)) {
                        CapturedResult::where('sample_detail_id', $detail->id)->where('analysis_type_id', $ca)->delete();

                        Result::where('sample_detail_id', $detail->id)->where('analysis_type_id', $ca)->delete();
                    }
                }
            }
            if ($SampleHeader->status == 'Samples Reception') {
                $detail->analysis_type_id = implode(',', $request->sample_details['sample_analysis'][$k] ?? []);
                $detail->sample_condition_id = $request->sample_details['sample_condition'][$k];
                $detail->sample_point_id = $request->sample_details['sample_point'][$k];
                $detail->company_product_id = $request->sample_details['product'][$k];
                $detail->barcode = $request->sample_details['barcode'][$k];

                $detail->disposal_date = $request->sample_details['disposal_date'][$k];
                // $detail->lab_sub_no = $request->sample_details['submission_no'][$k];

                $detail->main_standard = $request->sample_details['main_standard'][$k];
                $detail->secondary_standard = $request->sample_details['secondary_standard'][$k];
                $detail->third_standard_id = $request->sample_details['third_standard'][$k];
                $detail->lab_id = $request->sample_details['lab_id'][$k];
            }
            $s_samples_comments = str_replace('<p>&nbsp;</p>', '', $request->sample_details['comments'][$k]);
            $detail->comments = trim($s_samples_comments);

            // Calculate has_no_result_capture
            $hasNoResultCapture = true;
            if (!empty($detail->analysis_type_id)) {
                $aTypes = explode(',', $detail->analysis_type_id);
                if (count($aTypes) > 0) {
                    foreach ($aTypes as $atId) {
                        if (trim($atId) != "") {
                            $at = AnalysisType::find($atId);
                            if (!$at || !$at->has_no_result) {
                                $hasNoResultCapture = false;
                                break;
                            }
                        }
                    }
                } else {
                    $hasNoResultCapture = false;
                }
            } else {
                $hasNoResultCapture = false;
            }
            $detail->has_no_result_capture = $hasNoResultCapture;

            $detail->save();
            if ($SampleHeader->status == 'Samples Reception') {
                if ($request->sample_details['is_duplicate'][$k] != '0') {
                    $duplicate_samples[$detail->id] = $request->sample_details['is_duplicate'][$k];
                    array_push($duplicate_samples_ids, $detail->id);
                    $duplicateDataSampleIds[$detail->id] = $detail->sample_code;
                }

                $this->createDetailAnalysisRelation($SampleHeader->id, $detail->id, explode(',', $detail->analysis_type_id));

                if (isset($current_analysis) && sizeof($current_analysis) > 0) {
                    $update = $request->sample_details['sample_analysis'][$k];
                    foreach ($current_analysis as $ca) {
                        if (!in_array($ca, $update)) {
                            $analysis_type = AnalysisType::find(intval($ca));
                            if (isset($analysis_type->id)) {
                                $captured_reults = CapturedResult::where('analysis_type_id', $analysis_type->id)->where('sample_detail_id', $detail->id)->where('sample_header_id', $detail->sample_header_id)->get();
                                foreach ($captured_reults as $cr) {
                                    $result = Result::where('captured_result_id', $cr->id)->first();
                                    if ($result) {
                                        $result->delete();
                                    }
                                    $cr->delete();
                                }
                            }
                        }
                    }
                }
                $analysis_max_report_time = AnalysisType::whereIn('id', $request->sample_details['sample_analysis'][$k])->max('reporting_time');
                $analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $request->sample_details['sample_analysis'][$k])->max('reporting_time');
                $maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;

                if (trim($request->sample_details['sample_store'][$k]) != '' && trim($request->sample_details['sample_store_slot'][$k]) != '' && trim($request->sample_details['sample_quantity'][$k]) != '') {
                    $ISCC = new InventorySubCategoriesController();

                    $sampleLabItemExists = \App\InventorySubCategories::where('name', $SampleHeader->batch_code . '/' . $detail->sample_code)->first();

                    if (!isset($sampleLabItemExists->id)) {
                        $category = SystemConfiguration::where('key', 'lab_samples_inventory_category_id')->first();
                        $labSupplier = SystemConfiguration::where('key', 'lab_samples_supplier_id')->first();
                        $inventoryLabDep = SystemConfiguration::where('key', 'inventory_lab_dep_id')->first();
                        if (!isset($category->id)) {
                            return redirect()->back()->with('error', 'Kindly a inventory sub-category for lab items;');
                        }
                        if (!isset($labSupplier->id)) {
                            return redirect()->back()->with('error', 'Kindly set the default lab supplier for lab sample storage;');
                        }
                        if (!isset($inventoryLabDep->id)) {
                            return redirect()->back()->with('error', 'Kindly set Inventory Lab Department;');
                        }
                        $req = new Request();
                        $req->category_id = $category->value;
                        $req->name = $SampleHeader->batch_code . '/' . $detail->sample_code;
                        $req->description = 'Sample for batch - ' . $SampleHeader->batch_code;
                        $req->manufacturer = 'Source: Lab';
                        $req->minimum_level = 0;
                        $req->unit_type = $request->sample_details['sample_reporting_unit'][$k];
                        $req->unit_price = 0;
                        $req->reporting_decimal_places = 2;
                        $req->parent = "\App\SampleDetails";
                        $req->parent_id = $detail->id;

                        $sampleItem = $ISCC->add($req, true); //Create Item in Inventory for storage

                        // return response()->json($sampleItem);

                        $IIC = new InventoryItemController();

                        $req = new Request();
                        $req->category_id = $category->value;
                        $req->sub_category_id = $sampleItem->id;
                        $req->batchcode = $sampleItem->name;
                        $req->inventory_department_id = $inventoryLabDep->value;
                        $req->supplier_id = $labSupplier->value;
                        $req->received_by = 0;
                        $req->previous_batch_code = 'N/A';
                        $req->quantity = $request->sample_details['sample_quantity'][$k] ?? 0;
                        $req->barcode = $request->sample_details['barcode'][$k] ?? 'n/a';
                        $req->store = $request->sample_details['sample_store'][$k] ?? 0;
                        $req->slot = $request->sample_details['sample_store_slot'][$k] ?? 0;
                        $req->price = 0;

                        $inventoryItem = $IIC->add($req, true);

                        $samplesRequiringStorage['samples'][] = $detail->sample_code;
                        $samplesRequiringStorage['store_ids'][] = $request->sample_details['sample_store'][$k] ?? 0;

                        // $ISSCC = new InventoryStoreSlotContentController;

                        // $req = new Request();
                        // $req->item = $inventoryItem->batchcode;

                        // $content = $ISSCC->add($req, $request->sample_details['sample_store_slot'][$k], $request->sample_details['sample_store'][$k], true);
                        // return json_encode($content);
                    }
                }
            }
        }

        if ($SampleHeader->status == 'Samples Reception') {
            if (count($samplesRequiringStorage['samples']) > 0) {
                $companyDetails = getCompanyDetails();
                $samplesRequiringStorage['batch_route'] = route('view-batch-details', ['batch' => $SampleHeader->id]);
                $samplesRequiringStorage['batch_code'] = $SampleHeader->batch_code;

                $samplesOL = '<ol>';

                foreach ($samplesRequiringStorage['samples'] as $sampleC) {
                    $samplesOL .= '<li>' . $sampleC . '</li>';
                }

                $samplesOL .= '</ol>';

                $body = '
					Hi,<br>
					<p>
						The following samples have been added to the batch ' . $SampleHeader->batch_code . '.<br>
						' . $samplesOL . '
					</p>
					' . ($SampleHeader->status == 'Samples En-Route' ? 'The samples are expected on ' . $SampleHeader->date_expected : '') . '
					<p>
						Please view the batch for more information about the samples: <a href="' . $samplesRequiringStorage['batch_route'] . '">' . $samplesRequiringStorage['batch_route'] . '</a>
					</p>
					Regards,<br>
					' . $companyDetails['name'] . '
				';

                $subject = '[' . $companyDetails['name'] . '] Samples En-Route Storage Notification for Batch - ' . $SampleHeader->batch_code;

                $emails = \App\InventoryStoreContact::join('users as u', 'u.id', 'inventory_store_contacts.user_id')
                    ->selectRaw('u.email')->whereIn('inventory_store_contacts.store', $samplesRequiringStorage['store_ids'])->get()->pluck('email')->toArray();

                // return json_encode($emails, JSON_PRETTY_PRINT);

                $emails = array_unique($emails);
                notify_user($body, $emails, $subject);
            }

            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $SampleHeader->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $SampleHeader->id;
            $targetDate->date = \Carbon\Carbon::parse($SampleHeader->receipt_date)->addDays($maxReportingTime);
            $targetDate->save();

            $batch = SampleHeader::find($request->batch);
            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';

            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
            $batch->days_of_analysis = $maxReportingTime;
            $batch->sample_tracking_stage = $stage->id;
            $batch->save();
            $duplicateSampleAnalysis = [];
            if (sizeof($duplicate_samples_ids) > 0) {
                foreach ($duplicate_samples as $key => $value) {
                    $choosen_analysis = explode(',', SampleDetails::find($key)->analysis_type_id);
                    $done_analysis = [];
                    $captured_results = CapturedResult::where('sample_detail_code', $value)->whereIn('analysis_type_id', $choosen_analysis)->get();
                    foreach ($captured_results as $c_value) {
                        array_push($done_analysis, $c_value->analysis_type_id);
                        $new_cr = $c_value->replicate()->fill([
                            'sample_detail_id' => $key,
                            'sample_detail_code' => $duplicateDataSampleIds[$key],
                            'result' => '',
                            'user_id' => auth()->user()->id,
                            'remark' => '',
                        ]);
                        $new_cr->save();
                        $result = Result::where('captured_result_id', $c_value->id)->first();
                        if (!$result) {
                            $result = new Result();
                            $result->captured_result_id = $c_value->id;
                            $result->sample_detail_code = $c_value->sample_detail_code;
                            $result->sample_detail_id = $c_value->sample_detail_id;
                            $result->sample_header_id = $c_value->sample_header_id;
                            $result->analyte_id = $c_value->analyte_id;
                            $result->analyte_code = $c_value->analyte_code;
                            $result->analysis_type_id = $c_value->analysis_type_id;
                            $result->lab_section_id = $c_value->lab_section_id;
                            $result->parameters_order = $c_value->parameters_order ?? 0;
                            $result->remark_is_manual = $c_value->remark_is_manual ?? false;
                            $result->has_no_result_capture = $c_value->has_no_result_capture ?? false;
                            $result->save();
                        }
                        $new_result = $result->replicate()->fill([
                            'captured_result_id' => $new_cr->id,

                            'sample_detail_id' => $new_cr->sample_detail_id,
                            'sample_detail_code' => $new_cr->sample_detail_code,
                            'result' => '',
                            'remarks' => '',
                        ]);
                        $new_result->save();
                    }
                    $analysis_diff = array_diff($choosen_analysis, $done_analysis);
                    $duplicateSampleAnalysis[$key] = $analysis_diff;
                }
            }

            $hasCapturedResults = false;
            $analysis_to_be_done = [];

            $batch_analysis = $batch->samples;

            foreach ($batch_analysis as $a) {
                if (!isset($analysis_to_be_done[$a->sample_code]) && !in_array($a->id, $duplicate_samples_ids)) {
                    $analysis_to_be_done[$a->sample_code] = [
                        'sample_detail_code' => $a->sample_code,
                        'sample_detail_id' => $a->id,
                        'sample_header_id' => $batch->id,
                        'analysis_to_do' => [],
                    ];
                }
                if (!in_array($a->id, $duplicate_samples_ids)) {
                    $analysis_to_be_done[$a->sample_code]['analysis_to_do'] = array_merge($analysis_to_be_done[$a->sample_code]['analysis_to_do'], $a->analysis());
                }
                if (in_array($a->id, $duplicate_samples_ids)) {
                    if (sizeof($duplicateSampleAnalysis[$a->id]) > 0) {
                        $analysis_d = AnalysisType::whereIn('id', $duplicateSampleAnalysis[$a->id])->get();
                        if (!isset($analysis_to_be_done[$a->sample_code])) {
                            $analysis_to_be_done[$a->sample_code] = [
                                'sample_detail_code' => $a->sample_code,
                                'sample_detail_id' => $a->id,
                                'sample_header_id' => $batch->id,
                                'analysis_to_do' => [],
                            ];
                        }
                        $analysis_to_be_done[$a->sample_code]['analysis_to_do'] = array_merge($analysis_to_be_done[$a->sample_code]['analysis_to_do'], $analysis_d);
                    }
                }
            }

            $analysis_to_be_done = array_values($analysis_to_be_done);
            $labstr = implode(',', $batch->labs(true));
            $labarr = explode(' - ', $labstr);
            $checklab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
            if (!isset($checklab->id)) {
                $param = explode(',', $labarr[1]);
                $lab = Lab::where('code', $labarr[0])->where('name', $param[0])->first();
            } else {
                $lab = $checklab;
            }

            // return response()->json($analysis_to_be_done,200);

            foreach ($analysis_to_be_done as $atbs) {
                foreach ($atbs['analysis_to_do'] as $a) {
                    if (isset($currentAnalysisSample[$atbs['sample_detail_code']]) && in_array($a->id, $currentAnalysisSample[$atbs['sample_detail_code']])) {
                        $analytes = [];
                    } else {
                        $analytes = $a->active_analysis_elements();
                    }

                    // return response()->json($analytes,200);

                    foreach ($analytes as $an) {
                        $analysisType = AnalysisElements::where('analysis_type_id', $a->id)
                            ->where('analyte_id', $an->analyte_id)->where('equipment_id', $an->equipment_id)->first();

                        $captured = CapturedResult::where('sample_detail_code', $atbs['sample_detail_code'])
                            ->where('sample_detail_id', $atbs['sample_detail_id'])
                            ->where('analyte_id', $an->analyte_id)
                            ->where('analysis_type_id', $a->id)
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new CapturedResult();
                        $captured->sample_detail_code = $atbs['sample_detail_code'];
                        $captured->sample_detail_id = $atbs['sample_detail_id'];
                        $captured->sample_header_id = $atbs['sample_header_id'];
                        $captured->analyte_id = $an->analyte_id;
                        $captured->analysis_type_id = $a->id;
                        $captured->analyte_code = $an->analyte_code;
                        $captured->equipment_id = $an->equipment_id;
                        $captured->method_id = $analysisType->method;
                        $captured->reporting_unit_id = resolveReportingUnitIdFromName($analysisType->reporting_unit);
                        $captured->user_id = \Auth::user()->id;
                        $captured->operator_id = $analysisType->operator_id;
                        $captured->ltm_method_id = $analysisType->ltm_method_id;
                        $captured->analyte_accredited = $analysisType->non_accredited;
                        $captured->analyte_status_contracted = $lab->is_external ?? 0;
                        $captured->lab_section_id = $analysisType->lab_section_id;
                        $captured->parameters_order = $analysisType->level ?? 0;
                        $captured->remark_is_manual = $analysisType->remark_is_manual;
                        $captured->formular_id = $analysisType->formular_id;
                        $captured->method_sequence_id = $analysisType->method_sequence_id;
                        $captured->stage_header_id = $analysisType->stage_header_id;
                        $captured->analysis_element_id = $analysisType->id ?? null;

                        // Get analysis type for has_no_result_capture
                        $aType = AnalysisType::find($a->id);
                        $captured->has_no_result_capture = $aType ? $aType->has_no_result : 0;

                        $captured->save();

                        $result = Result::where('sample_detail_code', $atbs['sample_detail_code'])
                            ->where('sample_detail_id', $atbs['sample_detail_id'])
                            ->where('captured_result_id', $captured->id)
                            ->where('analyte_id', $an->analyte_id)
                            ->where('analysis_type_id', $a->id)
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new Result();
                        $result->captured_result_id = $captured->id;
                        $result->sample_detail_code = $atbs['sample_detail_code'];
                        $result->sample_detail_id = $atbs['sample_detail_id'];
                        $result->sample_header_id = $atbs['sample_header_id'];
                        $result->analyte_id = $an->analyte_id;
                        $result->analysis_type_id = $a->id;
                        $result->analyte_code = $an->analyte_code;
                        $result->unit_code = $an->reporting_unit;
                        $result->reporting_symbol = $an->reporting_symbol;
                        $result->recheck = 0;
                        $result->analyte_status_contracted = $lab->is_external ?? 0;
                        $result->lab_section_id = $analysisType->lab_section_id;
                        $result->parameters_order = $analysisType->level ?? 0;
                        $result->remark_is_manual = $analysisType->remark_is_manual;
                        $result->has_no_result_capture = $captured->has_no_result_capture;

                        $result->save();
                    }
                }
            }
        }

        return redirect()->back()->within('success', 'Batch Samples updated.');
    }

    public function createDetailAnalysisRelation($batch_id, $sample_id, $analysis_type)
    {
        $data = [];
        SampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->whereNotIn('analysis_type_id', $analysis_type)->delete();
        $existing = SampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->pluck('analysis_type_id')->toArray();
        foreach ($analysis_type as $at) {
            if (!in_array($at, $existing)) {
                $data[] = [
                    'analysis_type_id' => $at,
                    'batch_id' => $batch_id,
                    'sample_detail_id' => $sample_id,
                ];
            }
        }
        sizeof($data) > 0 ? SampleAnalysisTypeRelation::insert($data) : '';

        return 'success';
    }

    public function addBatchSamplesDynamically()
    {
        $batches = SampleHeader::all();
        foreach ($batches as $batch) {
            $batch_analysis = $batch->samples;
            $hasCapturedResults = false;

            $analysis_to_be_done = [];

            foreach ($batch_analysis as $a) {
                if (!isset($analysis_to_be_done[$a->sample_code])) {
                    $analysis_to_be_done[$a->sample_code] = [
                        'sample_detail_code' => $a->sample_code,
                        'sample_detail_id' => $a->id,
                        'sample_header_id' => $batch->id,
                        'analysis_to_do' => [],
                    ];
                }

                $analysis_to_be_done[$a->sample_code]['analysis_to_do'] = array_merge($analysis_to_be_done[$a->sample_code]['analysis_to_do'], $a->analysis());
            }

            $analysis_to_be_done = array_values($analysis_to_be_done);
            $labstr = implode(',', $batch->labs(true));
            $labarr = explode(' - ', $labstr);
            $checklab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
            if (!isset($checklab->id)) {
                $param = explode(',', $labarr[1]);
                $lab = Lab::where('code', $labarr[0])->where('name', $param[0])->first();
            } else {
                $lab = $checklab;
            }

            foreach ($analysis_to_be_done as $atbs) {
                foreach ($atbs['analysis_to_do'] as $a) {
                    if (isset($current_analysis) && in_array($a->id, $current_analysis)) {
                        $analytes = [];
                    } else {
                        $analytes = $a->active_analysis_elements();
                    }
                    // return response()->json($analytes,200);

                    foreach ($analytes as $an) {
                        $analysisType = AnalysisElements::where('analysis_type_id', $a->id)
                            ->where('analyte_id', $an->analyte_id)->where('equipment_id', $an->equipment_id)->first();

                        $captured = CapturedResult::where('sample_detail_code', $atbs['sample_detail_code'])
                            ->where('sample_detail_id', $atbs['sample_detail_id'])
                            ->where('analyte_id', $an->analyte_id)
                            ->where('analysis_type_id', $a->id)
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new CapturedResult();
                        $captured->sample_detail_code = $atbs['sample_detail_code'];
                        $captured->sample_detail_id = $atbs['sample_detail_id'];
                        $captured->sample_header_id = $atbs['sample_header_id'];
                        $captured->analyte_id = $an->analyte_id;
                        $captured->analysis_type_id = $a->id;
                        $captured->analyte_code = $an->analyte_code;
                        $captured->equipment_id = $an->equipment_id;
                        $captured->method_id = $analysisType->method;
                        $captured->user_id = \Auth::user()->id;

                        $captured->analyte_status_contracted = $lab->is_external ?? 0;

                        $captured->save();

                        $result = Result::where('sample_detail_code', $atbs['sample_detail_code'])
                            ->where('sample_detail_id', $atbs['sample_detail_id'])
                            ->where('captured_result_id', $captured->id)
                            ->where('analyte_id', $an->analyte_id)
                            ->where('analysis_type_id', $a->id)
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new Result();
                        $result->captured_result_id = $captured->id;
                        $result->sample_detail_code = $atbs['sample_detail_code'];
                        $result->sample_detail_id = $atbs['sample_detail_id'];
                        $result->sample_header_id = $atbs['sample_header_id'];
                        $result->analyte_id = $an->analyte_id;
                        $result->analysis_type_id = $a->id;
                        $result->analyte_code = $an->analyte_code;
                        $result->unit_code = $an->reporting_unit;
                        $result->reporting_symbol = $an->reporting_symbol;
                        $result->recheck = 0;
                        $result->analyte_status_contracted = $lab->is_external ?? 0;
                        $result->save();
                    }
                }
            }
        }

        return response()->json('success', 200);
    }

    // public function add(Request $request)
    // {

    // 	$selectedCustomer = CRMCustomer::find($request->crm_customer_id);
    // 	$selectedSampleType = SampleType::find($request->sample_type_id);

    // 	$cP = "BC".$selectedCustomer->code;

    // 	$header = isset($request->sample_header_id) ? SampleHeader::find($request->sample_header_id) : new SampleHeader;
    // 	if(!isset($request->sample_header_id)){
    // 		$header->batch_code = getNamingConventionCode("Samples", false, $cP);
    // 	}

    // 	$header->receipt_date = $request->receipt_date;
    // 	$header->date_collected = $request->date_collected;
    // 	$header->crm_customer_id = $request->crm_customer_id;
    // 	$header->sample_type_id = $request->sample_type_id;
    // 	$header->crm_unit_name = $request->crm_unit_name;
    // 	$header->reference_number = $request->reference_number ?? 'n/a';
    // 	$header->is_routine = $request->is_routine ?? 0;
    // 	$header->routine_frequency = isset($request->is_routine) ? $request->routine_frequency : 0;
    // 	$header->status = 'samples_in_reception';

    // 	$header->save();

    // 	$codePrefix = $selectedCustomer->code.$selectedSampleType->code;

    // 	// return response()->json($request->all(), 200);

    // 	foreach($request->sample_details['sample_code'] as $k=>$v){
    // 		$detailId = $request->sample_details['detail_header'][$k];

    // 		$detail = gettype($detailId) == 'integer' ? SampleDetails::find($detailId) : new SampleDetails;
    // 		$detail->sample_header_id = $header->id;

    // 		if(gettype($detailId) != 'integer'){
    // 			$detail->sample_code = getNamingConventionCode("Samples", false, $codePrefix);
    // 		}

    // 		$detail->analysis_type_id =implode(',', $request->sample_details['sample_analysis'][$k]);
    // 		$detail->sample_condition_id = $request->sample_details['sample_condition'][$k];
    // 		$detail->sample_point_id = $request->sample_details['sample_point'][$k];
    // 		$detail->company_product_id = $request->sample_details['product'][$k];
    // 		$detail->barcode = $request->sample_details['barcode'][$k];
    // 		$detail->comments = $request->sample_details['comments'][$k];
    // 		$detail->gps = $request->sample_details['gps'][$k];

    // 		$detail->photo_url = null;
    // 		$detail->save();

    // 	}

    //   return redirect()->back()->within('success', 'Sample details added.');
    // }

    public function show(Request $request, $batch, $client = false, $portal = false, $status = false)
    {
        $batchID = $batch;



        // Automatically configure and seed required report formats in the database if they do not exist
        try {
            // Ensure samples_by_category view exists in PostgreSQL
            try {
                \Illuminate\Support\Facades\DB::table('samples_by_category')->first();
            } catch (\Exception $e) {
                try {
                    \Illuminate\Support\Facades\DB::statement("
                        CREATE OR REPLACE VIEW samples_by_category AS
                        SELECT 
                            sh.batch_code AS batch_code,
                            sh.receipt_date AS receipt_date,
                            sh.date_collected AS date_collected,
                            sh.crm_customer_id AS crm_customer_id,
                            sh.sample_type_id AS sample_type_id,
                            sh.reference_number AS reference_number,
                            sh.status AS workflow_stage,
                            sh.is_routine AS is_routine,
                            sh.priority AS priority,
                            sh.batch_scope AS batch_scope,
                            sh.customer_survey AS customer_survey,
                            sh.approval_date AS approval_date,
                            sh.submit_by AS submit_by,
                            sh.sampled_by_company_personnel AS sampled_by_company_personnel,
                            sh.radio_active_levels AS batch_no,
                            sh.description AS product_description,
                            sh.batch_instructions AS batch_instructions,
                            sh.sampling_officer_name AS sampling_officer_name,
                            sh.retention_date AS retention_date,
                            sh.kra_office_ref AS kra_office_ref,
                            cc.code AS crm_code,
                            cc.name AS crm_name,
                            cc.postal_address AS postal_address,
                            cc.physical_address AS physical_address,
                            sh.crm_unit_name AS crm_unit_name,
                            st.code AS sample_type_code,
                            st.name AS sample_type_name,
                            sd.id AS id,
                            sd.sample_code AS sample_code,
                            sd.analysis_type_id AS analysis_type_id,
                            sd.sample_condition_id AS sample_condition_id,
                            sd.barcode AS barcode,
                            sd.comments AS comments,
                            sd.gps AS gps,
                            sd.photo_url AS photo_url,
                            sd.created_at AS created_at,
                            sd.updated_at AS updated_at,
                            sd.sample_header_id AS sample_header_id,
                            sd.sample_point_id AS sample_point_id,
                            sd.company_product_id AS company_product_id,
                            sd.main_body AS main_body,
                            sd.header_body AS header_body,
                            sd.is_ammendment AS is_ammendment,
                            sd.ammendment_number AS ammendment_number,
                            sd.main_standard AS main_standard,
                            sd.secondary_standard AS secondary_standard,
                            sd.short_code AS short_code,
                            sd.material_status AS material_status,
                            sd.third_standard_id AS third_standard_id,
                            sd.sample_no AS sample_no,
                            sd.no_of_samples AS no_of_samples,
                            sd.no_of_pots_plants AS no_of_pots_plants,
                            sd.standard_tests AS standard_tests,
                            sd.compartiment_lot AS compartiment_lot,
                            sd.coa_number AS coa_number,
                            sd.results AS results,
                            sd.lab_sub_no AS lab_sub_no,
                            sd.store_id AS store_id,
                            sd.store_slot_id AS store_slot_id,
                            sd.quantity AS quantity,
                            sd.reporting_unit_id AS reporting_unit_id,
                            sd.mfg_date AS mfg_date,
                            sd.expiry_date AS expiry_date,
                            sd.batch_lot_no AS batch_lot_no,
                            sd.coa_number_target AS coa_number_target,
                            sd.disposal_date AS disposal_date,
                            sd.is_disposed AS is_disposed,
                            sd.notes_body AS notes_body,
                            sd.report_number AS report_number,
                            cp.name AS product_name,
                            sp.name AS sample_point_name,
                            NULL AS sample_point_area_name,
                            sc.name AS sample_condition_name,
                            smain.code AS main_standard_code,
                            ssec.code AS sec_standard_code,
                            sthird.code AS third_standard_code,
                            iss.name AS store_slot_name,
                            is2.name AS store_name,
                            ru.name AS reporting_unit_name,
                            ci.invoice_number AS invoice_number,
                            am.name AS sampling_method_name,
                            am.code AS sampling_method_code,
                            l.code AS main_lab_code,
                            l.name AS main_lab_name,
                            l.id AS main_lab_id,
                            ccu.name AS customer_crm_unit
                        FROM sample_headers sh
                        JOIN sample_details sd ON sh.id = sd.sample_header_id
                        JOIN crm_customers cc ON sh.crm_customer_id = cc.id
                        JOIN sample_types st ON sh.sample_type_id = st.id
                        LEFT JOIN company_products cp ON sd.company_product_id = cp.id
                        LEFT JOIN sample_conditions sc ON sd.sample_condition_id = sc.id
                        LEFT JOIN sample_points sp ON sd.sample_point_id = sp.id
                        LEFT JOIN crm_company_units ccu ON sh.crm_unit_id = ccu.id
                        LEFT JOIN standards smain ON sd.main_standard = smain.id
                        LEFT JOIN standards ssec ON sd.secondary_standard = ssec.id
                        LEFT JOIN standards sthird ON sd.third_standard_id = sthird.id
                        LEFT JOIN inventory_stores is2 ON sd.store_id = is2.id
                        LEFT JOIN inventory_store_slots iss ON sd.store_slot_id = iss.id
                        LEFT JOIN reporting_units ru ON sd.reporting_unit_id = ru.id
                        LEFT JOIN customer_invoice ci ON sh.invoice_id = ci.id
                        LEFT JOIN analysis_methods am ON sh.sampling_method_id = am.id
                        LEFT JOIN labs l ON sd.lab_id = l.id
                    ");
                } catch (\Exception $inner) {
                    \Illuminate\Support\Facades\Log::error("Failed to create samples_by_category view: " . $inner->getMessage());
                }
            }

            $companyId = null;
            $activeCompanyConfig = \Illuminate\Support\Facades\DB::table('system_configurations')->where('key', 'active_company')->first();
            if ($activeCompanyConfig) {
                $companyId = $activeCompanyConfig->value;
            }
            if (!$companyId) {
                $company = \Illuminate\Support\Facades\DB::table('companies')->first();
                if ($company) {
                    $companyId = $company->id;
                }
            }
            if (!$companyId) {
                $companyId = \Illuminate\Support\Str::uuid()->toString();
            }

            $defaultFormats = [
                ['name' => 'Final Results', 'code' => 'FINAL_RESULTS', 'display' => 'grid'],
                ['name' => 'Microbiology', 'code' => '1', 'display' => 'grid'],
                ['name' => 'Hygiene Swabs', 'code' => '2', 'display' => 'grid'],
                ['name' => 'Serology', 'code' => 'SER-COA', 'display' => 'grid'],
                ['name' => 'Water Report', 'code' => 'water_report', 'display' => 'grid'],
            ];

            foreach ($defaultFormats as $df) {
                $format = \App\ReportFormat::where('report_code', $df['code'])->first();
                if (!$format) {
                    $format = new \App\ReportFormat();
                    $format->report_name = $df['name'];
                    $format->report_code = $df['code'];
                    $format->results_display_type = $df['display'];
                    $format->is_active = true;
                    $format->company_id = $companyId;
                    $format->save();
                } else if (!$format->is_active) {
                    $format->is_active = true;
                    $format->save();
                }

                // Link to all active lab sections
                if (\Illuminate\Support\Facades\Schema::hasColumn('sample_analysis_stages', 'active')) {
                    $stages = \App\SampleAnalysisStage::where('active', 1)->get();
                } else {
                    $stages = \App\SampleAnalysisStage::all();
                }
                foreach ($stages as $stage) {
                    $configExists = \App\Models\LabSectionReportConfig::where('sample_analysis_stage_id', $stage->id)
                        ->where('report_format_id', $format->id)
                        ->exists();
                    if (!$configExists) {
                        $config = new \App\Models\LabSectionReportConfig();
                        $config->sample_analysis_stage_id = $stage->id;
                        $config->report_format_id = $format->id;
                        $config->document_code = 'DOC-' . ($stage->code ?: 'GEN');
                        $config->issue_date = now();
                        $config->revision_number = '1';
                        $config->is_default = ($df['code'] === 'FINAL_RESULTS');
                        $config->save();
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to dynamically configure report formats: " . $e->getMessage());
        }

        $batchQuery = SampleHeader::with('comments.creator', 'samples.sample_detail_lab', 'captured_results.my_analyte', 'captured_results.defacto_analyst_with', 'captured_results.sample', 'stagingDetails', 'sample_type', 'sampleSubmissionRequest.suspects', 'sampleSubmissionRequest.exhibits', 'sampleSubmissionRequest.requestedAnalyses');
        $batchLookup = trim((string) $batchID);
        $batch = Str::isUuid($batchLookup)
            ? $batchQuery->find($batchLookup)
            : $batchQuery->where('batch_code', $batchLookup)->first();
        if (isset($batch->id) && $batch->crm_unit_id < 1) {
            $crm_unit = CRMCompanyUnit::where('crm_customer_id', $batch->crm_customer_id)->where('name', $batch->crm_unit_name)->first();
            $batch->crm_unit_id = isset($crm_unit->id) ? $crm_unit->id : $batch->crm_unit_id;
            // return response()->json($batch);
            $batch->save();
        }

        $qc_schemes = QcSchemes::where('is_active', 1)->get();
        $qc_types = QcTypes::where('is_active', 1)->get();
        if (isset($batch->id) && $batch->is_qc_batch == 1) {
            $qcconfigperc = SystemConfiguration::where('key', 'qc_percentage_config')->first();
            $qc_config_perc = $qcconfigperc->value;
        } else {
            $qc_config_perc = '';
        }
        $section_approvers_users = [];
        if (isset($batch->id)) {
            $labSectionIds = array_filter(array_map('trim', explode(',', (string) $batch->lab_section_ids)));
            if ($labSectionIds) {
                $section_approvers_users = LabSectionApproverRelationShip::whereIn('lab_section_id', $labSectionIds)->get();
            }
        }

        $receivingOfficerId = isset($batch->receiving_officer) ? $batch->receiving_officer : null;
        $recieving_users = User::query()
            ->where('active', 1)
            ->where(function ($query) use ($receivingOfficerId) {
                $query->where(function ($internal) {
                    $internal->where('is_client', 0)->whereNull('supplier_id');
                });
                if ($receivingOfficerId) {
                    $query->orWhere('id', $receivingOfficerId);
                }
            })
            ->orderBy('name')
            ->get();

        $batch_scope = SystemConfiguration::where('key', 'batch_scope')->first();
        $customer_survey = SystemConfiguration::where('key', 'customer_survey')->first();
        $countries = Country::orderBy('name')->get();
        // $methods = AnalysisMethod::where('active', 1)->where('is_sampling_method',0)->where('is_ltm',0)->get();
        $is_ltm_id = SystemConfiguration::where('key', 'method_ltm_id')->first();
        $ltMethodTypeId = $this->resolveIntegerConfigValue($is_ltm_id->value ?? null, 'method_ltm_id');
        $ltmethods = $ltMethodTypeId !== null
            ? AnalysisMethod::where('active', 1)->where('method_type_id', $ltMethodTypeId)->get()
            : collect();
        // return response()->json(['methods'=>$methods,'ltm'=>$ltmethods])

        $account_settings = getConfigTypeByName('Account Settings');
        $atachment_type = SystemConfiguration::where('key', 'attachment_type')->get();
        $users = User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->get();
        $labsections = SampleAnalysisStage::where('active', 1)->where('is_system', 0)->get();
        $reportingUnits = getReportingUnits();
        $labStores = getStorageByType('lab_store');
        // return response()->json($reportingUnits);
        $conditions = SampleCondition::where('active', 1)->get();
        $products = CompanyProduct::all();
        $workflowstages = [];
        $workflows = getSampleWorflowStages();
        $sample_types = getSampleTypes();
        $is_sampling = SystemConfiguration::where('key', 'sampling_method_type_id')->first();
        $samplingMethodTypeId = $this->resolveIntegerConfigValue($is_sampling->value ?? null, 'sampling_method_type_id');
        $samplingmethods = $samplingMethodTypeId !== null
            ? AnalysisMethod::where('active', 1)->where('method_type_id', $samplingMethodTypeId)->get()
            : collect();
        $processed_results = [];
        $raw_results = [];
        $interlabs = [];
        $disposal_date = '';
        if (isset($account_settings->id)) {
            $accounts = getconfigByID($account_settings->id);
        } else {
            $accounts = [];
        }
        $not_captured = [];
        $payment_detail = [];
        $contacts = [];
        $batch_sample_codes = '';
        $report_formats = [];
        $approvers = [];
        $approvers_user_ids = [];
        $headerDetails = isset($batch->id) ? $batch->report_header_details() : [];
        $ammendments = isset($batch->id) ? getBatchAmmendmentsById($batch->id) : [];
        $allsamples = isset($batch->id) ? $batch->all_samples() : [];

        if (isset($batch->id)) {
            if (in_array($batch->status, ['Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports In Payment', 'Reports for Collection'])) {
                $raw_results = CapturedResult::with(['sample', 'analysis_type', 'operator'])->where('sample_header_id', $batch->id)->orderBy('sample_detail_id', 'ASC')->get();
                $processed_results = Result::with(['captured', 'captured.sample', 'captured.analysis_type', 'captured.operator'])->where('sample_header_id', $batch->id)->orderBy('sample_detail_id', 'ASC')->get();

                // return response()->json(['raw'=>$raw_results,'processed' => $processed_results]);
            }
            $workflowstages = getWorkflowStage_Stages($batch->status);
            if (in_array($batch->status, ['Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports In Payment', 'Reports for Collection'])) {
                // Fetch report formats configured for this batch's lab sections (report_format_sample_analysis_stage)
                $labSectionIds = array_filter(explode(',', $batch->lab_section_ids ?? ''));

                if (!empty($labSectionIds)) {
                    $configuredFormatIds = \App\Models\LabSectionReportConfig::whereIn('sample_analysis_stage_id', $labSectionIds)
                        ->select('report_format_id', 'is_default', 'sample_analysis_stage_id')
                        ->get();

                    if ($configuredFormatIds->isNotEmpty()) {
                        $formatIds = $configuredFormatIds->pluck('report_format_id')->unique()->toArray();
                        $report_formats = \App\ReportFormat::whereIn('id', $formatIds)->where('is_active', true)->get();

                        foreach ($report_formats as $format) {
                            $format->is_default = $configuredFormatIds->where('report_format_id', $format->id)->where('is_default', true)->isNotEmpty();
                        }
                    }
                }

                if (empty($report_formats) || (is_object($report_formats) && method_exists($report_formats, 'isEmpty') && $report_formats->isEmpty())) {
                    $report_formats = \App\ReportFormat::active()->get();
                }
            }
            $disposal_date = \Carbon\Carbon::parse($batch->receipt_date)->addDays(14)->format('Y-m-d');
            // return response()->json($disposal_date);
            $contacts = getCrmCustomerContactSchedule($batch->crm_customer_id);
            // return response()->json($contacts);
            $batch_sample_codes = getBacthSampleCodes($batch->id);
            $payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->get();
            $interlabs = InterLabLogView::where('sample_header_id', $batch->id)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get();
            // $equipment_data = $batch->get_captured();
            $approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->get();
            $approvers_user_ids = BatchLabSectionApprover::where('batch_id', $batch->id)->pluck('user_id')->toArray();
            // return response()->json(["ids"=>$approvers_user_ids,'users'=>$users]);
            // foreach ($equipment_data['items'] as $b => $d) {
            // 	foreach ($d as $a => $k) {
            // 		foreach ($k as $i => $e) {

            // 			if ($e == '') {
            // 				if (!isset($not_captured[$b])) {
            // 					$not_captured[$b] = array();
            // 					array_push($not_captured[$b], $a);
            // 				} else {
            // 					array_push($not_captured[$b], $a);
            // 				}
            // 			}
            // 		}
            // 	}
            // }
            $not_captured = CapturedResult::where('sample_header_id', $batch->id)->whereNull('result')->join('analysis_elements as ae', function ($join) {
                $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
                $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
            })->where('ae.active', 1)->selectRaw("string_agg(analyte_code, ',') as codes,sample_detail_code")->groupBy('sample_detail_id', 'sample_detail_code')->get();
            // return response()->json($test);
        }

        $sampleTypeId = trim((string) ($batch->sample_type_id ?? ''));
        $selectedSampleType = ($sampleTypeId !== '' && $sampleTypeId !== '0' && Str::isUuid($sampleTypeId))
            ? \App\SampleType::find($sampleTypeId)
            : null;

        $selected_analysis_types = $selectedSampleType ? $selectedSampleType->analysis_types : [];
        if (isset($batch->id)) {
            if ($batch->is_qc_batch) {
                $standards = Standards::where('status', 1)->where('qc_type_id', $batch->qc_type_id)->get();
            } else {
                $standards = Standards::where('status', 1)->get();
            }
        } else {
            $standards = [];
        }
        $defaultClient = $client > 0 ? $client : false;
        $client_portal = $portal > 0 ? $portal : false;
        if (isset($batch->id)) {
            $attachments = BatchAttachment::where('batch_id', $batch->id)->get();

            if ($batch->in_ammendment_proccess == 1) {
                $ammendment = BatchAmmendment::where('batch_id', $batch->id)->where('version_number', $batch->is_amendment)->first();
                // return response()->json($batch,200);
                if (isset($ammendment->id)) {
                    $samples = json_decode($ammendment->samples, true);
                    $ammendable = array_keys($samples);
                }
                // return response()->json($ammendments,200);
            } else {
                $ammendable = $batch->all_samples()->pluck('sample_code');
            }
        } else {
            $ammendable = [];
            $attachments = [];
            // return response()->json($ammendable,200);
        }

        $analysts = getActiveUsersByRole('Laboratory Analyst');
        $labs = Lab::where('active', 1)->get();

        // -----------------------------------

        $analaytesHolder = [];
        $analaytesHolderPesticide = [];
        $analysisBySample = [];
        $analysisBySampleNames = [];
        $labSamples = [];

        // echo date('Y-m-d H:i:s');
        $l = 1;

        // return response()->json($batch);


        $methodsQuery = AnalysisMethod::query();
        if ($samplingMethodTypeId !== null) {
            $methodsQuery->whereNotIn('method_type_id', [$samplingMethodTypeId]);
        }
        $methods = $methodsQuery->pluck('name', 'id')->toArray();

        // return response()->json($methods);

        foreach ($batch->captured_results ?? [] as $item) {
            // return response()->json($item);

            if (!isset($analaytesHolderPesticide[$item->sample_detail_code]) && $item->pesticide == 1) {
                $analaytesHolderPesticide[$item->sample_detail_code] = [];
            } else {
                if (!isset($analaytesHolder[$item->sample_detail_code])) {
                    $analaytesHolder[$item->sample_detail_code] = [];
                }
            }

            // $item->ops = $analysts;
            $item->equip_name = $item->equipment()->name ?? '-';

            $item->def_operator = $item->defacto_analyst_with;
            $item->analysis_type = $item->analysis_type;
            $analyte = $item->my_analyte;

            if (isset($analyte->id)) {
                $item->analyte_name = $analyte->name;
            } else {
                $item->analyte_name = $item->analyte_code;
            }
            $getLimitMeasure = [
                'Max' => 'Max',
                'Min' => 'Min',
                'greater_than' => '>',
                'less_than' => '<',
            ];
            $item->methods = $this->methodNameFromId($methods, $analyte->method);
            $lab_section = SampleAnalysisStage::find($item->lab_section_id);
            // !isset($analaytesHolder[$item->sample_detail_code]) ? $analaytesHolder[$item->sample_detail_code] = [] : '';
            // isset($lab_section->id) && !isset($analaytesHolder[$item->sample_detail_code][$item->lab_section_id]) ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id] = [] : '';
            if ($item->pesticide == 0) {
                $item->lab_section_id > 0 ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id]['section'] = $lab_section->name : $analaytesHolder[$item->sample_detail_code]['000']['section'] = 'Not Set';
                $item->lab_section_id > 0 ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id]['cr'][] = $item : $analaytesHolder[$item->sample_detail_code]['000']['cr'][] = $item;
            } else {
                $item->lab_section_id > 0 ? $analaytesHolderPesticide[$item->sample_detail_code][$item->lab_section_id]['section'] = $lab_section->name : $analaytesHolderPesticide[$item->sample_detail_code]['000']['section'] = 'Not Set';
                $item->lab_section_id > 0 ? $analaytesHolderPesticide[$item->sample_detail_code][$item->lab_section_id]['cr'][] = $item : $analaytesHolderPesticide[$item->sample_detail_code]['000']['cr'][] = $item;
            }

            $sample_details_test = $item->sample;
            $item->standard_limit_value = '';
            if (isset($sample_details_test->id)) {
                $standard = $sample_details_test->main_standard;
                $sec = $sample_details_test->secondary_standard;
                $third = $sample_details_test->third_standard_id;
                if ($standard != '') {
                    // $analyte_standard = $item->analyte_standard_value;
                    $analyte_standard = StandardAnalytes::where('standard_id', $standard)->where('analyte_id', $item->analyte_id)->first();
                    if (isset($analyte_standard->id)) {
                        if ($analyte_standard->standard_value_type == 'is_range') {
                            $item->standard_value = $analyte_standard->low . ' - ' . $analyte_standard->high;
                        } elseif ($analyte_standard->standard_value_type == 'is_standard_value') {
                            $value_id = StandardValue::find($analyte_standard->standard_value_id);
                            if (isset($value_id->id)) {
                                if ($value_id->code == 'IsValue') {
                                    $item->standard_value = $analyte_standard->standard_is_value;
                                    $item->standard_limit_value = $analyte_standard->value_type;
                                } else {
                                    $item->standard_value = $value_id->code;
                                }
                            }
                        }
                    } else {
                        $item->standard_value = 'NS';
                    }
                    $item->main_standard = Standards::find($standard)->code ?? '';
                }
                if ($sec != '') {
                    $s_analyte_standard = StandardAnalytes::where('standard_id', $sec)->where('analyte_id', $item->analyte_id)->first();
                    if (isset($s_analyte_standard->id)) {
                        if ($s_analyte_standard->standard_value_type == 'is_range') {
                            $item->sec_Standard_value = $s_analyte_standard->low . ' - ' . $s_analyte_standard->high;
                        } elseif ($s_analyte_standard->standard_value_type == 'is_standard_value') {
                            $value_id = StandardValue::find($s_analyte_standard->standard_value_id);
                            if (isset($value_id->id)) {
                                if ($value_id->code == 'IsValue') {
                                    $item->sec_standard_value = $s_analyte_standard->standard_is_value;
                                    $item->sec_standard_limit_value = $s_analyte_standard->value_type;
                                } else {
                                    $item->sec_standard_value = $value_id->code;
                                }
                            }
                        }
                    } else {
                        $item->sec_standard_value = 'NS';
                    }

                    $item->secondary_standard = Standards::find($sec)->code ?? '';
                }
                if ($third != '') {
                    $t_analyte_standard = StandardAnalytes::where('standard_id', $third)->where('analyte_id', $item->analyte_id)->first();
                    if (isset($t_analyte_standard->id)) {
                        if ($t_analyte_standard->standard_value_type == 'is_range') {
                            $item->third_Standard_value = $t_analyte_standard->low . ' - ' . $t_analyte_standard->high;
                        } elseif ($t_analyte_standard->standard_value_type == 'is_standard_value') {
                            $value_id = StandardValue::find($t_analyte_standard->standard_value_id);
                            if (isset($value_id->id)) {
                                if ($value_id->code == 'IsValue') {
                                    $item->third_standard_value = $t_analyte_standard->standard_is_value;
                                    $item->third_standard_limit_value = $t_analyte_standard->value_type;
                                } else {
                                    $item->third_standard_value = $value_id->code;
                                }
                            }
                        }
                    } else {
                        $item->third_standard_value = 'NS';
                    }

                    $item->third_standard = Standards::find($third)->code ?? '';
                }
            }
            // return response()->json($batch);
        }

        // return response()->json($analaytesHolder);

        foreach ($batch->samples ?? [] as $sample) {
            $labSamples[$sample->sample_code] = getSampleDetailsLab($sample->id);
            if (!isset($analysisBySample[$sample->sample_code])) {
                $analysisBySample[$sample->sample_code] = [];
            }
            $analysisBySample[$sample->sample_code] = array_merge(explode(',', $sample->analysis_type_id), $analysisBySample[$sample->sample_code]);

            foreach ($analysisBySample[$sample->sample_code] as $id) {
                $analysis = getAnalysisTypeID($id);
                if (isset($analysis->id)) {
                    if (!isset($analysisBySampleNames[$sample->sample_code])) {
                        $analysisBySampleNames[$sample->sample_code] = [];
                    }
                    $analysisBySampleNames[$sample->sample_code][$analysis->name] = $analysis->id;
                }
            }
        }
        // echo "Ending - ".date('Y-m-d H:i:s');
        $active_company = getActiveCompany();
        // return response()->json($analaytesHolder);
        // ---------------------------------------
        $userLabSections = auth()->user()->labsectionids;
        $customer = isset($batch->id) ? getCrmCustomerByID($batch->crm_customer_id) : [];
        $requestTypes = getRequestTypes();
        $notifiable_users = getNotifiableUsers();
        $notesReminderType = getNotesReminderTypes();
        $clientPageSize = 50;
        $clients = CRMCustomer::query()
            ->select(['id', 'name'])
            ->where('active', 1)
            ->orderBy('name')
            ->limit($clientPageSize)
            ->get();

        $selectedClientId = $batch->crm_customer_id ?? ($defaultClient !== false ? $defaultClient : null);

        if ($selectedClientId) {
            $selectedClient = CRMCustomer::query()
                ->select(['id', 'name'])
                ->where('id', $selectedClientId)
                ->where('active', 1)
                ->first();

            if ($selectedClient !== null && !$clients->contains('id', $selectedClientId)) {
                if ($clients->count() >= $clientPageSize) {
                    $clients->pop();
                }

                $clients->push($selectedClient);
            }
        }

        $clients = $clients->sortBy('name')->values();
        // return response()->json($analaytesHolder);
        return view('batches.show', compact('batch', 'labStores', 'batchID', 'defaultClient', 'selectedSampleType', 'client_portal', 'ammendable', 'standards', 'attachments', 'not_captured', 'analysts', 'countries', 'accounts', 'methods', 'atachment_type', 'batch_scope', 'customer_survey', 'interlabs', 'labs', 'users', 'payment_detail', 'labsections', 'contacts', 'batch_sample_codes', 'report_formats', 'approvers', 'reportingUnits', 'conditions', 'products', 'headerDetails', 'analaytesHolder', 'analysisBySample', 'analysisBySampleNames', 'labSamples', 'workflowstages', 'workflows', 'sample_types', 'samplingmethods', 'active_company', 'ammendments', 'allsamples', 'selected_analysis_types', 'userLabSections', 'customer', 'requestTypes', 'notifiable_users', 'notesReminderType', 'clients', 'disposal_date', 'status', 'recieving_users', 'section_approvers_users', 'analaytesHolderPesticide', 'approvers_user_ids', 'ltmethods', 'processed_results', 'raw_results', 'qc_schemes', 'qc_types', 'qc_config_perc', 'clientPageSize'));
    }

    public function fetch_unit_stuff($name, $client)
    {
        $unit = CRMCompanyUnit::where('id', $name)->where('crm_customer_id', $client)->first();

        return response()->json([
            'products' => $unit->products,
            'sample_points' => $unit->sample_points,
        ], 200);
    }

    private function resolveIntegerConfigValue(mixed $rawValue, string $configKey): ?int
    {
        if (is_int($rawValue)) {
            return $rawValue;
        }

        $value = trim((string) $rawValue);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        try {
            $decrypted = decrypt($value);
            if (is_int($decrypted)) {
                return $decrypted;
            }

            $decryptedString = trim((string) $decrypted);
            if ($decryptedString !== '' && ctype_digit($decryptedString)) {
                return (int) $decryptedString;
            }
        } catch (\Throwable $e) {
            // Non-encrypted values will fail decryption and are handled below.
        }

        Log::warning('System configuration value is not a valid integer', [
            'key' => $configKey,
            'value' => $value,
        ]);

        return null;
    }

    public function updateChainofCustody($data)
    {
        \App\ChainOfCustody::where('sample_header_id', $data['batch_id'])
            ->whereNull('moved_out_date')->update([
                'moved_out_date' => \Carbon\Carbon::now(),
                'moved_out_by' => \Auth::user()->id,
                'comments' => $data['comments'],
            ]);

        $custody = new \App\ChainOfCustody();
        $custody->workflow_stage = $data['target']['status'];
        $custody->tracking_stage_id = $data['target']['tracking_stage'];

        $custody->moved_in_by = \Auth::user()->id;
        $custody->sample_header_id = $data['batch_id'];

        $custody->save();

        return true;
    }

    public function change_workflow_status(Request $request)
    {
        // return response()->json($request->all());
        if ($request->status == 'Samples Request Review') {
            if (isset($request->batch_code)) {
                foreach ($request->batch_code as $code) {
                    $batch = Sampleheader::where('batch_code', $code)->first();
                    if ($batch->status == 'Samples Reception' && $batch->customer_paid == 0 && $batch->begin_proccess == 0) {
                        return redirect()->back()->with('error', 'The following batch has not being paid for!');
                    }
                }
            }
        }
        if (isset($request->notification)) {
            $companyDetails = getCompanyDetails();
            // return response()->json($request->all,200);

            if (isset($request->batch_id)) {
                // return response()->json('test',200);
                $batch = Sampleheader::find($request->bacth_id);
                $responsibility = SystemConfiguration::where('key', $batch->status)->first();

                $users = JobDescription::where(function ($query) use ($responsibility) {
                    $query->where('job_designation_responsibility.name', $responsibility->key)
                        ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                })
                    ->join('users', function ($join) {
                        $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                    })
                    ->get('users.*');
                $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';

                $emails = $users->pluck('email')->toarray();
                $position = $users->pluck('position')->toarray();
                $position = array_unique($position);
                if (!in_array(auth()->user()->email, $emails) && !auth()->user()->hasRole('admin')) {
                    return redirect()->back()->with('error', 'Your are not allowed to perform this task!');
                }
                foreach ($users as $user) {
                    $body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $companyDetails['name'];
                    $subject = '[' . $companyDetails['name'] . '] Batch Notification';
                    notify_user($body, $user->email, $subject);
                }

                $notification = new SystemNotifications();
                foreach ($position as $pos) {
                    $notification->batchNotification($batch, $pos, $message, $request->status);
                }
            } elseif (isset($request->batch_code)) {
                $batchcodes = $request->batch_code;
                foreach ($batchcodes as $code) {
                    $batch = Sampleheader::where('batch_code', $code)->first();

                    $responsibility = SystemConfiguration::where('key', $batch->status)->first();

                    $users = JobDescription::where(function ($query) use ($responsibility) {
                        $query->where('job_designation_responsibility.name', $responsibility->key)
                            ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                    })
                        ->join('users', function ($join) {
                            $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                        })
                        ->get('users.*');
                    $message = 'Batch ' . $code . ' needs your attention - ' . $request->status . '.';
                    $emails = $users->pluck('email')->toarray();
                    $position = $users->pluck('position')->toarray();
                    $position = array_unique($position);
                    if (!in_array(auth()->user()->email, $emails) && !auth()->user()->hasRole('admin')) {
                        return redirect()->back()->with('error', 'Your are not allowed to perform this task!');
                    }
                    foreach ($users as $user) {
                        $body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $companyDetails['name'];
                        $subject = '[' . $companyDetails['name'] . '] Batch Notification';
                        notify_user($body, $user->email, $subject);
                    }

                    // return response()->json('test',200);
                    $notification = new SystemNotifications();
                    foreach ($position as $pos) {
                        $notification->batchNotification($batch, $pos, $message, $request->status);
                    }
                }
                // $body = 'Your a have a '
            }
        } else {
            if (isset($request->batch_code)) {
                foreach ($request->batch_code as $code) {
                    $batch = Sampleheader::where('batch_code', $code)->first();

                    $responsibility = SystemConfiguration::where('key', $batch->status)->first();

                    $users = JobDescription::where(function ($query) use ($responsibility) {
                        $query->where('job_designation_responsibility.name', $responsibility->key)
                            ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                    })
                        ->join('users', function ($join) {
                            $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                        })
                        ->get('users.*');
                    $message = 'Batch ' . $code . ' needs your attention - ' . $request->status . '.';
                    $emails = $users->pluck('email')->toarray();
                    $position = $users->pluck('position')->toarray();
                    $position = array_unique($position);
                    $notification = new SystemNotifications();
                    foreach ($position as $pos) {
                        // return response()->json($request->status,200);
                        if ($request->status != 'Samples Request Review') {
                            $notification->batchNotification($batch, $pos, $message, $request->status);
                        }
                    }
                }
            } elseif (isset($request->batch_id)) {
                $batch = Sampleheader::find($request->batch_id);
                $responsibility = SystemConfiguration::where('key', $batch->status)->first();

                $users = JobDescription::where(function ($query) use ($responsibility) {
                    $query->where('job_designation_responsibility.name', $responsibility->key)
                        ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                })
                    ->join('users', function ($join) {
                        $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                    })
                    ->get('users.*');
                $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';

                $emails = $users->pluck('email')->toarray();
                $position = $users->pluck('position')->toarray();
                $position = array_unique($position);
                $notification = new SystemNotifications();
                foreach ($position as $pos) {
                    $notification->batchNotification($batch, $pos, $message, $request->status);
                }
            }
        }
        $previousStatus = '';
        if ($request->status == 'Samples Request Review' && $request->has('tracking_stage')) {
            $batch = Sampleheader::find($request->bacth_id);
            // return response()->json($batch,200);
            $previousStatus = $batch->status;
            $custodyDetails = [
                'batch_id' => $request->bacth_id,
                'comments' => $request->comments,
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $request->status,
                    'tracking_stage' => $request->tracking_stage,
                ],
            ];

            $requestTypes = (array) ($request->request_type_id ?? []);
            $requestTypes = array_diff($requestTypes, ['Other']);

            if ($request->has('other_type') && $request->other_reason) {
                $reason = new \App\RequestType();
                $reason->name = $request->other_type;
                $reason->visible = 0;
                $reason->save();

                $requestTypes = array_merge($requestTypes, [$reason->id]);
            }

            $batch->reason_for_submission = implode(',', $requestTypes);

            $batch->sample_tracking_stage = $request->tracking_stage;
            $batch->priority = $request->is_priority ?? 'Normal';
            $batch->save();

            $this->updateChainofCustody($custodyDetails);

            return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Approval was successful');
        }

        if ($request->status == 'Samples In Lab') {
            // Collect batch codes from direct batch selection
            $batchCodes = collect((array) ($request->batch_code ?? []))
                ->filter(fn ($c) => is_string($c) && trim($c) !== '')
                ->map(fn ($c) => trim($c))
                ->unique()
                ->values();

            $resolvedBatchCodes = collect();
            $unresolvedSubmissionRequestIds = collect();
            $unresolvedSubmissionFormInstanceIds = collect();

            // Also resolve batches from any submitted form instance IDs
            $formInstanceIds = collect((array) ($request->submission_form_instance_id ?? []))
                ->filter(fn ($id) => (string) $id !== '')
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values();

            if ($formInstanceIds->isNotEmpty()) {
                $formInstances = SubmissionFormInstance::with('batches')
                    ->whereIn('id', $formInstanceIds)
                    ->get();

                foreach ($formInstances as $formInstance) {
                    $resolvedCodes = $this->resolveBatchCodesFromFormInstance($formInstance);

                    if ($resolvedCodes->isNotEmpty()) {
                        $resolvedBatchCodes = $resolvedBatchCodes->merge($resolvedCodes);
                        continue;
                    }

                    $unresolvedSubmissionFormInstanceIds->push((string) $formInstance->id);
                }
            }

            $submissionRequestIds = collect((array) ($request->submission_request_id ?? []))
                ->filter(fn ($id) => (string) $id !== '')
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values();

            if ($submissionRequestIds->isNotEmpty()) {
                $submissionRequests = SampleSubmissionRequest::query()
                    ->with(['batch'])
                    ->whereIn('id', $submissionRequestIds)
                    ->get();

                foreach ($submissionRequests as $submissionRequest) {
                    $linkedBatch = $submissionRequest->batch;
                    if (! $linkedBatch && !empty($submissionRequest->sample_header_id)) {
                        $linkedBatch = SampleHeader::query()->find($submissionRequest->sample_header_id);
                    }

                    if ($linkedBatch && !empty($linkedBatch->batch_code)) {
                        $resolvedBatchCodes->push((string) $linkedBatch->batch_code);
                        continue;
                    }

                    $unresolvedSubmissionRequestIds->push((string) $submissionRequest->id);
                }
            }

            $batchCodes = $batchCodes
                ->merge($resolvedBatchCodes)
                ->unique()
                ->values();

            if ($batchCodes->isEmpty()) {
                $failureParts = [];

                if ($unresolvedSubmissionFormInstanceIds->isNotEmpty()) {
                    $failureParts[] = 'form instance ID(s): ' . $unresolvedSubmissionFormInstanceIds->implode(', ');
                }

                if ($unresolvedSubmissionRequestIds->isNotEmpty()) {
                    $failureParts[] = 'submission request ID(s): ' . $unresolvedSubmissionRequestIds->implode(', ');
                }

                $failureMessage = 'No batches could be created or resolved for the selected items.';
                if (!empty($failureParts)) {
                    $failureMessage .= ' Unresolved ' . implode('; ', $failureParts) . '.';
                } else {
                    $failureMessage .= ' Please ensure samples have been received and a batch has been created before approving.';
                }

                return redirect()->back()->with('error', $failureMessage);
            }

            $processedBatch = null;
            foreach ($batchCodes as $code) {
                $batch = SampleHeader::where('batch_code', $code)->first();
                if (!$batch) {
                    continue;
                }

                $batch->in_lab_date = date('Y-m-d');
                $previousStatus = $batch->status;
                $targetTrackingStage = $request->tracking_stage;

                $stages = $batch->stages($request->status);
                if (isset($stages[0]->id)) {
                    $targetTrackingStage = $targetTrackingStage ?: $stages[0]->id;
                } else {
                    $fallbackStages = $batch->stages();
                    if (isset($fallbackStages[0]->id)) {
                        $targetTrackingStage = $targetTrackingStage ?: $fallbackStages[0]->id;
                        Log::warning('No workflow-specific stage found; using fallback stage for Samples In Lab transition', [
                            'batch_id' => $batch->id,
                            'batch_code' => $batch->batch_code,
                            'status' => $request->status,
                            'fallback_stage_id' => $targetTrackingStage,
                        ]);
                    } else {
                        Log::warning('No sample analysis stages configured for sample type; proceeding without tracking stage', [
                            'batch_id' => $batch->id,
                            'batch_code' => $batch->batch_code,
                            'sample_type_id' => $batch->sample_type_id,
                            'status' => $request->status,
                        ]);
                    }
                }

                $custodyDetails = [
                    'batch_id' => $batch->id,
                    'comments' => $request->comments,
                    'current' => [
                        'status' => $batch->status,
                        'tracking_stage' => $batch->sample_tracking_stage,
                    ],
                    'target' => [
                        'status' => $request->status,
                        'tracking_stage' => \Illuminate\Support\Str::isUuid($targetTrackingStage) ? $targetTrackingStage : null,
                    ],
                ];

                $batch->status = $request->status;
                $batch->sample_tracking_stage = \Illuminate\Support\Str::isUuid($targetTrackingStage) ? $targetTrackingStage : null;
                if (!empty($request->specialist_analyst_id) && \Illuminate\Support\Str::isUuid($request->specialist_analyst_id)) {
                    $batch->specialist_analyst_id = $request->specialist_analyst_id;
                } else {
                    $batch->specialist_analyst_id = null;
                }
                $batch->priority = $request->is_priority ?? 'Normal';
                $batch->save();

                $this->persistLaboratoryAcceptanceForm(
                    $batch,
                    (array) $request->input('lab_acceptance', [])
                );

                $this->updateChainofCustody($custodyDetails);
                $processedBatch = $batch;
            }

            if ($processedBatch) {
                return redirect()
                    ->to(route('view-batch-details', ['batch' => $processedBatch->id, 'client' => 0, 'portal' => 0, 'status' => 'Samples In Lab']) . '#samples')
                    ->with('success', 'Lab acceptance approved. Complete Sample Receipt Notification (GCLA 01) under Lab Acceptance tab (Part E) if still required.');
            }

            return redirect()->route('sample-workflow', ['status' => 'Samples Request Review'])
                ->with('success', 'Batches Successfully Moved to ' . $request->status);
        } elseif ($request->status == 'Samples Request Review') {
            $strStage = 'Sample Labeling';

            $batchCodes = collect((array) $request->batch_code)
                ->filter(fn ($code) => is_string($code) && trim($code) !== '')
                ->map(fn ($code) => trim($code))
                ->values();

            $submissionRequestIds = collect((array) $request->submission_request_id)
                ->filter(fn ($id) => (string) $id !== '')
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values();

            $submissionFormInstanceIds = collect((array) $request->submission_form_instance_id)
                ->filter(fn ($id) => (string) $id !== '')
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values();

            if ($batchCodes->isEmpty() && $submissionRequestIds->isEmpty() && $submissionFormInstanceIds->isEmpty()) {
                return redirect()->back()->with('error', 'No requests were selected. Please select at least one submission request, form, or batch and try again.');
            }

            Log::info('sample_workflow.request_review.transition_payload', [
                'user_id' => optional(auth()->user())->id,
                'batch_code_count' => $batchCodes->count(),
                'submission_request_id_count' => $submissionRequestIds->count(),
                'submission_form_instance_id_count' => $submissionFormInstanceIds->count(),
            ]);

            if ($submissionRequestIds->isNotEmpty()) {
                $submissionRequests = SampleSubmissionRequest::query()
                    ->with(['supportingDocumentInstances'])
                    ->whereIn('id', $submissionRequestIds)
                    ->get();

                foreach ($submissionRequests as $submissionRequest) {
                    // Move selected portal forms into request-review queue even before batch creation.
                    $submissionRequest->status = 'in_review';
                    $submissionRequest->save();

                    foreach ($submissionRequest->supportingDocumentInstances as $docInstance) {
                        if (in_array($docInstance->status, ['draft', 'submitted'], true)) {
                            $docInstance->status = 'in_review';
                            $docInstance->save();
                        }
                    }

                    if (!empty($submissionRequest->sample_header_id)) {
                        $linkedBatchCode = (string) optional($submissionRequest->batch)->batch_code;
                        if ($linkedBatchCode !== '') {
                            $batchCodes->push($linkedBatchCode);
                        }
                    }
                }
            }

            if ($submissionFormInstanceIds->isNotEmpty()) {
                $submissionFormInstances = SubmissionFormInstance::query()
                    ->with(['attachmentInstances', 'batches'])
                    ->whereIn('id', $submissionFormInstanceIds)
                    ->get();

                $reviewActor = auth()->user();
                $reviewComment = $request->input('comment');

                foreach ($submissionFormInstances as $submissionFormInstance) {
                    if ($reviewActor instanceof \App\User) {
                        $submissionFormInstance->markAsInReview($reviewActor, is_string($reviewComment) ? $reviewComment : null);
                    }

                    foreach ($submissionFormInstance->batches as $linkedBatch) {
                        if (!empty($linkedBatch->batch_code)) {
                            $batchCodes->push((string) $linkedBatch->batch_code);
                        }
                    }
                }
            }

            $batch_codes = $batchCodes->unique()->values()->all();
            $previousStatus = 'Samples En-Route';
            $returnStatus = (string) $request->input('return_status', $previousStatus);
            $returnTab = (string) $request->input('return_tab', 'requests');
            $allowedTabs = match ($returnStatus) {
                'Samples Receiving' => array_keys(\App\Livewire\Sampleworkflow\WorkflowBoard::receivingRequestTabs()),
                'Samples Request Review' => array_keys(\App\Livewire\Sampleworkflow\WorkflowBoard::requestReviewTabs()),
                default => ['requests', 'received'],
            };
            $legacyReviewTabMap = ['requests' => 'in_review', 'received' => 'accepted'];
            if ($returnStatus === 'Samples Request Review' && isset($legacyReviewTabMap[$returnTab])) {
                $returnTab = $legacyReviewTabMap[$returnTab];
            }
            $defaultTab = match ($returnStatus) {
                'Samples Receiving' => 'submitted',
                'Samples Request Review' => 'in_review',
                default => 'requests',
            };
            $returnRedirect = [
                'status' => $returnStatus !== '' ? $returnStatus : $previousStatus,
                'tab' => in_array($returnTab, $allowedTabs, true) ? $returnTab : $defaultTab,
            ];

            $responsibility = SystemConfiguration::where('key', 'Samples En-Route')->first();

            if (!$responsibility) {
                return redirect()->back()->with('error', 'Workflow responsibility configuration for Samples En-Route is missing.');
            }

            $users = JobDescription::where(function ($query) use ($responsibility) {
                $query->where('job_designation_responsibility.name', $responsibility->key)
                    ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
            })
                ->join('users', function ($join) {
                    $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                })
                ->get('users.*');
            $companyDetails = getCompanyDetails();
            $position = $users->pluck('position')->toarray();
            $position = array_unique($position);
            $emails = $users->pluck('email')->toarray();
            // return response()->json($responsibility,200);
            if (!in_array(auth()->user()->email, $emails) && !auth()->user()->hasRole('admin')) {
                return redirect()->back()->with('error', 'You are not allowed to perform this task!');
            }
            // return response()->json($batch_codes,200);
            foreach ($batch_codes as $code) {
                $batch = SampleHeader::whereIn('status', ['Samples En-Route', 'Samples Reception'])->where('batch_code', $code)->first();

                if (!isset($batch->id)) {
                    return redirect()->back()->with('error', 'Batch ' . $code . ' cannot be moved to request review from its current stage.');
                }
                // return response()->json('test',200);
                if (($batch->current_account_status == 'Account Holder(Overdue)' || $batch->current_account_status == 'Pay Upfront') && ($batch->begin_proccess == 0)) {
                    return redirect()->back()->with('error', 'Please check account status - ' . $batch->current_account_status);
                }
                $currentStatus = $batch->status;
                $currentTrackingStage = $batch->sample_tracking_stage;
                $batch->status = $request->status;
                
                $reviewStage = \App\SampleAnalysisStage::where('name', 'Samples Request Review')->first();
                $batch->sample_tracking_stage = $reviewStage ? $reviewStage->id : null;
                $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
                if (isset($request->notification)) {
                    $emails = $users->pluck('email')->toarray();

                    foreach ($users as $user) {
                        $body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $companyDetails['name'];
                        $subject = '[' . $companyDetails['name'] . '] Batch Notification';
                        notify_user($body, $user->email, $subject);
                    }
                }

                $notification = new SystemNotifications();
                foreach ($position as $pos) {
                    $notification->batchNotification($batch, $pos, $message, $request->status);
                }

                $custodyDetails = [
                    'batch_id' => $batch->id,
                    'comments' => $request->comments ?? '',
                    'current' => [
                        'status' => $currentStatus,
                        'tracking_stage' => $currentTrackingStage,
                    ],
                    'target' => [
                        'status' => $request->status,
                        'tracking_stage' => $batch->sample_tracking_stage,
                    ],
                ];
                $this->updateChainofCustody($custodyDetails);

                $batch->save();
            }

            if (empty($batch_codes) && ($submissionRequestIds->isNotEmpty() || $submissionFormInstanceIds->isNotEmpty())) {
                return redirect()->route('sample-workflow', $returnRedirect)
                    ->with('success', 'Selected submission forms were queued for Sample Request Review.');
            }
            // return response()->json($batches,200);
        }

        if (isset($request->send_message) && isset($batch) && isset($batch->status)) {
            $responsibility = SystemConfiguration::where('key', $batch->status)->first();
            $users = JobDescription::where(function ($query) use ($responsibility) {
                $query->where('job_designation_responsibility.name', $responsibility->key)
                    ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
            })
                ->join('users', function ($join) {
                    $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                })
                ->get('users.*');

            $numbers = $users->pluck('phone')->toarray();
            $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
            foreach ($numbers as $num) {
                $send = sendTextMessage($num, $message);
                if ($send == 'error') {
                    return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                }
            }
        }

        // return;

        return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Batches Successfully Moved to ' . $request->status);
    }

    /**
     * Validate that verification approvals are in proper order:
     * - Technical Reviewer (order 1) must approve first
     * - Lab Manager (order 2) can only see/approve after Technical Reviewer approves
     * - Both must approve before moving to Sample Approval
     */
    protected function validateVerificationApprovalOrder($batchId)
    {
        $technicalReviewer = BatchLabSectionApprover::where('batch_id', $batchId)
            ->where('batch_status', 'Sample Verification')
            ->where('approver_order', 1)
            ->where('is_technical_reviewer', true)
            ->first();

        $labManager = BatchLabSectionApprover::where('batch_id', $batchId)
            ->where('batch_status', 'Sample Verification')
            ->where('approver_order', 2)
            ->where('can_send_back_to_lab', true)
            ->first();

        // Both approvers must exist
        if (!$technicalReviewer || !$labManager) {
            throw new \Exception('Verification approvers not properly configured. Both Technical Reviewer and Lab Manager must be assigned.');
        }

        // Technical Reviewer must approve first (status = 1)
        if ($technicalReviewer->status != 1) {
            throw new \Exception('Technical Reviewer must approve first before Lab Manager can take action.');
        }

        // Lab Manager must also approve
        if ($labManager->status != 1) {
            throw new \Exception('Lab Manager approval is pending. Both approvers must approve before moving to Sample Approval.');
        }

        return true;
    }

    /**
     * Check if current user can approve at verification stage,
     * respecting the approval order sequence.
     */
    protected function canApproveAtVerificationStage($batchId, $userId)
    {
        $approver = BatchLabSectionApprover::where('batch_id', $batchId)
            ->where('batch_status', 'Sample Verification')
            ->where('user_id', $userId)
            ->first();

        if (!$approver) {
            return false; // User is not an assigned approver
        }

        // If this is Technical Reviewer (order 1), they can always approve
        if ($approver->approver_order == 1) {
            return true;
        }

        // If this is Lab Manager (order 2), Technical Reviewer must have already approved
        if ($approver->approver_order == 2) {
            $technicalReviewer = BatchLabSectionApprover::where('batch_id', $batchId)
                ->where('batch_status', 'Sample Verification')
                ->where('approver_order', 1)
                ->where('is_technical_reviewer', true)
                ->first();

            return $technicalReviewer && $technicalReviewer->status == 1;
        }

        return false;
    }

    public function move_to_stage(Request $request, $stage, $batch_id)
    {
        $batch = SampleHeader::find($batch_id);

        $custodyDetails = [
            'batch_id' => $batch_id,
            'comments' => $request->comments ?? '',
            'current' => [
                'status' => $batch->status,
                'tracking_stage' => $batch->sample_tracking_stage,
            ],
            'target' => [
                'status' => $batch->status,
                'tracking_stage' => $stage,
            ],
        ];

        $this->updateChainofCustody($custodyDetails);

        $batch->sample_tracking_stage = $stage;
        $batch->save();

        return redirect()->back()->with('success', 'Batch move was successful');
    }

    public function move_to_workflow(Request $request, $status, $batch_id)
    {
        $batch = SampleHeader::find($batch_id);

        if ($batch->status == 'Sample Verification' && $status == 'Sample Approval') {
            // Validate verification approval order first
            try {
                $this->validateVerificationApprovalOrder($batch_id);
            } catch (\Exception $exception) {
                return redirect()->back()->with('error', $exception->getMessage());
            }

            try {
                app(WorkflowService::class)->assertStageApprovalsCompleted((string) $batch->id, 'Sample Verification');
            } catch (ValidationException $exception) {
                $message = $exception->validator->errors()->first();
                $checklistUrl = route('sample-approval-checklist.show', [
                    'sample' => $batch->id,
                    'stage_name' => 'Sample Verification',
                ]);

                return redirect()->back()->with('error', $message . ' Complete checklist here: ' . $checklistUrl);
            }
        }

        if ($batch->status == 'Sample Approval' && in_array($status, ['Reports for Collection', 'Reports In Payment'], true)) {
            try {
                app(WorkflowService::class)->assertStageApprovalsCompleted((string) $batch->id, 'Sample Approval');
            } catch (ValidationException $exception) {
                $message = $exception->validator->errors()->first();
                $checklistUrl = route('sample-approval-checklist.show', [
                    'sample' => $batch->id,
                    'stage_name' => 'Sample Approval',
                ]);

                return redirect()->back()->with('error', $message . ' Complete checklist here: ' . $checklistUrl);
            }
        }

        if (in_array($batch->status, ['Sample Verification', 'Sample Approval', 'Reports for Collection', 'Reports In Payment']) && in_array($status, ['Samples In Lab', 'Samples Reception', 'Samples Request Review', 'Sample Verification'])) {
            $batch->approve_user_id = null;
            $batch->verify_user_id = $status != 'Sample Verification' ? null : $batch->verify_user_id;
            $batch->report_verified_date = null;
            $batch->approval_date = null;
            $batch->save();
            $status != 'Sample Verification' ? BatchLabSectionApprover::where('batch_id', $batch->id)->delete() : '';
            $status == 'Sample Verification' ? BatchLabSectionApprover::where('batch_id', $batch->id)->update(['status' => 0, 'approval_date' => null]) : '';
            BatchLabSectionApprover::where('batch_id', $batch->id)->where('batch_status', 'Sample Approval')->delete();
        }
        if (in_array($batch->status, ['Samples In Lab', 'Samples Reception', 'Samples Request Review', 'Sample Verification']) && in_array($status, ['Sample Approval', 'Reports for Collection', 'Reports In Payment']) && !isset($request->is_approval)) {
            return redirect()->back()->with('error', 'Kindly send the batch for verification');
        }
        if (in_array($batch->status, ['Sample Approval']) && !isset($request->is_approval) && in_array($status, ['Reports for Collection', 'Reports In Payment']) && $batch->approve_user_id < 0) {
            return redirect()->back()->with('error', 'Kindly approve the batch!');
        }
        if ($batch->status == 'Samples Reception' && $status == 'Samples Request Review' && $batch->customer_paid == 0 && $batch->begin_proccess == 0) {
            return redirect()->back()->with('error', 'The following batch has not being paid for!');
        }
        // return response()->json('test');
        $previousWorkflow = $batch->status;

        $stageSync = app(\App\Services\Sampleworkflow\BatchWorkflowStageSyncService::class);
        $targetTrackingStage = $stageSync->resolveTrackingStageId($batch, $status);

        if (! $targetTrackingStage) {
            Log::warning('Missing sample tracking stage while moving workflow; proceeding with null tracking stage', [
                'batch_id' => $batch_id,
                'from_status' => $batch->status,
                'to_status' => $status,
            ]);
        }

        $custodyDetails = [
            'batch_id' => $batch_id,
            'comments' => $request->comments ?? '',
            'current' => [
                'status' => $batch->status,
                'tracking_stage' => $batch->sample_tracking_stage,
            ],
            'target' => [
                'status' => $status,
                'tracking_stage' => \Illuminate\Support\Str::isUuid($targetTrackingStage) ? $targetTrackingStage : null,
            ],
        ];

        $this->updateChainofCustody($custodyDetails);

        // Keep batch tracking stage in sync with the target workflow stage
        $batch->sample_tracking_stage = \Illuminate\Support\Str::isUuid($targetTrackingStage) ? $targetTrackingStage : null;

        if ($batch->status == 'Samples In Lab' && $status == 'Sample Verification') {
            // Persist method deviation details at batch level when moving to verification
            $batch->has_method_deviation = $request->boolean('has_method_deviation');
            $batch->method_deviation_reason = $batch->has_method_deviation
                ? ($request->input('method_deviation_reason') ?? '')
                : null;

            if (isset($request->approver_id)) {
                $batch->verify_user_id = $request->approver_id;
            }
        }
        if ($batch->status == 'Sample Verification' && $status == 'Sample Approval') {
            $batch->report_verified_date = getTodayDate();
            if (isset($request->approver_id)) {
                $batch->approve_user_id = $request->approver_id;
                $batch->verify_user_id = auth()->user()->id;
            }
            // $batch->approve_user_id = auth()->user()->id;
        }
        if ($status == 'Reports for Collection') {
            $customer = getCrmCustomerByID($batch->crm_customer_id);
            $account_status = SystemConfiguration::find($customer->account_status);
            if (isset($account_status->id)) {
                if ($account_status->value != 'Account Holder(OK)') {
                    // if ($batch->invoice_id == 0) {
                    // 	return redirect()->back()->with('error', 'The following Batch has no invoice attached to it!');
                    // }
                }
            }
        }
        if ($batch->status == 'Samples In Lab') {
            $fail = SystemConfiguration::where('key', 'lab_report_comment_fail')->first();
            $pass = SystemConfiguration::where('key', 'lab_report_comment_pass')->first();
            if (!isset($fail->id) && !isset($pass->id)) {
                return redirect()->back()->with('error', 'Kindly add the PASS and FAIL lab report comments on system configurations.');
            }
            // $fail_arr = explode('_', $fail->value);

            // $pass_arr = explode('_', $pass->value);

            // $sample_type = SampleType::find($batch->sample_type_id);
            $samples = SampleDetails::where('sample_header_id', $batch->id)->get();
            // return response()->json($samples,200);
            foreach ($samples as $sample) {
                $captured = CapturedResult::where('sample_detail_id', $sample->id)->where('remark', 'FAIL')->get();
                $analytes = [];
                foreach ($captured as $ca) {
                    $a = Analyte::find($ca->analyte_id);
                    // return response()->json($a->name,200);
                    array_push($analytes, $a->name);
                }
                //$main_s = Standards::find($sample->main_standard);
                //if (sizeof($analytes) > 0) {
                //$message = $fail_arr[0] . ' ' . $sample_type->name . ' (' . $main_s->name . ')' . $fail_arr[4] . ' ' . implode(', ', $analytes) . ' ' . $fail_arr[5];
                //$sample->header_body = $message;
                //} else {
                //	$message = $pass_arr[0] . ' ' . $sample_type->name . ' (' . $main_s->name . ')';
                //	$sample->header_body = $message;
                //}
                $sample->save();
            }
        }

        $batch->status = $status;
        $batch->save();

        if (isset($request->send_message)) {
            $responsibility = SystemConfiguration::where('key', $batch->status)->first();
            if (isset($responsibility->id)) {
                $users = JobDescription::where(function ($query) use ($responsibility) {
                    $query->where('job_designation_responsibility.name', $responsibility->key)
                        ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                })
                    ->join('users', function ($join) {
                        $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                    })
                    ->get('users.*');

                $numbers = $users->pluck('phone')->toarray();
                $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
                foreach ($numbers as $num) {
                    $send = sendTextMessage($num, $message);
                    if ($send == 'error') {
                        return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                    }
                }
            }
        }

        if (isset($request->notification)) {
            $companyDetails = getCompanyDetails();
            // return response()->json($request->all,200);

            $responsibility = SystemConfiguration::where('key', $batch->status)->first();

            if ($batch->status == 'Samples In Lab') {
                $batch->verification_email_date = getTodayDate();
            } elseif ($batch->status == 'Sample Verification') {
                $batch->approval_email_date = getTodayDate();
            }
            if (isset($request->type) && $request->type == 'Recheck') {
                $message = 'Batch ' . $batch->batch_code . ' needs a recheck - Samples In Lab.';
            } else {
                $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
            }

            if (isset($responsibility->id)) {
                $users = JobDescription::where(function ($query) use ($responsibility) {
                    $query->where('job_designation_responsibility.name', $responsibility->key)
                        ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                })
                    ->join('users', function ($join) {
                        $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                    })
                    ->get('users.*');

                $emails = $users->pluck('email')->toarray();
                $position = $users->pluck('position')->toarray();

                $position = array_unique($position);
                if (!in_array(auth()->user()->email, $emails) && !auth()->user()->hasRole('admin')) {
                    return redirect()->back()->with('error', 'You are not allowed to perform this task!');
                }
                $active_company = getActiveCompany();
                if (!isset($request->type)) {
                    foreach ($users as $user) {
                        $body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $active_company->name;
                        $subject = '[' . $active_company->name . '] Batch Notification';
                        notify_user($body, $user->email, $subject);
                    }
                }

                $notification = new SystemNotifications();
                foreach ($position as $pos) {
                    $notification->batchNotification($batch, $pos, $message, $request->status);
                }
            }
        } else {
            if (isset($request->batch_code)) {
                foreach ($request->batch_code as $code) {
                    $batch = Sampleheader::where('batch_code', $code)->first();

                    $responsibility = SystemConfiguration::where('key', $batch->status)->first();
                    if (isset($responsibility->id)) {
                        $users = JobDescription::where(function ($query) use ($responsibility) {
                            $query->where('job_designation_responsibility.name', $responsibility->key)
                                ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                        })
                            ->join('users', function ($join) {
                                $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                            })
                            ->get('users.*');
                        // return response()->json($users,200);
                        $message = 'Batch ' . $code . ' needs your attention - ' . $request->status . '.';
                        $emails = $users->pluck('email')->toarray();
                        $position = $users->pluck('position')->toarray();
                        $position = array_unique($position);
                        $notification = new SystemNotifications();

                        foreach ($position as $pos) {
                            $notification->batchNotification($batch, $pos, $message, $request->status);
                        }
                    }
                }
            } elseif (isset($request->batch_id)) {
                $batchId = trim((string) $request->batch_id);
                $batch = Str::isUuid($batchId) ? Sampleheader::find($batchId) : null;

                if (!$batch) {
                    Log::warning('Skipping batch notification due to invalid or unknown batch_id', [
                        'batch_id' => $request->batch_id,
                        'status' => $request->status,
                    ]);
                } else {
                    $responsibility = SystemConfiguration::where('key', $batch->status)->first();
                    if (isset($responsibility->id)) {
                        $users = JobDescription::where(function ($query) use ($responsibility) {
                            $query->where('job_designation_responsibility.name', $responsibility->key)
                                ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                        })
                            ->join('users', function ($join) {
                                $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                            })
                            ->get('users.*');
                        // return response()->json($responsibility,200);
                        $message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';

                        $emails = $users->pluck('email')->toarray();
                        $position = $users->pluck('position')->toarray();
                        $position = array_unique($position);
                        $notification = new SystemNotifications();
                        foreach ($position as $pos) {
                            $notification->batchNotification($batch, $pos, $message, $request->status);
                        }
                    }
                }
            }
        }

        if (isset($request->type) && $request->type == 'Recheck') {
            $comment = new BatchComment();
            $comment->created_by = \Auth::user()->id;
            $comment->personnel_to_cc = $request->has('followers') ? implode(',', $request->followers) : 0;
            $comment->reminder_for = $request->user_id;
            $comment->comments = $request->comments;
            $comment->comment_type = $request->type;
            $comment->sample_header_id = $request->batch_id;
            $comment->save();

            $companyDetails = getCompanyDetails();
            $batch = getSampleHeaderByID($request->batch_id);
            $active_company = getActiveCompany();

            if ($comment->personnel_to_cc != 0) {
                $contacts = explode(',', $comment->personnel_to_cc);
                array_push($contacts, $comment->reminder_for);
                foreach ($contacts as $contact) {
                    $user = getUserById((int) $contact);
                    $message = 'There is a new note for batch ' . $batch->batch_code . '.<br><br> Kindly review the notes.';
                    $body = 'Hi ' . $user->name . ',<br><br>'
                        . $message . '<br>
							Regards, <br>'
                        . $active_company->name . ' ';
                    $subject = '[' . $companyDetails['name'] . '] Batch Recheck Notification';
                    $notify = notify_user($body, $user->email, $subject);
                }
            } else {
                $user = getUserById($comment->reminder_for);
                $message = 'There is a new note for batch ' . $batch->batch_code . '. Kindly review the notes.';
                $body = 'Hi ' . $user->name . ',<br><br>'
                    . $message . '<br>
						Regards, <br><br>'
                    . $companyDetails['name'] . ' ';
                $subject = '[' . $companyDetails['name'] . '] Batch Recheck Notification';
                $notify = notify_user($body, $user->email, $subject);
            }
        }

        return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch move was successful');
    }

    public function missing_analysis_parameters_by_sample_code(Request $request)
    {
        $analytes = [];
        $ids = json_decode($request->id, true);
        // return json_encode(DB::table('captured_results')->where('analysis_type_id', 10008)->pluck('analyte_id'));
        foreach ($ids as $id) {
            $elements = AnalysisElements::join('analytes as a', 'a.id', '=', 'analysis_elements.analyte_id')
                ->join('analysis_types as at', 'at.id', '=', 'analysis_elements.analysis_type_id')
                ->leftJoin('users as u', 'u.id', '=', 'analysis_elements.operator_id')
                ->leftJoin('equipment as e', 'e.id', '=', 'analysis_elements.equipment_id')
                ->leftjoin('captured_results as cr', 'cr.analyte_id', '=', 'analysis_elements.analyte_id')
                ->selectRaw('DISTINCT a.id, a.name, a.code, analysis_elements.reporting_unit, analysis_elements.reporting_symbol, e.name as equipment, e.id as equipment_id, u.name as operator, at.name as analysis_type, at.id as analysis_type_id,cr.analyte_status_contracted as analyte_status')
                ->whereNotIn(
                    'analysis_elements.analyte_id',
                    DB::table('captured_results')->where('sample_detail_code', $request->sample)->where('analysis_type_id', $id)
                        ->pluck('analyte_id')
                )

                ->where('analysis_elements.active', 1)
                ->where('analysis_elements.analysis_type_id', $id)->get()->toArray();

            $analytes = array_merge($analytes, $elements);
        }
        // return response()->json($analytes,200);
        return $analytes;
    }

    public function add_analyte_to_sample_analysis(Request $request)
    {
        $item = $request->item;

        if (!$item || count($item) == 0) {
            return redirect()->back()->with('error', 'No analytes to be added were selected.');
        }

        // return json_encode($item);

        foreach ($item as $i) {
            $i = json_decode($i);

            $sampleDetail = SampleDetails::where('sample_code', $i->sample_code)->first();
            $analysisType = AnalysisElements::where('analysis_type_id', $i->analysis_type_id)
                ->where('analyte_id', $i->id)->first();

            $captured = CapturedResult::where('sample_detail_code', $i->sample_code)
                ->where('sample_detail_id', $sampleDetail->id)
                ->where('analyte_id', $i->id)
                ->where('analysis_type_id', $i->analysis_type_id)
                ->where('sample_header_id', $sampleDetail->sample_header_id)->first() ?? new CapturedResult();
            $captured->sample_detail_code = $i->sample_code;
            $captured->sample_detail_id = $sampleDetail->id;
            $captured->sample_header_id = $sampleDetail->sample_header_id;
            $captured->analyte_id = $i->id;
            $captured->analysis_type_id = $i->analysis_type_id;
            $captured->analyte_code = $i->code;
            $captured->equipment_id = $i->equipment_id;
            $captured->method_id = $analysisType->method ?? null;
            $captured->reporting_unit_id = resolveReportingUnitIdFromName($analysisType->reporting_unit ?? null);
            $captured->analysis_element_id = $analysisType->id ?? null;
            $batch = getSampleHeaderByID($sampleDetail->sample_header_id);
            $labstr = implode(',', $batch->labs(true));
            $labarr = explode(' - ', $labstr);
            $lab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
            $captured->analyte_status_contracted = $lab->is_external ?? 0;

            $actingUserId = auth()->id() ? (string) auth()->id() : null;
            app(CapturedResultCaptureService::class)->applyOnSave($captured, [], $actingUserId);

            $result = Result::where('sample_detail_code', $i->sample_code)
                ->where('sample_detail_id', $sampleDetail->id)
                ->where('captured_result_id', $captured->id)
                ->where('analyte_id', $i->id)
                ->where('analysis_type_id', $i->analysis_type_id)
                ->where('sample_header_id', $sampleDetail->sample_header_id)->first() ?? new Result();
            $result->captured_result_id = $captured->id;
            $result->sample_detail_code = $i->sample_code;
            $result->sample_detail_id = $sampleDetail->id;
            $result->sample_header_id = $sampleDetail->sample_header_id;
            $result->analyte_id = $i->id;
            $result->analysis_type_id = $i->analysis_type_id;
            $result->analyte_code = $i->code;
            $result->unit_code = $analysisType->reporting_unit ?? $i->reporting_unit ?? null;
            $result->reporting_symbol = $analysisType->reporting_symbol ?? $i->reporting_symbol ?? null;
            $result->analyte_status_contracted = $lab->is_external ?? 0;
            $result->recheck = 0;

            $result->save();
        }

        return redirect()->back()->with('success', 'Analyte has been added.');
    }

    public function send_payment_notification(Request $request)
    {
        $company = getActiveCompany();
        foreach ($request->batch_code as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();
            if (isset($batch->id)) {
                $customer = CRMCustomer::find($batch->crm_customer_id);

                if (isset($customer->id)) {
                    $body = getPaymentReminderBody($customer->name);
                    $subject = '[' . $company->name . '] Payment Reminder Batch - ' . $batch->batch_code;
                    $customer_contacts = CustomerContact::where('crm_customer_id', $customer->id)->where('receive_invoice', 1)->get();
                    foreach ($customer_contacts as $contact) {
                        notify_user($body, $contact->email, $subject);
                    }
                }
            } else {
                return redirect()->back()->with('error', 'No Batch record with the specified code!');
            }
        }

        return redirect()->back()->with('success', 'Payment reminders sent successfully!');
    }

    public function capture_raw_results(Request $request)
    {
        $batchid = 0;
        $captureService = app(CapturedResultCaptureService::class);
        $actingUserId = auth()->id() ? (string) auth()->id() : null;

        foreach ($request->captured_result_id as $cID) {
            $captured = CapturedResult::find($cID);

            $batchid = $captured->sample_header_id;
            $batch = SampleHeader::find($batchid);

            if (isset($request->subcontracted[$cID])) {
                $captured->analyte_status_contracted = 1;
            } else {
                $captured->analyte_status_contracted = 0;
            }
            if (isset($request->accredited[$cID])) {
                $captured->analyte_accredited = 1;
            } else {
                $captured->analyte_accredited = 0;
            }
            $captured->result_reporting_symbol = $request->result_reporting_symbol[$cID] ?? '';
            $reportingUnitInput = $request->reporting_unit[$cID] ?? null;
            $captured->reporting_unit_id = resolveReportingUnitIdFromName(
                $reportingUnitInput !== null && $reportingUnitInput !== '' ? (string) $reportingUnitInput : null
            );
            $captured->measure_uncertanity = $request->measure_uncertanity[$cID] ?? 0;
            $methodId = $request->method_id[$cID] ?? null;
            $captured->method_id = ($methodId !== null && $methodId !== '' && $methodId !== '0')
                ? (string) $methodId
                : null;
            $captured->result_reporting_symbol = $request->result_reporting_symbol[$cID] ?? '';
            $captured->analyte_code = Analyte::find($captured->analyte_id)->code;

            if ($batch->status == 'Samples In Lab') {
                $captured->ltm_method_id = $request->ltm_method_id[$cID] ?? '';
                $scientific_arr = is_numeric($request->result[$cID]) ? $this->toScientificNotation($request->result[$cID]) : [];
                $captured->scienctific_result = !is_numeric($request->result[$cID]) ? $request->result[$cID] : $scientific_arr['scientific'];
                $captured->result = $request->result[$cID] ?? '';
                $captured->remark = $captured->remark_is_manual == 0 ? $request->remark[$cID] : $request->remarkmanual[$cID];
                if (is_numeric($request->result[$cID])) {
                    if ($scientific_arr['to_power'] <= 0) {
                        $captured->supercsript_base = number_format($scientific_arr['value'], 1);
                        $captured->superscript_number = $scientific_arr['to_power'];
                        $captured->superscript_negative = round(intval($request->result[$cID])) >= 1 ? 0 : 1;
                    }
                }
                $main_std_code = $request->input('main_standard.' . $cID);
                $standard_main = $main_std_code ? Standards::where('code', $main_std_code)->first() : null;

                $sec_std_code = $request->input('secondary_standard.' . $cID);
                $sec_standard = $sec_std_code ? Standards::where('code', $sec_std_code)->first() : null;

                $third_std_code = $request->input('third_standard.' . $cID);
                $third_standard = $third_std_code ? Standards::where('code', $third_std_code)->first() : null;
                if (isset($standard_main->id)) {
                    $main_standard_analyte = StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $standard_main->id)->first();
                    $sec_standard_analyte = isset($sec_standard->id) ? StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $sec_standard->id)->first() : '';
                    $third_standard_analyte = isset($third_standard->id) ? StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $third_standard->id)->first() : '';
                } else {
                    return redirect()->back()->with('error', 'Kindly set Main and Secondary standard for the following sample!');
                }
                $captured->main_standard_id = isset($main_standard_analyte->id) ? $main_standard_analyte->id : 0;
                $captured->secondary_standard_id = isset($sec_standard_analyte->id) ? $sec_standard_analyte->id : 0;
                $captured->third_standard_id = isset($third_standard_analyte->id) ? $third_standard_analyte->id : 0;
                $captured->main_value = $request->main_value[$cID];
                if (isset($sec_standard_analyte->id)) {
                    if ($sec_standard_analyte->standard_value_type == 'is_range') {
                        $captured->secondary_value = $sec_standard_analyte->low . ' - ' . $sec_standard_analyte->high;
                    } else {
                        if ($sec_standard_analyte->standard_is_value == '') {
                            $standard_value = StandardValue::find($sec_standard_analyte->standard_value_id);
                            $captured->secondary_value = $standard_value->code;
                        } else {
                            $captured->secondary_value = $sec_standard_analyte->standard_is_value;
                        }
                    }
                } else {
                    $captured->secondary_value = '-';
                }
                if (isset($third_standard_analyte->id)) {
                    if ($third_standard_analyte->standard_value_type == 'is_range') {
                        $captured->third_value = $third_standard_analyte->low . ' - ' . $third_standard_analyte->high;
                    } else {
                        if ($third_standard_analyte->standard_is_value == '') {
                            $standard_value = StandardValue::find($third_standard_analyte->standard_value_id);
                            $captured->third_value = $standard_value->code;
                        } else {
                            $captured->third_value = $third_standard_analyte->standard_is_value;
                        }
                    }
                } else {
                    $captured->third_value = '-';
                }
            }

            $captureService->applyOnSave($captured, [], $actingUserId);

            $sample_detail = getSampleDetailById($captured->sample_detail_id);
            $sample_detail->ammendment_number = $batch->is_amendment;
            $sample_detail->save();
        }
        $batch = SampleHeader::find($batchid);
        $strStage = 'Capture Results';
        $samWk = 'Samples In Lab';
        $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
        if (!$stage) {
            $stage = SampleAnalysisStage::find(20015);
        }

        $custodyDetails = [
            'batch_id' => $batch->id,
            'comments' => $request->comments ?? '',
            'current' => [
                'status' => $batch->status,
                'tracking_stage' => $batch->sample_tracking_stage,
            ],
            'target' => [
                'status' => $samWk,
                'tracking_stage' => $stage ? $stage->id : $batch->sample_tracking_stage,
            ],
        ];
        $this->updateChainofCustody($custodyDetails);

        return redirect()->back()->with('success', 'Result details saved.');
    }

    public function fetch_results_remark(Request $request)
    {
        $first_res = '';
        $second_res = '';
        $third_res = '';

        $data = explode(',', $request->sample_code);
        $sample_detail = SampleDetails::where('sample_code', $data[0])->first();
        $reporting_symbol = $request->reporting_symbol;
        $analyte = Analyte::find($data[3]);
        $standard = Standards::find($sample_detail->main_standard);
        $sec_standard = Standards::find($sample_detail->secondary_standard);
        $third_standard = Standards::find($sample_detail->third_standard_id);
        $captured_result = CapturedResult::find($request->captured_result_id);
        if ($captured_result->repeat_captured_id > 0) {
            $result = $request->result;
            $range = explode(' - ', $captured_result->repeatsampleresult);
            if ($range[0] <= $result && $result <= $range[1]) {
                return response()->json('PASS', 200);
            } else {
                return response()->json('FAIL', 200);
            }
        }

        $first_res = isset($standard->id) ? $this->getResultRenark($standard, $analyte, $request->result, $reporting_symbol) : $first_res;
        $second_res = isset($sec_standard->id) ? $this->getResultRenark($sec_standard, $analyte, $request->result, $reporting_symbol) : $second_res;
        $third_res = isset($third_standard->id) ? $this->getResultRenark($third_standard, $analyte, $request->result, $reporting_symbol) : $third_res;
        $remarkArr = [];
        if ($first_res != '') {
            array_push($remarkArr, $first_res);
        }
        if ($second_res != '') {
            array_push($remarkArr, $second_res);
        }
        if ($third_res != '') {
            array_push($remarkArr, $third_res);
        }

        return response()->json($remarkArr, 200);

        if (in_array('FAIL', array_unique($remarkArr))) {
            return response()->json('FAIL', 200);
        } elseif (in_array('-', array_unique($remarkArr)) && in_array('PASS', array_unique($remarkArr))) {
            return response()->json('PASS', 200);
        } elseif (in_array('PASS', array_unique($remarkArr))) {
            return response()->json('PASS', 200);
        } else {
            return response()->json('-', 200);
        }
    }
    private function getResultRenark($standard, $analyte, $result, $reporting_symbol)
    {
        if (isset($standard->id) && isset($analyte->id)) {
            $analyte_guide = StandardAnalytes::where('analyte_id', $analyte->id)->where('standard_id', $standard->id)->first();
            if (isset($analyte_guide->standard_value_type)) {
                if ($analyte_guide->standard_value_type == 'is_range') {
                    if ($analyte_guide->low <= $result && $result <= $analyte_guide->high) {
                        return 'PASS';
                    } else {
                        return 'FAIL';
                    }
                } else {
                    $standard_value = StandardValue::find($analyte_guide->standard_value_id);
                    if (is_numeric($result)) {
                        $type = gettype($analyte_guide->standard_is_value);
                        if ($type == 'integer' || $type == 'double') {
                            if (trim($reporting_symbol) == '>') {
                                if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                    $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                    $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'less_than') {
                                    $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'greater_than') {
                                    $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                            } elseif ($reporting_symbol == '<') {
                                if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                    $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                    $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'less_than') {
                                    $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'greater_than') {
                                    $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                            } else {
                                if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                    $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                    $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'less_than') {
                                    $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'greater_than') {
                                    $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                            }

                            return $response;
                        } else {
                            if ($analyte_guide->standard_is_value != '') {
                                if (trim($reporting_symbol) == '>') {
                                    if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                        $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'less_than') {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'greater_than') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                } elseif ($reporting_symbol == '<') {

                                    if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                        $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'less_than') {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'greater_than') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                } else {
                                    if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '') {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'less_than') {
                                        $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'greater_than') {
                                        $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                }

                                return $response;
                            } else {
                                if (strtoupper(trim($standard_value->code)) == 'NS') {
                                    $response = '-';

                                    return $response;
                                } elseif (strtoupper(trim($standard_value->code)) == 'NIL') {
                                    $response = $result <= 0 ? 'PASS' : 'FAIL';

                                    return $response;
                                } elseif (strtoupper(trim($standard_value->code)) == 'ND') {
                                    $response = $result <= 0 ? 'PASS' : 'FAIL';

                                    return $response;
                                } elseif (strtoupper(trim($standard_value->code)) == 'ABSENT') {
                                    $response = in_array(strtoupper($result), ['ABSENT', 'ND']) ? 'PASS' : 'FAIL';

                                    return $response;
                                } else {
                                    $response = '-';

                                    return $response;
                                }
                            }
                            // $eresult->remarks = trim($analyte_guide->standard_is_value) == "" ? "PASS" : "++";
                        }
                    } else {
                        // return response()->json($analyte_guide,200);
                        $standard_value = StandardValue::find($analyte_guide->standard_value_id);
                        if (strtoupper($result) == 'TN') {
                            $response = 'FAIL';

                            return $response;
                        } elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == 'NS') {
                            $response = '-';

                            return $response;
                        } elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == 'NIL') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == 'ND') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'NIL' && strtoupper(trim($standard_value->code)) == 'ND') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'ABSENT' && strtoupper(trim($standard_value->code)) == 'ABSENT') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'PRESENT' && strtoupper(trim($standard_value->code)) == 'ABSENT') {
                            $response = 'FAIL';
                            return $response;
                        } else {
                            $response = '-';

                            return $response;
                        }
                    }
                }
            } else {
                return '-';
            }

            // if (isset($analyte_guide->id)) {
            // 	if ($analyte_guide->standard_value_type == 'is_range') {
            // 		if (floatval($analyte_guide->low )<= $result && $result <= floatval($analyte_guide->high)) {
            // 			return response()->json('PASS', 200);
            // 		} else {
            // 			return response()->json('FAIL', 200);
            // 		}
            // 	} elseif ($analyte_guide->standard_value_type == 'is_standard_value') {
            // 		$standard_value = StandardValue::find($analyte_guide->standard_value_id);
            // 		if (isset($standard_value->id)) {
            // 			if ($standard_value->code == 'IsValue') {
            // 				if ($result <= $analyte_guide->standard_is_value) {
            // 					return response()->json('PASS', 200);
            // 				} else {
            // 					return response()->json('FAIL', 200);
            // 				}
            // 			} else {
            // 				if($standard_value->code == "NIL" || $standard_value->code == "ND"){
            // 					if(!is_numeric($result)){
            // 						$response = in_array($result,$check_arr) ? "PASS" : 'FAIL';
            // 						return response()->json($response,200);
            // 					}else{

            // 					}
            // 				}
            // 				if($standard_value->code == 'NS'){
            // 					return '-';
            // 				}

            // 			}
            // 		}
            // 	}
            // } else {
            // 	return response()->json('-', 200);
            // }
        } else {
            return '-';
        }
    }

    public function process_raw_results(Request $request, $batch_id, $internal = false)
    {
        $header = app(ProcessedResultSyncService::class)->syncBatch((string) $batch_id, [
            'apply_lod_formatting' => true,
        ]);

        if ($internal) {
            return $header;
        }

        return redirect()->back()->with('success', 'Results Processed saved.');
    }

    public function process_results(Request $request, $batch_id, $internal = false)
    {
        $report_format = $request->report_format;
        \Log::info('process_results called', [
            'batch_id' => $batch_id,
            'report_format' => $report_format,
            'include_pesticide' => isset($request->add_pesticide) ? 1 : 0,
            'merge_with_attachments' => $request->boolean('merge_with_attachments'),
            'attachment_ids' => $request->input('attachment_ids', ''),
        ]);

        $header = app(ProcessedResultSyncService::class)->syncBatch((string) $batch_id);

        // When processing results at Sample Approval stage, generate Procedure Worksheet PDFs
        // and attach them to the batch as "Procedure Worksheet" attachments.
        if (! $internal && $header && $header->status === 'Sample Approval') {
            // Keep the procedure-worksheet "Checked By" aligned with the COA's final approver.
            // COA templates use: show_report=1 and status=1 (no strict batch_status filter),
            // then pick a final approver by title keywords.
            $batchApprovers = BatchLabSectionApprover::where('batch_id', $header->id)
                ->where('show_report', 1)
                ->where('status', 1)
                ->get();

            $checker = $batchApprovers->first(function (BatchLabSectionApprover $a) {
                $title = (string) ($a->title ?? '');

                return stripos($title, 'author') !== false
                    || stripos($title, 'approv') !== false
                    || stripos($title, 'signatory') !== false;
            }) ?? $batchApprovers->last();

            // Fallback for legacy data where show_report/status might not be populated consistently.
            if (! $checker) {
                $checker = BatchLabSectionApprover::where('batch_id', $header->id)
                    ->where('batch_status', 'Sample Approval')
                    ->where('status', 1)
                    ->orderByDesc('approval_date')
                    ->first();
            }

            // One worksheet PDF per (worksheet, analyte) combination in this batch.
            $combos = CapturedResult::where('sample_header_id', $header->id)
                ->whereNotNull('procedure_worksheet_id')
                ->whereNotNull('analyte_id')
                ->get(['procedure_worksheet_id', 'analyte_id'])
                ->unique(function ($row) {
                    return $row->procedure_worksheet_id . '-' . $row->analyte_id;
                });

            if ($combos->isNotEmpty()) {
                $pdfService = app(ProcedureWorksheetPdfService::class);

                foreach ($combos as $combo) {
                    $worksheet = ProcedureWorksheet::find($combo->procedure_worksheet_id);
                    if (! $worksheet) {
                        continue;
                    }

                    $analyteId = (string) $combo->analyte_id;

                    // Optionally scope to samples that have this analyte + worksheet in this batch.
                    $sampleIds = CapturedResult::where('sample_header_id', $header->id)
                        ->where('procedure_worksheet_id', $combo->procedure_worksheet_id)
                        ->where('analyte_id', $analyteId)
                        ->pluck('sample_detail_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $pdfService->generateAndAttach(
                        $header,
                        $worksheet,
                        $checker,
                        [$analyteId],
                        $sampleIds
                    );
                }
            }
        }

        if ($internal) {
            return $header;
        }

        $include_pesticide = isset($request->add_pesticide) ? 1 : 0;

        // Optional attachment merge parameters
        $merge_with_attachments = $request->boolean('merge_with_attachments');
        $attachment_ids = $request->input('attachment_ids', '');

        return redirect()->route('process-pdf-report', [
            'batch_id' => $batch_id,
            'report_format' => $report_format,
            'include_pesticide' => $include_pesticide,
            'merge_with_attachments' => $merge_with_attachments ? 1 : 0,
            'attachment_ids' => $attachment_ids,
            'gcla_language' => $request->input('gcla_language', 'sw'),
        ]);
    }

    public function remove_analyte_from_captured_result(Request $request)
    {
        $this->refactorReportingTime($request->id);

        CapturedResult::find($request->id)->delete();

        Result::where('captured_result_id', $request->id)->delete();

        return json_encode([
            'status' => true,
        ]);
    }

    private function refactorReportingTime($id)
    {
        $actual_c = CapturedResult::find($id);
        $analysis_type_ids = CapturedResult::where('sample_header_id', $actual_c->sample_header_id)->pluck('analysis_type_id')->toArray();
        $unique_typeIds = array_unique($analysis_type_ids);
        $analyte_ids = CapturedResult::where('sample_header_id', $actual_c->sample_header_id)->pluck('analyte_id')->toArray();
        $unique_analyteIds = array_unique($analyte_ids);

        $analysis_max_report_time = AnalysisType::whereIn('id', $unique_typeIds)->max('reporting_time');
        $analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $unique_typeIds)->whereIn('analyte_id', $unique_analyteIds)->max('reporting_time');
        $maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
        $targetDateStr = 'Target Date';
        $targetDate = \App\SampleDate::where('sample_header_id', $actual_c->sample_header_id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
        $targetDate->name = $targetDateStr;
        $targetDate->sample_header_id = $actual_c->sample_header_id;
        $sampleheader = getSampleHeaderByID($actual_c->sample_header_id);
        $targetDate->date = \Carbon\Carbon::parse($sampleheader->receipt_date)->addDays($maxReportingTime);
        $targetDate->save();

        return 'success';
    }

    public function send_report_email(Request $request)
    {
        if (!$request->has('sample_code')) {
            return redirect()->back()->with('error', 'No batch selected.');
        }

        if (!$request->has('contacts')) {
            return redirect()->back()->with('error', 'No customer contact selected.');
        }

        $mailData = ['batches' => $request->sample_code];
        // return response()->json($request->sample_code);

        $contact = CustomerContact::whereIn('id', $request->contacts)
            ->selectRaw('first_name, middle_name, last_name, email')->get();

        $cArr = [];
        $company = getActiveCompany();
        $message = $request->email_body;
        foreach ($request->sample_code as $code) {
            $batch = SampleHeader::where('id', $code)->first();
            $customer = CRMCustomer::find($batch->crm_customer_id);
            $samples = SampleDetails::where('sample_header_id', $batch->id)->get();
            $start = SampleDetails::where('sample_header_id', $batch->id)->first();
            $end = SampleDetails::where('sample_header_id', $batch->id)->orderBy('id', 'DESC')->first();
            $previous = $batch->status;
            // return response()->json(['start'=>$start,'end'=>$end],200);
            foreach ($contact as $c) {
                $body = 'Dear ' . $customer->name . ',<br><br>
                We are pleased to inform you that your test report is now ready. Please find the report
                attached for your review.<br>
                ' . ($message == '' ? '' : $message . '<br>') . '
                If you have any questions or clarifications, feel free to contact us.<br><br>
                Thank you for choosing FIVET COMPANY LIMITED.<br><br>
                Best regards, <br>
				
				' . $company->name;
                $subject = 'TEST REPORTS;' . $customer->name . ' - ' . $start->sample_code . ' - ' . $end->sample_code;
                $file = \storage_path() . '/app' . $batch->batch_report_url;
                $bcc = true;
                $notify = notify_user($body, $c->email, $subject, $file, $bcc);
            }
            $batch->email_date = getTodayDate();
            $batch->status = 'Completed';
            $batch->save();

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => 'Send out sample report to the client',
                'current' => [
                    'status' => $previous,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

            $this->updateChainofCustody($custodyDetails);
            // return response()->json($batch);
        }

        // return response()->json($cArr,200);

        // return $sendMail;

        return redirect()->back()->with('success', 'Reports sent out.');
    }

    public function certificate_analysis($id)
    {
        $sample = SampleHeader::find($id);
        $sample_details = SampleDetails::where('sample_header_id', $sample->id)->get();
        $customer = getCrmCustomerByID($sample->crm_customer_id);

        return view('layouts.lab.sample-workflow.certificateAnalysis', compact('sample', 'sample_details', 'customer'));
    }

    public function approve_batch($id)
    {
        abort_unless(auth()->user()->can('laboratory.components.approve for analysis.edit'), 403);
        
        $batch = getSampleHeaderByID($id);

        // QC results are written only by markQCBatchComplete (QcBatchCompletionService).
        if ($batch->verify_user_id == auth()->user()->id) {
            return redirect()->back()->with('error', 'You are not allowed to approve this batch');
        }
        $batch->approve_user_id = auth()->user()->id;
        $batch->approval_date = getTodayDate();
        $current_stage = $batch->status;

        $batch->save();

        if ($batch->is_qc_batch) {
            return redirect()->route('sample-workflow', ['status' => $current_stage])->with('success', 'Approval was successful. Mark the QC batch complete to record QC results.');
        }

        return redirect()->back()->with('success', 'Batch approved successfully');
    }

    public function delete_batch(Request $request)
    {
        abort_unless(auth()->user()->can('laboratory.components.all samples.delete'), 403);
        
        $codes = $request->batch_code ?: [];

        if (empty($codes)) {
            return redirect()->back()->with('error', 'No batches selected.');
        }

        foreach ($codes as $code) {
            $batch = SampleHeader::where('batch_code', $code)->get();
            $batch[0]->isactive = 0;

            $batch[0]->save();
        }

        return redirect()->back()->with('success', 'Batches deleted successfully!');
    }

    public function generate_batch_invoice(Request $request)
    {
        // DEPRECATED: Redirecting to new Sales Order Wizard
        // This method has been replaced with the modern Livewire-based wizard

        $batchCodes = $request->batch_code ?? [];
        $batchesParam = http_build_query(['batches' => $batchCodes]);

        return redirect()->route('billing.sales-order.create', $batchesParam)
            ->with('info', 'Using new Sales Order Wizard interface');
    }

    /**
     * DEPRECATED: Old pricelist-based invoice generation
     * Kept for reference only
     */
    private function generate_batch_invoice_OLD(Request $request)
    {
        $config = getConfigByName('generate_sample_invoice');
        if (!isset($config[0]->id)) {
            return redirect()->back()->with('error', 'generate_sample_invoice configuration is not set');
        }
        if ($config[0]->value == 'true') {
            $customer_ids = [];
            foreach ($request->batch_code as $code) {
                $batch = SampleHeader::where('batch_code', $code)->first();
                if ($batch->invoice_id != 0) {
                    return redirect()->back()->with('error', 'Batch' . $code . ' has an existing Invoice!');
                }
                array_push($customer_ids, $batch->crm_customer_id);
            }
            $check_customer = array_unique($customer_ids);
            if (sizeof($check_customer) > 1) {
                return redirect()->back()->with('error', 'The choosen batches are of different customers!');
            }
            $customer = CRMCustomer::find($check_customer[0]);
            if (!isset($customer->id)) {
                return redirect()->back()->with('error', 'There is no customer with the specified Batches!');
            }

            // OLD PRICELIST CODE REMOVED
            $invoice = new Invoice();
            $invoice->pricelist_id = $customer_pricelist[0]->pricelist_id;
            $invoice->currency_id = $pricelist->currency_id;
            $invoice->customer_id = $customer->id;
            $invoice->save();
            if ($customer->credit_days > 0) {
                $date = date('Y-m-d', strtotime($invoice->created_at . '+' . $customer->credit_days . ' days'));
            } else {
                $date = date('Y-m-d', strtotime($invoice->created_at . '+ 30 days'));
            }
            $invoice->due_date = $date;
            $invoice->invoice_number = app(InvoiceNumberGenerator::class)->next();
            $invoice->save();
            foreach ($request->batch_code as $code) {
                $batch = SampleHeader::where('batch_code', $code)->first();
                if (isset($batch->id)) {
                    $batch->invoice_id = $invoice->id;
                    $details = SampleDetails::where('sample_header_id', $batch->id)->get();
                    foreach ($details as $detail) {
                        $analysis_ids = explode(',', $detail->analysis_type_id);
                        foreach ($analysis_ids as $analysis_id) {
                            $price = PricelistItem::where('pricelist_id', $pricelist->id)->where('analysis_id', (int) $analysis_id)->where('sample_type_id', $batch->sample_type_id)->first();
                            if (!isset($price->id)) {
                                return redirect()->back()->with('error', 'kindly add analysis to pricelist!');
                            }
                            $check_invoice_detail = InvoiceDetails::where('invoice_id', $invoice->id)->where('analysis_type', (int) $analysis_id)->first();
                            if (!isset($check_invoice_detail->id)) {
                                $invoice_detail = new InvoiceDetails();
                                $invoice_detail->crm_customer_id = $batch->crm_customer_id;
                                $invoice_detail->analysis_type = $analysis_id;
                                $analysis = getAnalysisTypeID($analysis_id);
                                $invoice_detail->analysis_type_name = $analysis->name;
                                $invoice_detail->sample_header_id = $batch->id;
                                $invoice_detail->sample_detail_id = $detail->id;
                                $invoice_detail->invoice_id = $invoice->id;
                                $invoice_detail->cost_price = $price->cost_price;
                                $invoice_detail->selling_price = $price->selling_price;
                                if ($price->vat == 1) {
                                    $rate = TaxRegime::where('active', 1)->first();
                                    $tax = $rate->value / 100 * $price->selling_price;
                                    $total_price = $tax + $price->selling_price;
                                    $invoice_detail->selling_amount = $total_price;
                                    $invoice_detail->tax_rate = strval($rate->value);
                                    $invoice_detail->tax_amount = $tax;
                                    $invoice_detail->total = $total_price;
                                } else {
                                    $invoice_detail->selling_amount = $price->selling_price;
                                    $invoice_detail->total = $price->selling_price;
                                }
                                // return response()->json($price,200);
                                $invoice_detail->save();
                            } else {
                                $current = $check_invoice_detail->quantity;
                                $current_tax = $check_invoice_detail->tax_amount;
                                $unit_price = $check_invoice_detail->selling_amount;
                                $current_total = $check_invoice_detail->total;
                                $check_invoice_detail->total = $unit_price + $current_total;
                                $check_invoice_detail->quantity = $current + 1;
                                if ($check_invoice_detail->tax_rate != 0) {
                                    $taxable = strval($check_invoice_detail->tax_rate / 100 * $check_invoice_detail->selling_price);
                                    $check_invoice_detail->tax_amount = $taxable + $current_tax;
                                }
                                $check_invoice_detail->save();
                            }
                        }
                    }
                    $batch->save();
                }
            }
            $details_invoice = InvoiceDetails::where('invoice_id', $invoice->id);
            $details_invoice_total_including_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('total')->toarray();
            $details_invoice_total_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('tax_amount')->toarray();
            foreach ($details_invoice as $detail) {
                $selling_amount = $detail->selling_price * $detail->quantity;
                $detail->selling_price_amount = $selling_amount;
                $detail->save();
            }
            $total_including_tax = array_sum($details_invoice_total_including_tax);
            $total_invoice_tax = array_sum($details_invoice_total_tax);

            $invoice->total = $total_including_tax;
            $invoice->total_tax = $total_invoice_tax;
            $invoice->save();

            return redirect()->back()->with('success', 'Invoice Created Successfully');
        } else {
            return redirect()->back()->with('error', 'Kindly set generate_sample_invoice configuration value to true! ');
        }
    }

    public function generate_batch_invoice_ajax(Request $request)
    {
        // DEPRECATED: Redirecting to new Sales Order Wizard
        // Return URL for frontend to redirect
        $batchCodes = $request->batch_code ?? [];
        $url = route('billing.sales-order.create') . '?' . http_build_query(['batches' => $batchCodes]);

        return response()->json([
            'redirect' => $url,
            'message' => 'Redirecting to Sales Order Wizard...'
        ]);
    }

    /**
     * DEPRECATED: Old Zoho-based AJAX invoice generation
     * Kept for reference only
     */
    private function generate_batch_invoice_ajax_OLD(Request $request)
    {
        $config = getConfigByName('generate_sample_invoice');
        if (!isset($config[0]->id)) {
            return response()->json(['error' => 'generate_sample_invoice configuration is not set']);
        }
        if ($config[0]->value == 'true') {
            $batch_ids = SampleHeader::whereIn('batch_code', $request->batch_code)->leftJoin('customer_invoice', 'customer_invoice.id', '=', 'sample_headers.invoice_id')->whereNull('customer_invoice.sales_order_id')->pluck('sample_headers.id')->toArray();
            $customer_ids = SampleHeader::whereIn('id', $batch_ids)->pluck('crm_customer_id')->toArray();

            if (sizeof($batch_ids) < 1) {
                return response()->json(['error' => 'The selected batch(es) have sales order attached to already sent to zoho']);
            }
            $analysis_with_no_zoho = SampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->join('analysis_types', 'analysis_types.id', '=', 'sample_analysis_type_relation.analysis_type_id')->whereNull('analysis_types.zoho_id')->pluck('analysis_types.name')->toArray();
            if (sizeof($analysis_with_no_zoho) > 0) {
                return response()->json(['error' => 'The following analysis types (' . implode(',', $analysis_with_no_zoho) . ') have not been tied to a zoho item']);
            }
            $check_customer = array_unique($customer_ids);
            if (sizeof($check_customer) > 1) {
                return response()->json(['error' => 'The choosen batches are of different customers!']);
            }
            $customer = CRMCustomer::find($check_customer[0]);
            if (!isset($customer->id)) {
                return response()->json(['error' => 'There is no customer with the specified Batches!']);
            }
            if ($customer->currency_id == '') {
                return response()->json(['error' => 'The specified customer has no currency assigned']);
            }
            if (!isset($customer->zohocustomer->zoho_contact_id)) {
                return response()->json(['error' => 'The specified customer has not been tied to zoho customer']);
            }
            $invoices_ids = SampleHeader::whereIn('batch_code', $request->batch_code)->leftJoin('customer_invoice', 'customer_invoice.id', '=', 'sample_headers.invoice_id')->whereNull('customer_invoice.sales_order_id')->pluck('customer_invoice.id')->toArray();
            if (sizeof($invoices_ids) > 0) {
                Invoice::whereIn('id', $invoices_ids)->update(['deleted_at' => date('Y-m-d'), 'delete_reason' => 'Generation of another invoice for batch ' . implode(', ', $request->batch_code)]);
                SampleHeader::whereIn('invoice_id', $invoices_ids)->update(['invoice_id' => null]);
            }

            $module = "Inventory-Management";
            $defaultCurrency = ModulePreConfigs::where('type', 'Currency')->where('module', $module)->where("name", "KES")->first();

            $invoice = new Invoice();
            $invoice->pricelist_id = 0;
            $invoice->currency_id = $customer->currency_id > 0 ? $customer->currency_id : $defaultCurrency->id;
            $invoice->customer_id = $customer->id;
            $invoice->zoho_customer_id = $customer->zohocustomer->zoho_contact_id;
            $invoice->save();
            if ($customer->credit_days > 0) {
                $date = date('Y-m-d', strtotime($invoice->created_at . '+' . $customer->credit_days . ' days'));
            } else {
                $date = date('Y-m-d', strtotime($invoice->created_at . '+ 30 days'));
            }
            $invoice->due_date = $date;
            $id_str = strval($invoice->id);
            if (strlen($id_str) < 4) {
                $count = 4 - strlen($id_str);
                $zeros = str_repeat('0', $count);
                $number = 'IM/SO/' . $zeros . $id_str;
            } else {
                $number = 'IM/SO/' . $id_str;
            }
            $invoice->invoice_number = $number;
            $invoice->save();
            $analyis_types = SampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->join('analysis_types', 'analysis_types.id', '=', 'sample_analysis_type_relation.analysis_type_id')->join('inventory_sub_categories', 'inventory_sub_categories.id', '=', 'analysis_types.zoho_id')->leftjoin('zoho_items_pricelist', function ($join) use ($customer) {
                $join->on('inventory_sub_categories.id', '=', 'zoho_items_pricelist.item_id');
                $join->on('zoho_items_pricelist.customer_id', '=', DB::raw($customer->id));
            })->selectRaw('sample_analysis_type_relation.*,analysis_types.name,inventory_sub_categories.name as zoho_name,inventory_sub_categories.unit_price,inventory_sub_categories.zoho_item_code,inventory_sub_categories.id as zoho_analysis_type,zoho_items_pricelist.unit_price as unit_price_rate')->get();

            $details_arr = [];
            foreach ($analyis_types as $a_type) {
                $unit_price = $a_type->unit_price_rate > 0 ? $a_type->unit_price_rate : $a_type->unit_price ?? 0;
                if (!isset($details_arr[$a_type->zoho_item_code])) {
                    // $details_arr[$a_type->zoho_item_code] = [];
                    $details_arr[$a_type->zoho_item_code] = [
                        "crm_customer_id" => $customer->id,
                        "analysis_type" => $a_type->zoho_analysis_type,
                        "analysis_type_name" => $a_type->name,
                        "sample_header_id" => $a_type->batch_id,
                        "sample_detail_id" => $a_type->sample_detail_id,
                        "invoice_id" => $invoice->id,
                        "selling_price" => $unit_price,
                        "cost_price" => 0,
                        "zoho_item_id" => $a_type->zoho_item_code,
                        "zoho_item_name" => $a_type->zoho_name,
                        "quantity" => 1,
                        "total" => $unit_price,
                        "final_unit_price" => $unit_price,
                    ];
                } else {
                    $analysis_arr = explode(',', $details_arr[$a_type->zoho_item_code]['analysis_type_name']);
                    if (!in_array($a_type->name, $analysis_arr)) {
                        $details_arr[$a_type->zoho_item_code]['analysis_type_name'] .= ', ' . $a_type->name;
                    }
                    $details_arr[$a_type->zoho_item_code]['sample_header_id'] .= ', ' . $a_type->batch_id;
                    $details_arr[$a_type->zoho_item_code]['sample_detail_id'] .= ', ' . $a_type->sample_detail_id;
                    $details_arr[$a_type->zoho_item_code]['quantity'] += 1;
                    $details_arr[$a_type->zoho_item_code]['total'] = $details_arr[$a_type->zoho_item_code]['quantity'] * $unit_price;
                }
            }
            InvoiceDetails::where('invoice_id', $invoice->id)->delete();
            $details = array_values($details_arr);
            InvoiceDetails::insert($details);
            $details_invoice = InvoiceDetails::where('invoice_id', $invoice->id)->get();

            $invoice->total = InvoiceDetails::where('invoice_id', $invoice->id)->sum('total');
            // $invoice->total_tax = 0;
            $invoice->save();
            SampleHeader::wherein('id', $batch_ids)->update(['invoice_id' => $invoice->id]);
            return response()->json(['success' => 'Invoice Created Successfully', "invoice" => $invoice, "details" => $details_invoice, 'customer' => $customer]);
        } else {
            return response()->json(['error' => 'Kindly set generate_sample_invoice configuration value to true!']);
        }
    }
    public function sendSalesOrder($invoice_id)
    {
        $invoice = Invoice::with(['currencyinfo', 'crmCustomer'])->find($invoice_id);
        $details = InvoiceDetails::where('invoice_id', $invoice_id)->get();
        $batchids = SampleHeader::where('invoice_id', $invoice->id)->pluck('id')->toArray();
        $sample = SampleDetails::whereIn('sample_header_id', $batchids)->orderBy('id', 'ASC')->first();
        $lineitems = [];
        $itemcounter = 0;
        foreach ($details as $detail) {
            $lineitems[] = [
                "item_order" => $itemcounter,
                "item_id" => $detail->zoho_item_id,
                "rate" => $detail->selling_price,
                "name" => $detail->zoho_item_name,
                "description" => $detail->analysis_title,
                "quantity" => $detail->quantity,
                "discount" => $detail->discount > 0 ? ($detail->discount_type == 'percentage' ? $detail->discount . '%' : $detail->discount) : 0,
            ];
            $itemcounter = $itemcounter + 1;
        }

        $salesOrder = [
            "customer_id" => $invoice->zoho_customer_id,
            "currency_id" => $invoice->currencyinfo->zoho_id,
            "date" => date('Y-m-d'),
            "line_items" => $lineitems,
            "reference_number" => $sample->sample_code,
            "custom_fields" => [
                [
                    "customfield_id" => config('zoho.ZOHO_SO_IMARAUSER_FIELD'),
                    "value" => auth()->user()->name,
                ]
            ],

        ];

        // return response()->json($salesOrder);
        $zohoService = new ZohoController();
        $zoho_sales = $zohoService->createSalesrder($salesOrder);
        $zoho_sales_id = isset($zoho_sales['salesorder']['salesorder_id']) ? $zoho_sales['salesorder']['salesorder_id'] : 0;
        $invoice->zoho_response = json_encode($zoho_sales);
        if ($zoho_sales_id != 0) {
            $invoice->sales_order_id = $zoho_sales_id;
            $invoice->save();
            return response()->json(['success' => 'Sales Order Created successfully!', 'invoice' => $invoice]);
        }
        $invoice->save();
        return response()->json(['error' => 'Sales Order not created successfully!', 'invoice' => $invoice, 'zoho_res' => $zoho_sales, 'salesorder' => $salesOrder]);
    }

    public function return_back_verification(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        // return response()->json($request->all(),200);
        if (isset($batch->id)) {
            $current = $batch->status;
            $batch->verify_user_id = null;
            $batch->approve_user_id = null;
            $batch->approval_date = null;
            $batch->status = 'Sample Verification';
            if (isset($request->batch_comment)) {
                $sample_detail = SampleDetails::where('sample_header_id', $batch->id)->first();
                $sample_detail->main_body = $request->comment ?? '';
                $sample_detail->save();
            }
            $batch->save();
            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comment ?? '',
                'current' => [
                    'status' => $current,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

            $this->updateChainofCustody($custodyDetails);
        } else {
            return redirect()->back()->with('error', 'No batch with the specified ID!');
        }

        return redirect()->back()->with('success', 'Batch return to verification successfully!');
    }

    public function approve_batch_begin_process(Request $request)
    {
        abort_unless(auth()->user()->can('laboratory.components.approve for analysis.edit'), 403);
        
        $codes = $request->batch_code ?: [];

        if (empty($codes)) {
            return redirect()->back()->with('error', 'No batches selected.');
        }

        foreach ($codes as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();
            if (isset($batch->id)) {
                $batch->begin_proccess = 1;
                $batch->save();
            } else {
                return redirect()->back()->with('error', 'There is no batch with the specified code -' . $code);
            }
        }

        return redirect()->back()->with('success', 'Batches approved to begin process successfully');
    }

    /**
     * Regenerate the submission form PDF for a batch and replace any existing Submission Form attachment.
     * Batch must have a submission_form_instance_id. Uses latest instance data for PDF generation.
     */
    public function regenerateSubmissionForm(Request $request, $batch): \Illuminate\Http\RedirectResponse
    {
        $sampleHeader = SampleHeader::find($batch);
        if (! $sampleHeader || ! $sampleHeader->hasSubmissionForm()) {
            return redirect()->back()->with('error', 'This batch has no linked submission form.');
        }

        $instance = $sampleHeader->submissionFormInstance;
        if (! $instance) {
            return redirect()->back()->with('error', 'Submission form instance not found.');
        }

        $submissionFormAttachmentTypeId = SystemConfiguration::where('key', 'attachment_type')
            ->where('value', 'Submission Form')
            ->value('id');
        if ($submissionFormAttachmentTypeId === null) {
            $submissionFormAttachmentTypeId = SystemConfiguration::where('key', 'attachment_type')->value('id');
        }
        if ($submissionFormAttachmentTypeId === null) {
            return redirect()->back()->with('error', 'Submission Form attachment type is not configured.');
        }

        $existing = BatchAttachment::where('batch_id', $sampleHeader->id)
            ->where('attachment_type', $submissionFormAttachmentTypeId)
            ->get();
        foreach ($existing as $att) {
            $url = $att->attachment_url;
            if ($url && str_starts_with($url, '/storage/batch-attachments/')) {
                $filename = basename(urldecode(parse_url($url, PHP_URL_PATH)));
                Storage::delete('batch-attachments/' . $filename);
            }
            $att->delete();
        }

        app(SubmissionFormPdfService::class)->attachSubmissionFormPdfToBatch(
            $instance,
            $sampleHeader,
            $submissionFormAttachmentTypeId
        );

        return redirect()->back()->with('success', 'Submission form PDF regenerated and attached.');
    }

    public function add_batch_attachment(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        if (! isset($batch->id)) {
            return redirect()->back()->with('error', 'No batch with the specified ID');
        }

        if ($request->boolean('show_on_coa')) {
            return $this->storeCoaMergedAttachment($request, $batch);
        }

        $request->validate([
            'batch_id' => 'required|string|exists:sample_headers,id',
            'title' => 'required|string|max:255',
            'attachment_type' => 'required',
            'attachment' => 'required|file',
            'selected_captured_result_ids' => 'nullable|string',
        ]);

        $new = new BatchAttachment();
        $new->batch_id = $batch->id;
        $new->uploaded_by = auth()->user()->id;
        $new->title = $request->title;
        $new->attachment_type = $request->attachment_type;
        if (isset($request->is_internal) || isset($request->internal_use)) {
            $new->is_internal = 1;
        }
        $new->show_on_coa = 0;
        $path = $request->attachment->path();
        $file = Storage::putFile('batch-attachments', new File($path));
        $file = explode('/', $file);

        $fName = '/storage/batch-attachments/' . urlencode(end($file));

        $new->attachment_url = (string) $fName;
        $new->save();

        $this->linkCapturedResultsToAttachment($request, $batch, $new);

        return redirect()->back()->with('success', 'Attachment Added Successfully');
    }

    private function storeCoaMergedAttachment(Request $request, SampleHeader $batch): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'batch_id' => 'required|string|exists:sample_headers,id',
            'title' => 'required|string|max:255',
            'attachment_type' => 'required',
            'coa_attachments' => 'required|array|min:1',
            'coa_attachments.*' => 'required|file|mimes:pdf',
            'coa_file_order' => 'nullable|string',
            'selected_captured_result_ids' => 'nullable|string',
        ]);

        $mergeService = app(\App\Services\Sampleworkflow\CoaAttachmentMergeService::class);
        $testRequestReportPath = $mergeService->resolveTestRequestReportPath($batch);

        if ($testRequestReportPath === null) {
            return redirect()->back()->with(
                'error',
                'Test Request Report PDF is not available for this batch. Generate the Test Request Report first, then try again.'
            );
        }

        $uploadedFiles = $request->file('coa_attachments', []);
        $indexedPaths = [];
        foreach ($uploadedFiles as $index => $uploadedFile) {
            $indexedPaths[(string) $index] = $uploadedFile->getRealPath();
        }

        $orderedPaths = [$testRequestReportPath];
        $order = array_values(array_filter(explode(',', (string) $request->input('coa_file_order', ''))));

        if ($order === []) {
            foreach ($indexedPaths as $path) {
                $orderedPaths[] = $path;
            }
        } else {
            foreach ($order as $orderedIndex) {
                if (isset($indexedPaths[$orderedIndex])) {
                    $orderedPaths[] = $indexedPaths[$orderedIndex];
                }
            }
        }

        try {
            $mergedContent = $mergeService->mergeFilesToPdfContent($orderedPaths);
        } catch (\Throwable $exception) {
            return redirect()->back()->with('error', 'Failed to merge PDFs with the Test Request Report: '.$exception->getMessage());
        }

        $fileName = 'TRR_Merged_'.time().'.pdf';
        $storagePath = 'batch-attachments/'.$fileName;
        Storage::put($storagePath, $mergedContent);

        $new = new BatchAttachment();
        $new->batch_id = $batch->id;
        $new->uploaded_by = auth()->user()->id;
        $new->title = $request->title;
        $new->attachment_type = $request->attachment_type;
        $new->is_internal = isset($request->is_internal) || isset($request->internal_use) ? 1 : 0;
        $new->show_on_coa = 1;
        $new->attachment_url = '/storage/'.$storagePath;
        $new->save();

        $this->linkCapturedResultsToAttachment($request, $batch, $new);

        return redirect()
            ->route('show-pdf-annotation-page', $new->id)
            ->with('success', 'Files merged with the Test Request Report. You can now annotate the combined PDF.');
    }

    private function linkCapturedResultsToAttachment(Request $request, SampleHeader $batch, BatchAttachment $new): void
    {
        if (! $request->filled('selected_captured_result_ids')) {
            return;
        }

        $ids = array_filter(
            array_map('intval', explode(',', $request->selected_captured_result_ids))
        );

        if (empty($ids)) {
            return;
        }

        $baseQuery = CapturedResult::whereIn('id', $ids)
            ->where('sample_header_id', $batch->id);

        $existingAttachmentIds = (clone $baseQuery)
            ->whereNotNull('batch_attachment_id')
            ->pluck('batch_attachment_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $baseQuery->update(['batch_attachment_id' => $new->id]);

        CapturedResult::whereIn('id', $ids)
            ->where('sample_header_id', $batch->id)
            ->whereIn('result', ['has attachment', 'No attachment', 'no attachment'])
            ->update(['result' => 'as attached']);

        foreach ($existingAttachmentIds as $oldAttachmentId) {
            if ((int) $oldAttachmentId === (int) $new->id) {
                continue;
            }

            $stillLinked = CapturedResult::where('sample_header_id', $batch->id)
                ->where('batch_attachment_id', (int) $oldAttachmentId)
                ->exists();

            if ($stillLinked) {
                continue;
            }

            $old = BatchAttachment::where('batch_id', $batch->id)
                ->where('id', (int) $oldAttachmentId)
                ->first();

            if (! $old) {
                continue;
            }

            $url = $old->attachment_url;
            if ($url && str_starts_with($url, '/storage/batch-attachments/')) {
                $filename = basename(urldecode(parse_url($url, PHP_URL_PATH)));
                Storage::delete('batch-attachments/'.$filename);
            }

            \App\Models\BatchAttachmentAnnotation::where('batch_attachment_id', $old->id)->delete();
            $old->delete();
        }
    }

    public function merge_attachments(Request $request)
    {
        $request->validate([
            'attachment_ids' => 'required|string',
            'title' => 'required|string',
            'attachment_type' => 'required',
            'batch_id' => 'required|integer'
        ]);

        $ids = explode(',', $request->attachment_ids);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No attachments selected for merging.');
        }

        // Fetch attachments. Note: WHERE IN does not guarantee order, so we sort manually
        $attachments = BatchAttachment::whereIn('id', $ids)->get();

        $orderedAttachments = [];
        foreach ($ids as $id) {
            $att = $attachments->firstWhere('id', $id);
            if ($att) {
                $orderedAttachments[] = $att;
            }
        }

        if (empty($orderedAttachments)) {
            return redirect()->back()->with('error', 'Could not retrieve selected attachments.');
        }

        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $validFiles = [];
        $totalPageCount = 0;

        foreach ($orderedAttachments as $attachment) {
            $relativePath = urldecode($attachment->attachment_url);
            $relativePath = ltrim($relativePath, '/');
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
                    $tempPdf = new TcpdfFpdi();
                    $pCount = $tempPdf->setSourceFile($filePath);
                    $totalPageCount += $pCount;
                    $validFiles[] = ['path' => $filePath, 'count' => $pCount, 'title' => $attachment->title];
                } catch (\Exception $e) {
                    \Log::warning("Could not pre-scan PDF {$attachment->title}: " . $e->getMessage());
                }
            }
        }

        $filesMerged = count($validFiles);
        if ($filesMerged === 0) {
            return redirect()->back()->with('error', 'No valid files found to merge.');
        }

        $currentPageGlobal = 1;
        foreach ($validFiles as $fileInfo) {
            try {
                $pdf->setSourceFile($fileInfo['path']);
                for ($pageNo = 1; $pageNo <= $fileInfo['count']; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
                    $pdf->useTemplate($templateId);

                    // Set white fill color for covering original page numbers
                    $pdf->SetFillColor(255, 255, 255); // White
                    $pdf->SetDrawColor(255, 255, 255);

                    // Cover common page number positions with comprehensive areas
                    // Use larger coverage to account for different font sizes, positions, and variations
                    $coverageWidth = 90; // Generous width for "Page 999 of 9999" in various font sizes
                    $coverageHeight = 22; // Generous height for page numbers in various font sizes

                    // 1. Bottom-right position (most common)
                    // Cover multiple variations to catch all possible positions
                    $pdf->Rect($size['width'] - 95, $size['height'] - 28, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect($size['width'] - 85, $size['height'] - 23, 80, 20, 'F');
                    $pdf->Rect($size['width'] - 75, $size['height'] - 18, 70, 18, 'F');

                    // 2. Top-right position (covers "Page 1 of 6" etc.)
                    // Cover multiple variations in top-right corner
                    $pdf->Rect($size['width'] - 95, 0, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect($size['width'] - 85, 0, 80, 28, 'F');
                    $pdf->Rect($size['width'] - 75, 0, 70, 22, 'F');

                    // 3. Bottom-center position (some reports use this)
                    $bottomCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
                    $pdf->Rect($bottomCenterX, $size['height'] - 28, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect(($size['width'] / 2) - 45, $size['height'] - 23, 90, 20, 'F');

                    // 4. Top-center position (less common but some documents use it)
                    $topCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
                    $pdf->Rect($topCenterX, 0, $coverageWidth, $coverageHeight, 'F');

                    // Now add "Page X of Y" numbering at the bottom center
                    $pdf->SetFont('helvetica', '', 10);
                    $text = "Page $currentPageGlobal of $totalPageCount";
                    $xTextPos = $size['width'] / 2 - 15; // Where text will be drawn
                    $yTextPos = $size['height'] - 10; // Where text will be drawn
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->Text($xTextPos, $yTextPos, $text);

                    $currentPageGlobal++;
                }
            } catch (\Exception $e) {
                \Log::error("Error merging file {$fileInfo['title']}: " . $e->getMessage());
            }
        }

        if ($filesMerged === 0) {
            return redirect()->back()->with('error', 'No valid files found to merge.');
        }

        // Output merged PDF
        $outputContent = $pdf->Output('', 'S');
        $fileName = 'Merged_Report_' . time() . '.pdf';
        $storagePath = 'batch-attachments/' . $fileName;

        // Save to storage
        Storage::put($storagePath, $outputContent);

        // Save to Database
        $newAttachment = new BatchAttachment();
        $newAttachment->batch_id = $request->batch_id;
        $newAttachment->uploaded_by = auth()->user()->id;
        $newAttachment->title = $request->title;
        $newAttachment->attachment_type = $request->attachment_type;
        $newAttachment->is_internal = 0; // Default to public/external
        $newAttachment->attachment_url = '/storage/' . $storagePath;
        $newAttachment->save();

        return redirect()->back()->with('success', 'Attachments merged successfully!');
    }

    public function delete_batch_attachmment(Request $request)
    {
        $attachment = BatchAttachment::find($request->attachment_id);
        if ($attachment) {

            // Delete the physical file
            $relativePath = urldecode($attachment->attachment_url);
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                // Fallback check in storage/app
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
                    \Log::info("Deleted attachment file: {$filePath}");
                } catch (\Exception $e) {
                    \Log::error("Failed to delete attachment file: {$filePath}. Error: " . $e->getMessage());
                }
            } else {
                \Log::warning("Attachment file to delete not found: {$attachment->attachment_url}");
            }

            // Delete the database record
            // Since User requested "Deletes it permanently", we force delete if soft deletes were enabled, 
            // but BatchAttachment model doesn't use SoftDeletes trait, so delete() is permanent.
            $attachment->delete();

            return redirect()->back()->with('success', 'Attachment deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'No attachment with the specified ID');
        }
    }

    public function downloadBatchAttachment($id)
    {
        $attachment = BatchAttachment::findOrFail($id);

        // Get the file path from attachment_url
        $relativePath = urldecode($attachment->attachment_url);
        $relativePath = ltrim($relativePath, '/');
        $filePath = public_path($relativePath);

        // Check if file exists in public path
        if (!file_exists($filePath)) {
            // Fallback: Check in storage/app
            $cleanPath = ltrim($relativePath, '/');
            if (strpos($cleanPath, 'storage/') === 0) {
                $storageInternalPath = substr($cleanPath, 8);
                $fallbackPath = storage_path('app/' . $storageInternalPath);

                if (file_exists($fallbackPath)) {
                    $filePath = $fallbackPath;
                }
            }
        }

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Attachment file not found.');
        }

        // Get the original filename from the path
        $fileName = basename($filePath);

        // Return the file as a download
        return response()->download($filePath, $fileName);
    }

    public function fetch_sample_type($id)
    {
        $sample_type = SampleType::find($id);
        $analysis_types = AnalysisType::where('sample_type_id', $sample_type->id)->get();

        $final['analysis'] = $analysis_types;
        $final['sample_type'] = $sample_type->name;

        return $final;
    }

    public function fetch_sample_analyte($id, $analysis, $detail = false)
    {
        $sample_type = SampleType::find($id);
        $analysis_types = AnalysisType::where('sample_type_id', $sample_type->id)->get();
        $analytes = [];
        $selected_analysis = explode(',', $analysis);
        $sub = [];
        $acc = [];
        $default = [];
        $both = [];
        if ($detail != false) {
            $detail_data = QuotationDetails::find($detail);
            $sub = explode(',', $detail_data->subcontracted_analytes);
            $acc = explode(',', $detail_data->accredited_analytes);
            $default = explode(',', $detail_data->default_analytes);
            $both = explode(',', $detail_data->sub_acc_analytes);
        }
        $check = array_merge($sub, $acc, $default, $both);
        foreach ($analysis_types as $type) {
            if (in_array($type->id, $selected_analysis)) {
                $analysis_analytes = AnalysisElements::where('analysis_type_id', $type->id)->get();
                foreach ($analysis_analytes as $aa) {
                    $analyte = getAnalyteByID($aa->analyte_id);
                    $aa->analyte_code = $analyte->code;
                    $aa->analyte_name = $analyte->name;
                    $aa->default = in_array($aa->id, $default) ? 1 : 0;
                    $aa->acc = in_array($aa->id, $acc) ? 1 : 0;
                    $aa->sub = in_array($aa->id, $sub) ? 1 : 0;
                    $aa->both = in_array($aa->id, $both) ? 1 : 0;
                    $aa->present = $detail != false ? 1 : 0;
                    if ($detail != false) {
                        if (in_array($aa->id, $check)) {
                            $aa->selected = 1;
                        } else {
                            $aa->selected = 0;
                        }
                    } else {
                        $aa->selected = 1;
                    }

                    if (!isset($analytes[$type->name])) {
                        $analytes[$type->name] = [];
                    }
                    array_push($analytes[$type->name], $aa);
                }
            }
        }

        return $analytes;
    }

    public function check_rft_no(Request $request)
    {
        $batch = SampleHeader::where('reference_number', $request->rft_no)->first();
        if (isset($batch->id)) {
            if ($batch->id == $request->batch_id) {
                return response()->json('success');
            } else {
                return response()->json('fail');
            }
        } else {
            return response()->json('success');
        }
    }

    public function resolveTest()
    {
        $range_r = range(738, 747);
        foreach ($range_r as $r) {
            $sample = getSampleDetailById($r);
            $captured = CapturedResult::where('sample_detail_id', $sample->id)->get();
            $results = Result::where('sample_detail_id', $sample->id)->get();
            foreach ($captured as $c) {
                $c->sample_detail_code = $sample->sample_code;
                $c->save();
            }
            foreach ($results as $r) {
                $r->sample_detail_code = $sample->sample_code;
                $r->save();
            }
        }

        return response()->json('done');
    }

    public function return_batch_reception(Request $request)
    {
        $batchCodes = collect((array) $request->batch_code)
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn ($code) => trim($code))
            ->unique()
            ->values();

        $submissionRequestIds = collect((array) $request->submission_request_id)
            ->filter(fn ($id) => (string) $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $submissionFormInstanceIds = collect((array) $request->submission_form_instance_id)
            ->filter(fn ($id) => (string) $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $rejectionPayload = (array) $request->input('sample_rejection', []);
        $processed = 0;

        foreach ($batchCodes as $code) {
            $header = SampleHeader::where('batch_code', $code)->first();
            if (isset($header->id)) {
                $header->status = 'Samples Reception';
                $header->save();
                $processed++;
                $responsibility = SystemConfiguration::where('key', $header->status)->first();
                $users = JobDescription::where(function ($query) use ($responsibility) {
                    $query->where('job_designation_responsibility.name', $responsibility->key)
                        ->orWhereRaw('job_designation_responsibility.config_id::text = ?', [(string) $responsibility->id]);
                })
                    ->join('users', function ($join) {
                        $join->whereRaw('users.position::text = job_designation_responsibility.job_id::text');
                    })
                    ->get('users.*');

                $numbers = $users->pluck('phone')->toarray();

                $message = 'Batch ' . $header->batch_code . ' Approval Request has been rejected because - ' . $request->comment . '.';
                if (isset($request->send_message)) {
                    foreach ($numbers as $num) {
                        $send = sendTextMessage($num, $message);
                        if ($send == 'error') {
                            return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                        }
                    }
                }
                if (isset($request->send_email)) {
                    foreach ($users as $user) {
                        $subject = '[' . $header->batch_code . '] Approval Request Reject.';
                        $body = 'Hi ' . $user->name . ',<br>' . $message;
                        notify_user($body, $user->email, $subject);
                    }
                }

                $custodyDetails = [
                    'batch_id' => $header->id,
                    'comments' => $request->comment ?? '',
                    'current' => [
                        'status' => $header->status,
                        'tracking_stage' => $header->sample_tracking_stage,
                    ],
                    'target' => [
                        'status' => $header->status,
                        'tracking_stage' => $header->sample_tracking_stage,
                    ],
                ];
                $this->updateChainofCustody($custodyDetails);

                $linkedSubmissionRequest = SampleSubmissionRequest::query()
                    ->where('sample_header_id', $header->id)
                    ->with(['customer', 'contact', 'requestedAnalyses'])
                    ->first();

                $linkedSubmissionFormInstance = null;
                if (!empty($header->submission_form_instance_id)) {
                    $linkedSubmissionFormInstance = SubmissionFormInstance::query()
                        ->with(['crmCustomer'])
                        ->find((string) $header->submission_form_instance_id);
                }

                $this->persistSampleRejectionForm(
                    $header,
                    $linkedSubmissionRequest,
                    $linkedSubmissionFormInstance,
                    $rejectionPayload
                );
            }
        }

        if ($submissionRequestIds->isNotEmpty()) {
            $submissionRequests = SampleSubmissionRequest::query()
                ->with(['customer', 'contact', 'requestedAnalyses', 'supportingDocumentInstances'])
                ->whereIn('id', $submissionRequestIds)
                ->get();

            foreach ($submissionRequests as $submissionRequest) {
                $submissionRequest->status = 'rejected';
                $submissionRequest->save();
                $processed++;

                foreach ($submissionRequest->supportingDocumentInstances as $docInstance) {
                    if (in_array((string) $docInstance->status, ['draft', 'submitted', 'in_review'], true)) {
                        $docInstance->status = 'rejected';
                        $docInstance->reviewed_at = now();
                        $docInstance->save();
                    }
                }

                $linkedSubmissionFormInstance = SubmissionFormInstance::query()
                    ->with(['crmCustomer'])
                    ->where('target_record_type', SampleSubmissionRequest::class)
                    ->where('target_record_id', (string) $submissionRequest->id)
                    ->latest('created_at')
                    ->first();

                $this->persistSampleRejectionForm(
                    null,
                    $submissionRequest,
                    $linkedSubmissionFormInstance,
                    $rejectionPayload
                );
            }
        }

        if ($submissionFormInstanceIds->isNotEmpty()) {
            $submissionFormInstances = SubmissionFormInstance::query()
                ->with(['crmCustomer', 'attachmentInstances'])
                ->whereIn('id', $submissionFormInstanceIds)
                ->get();

            foreach ($submissionFormInstances as $submissionFormInstance) {
                $submissionFormInstance->status = 'rejected';
                $submissionFormInstance->reviewed_at = now();
                $submissionFormInstance->reviewed_by = auth()->id();
                $submissionFormInstance->review_notes = (string) ($request->comment ?? 'Rejected from workflow request queue.');
                $submissionFormInstance->save();
                $processed++;

                foreach ($submissionFormInstance->attachmentInstances as $attachmentInstance) {
                    if (in_array((string) $attachmentInstance->status, ['draft', 'submitted', 'in_review'], true)) {
                        $attachmentInstance->status = 'rejected';
                        $attachmentInstance->reviewed_at = now();
                        $attachmentInstance->reviewed_by = auth()->id();
                        $attachmentInstance->review_notes = (string) ($request->comment ?? 'Rejected from workflow request queue.');
                        $attachmentInstance->save();
                    }
                }

                $linkedSubmissionRequest = null;
                if (
                    (string) $submissionFormInstance->target_record_type === SampleSubmissionRequest::class
                    && !empty($submissionFormInstance->target_record_id)
                ) {
                    $linkedSubmissionRequest = SampleSubmissionRequest::query()
                        ->with(['customer', 'contact', 'requestedAnalyses'])
                        ->find((string) $submissionFormInstance->target_record_id);
                }

                $this->persistSampleRejectionForm(
                    null,
                    $linkedSubmissionRequest,
                    $submissionFormInstance,
                    $rejectionPayload
                );
            }
        }

        if ($processed < 1) {
            return redirect()->back()->with('error', 'No request records were selected to reject.');
        }

        return redirect()->back()->with('success', 'Batch(es) rejected succesfully');
    }

    private function persistLaboratoryAcceptanceForm(SampleHeader $batch, array $input): void
    {
        $batch->loadMissing(['client', 'sample_type', 'samples']);

        $submissionRequest = SampleSubmissionRequest::query()
            ->with(['customer', 'contact', 'requestedAnalyses'])
            ->where('sample_header_id', $batch->id)
            ->first();

        $submissionFormInstance = null;
        if (!empty($batch->submission_form_instance_id)) {
            $submissionFormInstance = SubmissionFormInstance::query()
                ->with(['crmCustomer'])
                ->find((string) $batch->submission_form_instance_id);
        }

        $customer = $submissionRequest?->customer ?: $submissionFormInstance?->crmCustomer ?: $batch->client;
        $contact = $submissionRequest?->contact;

        // Try to use the new parameters_json if available
        $parameterRows = [];
        if (!empty($input['parameters_json'])) {
            try {
                $parsedParams = json_decode($input['parameters_json'], true);
                if (is_array($parsedParams)) {
                    $parameterRows = array_map(function($param) {
                        return [
                            'name' => (string) ($param['label'] ?? $param['name'] ?? ''),
                            'price' => (float) ($param['price'] ?? 0),
                            'analysis_id' => (int) ($param['analysis_id'] ?? 0),
                            'accepted' => true,
                            'rejected' => false,
                        ];
                    }, $parsedParams);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to parse parameters_json: ' . $e->getMessage());
            }
        }

        // Fallback to old text-based parsing if no parameters_json
        if (empty($parameterRows)) {
            $parameterCandidates = [];
            if ($submissionRequest && $submissionRequest->requestedAnalyses->count() > 0) {
                foreach ($submissionRequest->requestedAnalyses as $requestedAnalysis) {
                    $label = trim((string) ($requestedAnalysis->analysis_label ?: $requestedAnalysis->analysis_key));
                    if ($label !== '') {
                        $parameterCandidates[] = $label;
                    }
                }
            }

            if (empty($parameterCandidates)) {
                foreach ($batch->samples as $sample) {
                    $analysisIds = array_filter(array_map('trim', explode(',', (string) $sample->analysis_type_id)));
                    foreach ($analysisIds as $analysisId) {
                        $analysis = AnalysisType::query()->find($analysisId);
                        if ($analysis && trim((string) $analysis->name) !== '') {
                            $parameterCandidates[] = trim((string) $analysis->name);
                        }
                    }
                }
            }

            $parameterCandidates = collect($parameterCandidates)
                ->filter()
                ->unique()
                ->values();

            $typedParameters = collect(preg_split('/\r\n|\r|\n/', (string) ($input['parameters_text'] ?? '')))
                ->map(fn ($row) => trim((string) $row))
                ->filter()
                ->values();

            $finalParameters = $typedParameters->isNotEmpty() ? $typedParameters : $parameterCandidates;

            $parameterRows = $finalParameters->map(function ($name) {
                return [
                    'name' => (string) $name,
                    'accepted' => true,
                    'rejected' => false,
                ];
            })->values()->all();
        }

        $payload = [
            'date' => (string) ($input['date'] ?? now()->format('Y-m-d')),
            'lab_no' => $this->normalizeLabNumber($input['lab_no'] ?? null, (string) $batch->batch_code),
            'customer_name' => (string) ($input['customer_name'] ?? ($customer->name ?? '')),
            'address' => (string) ($input['address'] ?? ($customer->postal_address ?? $submissionRequest?->physical_address ?? '')),
            'email' => (string) ($input['email'] ?? ($contact->email ?? $submissionRequest?->email ?? $customer->email ?? '')),
            'tel' => (string) ($input['tel'] ?? ($submissionRequest?->mobile_telephone_no ?? $submissionRequest?->office_telephone_no ?? $customer->telephone1 ?? '')),
            'number_of_samples' => (string) ($input['number_of_samples'] ?? $batch->samples->count()),
            'type_of_sample' => (string) ($input['type_of_sample'] ?? ($batch->sample_type->name ?? '')),
            'date_of_sampling' => (string) ($input['date_of_sampling'] ?? optional($submissionRequest?->date_of_seizure)->format('Y-m-d')),
            'mode_of_work' => (string) ($input['mode_of_work'] ?? (strtolower((string) $batch->priority) === 'express' ? 'Express' : 'Normal')),
            'amount_usd' => (string) ($input['amount_usd'] ?? ''),
            'parameters' => $parameterRows,
            'deviation_answer' => (string) ($input['deviation_answer'] ?? 'No'),
            'customer_name_certified' => (string) ($input['customer_name_certified'] ?? ($contact?->first_name ?? '') . ' ' . ($contact?->last_name ?? '')),
            'customer_signature_name' => (string) ($input['customer_signature_name'] ?? ''),
            'customer_date' => (string) ($input['customer_date'] ?? now()->format('Y-m-d')),
            'conformity_request' => (string) ($input['conformity_request'] ?? 'not requested'),
            'laboratory_name' => (string) ($input['laboratory_name'] ?? (config('app.name') ?? '')),
            'laboratory_manager_name' => (string) ($input['laboratory_manager_name'] ?? (auth()->user()->name ?? '')),
            'manager_signature_name' => (string) ($input['manager_signature_name'] ?? ''),
            'manager_date' => (string) ($input['manager_date'] ?? now()->format('Y-m-d')),
        ];

        $pdfPath = $this->storeWorkflowFormPdf('workflow.forms.laboratory-analysis-acceptance-pdf', $payload);

        RequestWorkflowForm::create([
            'form_type' => 'laboratory_analysis_acceptance',
            'sample_header_id' => (string) $batch->id,
            'sample_submission_request_id' => $submissionRequest?->id,
            'submission_form_instance_id' => $submissionFormInstance?->id,
            'batch_code' => (string) $batch->batch_code,
            'request_reference' => (string) ($submissionFormInstance?->getDocumentControlNumber() ?? $submissionRequest?->formatted_number ?? $batch->batch_code),
            'payload' => $payload,
            'pdf_path' => $pdfPath,
            'created_by' => auth()->id(),
            'submitted_at' => now(),
        ]);
    }

    private function normalizeLabNumber(mixed $labNumber, string $fallbackLabNumber): string
    {
        $candidate = trim((string) $labNumber);
        $fallback = trim($fallbackLabNumber);

        if ($fallback === '') {
            return $candidate;
        }

        if ($candidate === '') {
            return $fallback;
        }

        if (str_contains(strtolower($candidate), 'auto-generated on approval')) {
            return $fallback;
        }

        return $candidate;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function resolveBatchCodesFromFormInstance(SubmissionFormInstance $formInstance): \Illuminate\Support\Collection
    {
        $resolvedCodes = collect();
        $visitedIds = collect();  // Prevent infinite loops in parent traversal

        $candidateInstances = collect([$formInstance]);

        // Recursively walk up the parent_instance_id chain
        if (!empty($formInstance->parent_instance_id) && (string) $formInstance->parent_instance_id !== (string) $formInstance->id) {
            $this->collectParentInstances($formInstance, $candidateInstances, $visitedIds);
        }

        // Also check portal_request_id as a form instance reference
        if (!empty($formInstance->portal_request_id)) {
            $portalInstance = SubmissionFormInstance::with('batches')
                ->find((string) $formInstance->portal_request_id);

            if ($portalInstance && !$visitedIds->contains((string) $portalInstance->id)) {
                $candidateInstances->push($portalInstance);
                $visitedIds->push((string) $portalInstance->id);
            }
        }

        // Check for child instances
        $childInstances = SubmissionFormInstance::with('batches')
            ->where('parent_instance_id', (string) $formInstance->id)
            ->orWhere('portal_request_id', (string) $formInstance->id)
            ->get();

        if ($childInstances->isNotEmpty()) {
            $candidateInstances = $candidateInstances->merge($childInstances);
        }

        $candidateInstances = $candidateInstances
            ->filter()
            ->unique(fn (SubmissionFormInstance $instance) => (string) $instance->id)
            ->values();

        foreach ($candidateInstances as $candidateInstance) {
            foreach ($candidateInstance->batches as $linkedBatch) {
                if (!empty($linkedBatch->batch_code)) {
                    $resolvedCodes->push((string) $linkedBatch->batch_code);
                }
            }

            if ($candidateInstance->batches->isNotEmpty()) {
                continue;
            }

            try {
                $created = $this->sampleCreationService->createSamplesFromForm($candidateInstance);
                $createdBatch = $created['sample_header'] ?? null;

                if ($createdBatch && !empty($createdBatch->batch_code)) {
                    $resolvedCodes->push((string) $createdBatch->batch_code);
                    $this->syncSubmissionRequestBatchLink($candidateInstance, $createdBatch);
                    $this->syncSubmissionRequestBatchLink($formInstance, $createdBatch);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to create batch from form instance during Samples In Lab approval', [
                    'form_instance_id' => $candidateInstance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($resolvedCodes->isEmpty()) {
            $requestIdCandidates = $candidateInstances
                ->flatMap(fn (SubmissionFormInstance $instance) => $this->inferSubmissionRequestIdsFromFormInstance($instance))
                ->merge($this->inferSubmissionRequestIdsFromFormInstance($formInstance))
                ->filter()
                ->unique()
                ->values();

            if ($requestIdCandidates->isNotEmpty()) {
                $linkedRequests = SampleSubmissionRequest::query()
                    ->whereIn('id', $requestIdCandidates)
                    ->whereNotNull('sample_header_id')
                    ->get();

                foreach ($linkedRequests as $linkedRequest) {
                    $linkedBatch = SampleHeader::query()->find((string) $linkedRequest->sample_header_id);
                    if ($linkedBatch && !empty($linkedBatch->batch_code)) {
                        $resolvedCodes->push((string) $linkedBatch->batch_code);
                    }
                }
            }
        }

        return $resolvedCodes
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn ($code) => trim($code))
            ->unique()
            ->values();
    }

    /**
     * Recursively collect parent form instances up the chain
     */
    private function collectParentInstances(
        SubmissionFormInstance $instance,
        \Illuminate\Support\Collection &$candidateInstances,
        \Illuminate\Support\Collection &$visitedIds,
        int $maxDepth = 10
    ): void {
        if ($maxDepth <= 0 || empty($instance->parent_instance_id)) {
            return;
        }

        $parentId = (string) $instance->parent_instance_id;

        if ($visitedIds->contains($parentId) || $parentId === (string) $instance->id) {
            return;  // Already visited or self-reference
        }

        $parentInstance = SubmissionFormInstance::with('batches')
            ->find($parentId);

        if (!$parentInstance) {
            return;
        }

        $visitedIds->push($parentId);
        $candidateInstances->push($parentInstance);

        // Continue recursion up the chain
        $this->collectParentInstances($parentInstance, $candidateInstances, $visitedIds, $maxDepth - 1);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function inferSubmissionRequestIdsFromFormInstance(SubmissionFormInstance $formInstance): \Illuminate\Support\Collection
    {
        $ids = collect();

        $targetType = strtolower(trim((string) ($formInstance->target_record_type ?? '')));
        $targetId = trim((string) ($formInstance->target_record_id ?? ''));
        $portalRequestId = trim((string) ($formInstance->portal_request_id ?? ''));
        $legacyRequestId = trim((string) ($formInstance->sample_submission_request_id ?? ''));

        if ($targetId !== '' && ($targetType === '' || str_contains($targetType, 'samplesubmissionrequest') || str_contains($targetType, 'sample_submission_request'))) {
            $ids->push($targetId);
        }

        if ($legacyRequestId !== '') {
            $ids->push($legacyRequestId);
        }

        if ($portalRequestId !== '' && SampleSubmissionRequest::query()->where('id', $portalRequestId)->exists()) {
            $ids->push($portalRequestId);
        }

        return $ids
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '')
            ->unique()
            ->values();
    }

    private function syncSubmissionRequestBatchLink(SubmissionFormInstance $formInstance, SampleHeader $batch): void
    {
        if ((string) $formInstance->target_record_type !== SampleSubmissionRequest::class || empty($formInstance->target_record_id)) {
            return;
        }

        $submissionRequest = SampleSubmissionRequest::query()->find((string) $formInstance->target_record_id);
        if (! $submissionRequest) {
            return;
        }

        if ((string) $submissionRequest->sample_header_id === (string) $batch->id) {
            return;
        }

        $submissionRequest->sample_header_id = (string) $batch->id;
        $submissionRequest->save();
    }

    private function persistSampleRejectionForm(
        ?SampleHeader $batch,
        ?SampleSubmissionRequest $submissionRequest,
        ?SubmissionFormInstance $submissionFormInstance,
        array $input,
    ): void {
        $customer = $submissionRequest?->customer ?: $submissionFormInstance?->crmCustomer ?: $batch?->client;

        $reasons = array_values(array_filter(array_map('trim', (array) ($input['reasons'] ?? []))));

        $payload = [
            'sample_id' => (string) ($input['sample_id'] ?? $batch?->batch_code ?? $submissionFormInstance?->getDocumentControlNumber() ?? $submissionRequest?->formatted_number ?? ''),
            'name_of_client' => (string) ($input['name_of_client'] ?? ($customer->name ?? '')),
            'date_sample_received' => (string) ($input['date_sample_received'] ?? optional($submissionFormInstance?->submitted_at)->format('Y-m-d') ?? optional($submissionRequest?->submitted_by_date)->format('Y-m-d')),
            'date_of_sample_collection' => (string) ($input['date_of_sample_collection'] ?? optional($submissionRequest?->date_of_seizure)->format('Y-m-d')),
            'number_of_samples_received' => (string) ($input['number_of_samples_received'] ?? ($batch ? $batch->samples()->count() : '')),
            'reasons' => $reasons,
            'reason_other' => (string) ($input['reason_other'] ?? ''),
            'explanation' => (string) ($input['explanation'] ?? ($input['reason_other'] ?? '')),
            'laboratory_staff' => (string) ($input['laboratory_staff'] ?? (auth()->user()->name ?? '')),
            'signature_name' => (string) ($input['signature_name'] ?? ''),
            'date' => (string) ($input['date'] ?? now()->format('Y-m-d')),
        ];

        $pdfPath = $this->storeWorkflowFormPdf('workflow.forms.sample-rejection-pdf', $payload);

        RequestWorkflowForm::create([
            'form_type' => 'sample_rejection',
            'sample_header_id' => $batch ? (string) $batch->id : null,
            'sample_submission_request_id' => $submissionRequest?->id,
            'submission_form_instance_id' => $submissionFormInstance?->id,
            'batch_code' => $batch?->batch_code,
            'request_reference' => (string) ($submissionFormInstance?->getDocumentControlNumber() ?? $submissionRequest?->formatted_number ?? $batch?->batch_code ?? ''),
            'payload' => $payload,
            'pdf_path' => $pdfPath,
            'created_by' => auth()->id(),
            'submitted_at' => now(),
        ]);
    }

    private function storeWorkflowFormPdf(string $view, array $payload): ?string
    {
        try {
            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView($view, [
                'payload' => $payload,
                'logos' => $this->resolveWorkflowFormLogos(),
            ]);

            $filename = sprintf('request-workflow-form-%s-%s.pdf', date('YmdHis'), substr((string) md5((string) microtime(true)), 0, 8));
            $relativePath = 'request-workflow-forms/' . $filename;
            Storage::disk('public')->put($relativePath, $pdf->output());

            return '/storage/' . $relativePath;
        } catch (\Throwable $exception) {
            Log::warning('Failed to render workflow form PDF', [
                'view' => $view,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function resolveSingleLogoPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }
        $path = ltrim($path, '/');
        
        if (file_exists($path)) {
            return $path;
        }

        $filename = basename($path);
        if ($filename !== '') {
            $relative = preg_replace('#^storage/#', '', $path);
            if ($relative !== $path) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
                if (file_exists($fullPath)) {
                    return $fullPath;
                }
            }

            $fullPath = storage_path('app/companies/' . $filename);
            if (file_exists($fullPath)) {
                return $fullPath;
            }

            if (file_exists(public_path($path))) {
                return public_path($path);
            }

            if (file_exists(base_path('public/' . $path))) {
                return base_path('public/' . $path);
            }
        }

        return null;
    }

    private function getResolvedTanzaniaLogoCandidates(): array
    {
        $company = getActiveCompany();
        if ($company) {
            $paths = [
                $company->getReportLogoPath('coat_of_arms'),
                $company->getReportLogoPath('tz_flag'),
                $company->report_logo,
            ];
            foreach ($paths as $path) {
                $resolved = $this->resolveSingleLogoPath($path);
                if ($resolved) {
                    return [$resolved];
                }
            }
        }

        return [
            public_path('images/forms/tanzanialogo.jpeg'),
            public_path('images/forms/tanzanialogo.jpg'),
            base_path('public/images/forms/tanzanialogo.jpeg'),
            base_path('public/images/forms/tanzanialogo.jpg'),
        ];
    }

    private function getResolvedGclaLogoCandidates(): array
    {
        $company = getActiveCompany();
        if ($company) {
            $paths = [
                $company->getReportLogoPath('gcla_logo'),
                $company->getReportLogoPath('gcla'),
                $company->logo,
            ];
            foreach ($paths as $path) {
                $resolved = $this->resolveSingleLogoPath($path);
                if ($resolved) {
                    return [$resolved];
                }
            }
        }

        return [
            public_path('images/forms/gclalogo.png'),
            public_path('images/forms/gclalogo.jpg'),
            base_path('public/images/forms/gclalogo.png'),
            base_path('public/images/forms/gclalogo.jpg'),
        ];
    }

    private function resolveWorkflowFormLogos(): array
    {
        return [
            'tanzania' => $this->encodeImageAsDataUri($this->getResolvedTanzaniaLogoCandidates()),
            'gcla' => $this->encodeImageAsDataUri($this->getResolvedGclaLogoCandidates()),
        ];
    }

    /**
     * @param array<int, string> $candidates
     */
    private function encodeImageAsDataUri(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if (!is_string($path) || trim($path) === '' || !is_file($path)) {
                continue;
            }

            $content = @file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $mimeType = mime_content_type($path) ?: 'image/png';
            return 'data:' . $mimeType . ';base64,' . base64_encode($content);
        }

        return null;
    }

    public function regerateCustomerInvoice($id)
    {
        $batch = SampleHeader::find($id);
        $invoice = Invoice::find($batch->invoice_id);
        $details = SampleDetails::where('sample_header_id', $batch->id)->get();
        $customer_pricelist = PricelistCustomer::where('customer_id', $batch->crm_customer_id)->get();
        $pricelist = Pricelist::find($customer_pricelist[0]->pricelist_id);
        foreach ($details as $detail) {
            $analysis_ids = explode(',', $detail->analysis_type_id);
            foreach ($analysis_ids as $analysis_id) {
                $price = PricelistItem::where('pricelist_id', $pricelist->id)->where('analysis_id', (int) $analysis_id)->where('sample_type_id', $batch->sample_type_id)->first();
                if (!isset($price->id)) {
                    return redirect()->back()->with('error', 'kindly add analysis to pricelist!');
                }
                $check_invoice_detail = InvoiceDetails::where('invoice_id', $invoice->id)->where('analysis_type', (int) $analysis_id)->first();
                if (!isset($check_invoice_detail->id)) {
                    $invoice_detail = new InvoiceDetails();
                    $invoice_detail->crm_customer_id = $batch->crm_customer_id;
                    $invoice_detail->analysis_type = $analysis_id;
                    $analysis = getAnalysisTypeID($analysis_id);
                    $invoice_detail->analysis_type_name = $analysis->name;
                    $invoice_detail->sample_header_id = $batch->id;
                    $invoice_detail->sample_detail_id = $detail->id;
                    $invoice_detail->invoice_id = $invoice->id;
                    $invoice_detail->cost_price = $price->cost_price;
                    $invoice_detail->selling_price = $price->selling_price;
                    if ($price->vat == 1) {
                        $rate = TaxRegime::where('active', 1)->first();
                        $tax = $rate->value / 100 * $price->selling_price;
                        $total_price = $tax + $price->selling_price;
                        $invoice_detail->selling_amount = $total_price;
                        $invoice_detail->tax_rate = strval($rate->value);
                        $invoice_detail->tax_amount = $tax;
                        $invoice_detail->total = $total_price;
                    } else {
                        $invoice_detail->selling_amount = $price->selling_price;
                        $invoice_detail->total = $price->selling_price;
                    }
                    // return response()->json($price,200);
                    $invoice_detail->save();
                } else {
                    $current = $check_invoice_detail->quantity;
                    $current_tax = $check_invoice_detail->tax_amount;
                    $unit_price = $check_invoice_detail->selling_amount;
                    $current_total = $check_invoice_detail->total;
                    $check_invoice_detail->total = $unit_price + $current_total;
                    $check_invoice_detail->quantity = $current + 1;
                    if ($check_invoice_detail->tax_rate != 0) {
                        $taxable = strval($check_invoice_detail->tax_rate / 100 * $check_invoice_detail->selling_price);
                        $check_invoice_detail->tax_amount = $taxable + $current_tax;
                    }
                    $check_invoice_detail->save();
                }
            }
        }
        $details_invoice = InvoiceDetails::where('invoice_id', $invoice->id);
        $details_invoice_total_including_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('total')->toarray();
        $details_invoice_total_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('tax_amount')->toarray();
        foreach ($details_invoice as $detail) {
            $selling_amount = $detail->selling_price * $detail->quantity;
            $detail->selling_price_amount = $selling_amount;
            $detail->save();
        }
        $total_including_tax = array_sum($details_invoice_total_including_tax);
        $total_invoice_tax = array_sum($details_invoice_total_tax);

        $invoice->total = $total_including_tax;
        $invoice->total_tax = $total_invoice_tax;
        $invoice->save();

        return response()->json(['invoice' => $invoice, 'detail' => $details_invoice]);
    }

    public function fillCapturedresultOperator()
    {
        $captured_reults = CapturedResult::where('operator_id', 0)->get();
        foreach ($captured_reults as $c) {
            $ae = AnalysisElements::where('analyte_id', $c->analyte_id)->where('analysis_type_id', $c->analysis_type_id)->first();
            if (isset($ae->id)) {
                $c->operator_id = $ae->operator_id;
            }
            $c->save();
        }

        return response()->json('success');
    }

    public function markQcSampleComplete($id)
    {
        $header = SampleHeader::find($id);
        $previous_status = $header->status;
        $header->status = 'QC Approved';
        $header->save();

        return redirect()->route('sample-workflow', ['status' => $previous_status])->with('success', 'Batch marked complete succesffuly');
    }

    public function markAccredittedSamples($header_id = 0)
    {
        if ($header_id == 0) {
            $sample_ids = SampleHeader::whereIn('status', ['Samples Reception', 'Samples Request Review', 'Samples In Lab'])->pluck('id')->toArray();

            $captured_results = CapturedResult::whereIn('id', $sample_ids)->get();
            foreach ($captured_results as $cr) {
                $analysisElement = AnalysisElements::where('analysis_type_id', $cr->analysis_type_id)
                    ->where('analyte_id', $cr->analyte_id)->first();
                if (isset($analysisElement->id)) {
                    $cr->analyte_accredited = $analysisElement->non_accredited;
                    $cr->save();
                    $result = Result::where('captured_result_id', $cr->id)->first();
                    if (!$result) {
                        $result = new Result();
                        $result->captured_result_id = $cr->id;
                        $result->sample_detail_code = $cr->sample_detail_code;
                        $result->sample_detail_id = $cr->sample_detail_id;
                        $result->sample_header_id = $cr->sample_header_id;
                        $result->analyte_id = $cr->analyte_id;
                        $result->analyte_code = $cr->analyte_code;
                        $result->analysis_type_id = $cr->analysis_type_id;
                        $result->lab_section_id = $cr->lab_section_id;
                        $result->parameters_order = $cr->parameters_order ?? 0;
                        $result->remark_is_manual = $cr->remark_is_manual ?? false;
                        $result->has_no_result_capture = $cr->has_no_result_capture ?? false;
                    }
                    $result->analyte_accredited = $analysisElement->non_accredited;
                    $result->save();
                }
            }
        }

        return response()->json('done');
    }

    public function getLabsByAnalysisTypeIdAjax(Request $request)
    {
        // $lab_ids = AnalysisType::whereIn('id',$request->ids)->pluck('lab_id')->toArray();
        $labs = Lab::where('active', 1)->get();

        return response()->json($labs);
    }

    public function assignLabSectionToAnalysisElement($id)
    {
        $analysistype = AnalysisType::find($id);
        $elements = AnalysisElements::where('analysis_type_id', $id)->update(['lab_section_id' => $analysistype->lab_section_id]);

        return response()->json('success');
    }

    public function create_sample_inter_lab_log(Request $request)
    {
        if ($request->quantity == '' || $request->to_lab_section_id == '') {
            return redirect()->back()->with('error', 'Quantity, To Lab are mandatory fields');
        }
        // return response()->json($request->all());
        if (isset($request->batch_level)) {
            $log = [];
            $batches = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('id')->toArray();
            $sample_ids = SampleDetails::whereIn('sample_header_id', $batches)->pluck('id')->toArray();
            foreach ($sample_ids as $id) {
                $batch = getSampleHeaderByID(getSampleDetailByID($id)->sample_header_id);
                $last_log = InterLabLog::where('sample_id', $id)->where('status', 1)->orderBy('date_received', 'DESC')->first();
                $log[] = [
                    'sample_id' => $id,
                    'to_lab_section_id' => $request->to_lab_section_id,
                    'from_lab_section_id' => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
                    'quantity' => $request->quantity,
                    'submited_by' => auth()->user()->id,
                    'date_submitted' => date('Y-m-d h:i:s a'),
                    'expected_date' => $request->expected_date == '' ? date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : $request->expected_date,
                    'prelim_date' => $request->prelim_date ?? '',
                    'remarks' => $request->remarks ?? '',
                ];
            }

            // return response()->json($log);
        } else {
            $last_log = InterLabLog::where('sample_id', $request->sample_id)->where('status', 1)->orderBy('date_received', 'DESC')->first();
            if (isset($request->interlab_id) && $request->interlab_id != '0') {
                $log = [
                    'sample_id' => $request->sample_id,
                    'to_lab_section_id' => $request->to_lab_section_id,
                    'quantity' => $request->quantity,
                    'submited_by' => auth()->user()->id,
                    'date_submitted' => date('Y-m-d h:i:s a'),
                    'expected_date' => $request->expected_date,
                    'from_lab_section_id' => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
                    'prelim_date' => $request->prelim_date ?? '',
                    'remarks' => $request->remarks ?? '',
                ];
            } else {
                $log = [
                    'sample_id' => $request->sample_id,
                    'to_lab_section_id' => $request->to_lab_section_id,
                    'from_lab_section_id' => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
                    'quantity' => $request->quantity,
                    'submited_by' => auth()->user()->id,
                    'date_submitted' => date('Y-m-d h:i:s a'),
                    'expected_date' => $request->expected_date,
                    'prelim_date' => $request->prelim_date ?? '',
                    'remarks' => $request->remarks ?? '',
                ];
            }
        }
        isset($request->interlab_id) && $request->interlab_id != '0' ? InterLabLog::find($request->interlab_id)->update($log) : InterLabLog::insert($log);
        if ($request->notify_user != '' || $request->sms_notify != '') {
            $sample_codes = isset($request->batch_level) ? SampleDetails::whereIn('id', $sample_ids)->get() : SampleDetails::where('id', $request->sample_id)->get();
            $samplecodesList = '';

            foreach ($sample_codes as $s_code) {
                $samplecodesList .= '<li><a href="http://172.16.16.252:8080/sample-workflow/batch/' . $s_code->sample_header_id . '/details/0/0/' . $s_code->getSampleHeader()->status . '">' . $s_code->sample_code . '</a></li>';
            }
            $bcc_emails = User::whereIn('id', $request->also_notify ?? [])->pluck('email')->toArray();
            $body = 'Hi Team, <br> The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name . '<br>Click the sample codes to access the sample InterLab Log <br><ul>' . $samplecodesList . '</ul>';
            $to_email = getUserById($request->notify_user);

            if (isset($to_email->id)) {
                notify_user($body, $to_email->email, '[FIVET LIMS] Inter Laboratory Transfer Approval Notification', false, false, $bcc_emails);
                // sendTextMessage($to_email->phone, 'Hi ' . $to_email->name . ', The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name);
                // foreach (User::whereIn('id', $request->also_notify ?? [])->get() as $user) {
                // 	$user->phone != '' ? sendTextMessage($user->phone, 'Hi ' . $user->name . ', The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name) . ':  ' . $sample_codes : '';
                // }
            }
        }

        return redirect()->back()->with('success', 'Inter laboratory Log updated successfully');
    }

    public function getSampleCurrentLabSection($id)
    {
        $last_log = InterLabLogView::where('sample_id', $id)->where('status', 1)->orderBy('date_received', 'DESC')->orderBy('id', 'DESC')->first();

        return response()->json(isset($last_log->id) ? $last_log->to_lab_code . ' ' . $last_log->to_lab_name : 'Reception');
    }

    public function changeInterLabLogStatus(Request $request)
    {
        if ($request->status == '') {
            return redirect()->back()->with('error', 'Status is a required field');
        }
        if (isset($request->inter_lab_id)) {
            InterLabLog::find($request->inter_lab_id)->update(['status' => $request->status, 'date_received' => date('Y-m-d h:i:s a'), 'received_by' => auth()->user()->id]);
        } else {
            InterLabLog::whereIn('id', explode(',', $request->inter_lab_ids))->update(['status' => $request->status, 'date_received' => date('Y-m-d h:i:s a'), 'received_by' => auth()->user()->id]);
        }

        return redirect()->back()->with('success', 'Inter laboratory Log status updated successfully');
    }

    public function interLabTransferIndex($is_archived = 0)
    {
        $interlabs = $is_archived == 0 ? InterLabLogView::orderBy('id', 'DESC')->where('batch_status', '!=', 'Completed')->get() : InterLabLogView::orderBy('id', 'DESC')->get();
        $samples = $interlabs->pluck('sample_code')->toArray();
        $labs = Lab::where('active', 1)->get();
        $users = User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->get();

        return view('layouts.lab.interlab.index', compact('interlabs', 'samples', 'labs', 'users'));
    }

    public function deleteInterLabTransferLogs(Request $request)
    {
        InterLabLog::whereIn('id', explode(',', $request->inter_lab_ids))->delete();

        return redirect()->back()->with('success', 'Inter Laboratory Transfer Log(s) deleted successfully');
    }

    public function generateCustomerFocusIndex(Request $request, $batch_id)
    {
        $batch = SampleHeader::with('submissionFormInstance')->find($batch_id);

        // Check if batch exists
        if (!$batch) {
            return redirect()->back()->with('error', 'Batch not found.');
        }

        // Check if batch has a submission form instance linked
        if ($batch->hasSubmissionForm()) {
            $instance = $batch->submissionFormInstance;

            return redirect()->route('submission-forms.instances.show', [
                $instance->submission_form_id,
                $instance->id,
            ]);
        }

        // No submission form instance - redirect back with error message
        return redirect()->route('view-batch-details', $batch_id)
            ->with('error', 'This batch does not have a submission form instance linked. Please create or link a submission form first.');

        // Old customer_focus logic below (kept for reference but unreachable)
        if ($batch_id == 0 || $batch->c_focus_ids_clustered != '') {
            $batches = $batch_id == 0 ? SampleHeader::whereIn('batch_code', $request->batch_code) : SampleHeader::whereIn('id', explode(',', $batch->c_focus_ids_clustered));
            $getCustomers = clone $batches;
            $customer_ids = array_unique($getCustomers->pluck('crm_customer_id')->toArray());
            if (sizeof($customer_ids) > 1) {
                return redirect()->back()->with('error', 'All batches should be of the same client! Kindly check on the batches you have selected');
            }
            $batch = $getCustomers->orderBy('created_at', 'ASC')->first();
            $customer = CrmCustomer::find($batch->crm_customer_id);
            $sample_type_ids = $batches->pluck('sample_type_id')->toArray();
            $sample_types = implode(', ', array_unique(SampleType::whereIn('id', $sample_type_ids)->pluck('name')->toArray()));
            $company = getActiveCompany();
            $config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
            $docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
            $payment_detail = [
                'balance' => $request->balance,
                // "total_amount"=>array_sum($batches->pluck('invoice_amount')->toArray()),
                'amount_paid' => $request->amount_paid,
                'vat' => $request->vat,
                'invoice_amount' => $request->invoice_amount,
            ];
            $batch_ids = $batches->pluck('id')->toArray();
            $samples = SamplesCategory::whereIn('sample_header_id', $batch_ids)->get();
            SampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->whereNotIn('sample_detail_id', $samples->pluck('id')->toArray())->delete();
            $review_staff = getUserById($batch->receiving_officer);
            $is_clustered = 1;
            // return response()->json($batch_ids);

            isset($request->batch_code) && sizeof($request->batch_code) > 0 ? SampleHeader::whereIn('batch_code', $request->batch_code)->update(['c_focus_ids_clustered' => sizeof($batch_ids) > 0 ? implode(',', $batch_ids) : '']) : '';

            return view('layouts.lab.sample-workflow.customer_focus', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'sample_types', 'is_clustered'));
        }
        $is_clustered = 0;
        $batch = SampleHeader::find($batch_id);
        $customer = CrmCustomer::find($batch->crm_customer_id);
        $company = getActiveCompany();
        $config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
        $docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
        $review_staff = getUserById($batch->receiving_officer);
        $samples = SamplesCategory::where('sample_header_id', $batch_id)->get();
        SampleAnalysisTypeRelation::where('batch_id', $batch->id)->whereNotIn('sample_detail_id', $samples->pluck('id')->toArray())->delete();
        $payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();

        return view('layouts.lab.sample-workflow.customer_focus', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered'));
    }

    public function sendBatchScheduleAnalysis(Request $request)
    {
        $batch = SampleHeader::with(['customer', 'sample_type'])->find($request->batch_id);

        if ($batch->samples()->count() == 0) {
            return redirect()->back()->with('error', 'You cannot send schedule of analysis for a batch with no sample');
        }

        $samples = SampleDetails::where('sample_header_id', $batch->id)->get();
        $sampleTrs = "";
        foreach ($samples as $sample) {
            $target_date = date('Y-m-d', strtotime($sample->targetDateRelation()));
            $sampleTrs .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample->sample_code) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars(implode(',', $sample->analyteNames())) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>  
            </tr>';
        }

        // $customer = CrmCustomer::find($batch->crm_customer_id);
        $contact = CustomerContact::find($request->contact_id);
        if (isset($contact->id) && $contact->email != '') {
            // return response()->json($sampleTrs);

            $body = '
			<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                <p style="font-size: 12px;">
                    Dear ' . $batch->customer->name . ', <br><br>
                    I hope this message finds you well. <br>
                    We are pleased to confirm that your samples <b>' . strtoupper($batch->sample_type->name) . '</b> have been successfully received and assigned following Ref IDs: 
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Test(s) Required</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                    </tr>
                    ' . $sampleTrs . '
                </table>

                <p style="font-size: 12px; margin-top: 15px;">
                    <br>
                    Once analysis is completed, you will receive an update regarding your test results.<br>
                    For any inquiries, please contact us on <b>fivet.co.ke, /+12345678 </b>. <br>
                    Thank you for the opportunity to serve you. <br><br>
                    Kind regards, <br>
                    FIVET COMPANY LIMITED
                </p>
            </div>
			';
            notify_user($body, $contact->email, '[FIVET] Confirmation of Sample Receipt and Schedule of Analysis ' . $batch->batch_code, false, true, ['dannyagah13@gmail.com']);

            $batch->schedule_sent = 1;
            $batch->schedule_analysis_sent = date('Y-m-d');
            $batch->schedule_analysis_sender = auth()->user()->id;
            $batch->save();
            $schedule_str = 'Schedule Of Analysis Sendoff';
            $schedueDate = SampleDate::where('sample_header_id', $batch->id)->where('name', $schedule_str)->first() ?? new SampleDate();
            $schedueDate->name = $schedule_str;
            $schedueDate->sample_header_id = $batch->id;
            $schedueDate->date = date('Y-m-d');
            $schedueDate->save();

            return redirect()->back()->with('success', 'Schedule of analysis sent out successfully');
        }

        return redirect()->back()->with('error', 'Kindly choose the customer contact first on the form before sending the schedule of analysis');
    }

    public function sendBatchPaymentReminder(Request $request)
    {
        $contact = CustomerContact::find($request->contact_id);
        $batch = SampleHeader::find($request->batch_id);
        notify_user($request->body, $contact->email, '[FIVET LIMS] Payment Reminder ' . $batch->batch_code);

        return redirect()->back()->with('success', 'Payment reminder sent out successfully');
    }

    public function getLabSectionsByLab($lab_id)
    {
        $sections = $lab_id > 0 ? SampleAnalysisStage::where('lab_id', $lab_id)->where('active', 1)->get() : SampleAnalysisStage::where('active', 1)->get();

        return response()->json($sections);
    }

    public function moveToLab(Request $request)
    {
        // return response()->json($request->all());
        $status = 'Samples In Lab';

        foreach ($request->batch_code as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

            $this->updateChainofCustody($custodyDetails);
            $batch->status = $status;
            $batch->save();
        }

        return redirect()->back()->with('success', 'Sample(s) moved to samples in Lab section successfully');
    }

    private function resolveBatchAnalysisDateRange(string $batchId): ?object
    {
        $records = SampleAnalysisDates::where('sample_header_id', $batchId)
            ->get(['start_analysis_date', 'analysis_dates']);

        $startDates = [];
        $endDates = [];

        foreach ($records as $record) {
            if (! empty($record->start_analysis_date)) {
                $startDates[] = $record->start_analysis_date;
                $endDates[] = $record->start_analysis_date;
            }

            $decoded = json_decode((string) $record->analysis_dates, true);
            if (! is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $sectionRange) {
                if (is_array($sectionRange)) {
                    $startDate = $sectionRange['start_date'] ?? $sectionRange['start'] ?? null;
                    $endDate = $sectionRange['end_date'] ?? $sectionRange['end'] ?? null;

                    if (! empty($startDate)) {
                        $startDates[] = $startDate;
                        $endDates[] = $startDate;
                    }
                    if (! empty($endDate)) {
                        $endDates[] = $endDate;
                    }

                    continue;
                }

                if (! empty($sectionRange)) {
                    $startDates[] = $sectionRange;
                    $endDates[] = $sectionRange;
                }
            }
        }

        sort($startDates);
        rsort($endDates);

        $startDate = $startDates[0] ?? '';
        $endDate = $endDates[0] ?? $startDate;

        if ($startDate === '' && $endDate === '') {
            return null;
        }

        return (object) [
            'start_analysis_date' => $startDate,
            'end_analysis_date' => $endDate,
        ];
    }

    public function processTestRequestReport(Request $request)
    {
        $request->validate([
            'batch_id' => ['required', 'string'],
            'language' => ['required', 'in:en,ar,pt'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        $batch = SampleHeader::find($request->batch_id);
        if (!$batch) {
            return redirect()->back()->with('error', 'Batch not found.');
        }

        // Bump revision sequence
        $batch->test_request_report_sequence = ($batch->test_request_report_sequence ?? 0) + 1;
        $batch->save();

        // Record revision
        \App\Models\TestRequestReportRevision::create([
            'batch_id'     => $batch->id,
            'revision_no'  => $batch->test_request_report_sequence,
            'language'     => $request->language,
            'notes'        => $request->notes,
            'generated_by' => auth()->id(),
        ]);

        return redirect()->route('generateTestRequestReport', [
            'batch_id' => $batch->id,
            'seq'      => $batch->test_request_report_sequence,
            'lang'     => $request->language,
            'mode'     => 'pdf',
        ]);
    }

    public function deliverTestRequestReport(Request $request)
    {
        $request->validate([
            'batch_id'    => ['required', 'string'],
            'channels'    => ['required', 'array', 'min:1'],
            'channels.*'  => ['in:email,whatsapp,portal'],
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['string'],
            'company_units' => ['nullable', 'array', 'min:1'],
            'company_units.*' => ['string', 'max:255'],
            'company_unit' => ['nullable', 'string', 'max:255'],
            'portal_languages' => ['nullable', 'array', 'min:1'],
            'portal_languages.*' => ['in:en,ar,pt'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $channels = $request->channels;
        if (in_array('portal', $channels, true)) {
            $request->validate([
                'portal_languages' => ['required', 'array', 'min:1'],
            ]);
        }

        $batch = SampleHeader::with(['customer'])->find($request->batch_id);
        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        }

        $company    = getActiveCompany();
        $companyName = $company->name ?? config('app.name', 'Laboratory');
        $revisionNo  = $batch->test_request_report_sequence ?? 1;
        $jobNumber   = $batch->batch_code;
        $reportNumber = $jobNumber . '-R' . str_pad($revisionNo, 2, '0', STR_PAD_LEFT);

        // Build download URL — prefer online URL, then storage URL
        $downloadUrl = null;
        if (!empty($batch->batch_report_online_url)) {
            $downloadUrl = $batch->batch_report_online_url;
        } elseif (!empty($batch->batch_report_url)) {
            $downloadUrl = url('/storage' . $batch->batch_report_url);
        }
        // Fallback: link to the generate route so they can view it
        if (!$downloadUrl) {
            $downloadUrl = route('generateTestRequestReport', [
                'batch_id' => $batch->id,
                'lang'     => $request->lang ?? 'en',
            ]);
        }

        $channels    = $request->channels;
        $contactIds  = $request->contact_ids;
        $notes       = $request->notes;
        $customerId  = $batch->crm_customer_id;
        $portalLanguages = collect($request->input('portal_languages', []))
            ->map(fn ($language) => (string) $language)
            ->filter(fn ($language) => in_array($language, ['en', 'ar', 'pt'], true))
            ->unique()
            ->values()
            ->all();
        $results     = [];
        $unitNameMap = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $customerId)
            ->pluck('name', 'id')
            ->map(fn ($name) => trim((string) $name));

        $resolveUnitLabel = function (string $unit) use ($unitNameMap): string {
            $unit = trim($unit);
            if ($unit === '') {
                return '';
            }

            // ContactForm stores unit UUIDs in unit_name; resolve them to display names.
            if (\Illuminate\Support\Str::isUuid($unit)) {
                return trim((string) ($unitNameMap->get($unit) ?? ''));
            }

            return $unit;
        };

        $selectedUnits = collect($request->input('company_units', []))
            ->map(fn ($unit) => $resolveUnitLabel((string) $unit))
            ->filter()
            ->unique(fn ($unit) => strtolower($unit))
            ->values();

        $legacyUnit = $resolveUnitLabel((string) $request->input('company_unit', ''));
        if ($selectedUnits->isEmpty() && $legacyUnit !== '') {
            $selectedUnits = collect([$legacyUnit]);
        }

        $validUnitNames = $unitNameMap
            ->map(fn ($name) => strtolower((string) $name))
            ->values();

        $selectedUnitsNormalized = $selectedUnits
            ->map(fn ($unit) => strtolower($unit))
            ->values();

        $invalidUnits = $selectedUnitsNormalized->reject(fn ($unit) => $validUnitNames->contains($unit));
        if ($selectedUnits->isNotEmpty() && $invalidUnits->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected departments/company units are invalid for this customer.',
            ], 422);
        }

        // ── Portal channel: batch-level operation, run once before contact loop ──
        if (in_array('portal', $channels) && $customerId) {
            $portalStatus = 'sent';
            $portalError  = null;

            try {
                $languageFiles = app(\App\Services\Sampleworkflow\TestRequestReportPdfService::class)
                    ->generatePortalLanguageFiles($batch, $revisionNo, $portalLanguages);

                $primaryFile = collect($languageFiles)->first(fn ($file) => $file->language === 'en')
                    ?? $languageFiles[0] ?? null;

                $downloadUrl = $primaryFile?->report_online_url
                    ?: ($primaryFile?->report_url ? url('/storage'.$primaryFile->report_url) : $downloadUrl);

                $languageSummary = collect($languageFiles)
                    ->map(fn ($file) => \App\Models\TestRequestReportRevision::$languages[$file->language] ?? strtoupper($file->language))
                    ->implode(', ');

                DB::transaction(function () use ($batch, $customerId, $downloadUrl, $reportNumber, $languageSummary): void {
                    // Keep the newer online URL and the legacy storage path in sync so
                    // either portal implementation can surface the report.
                    $batch->batch_report_online_url = $downloadUrl;

                    if (empty($batch->batch_report_url) && is_string($downloadUrl)) {
                        $downloadPath = parse_url($downloadUrl, PHP_URL_PATH);

                        if (is_string($downloadPath) && str_contains($downloadPath, '/storage/')) {
                            $legacyReportPath = str_replace('/storage', '', $downloadPath);
                            $batch->batch_report_url = $legacyReportPath;
                        }
                    }

                    // If the legacy path exists but online URL is empty/route-based,
                    // persist a direct storage URL so portal clients can download reliably.
                    if (!empty($batch->batch_report_url)) {
                        $normalizedStoragePath = '/storage/' . ltrim((string) $batch->batch_report_url, '/');
                        $batch->batch_report_online_url = url($normalizedStoragePath);
                    }

                    // Mark status as Completed so it passes the portal reports filter.
                    $batch->status = config('dashboard.report_status', 'Completed');
                    $batch->save();

                    // Push a CustomerNotification (surfaces in portal notifications bell).
                    \App\Models\CRM\CustomerNotification::create([
                        'customer_id'              => $customerId,
                        'entity_type'              => \App\SampleHeader::class,
                        'entity_id'                => $batch->id,
                        'notification_type'        => 'Laboratory Test Report Ready',
                        'notification_description' => "Report {$reportNumber} is available on the portal in: {$languageSummary}.",
                    ]);
                });

                // Bust dashboard cache so the portal reflects the new report immediately.
                $cacheService = app(\App\Services\Dashboard\DashboardCacheService::class);
                $cacheService->forgetCustomer($customerId);
                $cacheService->forgetList('reports', $customerId);

            } catch (\Throwable $e) {
                $portalStatus = 'failed';
                $portalError  = $e->getMessage();
                \Illuminate\Support\Facades\Log::error('TestRequestReport portal delivery failed', [
                    'batch_id' => $batch->id,
                    'error'    => $e->getMessage(),
                ]);
            }

            // Log the portal delivery once
            \App\Models\TestRequestReportDelivery::create([
                'batch_id'          => $batch->id,
                'revision_no'       => $revisionNo,
                'channel'           => 'portal',
                'recipient_name'    => 'Customer Portal',
                'recipient_contact' => null,
                'status'            => $portalStatus,
                'error'             => $portalError,
                'sent_by'           => auth()->id(),
            ]);

            $results[] = [
                'channel' => 'portal',
                'contact' => 'Customer Portal',
                'status'  => $portalStatus,
                'error'   => $portalError,
            ];

            // Remove portal from per-contact loop since it's already handled
            $channels = array_filter($channels, fn($c) => $c !== 'portal');
        }

        // Load requested contacts, limited to this customer and optional selected company unit.
        $contacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->whereIn('id', $contactIds)
            ->get()
            ->filter(function ($contact) use ($selectedUnitsNormalized, $selectedUnits, $unitNameMap, $resolveUnitLabel) {
                if ($selectedUnits->isEmpty()) {
                    return true;
                }

                $units = collect(explode(',', (string) ($contact->unit_name ?? '')))
                    ->map(fn ($unit) => strtolower($resolveUnitLabel((string) $unit)))
                    ->filter();

                if ($units->intersect($selectedUnitsNormalized)->isNotEmpty()) {
                    return true;
                }

                if (!empty($contact->crm_company_unit_id)) {
                    $mappedUnitName = strtolower((string) ($unitNameMap->get($contact->crm_company_unit_id) ?? ''));

                    return $mappedUnitName !== '' && $selectedUnitsNormalized->contains($mappedUnitName);
                }

                return false;
            })
            ->keyBy('id');

        $contactIds = array_values(array_filter($contactIds, fn($id) => $contacts->has($id)));
        if (empty($contactIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid contacts found for the selected department(s)/company unit(s).',
            ], 422);
        }

        foreach ($contactIds as $contactId) {
            $contact = $contacts->get($contactId);
            if (!$contact) continue;

            $contactName  = $contact->name ?? 'Customer';
            $contactEmail = $contact->email ?? null;
            $contactPhone = $contact->mobile ?? $contact->telephone ?? null;

            foreach ($channels as $channel) {
                $status = 'sent';
                $error  = null;

                try {
                    if ($channel === 'email') {
                        if (!$contactEmail) {
                            $status = 'failed';
                            $error  = 'No email address on file for this contact.';
                        } else {
                            \Illuminate\Support\Facades\Mail::to($contactEmail)->send(
                                new \App\Mail\TestRequestReportMail(
                                    contactName: $contactName,
                                    reportNumber: $reportNumber,
                                    companyName: $companyName,
                                    downloadUrl: $downloadUrl,
                                    notes: $notes,
                                )
                            );
                        }
                    } elseif ($channel === 'whatsapp') {
                        if (!$contactPhone) {
                            $status = 'failed';
                            $error  = 'No mobile/phone number on file for this contact.';
                        } else {
                            $tenantId = $company->id ?? config('app.tenant_id', 'default');
                            $account  = \App\Models\Messaging\TenantWhatsAppAccount::where('tenant_id', $tenantId)
                                ->where('active', true)->first();

                            if (!$account) {
                                $status = 'failed';
                                $error  = 'No active WhatsApp account configured for this tenant.';
                            } else {
                                $phone = preg_replace('/\D/', '', $contactPhone);

                                $payload = [
                                    'messaging_product' => 'whatsapp',
                                    'to'                => $phone,
                                    'type'              => 'text',
                                    'text'              => [
                                        'body' => "Hello {$contactName},\n\nYour Laboratory Test Report *{$reportNumber}* is ready.\n\nDownload: {$downloadUrl}\n\n{$companyName}",
                                    ],
                                ];

                                $outbound = \App\Models\Messaging\OutboundMessage::create([
                                    'tenant_id'     => $tenantId,
                                    'event_code'    => 'test_request_report',
                                    'recipient'     => $phone,
                                    'payload_json'  => $payload,
                                    'status'        => 'queued',
                                    'attempts'      => 0,
                                ]);

                                \App\Jobs\Messaging\SendWhatsAppMessageJob::dispatch($tenantId, $outbound->id);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $status = 'failed';
                    $error  = $e->getMessage();
                    \Illuminate\Support\Facades\Log::error('TestRequestReport delivery failed', [
                        'batch_id' => $batch->id,
                        'channel'  => $channel,
                        'contact'  => $contactId,
                        'error'    => $e->getMessage(),
                    ]);
                }

                // Log delivery
                \App\Models\TestRequestReportDelivery::create([
                    'batch_id'          => $batch->id,
                    'revision_no'       => $revisionNo,
                    'channel'           => $channel,
                    'recipient_name'    => $contactName,
                    'recipient_contact' => $channel === 'email' ? $contactEmail : ($channel === 'whatsapp' ? $contactPhone : null),
                    'status'            => $status,
                    'error'             => $error,
                    'sent_by'           => auth()->id(),
                ]);

                $results[] = [
                    'channel'  => $channel,
                    'contact'  => $contactName,
                    'status'   => $status,
                    'error'    => $error,
                ];
            }
        }

        $failCount = count(array_filter($results, fn($r) => $r['status'] === 'failed'));
        $sentCount = count($results) - $failCount;
        $isSuccess = $failCount === 0;

        return response()->json([
            'success' => $isSuccess,
            'message' => $isSuccess
                ? "{$sentCount} delivery(ies) queued successfully."
                : "{$sentCount} delivery(ies) queued successfully. {$failCount} failed.",
            'download_url' => $downloadUrl,
            'results' => $results,
        ]);
    }

    public function generateTestRequestReport(Request $request)
    {
        $batch = SampleHeader::with(['customer', 'sample_type', 'samples'])->find($request->batch_id);

        if (!$batch) {
            return redirect()->back()->with('error', 'Batch not found.');
        }

        // Only increment if not coming from processTestRequestReport (which already bumped it)
        if (!$request->has('seq')) {
            $batch->test_request_report_sequence = ($batch->test_request_report_sequence ?? 0) + 1;
            $batch->save();
        }

        $sequence     = $batch->test_request_report_sequence ?: 1;
        $jobNumber    = $batch->batch_code;
        $reportNumber = $jobNumber . '-R' . str_pad($sequence, 2, '0', STR_PAD_LEFT);

        $reportData = app(\App\Services\Sampleworkflow\TestRequestReportDataService::class)
            ->build($batch, $reportNumber);

        if (! empty($reportData['signatureWarning'])) {
            session()->flash('warning', $reportData['signatureWarning']);
        }

        // Language & translations
        $language = in_array($request->lang, ['en', 'ar', 'pt']) ? $request->lang : 'en';
        $isRTL    = ($language === 'ar');

        $labels = match ($language) {
            'ar' => [
                'report_title'        => 'تقرير الاختبار المعملي',
                'certificate_no'      => 'رقم الشهادة',
                'page_of'             => 'صفحة %d من %d',
                'attention'           => 'إلى عناية',
                'client'              => 'العميل',
                'address'             => 'العنوان والموقع',
                'report_no'           => 'رقم التقرير',
                'sample_no'           => 'رقم العينة',
                'date_received'       => 'تاريخ الاستلام',
                'date_reported'       => 'تاريخ التقرير',
                'container_type'      => 'نوع الحاوية',
                'sample_description'  => 'وصف العينة',
                'weight'              => 'الوزن',
                'sampled_by'          => 'أخذ العينة بواسطة',
                'sample_temperature'  => 'درجة حرارة العينة',
                'sample_preservation' => 'حفظ العينة',
                'production_date'     => 'تاريخ الإنتاج',
                'expiry_date'         => 'تاريخ الانتهاء',
                'lot_no'              => 'رقم الدُفعة',
                'no_of_pages'         => 'عدد الصفحات',
                'date_of_analysis'    => 'تاريخ التحليل',
                'sample_reference'    => 'مرجع العينة',
                'sample_point'        => 'نقطة العينة',
                'condition'           => 'الحالة',
                'analyte'             => 'المادة المحللة',
                'results'             => 'النتائج',
                'unit'                => 'الوحدة',
                'specification'       => 'المواصفة',
                'mu_percent'          => 'عدم اليقين %',
                'method'              => 'طريقة التحليل',
                'no_results'          => 'لا توجد نتائج لهذه العينة.',
                'no_samples'          => 'لم يتم العثور على عينات لهذه الدفعة.',
                'analysis_conducted'  => 'التحليل بواسطة',
                'test_method_dev'     => 'انحراف طريقة الاختبار: لا يوجد',
                'signed_behalf'       => 'موقّع لصالح',
                'no_signature'        => 'لا يوجد توقيع',
                'results_relate'      => 'تتعلق نتائج الاختبار بالعينات التي تم اختبارها فقط.',
                'no_reproduce'        => 'لا يجوز إعادة إنتاج هذا التقرير إلا كاملاً بإذن كتابي من المختبر.',
                'end_of_text'         => 'نهاية النص-',
                'issued_on'           => 'صدر في',
                'disclaimer'          => 'إخلاء المسؤولية: تمت اختبار جميع العينات في مختبر طرف ثالث',
            ],
            'pt' => [
                'report_title'        => 'RELATÓRIO DE ENSAIO LABORATORIAL',
                'certificate_no'      => 'Certificado n.º',
                'page_of'             => 'Página %d de %d',
                'attention'           => 'À atenção de',
                'client'              => 'Cliente',
                'address'             => 'Endereço e Localização',
                'report_no'           => 'N.º do Relatório',
                'sample_no'           => 'N.º da Amostra',
                'date_received'       => 'Data de Receção',
                'date_reported'       => 'Data do Relatório',
                'container_type'      => 'Tipo de Recipiente',
                'sample_description'  => 'Descrição da Amostra',
                'weight'              => 'Peso',
                'sampled_by'          => 'Amostrado por',
                'sample_temperature'  => 'Temperatura da Amostra',
                'sample_preservation' => 'Preservação da Amostra',
                'production_date'     => 'Data de Produção',
                'expiry_date'         => 'Data de Validade',
                'lot_no'              => 'N.º de Lote',
                'no_of_pages'         => 'N.º de Páginas',
                'date_of_analysis'    => 'Data da Análise',
                'sample_reference'    => 'Referência da Amostra',
                'sample_point'        => 'Ponto de Amostragem',
                'condition'           => 'Condição',
                'analyte'             => 'Analito',
                'results'             => 'Resultados',
                'unit'                => 'Unidade',
                'specification'       => 'Especificação',
                'mu_percent'          => 'I.M. %',
                'method'              => 'Método de análise',
                'no_results'          => 'Nenhum resultado registado para esta amostra.',
                'no_samples'          => 'Nenhuma amostra encontrada para este lote.',
                'analysis_conducted'  => 'Análise conduzida por',
                'test_method_dev'     => 'Desvio do método de ensaio: Nenhum',
                'signed_behalf'       => 'Assinado por e em nome de',
                'no_signature'        => 'Sem assinatura registada',
                'results_relate'      => 'Os resultados dos ensaios referem-se apenas às amostras ensaiadas.',
                'no_reproduce'        => 'Este relatório não pode ser reproduzido, exceto na íntegra, sem aprovação escrita do Laboratório.',
                'end_of_text'         => '-Fim do texto',
                'issued_on'           => 'Emitido em',
                'disclaimer'          => 'AVISO: TODAS AS AMOSTRAS FORAM ENSAIADAS NUM LABORATÓRIO EXTERNO',
            ],
            default => [
                'report_title'        => 'LABORATORY TEST REPORT',
                'certificate_no'      => 'Certificate no.',
                'page_of'             => 'Page %d of %d',
                'attention'           => 'Attention',
                'client'              => 'Client',
                'address'             => 'Address and Location',
                'report_no'           => 'Report No',
                'sample_no'           => 'Sample No.',
                'date_received'       => 'Date received',
                'date_reported'       => 'Date Reported',
                'container_type'      => 'Container Type',
                'sample_description'  => 'Sample Description',
                'weight'              => 'Weight',
                'sampled_by'          => 'Sampled By',
                'sample_temperature'  => 'Sample Temperature',
                'sample_preservation' => 'Sample Preservation',
                'production_date'     => 'Production Date',
                'expiry_date'         => 'Expiry Date',
                'lot_no'              => 'Lot No.',
                'no_of_pages'         => 'No. of pages',
                'date_of_analysis'    => 'Date of Analysis',
                'sample_reference'    => 'Sample Reference',
                'sample_point'        => 'Sample Point',
                'condition'           => 'Condition',
                'analyte'             => 'Analyte',
                'results'             => 'Results',
                'unit'                => 'Unit',
                'specification'       => 'Specification',
                'mu_percent'          => 'M.U%',
                'method'              => 'Method of Analysis',
                'no_results'          => 'No results captured for this sample.',
                'no_samples'          => 'No samples found for this batch.',
                'analysis_conducted'  => 'Analysis conducted by',
                'test_method_dev'     => 'Test method deviation: None',
                'signed_behalf'       => 'Signed for and on behalf of',
                'no_signature'        => 'No signature on file',
                'results_relate'      => 'Test results relate only to the samples tested.',
                'no_reproduce'        => 'This report shall not be reproduced except in full, without the written approval of the Laboratory.',
                'end_of_text'         => '-End of text',
                'issued_on'           => 'Issued on',
                'disclaimer'          => 'DISCLAIMER: ALL THE SAMPLES WERE TESTED AT A THIRD-PARTY LABORATORY',
            ],
        };

        $verificationUrl = route('generateTestRequestReport', [
            'batch_id' => $batch->id,
            'seq' => $sequence,
            'lang' => $language,
            'mode' => 'pdf',
        ]);

        $footerQrCode = '';
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $footerQrCode = 'data:image/svg+xml;base64,' . base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(110)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($verificationUrl)
            );
        }

        // Revision history for this batch
        $revisions = \App\Models\TestRequestReportRevision::where('batch_id', $batch->id)
            ->orderByDesc('revision_no')
            ->get();

        $mode = strtolower((string) $request->query('mode', 'view'));
        $isPdfMode = $mode === 'pdf';
        if ($mode === 'pdf') {
            $viewData = array_merge($reportData, compact(
                'language',
                'labels',
                'revisions',
                'isRTL',
                'isPdfMode',
                'footerQrCode',
                'verificationUrl'
            ));

            $pdf = Pdf::loadView('layouts.lab.sample-workflow.report-formats.test_request_report', $viewData);
            $pdf->setPaper('a4');

            $customerName = preg_replace('/[^A-Za-z0-9\-\_]/', '_', (string) ($batch->customer->name ?? 'customer'));
            $customerName = trim($customerName, '_') ?: 'customer';
            $filename = 'TRR_' . $reportNumber . '.pdf';
            $relativePath = '/reports/' . $customerName . '/' . $filename;
            $absoluteDir = storage_path('app/reports/' . $customerName);

            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }

            $absolutePath = $absoluteDir . '/' . $filename;
            $pdf->save($absolutePath);

            $batch->batch_report_url = $relativePath;
            $batch->batch_report_online_url = url('/storage' . $relativePath);
            $batch->save();

            return $pdf->stream($filename, [
                'Attachment' => false,
            ]);
        }

        return view('layouts.lab.sample-workflow.report-formats.test_request_report', array_merge($reportData, compact(
            'language',
            'labels',
            'revisions',
            'isRTL',
            'isPdfMode',
            'footerQrCode',
            'verificationUrl'
        )));
    }

    public function moveToVerificationApprovalLevel(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        $previousWorkflow = $batch->status;

        if ($batch->lab_section_ids == '') {
            return redirect()->back()->with('error', 'Kindly provide the lab sections associated with the sample at batch information section');
        }

        if ($request->status == 'Sample Verification') {
            TatCaptured::where('sample_header_id', $batch->id)->update(['is_complete' => 1]);

            // Clear previous approvers for this verification cycle
            BatchLabSectionApprover::where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Verification')
                ->delete();

            // Get Technical Reviewer from previous Sample Approval context
            // The Technical Reviewer is the first approver who approved at Sample Approval stage
            $technicalReviewer = BatchLabSectionApprover::where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Approval')
                ->where('status', 1)
                ->where('is_technical_reviewer', true)
                ->orWhere(function($q) use ($batch) {
                    $q->where('batch_id', $batch->id)
                    ->where('batch_status', 'Sample Approval')
                    ->where('status', 1)
                    ->where('approver_order', 1);
                })
                ->orderBy('approval_date', 'asc')
                ->first();

            // If no previous technical reviewer found, use the first lab section approver
            if (!$technicalReviewer) {
                $section_users = LabSectionApproverRelationShip::whereIn(
                    'lab_section_id',
                    explode(',', $batch->lab_section_ids)
                )->get();

                if ($section_users->count() > 0) {
                    $technicalReviewerUser = User::find($section_users->first()->user_id);
                } else {
                    return redirect()->back()->with('error', 'Kindly provide approval configuration for the selected batch lab sections');
                }
            } else {
                $technicalReviewerUser = User::find($technicalReviewer->user_id);
            }

            // Get Lab Manager (typically from configuration or system role)
            // Find user with Lab Manager or section head role
            $labManagerRole = \App\Role::where('name', 'like', '%Lab Manager%')->first();
            $labManager = null;

            if ($labManagerRole) {
                $labManager = User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
                    ->where('ur.role_id', $labManagerRole->id)
                    ->first();
            }

            // Fallback: Use section head if available
            if (!$labManager && $batch->lab_section_ids) {
                $section = SampleAnalysisStage::whereIn('id', explode(',', $batch->lab_section_ids))
                    ->first();
                if ($section && $section->section_head_id) {
                    $labManager = User::find($section->section_head_id);
                }
            }

            if (!$labManager && $technicalReviewerUser) {
                $labManager = $technicalReviewerUser; // Fallback to same user if needed
            }

            // Create Technical Reviewer approver (Order 1 - approves first)
            if ($technicalReviewerUser) {
                $trApprover = new BatchLabSectionApprover();
                $trApprover->user_id = $technicalReviewerUser->id;
                $trApprover->title = 'Technical Reviewer';
                $trApprover->lab_section_ids = $batch->lab_section_ids;
                $trApprover->batch_id = $batch->id;
                $trApprover->batch_status = 'Sample Verification';
                $trApprover->status = 0; // Not yet approved
                $trApprover->approver_order = 1; // Technical Reviewer approves first
                $trApprover->is_technical_reviewer = true;
                $trApprover->can_send_back_to_lab = false;
                $trApprover->show_report = 1;
                $trApprover->save();

                // Notify Technical Reviewer
                if (isset($request->notification)) {
                    $message = 'Hi ' . $technicalReviewerUser->name . ', <br>' . $batch->batch_code . ' batch is ready for technical review and verification. <br> Please review and approve.';
                    notify_user($message, $technicalReviewerUser->email, '[POLUCON LIMS] ' . $batch->batch_code . ' - Technical Review Required');
                }
            }

            // Create Lab Manager approver (Order 2 - approves after Technical Reviewer)
            if ($labManager) {
                $lmApprover = new BatchLabSectionApprover();
                $lmApprover->user_id = $labManager->id;
                $lmApprover->title = 'Lab Manager';
                $lmApprover->lab_section_ids = $batch->lab_section_ids;
                $lmApprover->batch_id = $batch->id;
                $lmApprover->batch_status = 'Sample Verification';
                $lmApprover->status = 0; // Not yet approved
                $lmApprover->approver_order = 2; // Lab Manager approves second
                $lmApprover->is_technical_reviewer = false;
                $lmApprover->can_send_back_to_lab = true; // Lab Manager can send back
                $lmApprover->show_report = 1;
                $lmApprover->save();

                // Notify Lab Manager
                if (isset($request->notification)) {
                    $message = 'Hi ' . $labManager->name . ', <br>' . $batch->batch_code . ' batch will be ready for your approval after technical review is complete.';
                    notify_user($message, $labManager->email, '[POLUCON LIMS] ' . $batch->batch_code . ' - Awaiting Lab Manager Approval');
                }
            }

            // Update batch status
            $batch->status = $request->status;
            $batch->report_status = '';
            $batch->prelim_report_status = 0;
            $batch->prelim_batch_status = '';
            $batch->save();

            return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch moved to Sample Verification with assigned Technical Reviewer and Lab Manager');
        }

        // Legacy handling for other status transitions
        $approvers_user_ids = BatchLabSectionApprover::where('batch_id', $batch->id)->pluck('user_id')->toArray();
        if (in_array($request->user_id, $approvers_user_ids)) {
            return redirect()->back()->with('error', 'System cannot assign the specified user as an approver since the user is already an approver');
        }

        BatchLabSectionApprover::where('batch_id', $batch->id)->where('lab_section_ids', 0)->delete();
        $approvers = new BatchLabSectionApprover();
        $approvers->status = 0;
        $approvers->user_id = $request->user_id;
        $approvers->title = $request->title;
        $approvers->lab_section_ids = 0;
        $approvers->batch_id = $batch->id;
        $approvers->batch_status = $request->status;
        $approvers->show_report = 1;
        $approvers->save();
        $batch->status = $request->status;
        $batch->save();
        if (isset($request->notification)) {
            $user = User::find($request->user_id);
            $message = 'Hi ' . $user->name . ', <br>' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. <br> Comments : ' . $request->comments;
            notify_user($message, $user->email, '[POLUCON LIMS] ' . $batch->batch_code . ' Batch Approval Notification');
        }
        if (isset($request->send_message)) {
            $user = User::find($request->user_id);
            $sms_message = 'Hi ' . $user->name . ', ' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. Comments : ' . $request->comments;
            sendTextMessage($user->phone, $sms_message);
        }

        return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch move was successful');
    }

    /**
     * Send batch back from verification to lab for amendment/reanalysis.
     * Only Lab Manager can perform this action.
     */
    public function sendBackToLabForAmendment(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        $labManager = auth()->user();

        // Verify user is a Lab Manager with can_send_back_to_lab permission
        $isLabManager = BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('user_id', $labManager->id)
            ->where('can_send_back_to_lab', true)
            ->where('batch_status', 'Sample Verification')
            ->exists();

        if (!$isLabManager) {
            return redirect()->back()->with('error', 'Only Lab Manager can send batch back for amendment');
        }

        // Create amendment record
        $amendment = new BatchAmmendment();
        $amendment->batch_id = $batch->id;
        $amendment->created_by_id = $labManager->id;
        $amendment->reason = $request->amendment_reason ?? 'Lab Manager requested amendment for further analysis';
        $amendment->samples = json_encode($request->selected_samples ?? []);
        $amendment->report_url = $batch->batch_report_url ?? '';
        $amendment->version_number = ($batch->is_amendment ?? 0) + 1;
        $amendment->save();

        // Update batch to amendment state
        $batch->status = 'Samples In Lab'; // Back to lab for re-analysis
        $batch->in_ammendment_proccess = 1;
        $batch->is_amendment = $amendment->version_number;
        $batch->save();

        app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
            ->syncReportNumbersForBatch($batch, (int) $batch->is_amendment);

        // Clear previous verification approvers
        BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->delete();

        // Notify relevant parties
        $analyst = User::find($batch->specialist_analyst_id);
        if ($analyst) {
            $message = 'Hi ' . $analyst->name . ', <br>' . $batch->batch_code . ' requires amendment/reanalysis. ' 
                . '<br>Reason: ' . $amendment->reason
                . '<br>Please complete the amendment and resubmit for verification.';
            notify_user($message, $analyst->email, '[POLUCON LIMS] ' . $batch->batch_code . ' - Amendment Required');
        }

        return redirect()->route('sample-workflow', ['status' => 'Sample Verification'])
            ->with('success', 'Batch sent back to lab for amendment. Version ' . $amendment->version_number . ' created.');
    }

    /**
     * When amendment is completed, re-assign Technical Reviewer and Lab Manager for another verification cycle.
     * This is called after analyst completes the amendment work.
     */
    public function resubmitAmendmentForVerification(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);

        if (!$batch->in_ammendment_proccess) {
            return redirect()->back()->with('error', 'Batch is not in amendment process');
        }

        // Re-assign Technical Reviewer and Lab Manager
        // Get the same users who were assigned before
        $previousTechnicalReviewer = BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->where('is_technical_reviewer', true)
            ->orderBy('created_at', 'desc')
            ->first();

        $previousLabManager = BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->where('can_send_back_to_lab', true)
            ->orderBy('created_at', 'desc')
            ->first();

        // Create new verification approver records for this cycle
        if ($previousTechnicalReviewer && $previousTechnicalReviewer->user_id) {
            $trApprover = new BatchLabSectionApprover();
            $trApprover->user_id = $previousTechnicalReviewer->user_id;
            $trApprover->title = 'Technical Reviewer';
            $trApprover->lab_section_ids = $batch->lab_section_ids;
            $trApprover->batch_id = $batch->id;
            $trApprover->batch_status = 'Sample Verification';
            $trApprover->status = 0; // Not yet approved
            $trApprover->approver_order = 1;
            $trApprover->is_technical_reviewer = true;
            $trApprover->can_send_back_to_lab = false;
            $trApprover->show_report = 1;
            $trApprover->save();
        }

        if ($previousLabManager && $previousLabManager->user_id) {
            $lmApprover = new BatchLabSectionApprover();
            $lmApprover->user_id = $previousLabManager->user_id;
            $lmApprover->title = 'Lab Manager';
            $lmApprover->lab_section_ids = $batch->lab_section_ids;
            $lmApprover->batch_id = $batch->id;
            $lmApprover->batch_status = 'Sample Verification';
            $lmApprover->status = 0; // Not yet approved
            $lmApprover->approver_order = 2;
            $lmApprover->is_technical_reviewer = false;
            $lmApprover->can_send_back_to_lab = true;
            $lmApprover->show_report = 1;
            $lmApprover->save();
        }

        // Update batch status
        $batch->status = 'Sample Verification';
        $batch->in_ammendment_proccess = 0;
        $batch->save();

        return redirect()->route('sample-workflow', ['status' => 'Samples In Lab'])
            ->with('success', 'Amendment submitted for verification. Technical Reviewer and Lab Manager reassigned.');
    }

    public function editVerificationApproverConfig(Request $request)
    {
        $config = BatchLabSectionApprover::find($request->approver_id);
        $config->user_id = $request->user_id;
        $config->title = $request->title;
        $config->save();

        return redirect()->back()->with('success', 'Batch Approval updated successfully');
    }

    public function deleteVerificationApproverConfig(Request $request)
    {
        BatchLabSectionApprover::find($request->approver_id)->delete();

        return redirect()->back()->with('success', 'Batch Approval deleted successfully');
    }

    public function changeBatchApprovalStatus(Request $request)
    {
        $approver = BatchLabSectionApprover::find($request->approver_id);
        $batch = SampleHeader::find($approver->batch_id);
        $currentUser = auth()->user();

        // For Sample Verification stage, enforce approval order
        if ($approver->batch_status == 'Sample Verification') {
            // Verify this is the current user approving
            if ($approver->user_id !== $currentUser->id) {
                return redirect()->back()->with('error', 'You do not have permission to approve on behalf of another user');
            }

            // Check if user can approve based on order
            if (!$this->canApproveAtVerificationStage($batch->id, $currentUser->id)) {
                if ($approver->approver_order == 2) {
                    return redirect()->back()->with('error', 'Lab Manager can only approve after Technical Reviewer has approved');
                }
                return redirect()->back()->with('error', 'You cannot approve at this time');
            }

            // Mark as approved
            $approver->status = $request->status;
            $approver->approval_date = date('Y-m-d H:i:s');
            $approver->remark = $request->remark;
            $approver->save();

            // Notify next approver if this is Technical Reviewer
            if ($approver->is_technical_reviewer && $approver->status == 1) {
                $labManager = BatchLabSectionApprover::where('batch_id', $batch->id)
                    ->where('batch_status', 'Sample Verification')
                    ->where('can_send_back_to_lab', true)
                    ->first();

                if ($labManager) {
                    $lmUser = User::find($labManager->user_id);
                    if ($lmUser) {
                        $message = 'Hi ' . $lmUser->name . ', <br>' . $batch->batch_code . ' batch has been reviewed by Technical Reviewer and is now ready for your approval.';
                        notify_user($message, $lmUser->email, '[POLUCON LIMS] ' . $batch->batch_code . ' - Ready for Lab Manager Approval');
                    }
                }
            }

            return redirect()->back()->with('success', 'Verification approval recorded successfully');
        }

        // Legacy approval logic for other stages
        $ip_address_link = request()->root();
        BatchLabSectionApprover::where('id', $request->approver_id)->update(['status' => $request->status, 'approval_date' => date('Y-m-d H:i:s'), 'remark' => $request->remark]);
        if (BatchLabSectionApprover::where('id', $request->approver_id)->where('status', 0)->get()->count() == 0) {
            if ($batch->status == 'Sample Approval') {
                $batch->approval_date = getTodayDate();
                $batch->save();
                $link = $ip_address_link . '/sample-workflow/batch/' . $batch->id . '/details/0/0/All%20Samples';
                $samplescodes = SampleDetails::where('sample_header_id', $batch->id)->pluck('sample_code')->toArray();
                $li_str = '';
                foreach ($samplescodes as $code) {
                    $li_str .= '<li><a href="' . $link . '" >' . $code . '</a></li>';
                }
                $subject = 'Automated Invoice Request - Job [' . implode(', ', $samplescodes) . ']';
                $message = 'Dear Finance Team,<br><br>

				This is an automated notification to inform you that the following job is now ready to be invoiced: <br>
				
				<b>*Job Number/Report Number:*</b> <br>
				<ul>' . $li_str . '</ul>
				Please proceed with creating an invoice for this job at your earliest convenience. If additional information is required, kindly reach out to the relevant department.
				Thank you for your attention.';
                $emails = ['laboratory@FIVET.com'];

                $invoice = Invoice::find($batch->invoice_id);
                if (isset($invoice->id)) {
                    $zohoService = new ZohoController();
                    $zoho_sales = $zohoService->changeSalesOrderStatus($invoice->zoho_id);
                    if ($zoho_sales['code'] == 0) {
                        $invoice->zoho_so_confirmed = date('Y-m-d');
                        $invoice->save();
                    }
                }

                try {
                    notify_user($message, 'Accounts@FIVET.com', $subject, false, true, $emails);
                } catch (\Exception $e) {
                    return redirect()->back()->with('success', 'Batch Approval updated successfully but notifications to accounts and lab were not set');
                }
            }
        }

        return redirect()->back()->with('success', 'Batch Approval updated successfully');
    }

    public function getClientDetailsAjax($id)
    {
        $customer = CrmCustomer::with(['units' => function ($q) {
            $q->where('active', 1);
        }, 'contacts'])->find($id);

        if (! $customer) {
            return response()->json([
                'units' => [],
                'unit_name' => 'Company Section',
                'sample_point_name' => 'Sample Point',
                'contacts' => [],
                'customer' => null,
                'mode_of_payment' => null,
            ]);
        }

        // Prefer the configurable "unit" label from CRM.
        // If not set, default to "Company Section" as requested.
        $unitName = trim((string) ($customer->unit_configurable_name ?? ''));
        if ($unitName === '') {
            $unitName = 'Company Section';
        }

        // Prefer configurable sample point label, with a sensible default.
        $samplePointName = trim((string) ($customer->sample_point_configurable_name ?? ''));
        if ($samplePointName === '') {
            $samplePointName = 'Sample Point';
        }

        return response()->json([
            'units' => $customer->units,
            'unit_name' => $unitName,
            'sample_point_name' => $samplePointName,
            'contacts' => $customer->contacts,
            'customer' => $customer,
            'mode_of_payment' => (int) ($customer->credit_days ?? 0) > 0 ? 'Post-Paid' : 'Pre-Paid',
        ]);
    }

    public function searchClients(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        if ($perPage < 1) {
            $perPage = 50;
        }
        $perPage = min($perPage, 100);

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $term = trim((string) $request->input('term', $request->input('q', '')));

        $builders = CrmCustomer::query()
            ->select(['id', 'name'])
            ->where('active', 1);

        if ($term !== '') {
            $builders->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%');
            });
        }

        $clients = $builders->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $results = collect($clients->items())->map(static function (CRMCustomer $client): array {
            return [
                'id' => $client->id,
                'text' => $client->name,
            ];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $clients->hasMorePages(),
            ],
        ]);
    }

    public function generateTabletCustomerFocusIndex(Request $request)
    {
        $sample_code = 'S' . date('Y') . $request->sample_no;
        $sample = SampleDetails::where('sample_code', $request->sample_no)->first();
        if (!isset($sample->id)) {
            return redirect()->back()->with('error', 'There is no sample with ' . $request->sample_no . ' sample/job number');
        }
        $batch = SampleHeader::find($sample->sample_header_id);
        if (!isset($batch->id)) {
            return redirect()->back()->with('error', 'There is no batch associated with the specified sample');
        }

        if (isset($request->is_clustered)) {
            $batches = SampleHeader::whereIn('id', explode(',', $batch->c_focus_ids_clustered));
            $getCustomers = clone $batches;
            $customer_ids = array_unique($getCustomers->pluck('crm_customer_id')->toArray());
            if (sizeof($customer_ids) > 1) {
                return redirect()->back()->with('error', 'All batches should be of the same client! Kindly check on the batches you have selected');
            }
            $batch = $getCustomers->orderBy('created_at', 'ASC')->first();
            if (!isset($batch->id)) {
                return redirect()->back()->with('error', 'the batch has no clustered customer focus');
            }
            $customer = CrmCustomer::find($batch->crm_customer_id);
            $sample_type_ids = $batches->pluck('sample_type_id')->toArray();
            $sample_types = implode(', ', array_unique(SampleType::whereIn('id', $sample_type_ids)->pluck('name')->toArray()));
            $company = getActiveCompany();
            $config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
            $docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
            $payment_detail = [
                'balance' => $batch->cluster_balance,
                // "total_amount"=>array_sum($batches->pluck('invoice_amount')->toArray()),
                'amount_paid' => $batch->cluster_amount_paid,
                'vat' => $batch->cluster_vat,
                'invoice_amount' => $batch->cluster_amount,
            ];
            $batch_ids = $batches->pluck('id')->toArray();
            $samples = SamplesCategory::whereIn('sample_header_id', $batch_ids)->get();
            $review_staff = getUserById($batch->receiving_officer);
            $is_clustered = 1;

            // SampleHeader::whereIn('batch_code',$request->batch_code)->update(['c_focus_ids_clustered'=>implode(',',$batch_ids),'cluster_amount'=>$request->invoice_amount,'cluster_vat'=>$request->vat,'cluster_amount_paid'=>$request->amount_paid,'cluster_balance'=>$request->balance]);
            return view('layouts.lab.sample-workflow.sign-customer-focus-show', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered', 'sample_types'));
        }

        $is_clustered = 0;

        $customer = CrmCustomer::find($batch->crm_customer_id);
        $company = getActiveCompany();
        $config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
        $docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
        $review_staff = getUserById($batch->receiving_officer);
        $samples = SamplesCategory::where('sample_header_id', $batch->id)->get();
        $payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();

        return view('layouts.lab.sample-workflow.sign-customer-focus-show', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered'));
    }

    public function getTableCustomerFocusSigning(Request $request)
    {
        if (isset($request->is_clustered)) {
            $batch = Sampleheader::find($request->sample_header_id);
            if ($batch->c_focus_ids_clustered != '') {
                Sampleheader::whereIn('id', explode(',', $batch->c_focus_ids_clustered))->update(['declaration_customer_approval_date' => date('Y-m-d h:i:s a'), 'declaration_customer_signature' => $request->signature, 'declaration_customer_contact_name' => $request->contact_person]);

                return response()->json('success');
            }
        }
        Sampleheader::find($request->sample_header_id)->update(['declaration_customer_approval_date' => date('Y-m-d h:i:s a'), 'declaration_customer_signature' => $request->signature, 'declaration_customer_contact_name' => $request->contact_person]);

        return response()->json('success');
        // return redirect()->back()->with('success','Customer Focus document signed successfully');
    }

    public function getSampleCodeToResultsAndCr()
    {
        $samples = SampleDetails::where('sample_header_id', '>', 13)->get();
        foreach ($samples as $sample) {
            CapturedResult::where('sample_detail_id', $sample->id)->update(['sample_detail_code' => $sample->sample_code]);
            Result::where('sample_detail_id', $sample->id)->update(['sample_detail_code' => $sample->sample_code]);
        }

        return response()->json('success');
    }

    public function anothershow($batch, $client = false, $portal = false, $status = false)
    {
        $batchID = $batch;
        $batchQuery = SampleHeader::with('comments', 'comments.creator');
        $batchLookup = trim((string) $batchID);
        $batch = Str::isUuid($batchLookup)
            ? $batchQuery->find($batchLookup)
            : $batchQuery->where('batch_code', $batchLookup)->first();
        $batch_scope = SystemConfiguration::where('key', 'batch_scope')->first();
        $customer_survey = SystemConfiguration::where('key', 'customer_survey')->first();
        $countries = Country::orderBy('name')->get();
        $methods = AnalysisMethod::where('active', 1)->get();
        $account_settings = getConfigTypeByName('Account Settings');
        $atachment_type = SystemConfiguration::where('key', 'attachment_type')->get();
        $users = User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->get();
        $labsections = SampleAnalysisStage::where('active', 1)->get();
        $reportingUnits = getReportingUnits();
        $conditions = SampleCondition::all();
        $products = CompanyProduct::all();
        $workflowstages = [];
        $workflows = getSampleWorflowStages();
        $sample_types = getSampleTypes();
        $samplingmethods = getSamplingMethods();
        $interlabs = [];
        $disposal_date = '';
        if (isset($account_settings->id)) {
            $accounts = getconfigByID($account_settings->id);
        } else {
            $accounts = [];
        }
        $not_captured = [];
        $payment_detail = [];
        $contacts = [];
        $batch_sample_codes = '';
        $report_formats = [];
        $approvers = [];
        $headerDetails = isset($batch->id) ? $batch->report_header_details() : [];
        $ammendments = isset($batch->id) ? getBatchAmmendmentsById($batch->id) : [];
        $allsamples = isset($batch->id) ? $batch->all_samples() : [];

        if (isset($batch->id)) {
            $workflowstages = getWorkflowStage_Stages($batch->status);
            if (in_array($batch->status, ['Sample Verification', 'Sample Approval'])) {
                // Fetch report formats configured specifically for this batch's lab sections
                $labSectionIds = array_filter(explode(',', $batch->lab_section_ids));

                if (!empty($labSectionIds)) {
                    $configuredFormatIds = \App\Models\LabSectionReportConfig::whereIn('sample_analysis_stage_id', $labSectionIds)
                        ->select('report_format_id', 'is_default', 'sample_analysis_stage_id')
                        ->get();

                    if ($configuredFormatIds->count() > 0) {
                        // Get the unique report format IDs
                        $formatIds = $configuredFormatIds->pluck('report_format_id')->unique()->toArray();

                        // Fetch the actual ReportFormat models
                        $report_formats = \App\ReportFormat::whereIn('id', $formatIds)->where('is_active', true)->get();

                        // Inject is_default flag into the formats for the view
                        foreach ($report_formats as $format) {
                            $format->is_default = $configuredFormatIds->where('report_format_id', $format->id)->where('is_default', true)->isNotEmpty();
                        }
                    } else {
                        // Fallback completely to all report formats if no configs exist
                        $report_formats = \App\ReportFormat::active()->get();
                    }
                } else {
                    $report_formats = \App\ReportFormat::active()->get();
                }
            }
            $disposal_date = \Carbon\Carbon::parse($batch->receipt_date)->addMonths(3)->format('Y-m-d');
            // return response()->json($disposal_date);
            $contacts = getCrmCustomerContactSchedule($batch->crm_customer_id);
            // return response()->json($contacts);
            $batch_sample_codes = getBacthSampleCodes($batch->id);
            $payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->get();
            $interlabs = InterLabLogView::where('sample_header_id', $batch->id)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get();
            // $equipment_data = $batch->get_captured();
            $approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->get();
            // foreach ($equipment_data['items'] as $b => $d) {
            // 	foreach ($d as $a => $k) {
            // 		foreach ($k as $i => $e) {

            // 			if ($e == '') {
            // 				if (!isset($not_captured[$b])) {
            // 					$not_captured[$b] = array();
            // 					array_push($not_captured[$b], $a);
            // 				} else {
            // 					array_push($not_captured[$b], $a);
            // 				}
            // 			}
            // 		}
            // 	}
            // }
            $not_captured = CapturedResult::where('sample_header_id', $batch->id)->whereNull('result')->join('analysis_elements as ae', function ($join) {
                $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
                $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
            })->where('ae.active', 1)->where('ae.active', 1)->selectRaw("string_agg(analyte_code, ',') as codes,sample_detail_code")->groupBy('sample_detail_id', 'sample_detail_code')->get();
            // return response()->json($test);
        }

        $selectedSampleType = \App\SampleType::find($batch->sample_type_id ?? 0) ?? false;
        $selected_analysis_types = isset($batch->sample_type_id) ? $selectedSampleType->analysis_types : [];
        if (isset($batch->id)) {
            if ($batch->is_qc_batch) {
                $standards = Standards::where('status', 1)->where('qc_type_id', $batch->qc_type_id)->get();
            } else {
                $standards = Standards::where('status', 1)->get();
            }
        } else {
            $standards = [];
        }
        $defaultClient = $client;
        $client_portal = $portal;
        if (isset($batch->id)) {
            $attachments = BatchAttachment::where('batch_id', $batch->id)->get();

            if ($batch->in_ammendment_proccess == 1) {
                $ammendment = BatchAmmendment::where('batch_id', $batch->id)->where('version_number', $batch->is_amendment)->first();
                // return response()->json($batch,200);
                if (isset($ammendment->id)) {
                    $samples = json_decode($ammendment->samples, true);
                    $ammendable = array_keys($samples);
                }
                // return response()->json($ammendments,200);
            } else {
                $ammendable = $batch->all_samples()->pluck('sample_code');
            }
        } else {
            $ammendable = [];
            $attachments = [];
            // return response()->json($ammendable,200);
        }
        $analysts = getActiveUsersByRole('Laboratory Analyst');
        $labs = Lab::where('active', 1)->get();

        // -----------------------------------
        // ---------------------------------------

        $customer = isset($batch->id) ? getCrmCustomerByID($batch->crm_customer_id) : [];
        $requestTypes = getRequestTypes();
        $notifiable_users = getNotifiableUsers();
        $notesReminderType = getNotesReminderTypes();
        $clients = getClients();
        $active_company = getActiveCompany();
        $samples = SamplesCategory::where('sample_header_id', $batch->id)->get();

        return view('layouts.lab.sample-workflow.show-again', compact('batch', 'batchID', 'defaultClient', 'selectedSampleType', 'client_portal', 'ammendable', 'standards', 'attachments', 'not_captured', 'analysts', 'countries', 'accounts', 'methods', 'atachment_type', 'batch_scope', 'customer_survey', 'interlabs', 'labs', 'users', 'payment_detail', 'labsections', 'contacts', 'batch_sample_codes', 'report_formats', 'approvers', 'reportingUnits', 'conditions', 'products', 'headerDetails', 'status', 'workflowstages', 'workflows', 'clients', 'sample_types', 'samplingmethods', 'active_company', 'ammendments', 'samples', 'customer', 'requestTypes', 'notifiable_users', 'notesReminderType', 'disposal_date'));
    }

    public function updateSampleSubmissionReception(Request $request, $requestId)
    {
        $submissionRequest = SampleSubmissionRequest::with('batch')->findOrFail($requestId);

        $validated = $request->validate([
            'received_by_full_name' => 'required|string|max:255',
            'received_by_title' => 'required|string|max:255',
            'received_by_signature' => 'nullable|string|max:255',
            'received_by_date' => 'required|date_format:Y-m-d',
            'received_by_time' => 'required|date_format:H:i',
            'submission_date' => 'required|date_format:Y-m-d',
            'submitted_by_full_name' => 'required|string|max:255',
            'physical_address' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_telephone_no' => 'nullable|string|max:255',
            'description_of_samples' => 'nullable|string|max:2000',
            'group_of_samples' => 'required|in:Confidential,PT,Research Government Samples,Uknown Samples',
            'number_of_samples' => 'required|integer|min:1',
            'gcla_file_reference_number' => 'nullable|string|max:255',
            'is_police_sample' => 'nullable|boolean',
            'ir_number' => 'nullable|required_if:is_police_sample,1|string|max:255',
        ]);

        $submissionRequest->fill($validated);
        $submissionRequest->is_police_sample = $request->boolean('is_police_sample');
        if (! $submissionRequest->is_police_sample) {
            $submissionRequest->ir_number = null;
        }
        $submissionRequest->status = 'received_at_lab';
        $submissionRequest->save();

        if ($submissionRequest->batch) {
            $submissionRequest->batch->receiving_officer_name = $validated['received_by_full_name'];
            $submissionRequest->batch->receipt_date = $validated['submission_date'];
            $submissionRequest->batch->submit_by = $validated['submitted_by_full_name'];
            $submissionRequest->batch->description = $validated['description_of_samples'] ?? $submissionRequest->batch->description;
            $submissionRequest->batch->batch_scope = $validated['group_of_samples'];
            $submissionRequest->batch->reference_number = $validated['gcla_file_reference_number'] ?? $submissionRequest->batch->reference_number;
            $submissionRequest->batch->schedule_customer_email = $validated['email'] ?? $submissionRequest->batch->schedule_customer_email;
            $submissionRequest->batch->case_id = $submissionRequest->is_police_sample
                ? ($validated['ir_number'] ?? null)
                : $submissionRequest->batch->case_id;
            $submissionRequest->batch->save();
        }

        return redirect()->back()->with('success', 'Receiving section updated successfully.');
    }

    public function getAnalysisTypeBySampleTypeIDAjax($sample_type_id)
    {
        return response()->json(AnalysisType::where('sample_type_id', $sample_type_id)->where('active', 1)->get());
    }

    public function getSampleConditionsAjax()
    {
        return response()->json(SampleCondition::where('active', 1)->get());
    }

    public function getSampleProductsAjax()
    {
        return response()->json(CompanyProduct::where('active', 1)->get());
    }

    public function getSampleStandardsAjax()
    {
        return response()->json(Standards::where('status', 1)->get());
    }

    public function getCrmCustomerSamplePointAjax($crm_id, $name)
    {
        $company_unit = CrmCompanyUnit::where('name', $name)->where('crm_customer_id', $crm_id)->first();

        return response()->json(isset($company_unit->id) ? SamplePoint::where('active', 1)->where('crm_company_unit_id', $company_unit->id)->get() : []);
    }

    public function getShowSampleParameterDataAjax($sample_id)
    {
        $captured_results = CapturedResultView::where('sample_detail_id', $sample_id)->get();
        $equipments = Equipment::where('active', 1)->get();
        $analysts = getActiveUsersByRole('Laboratory Analyst');
        $res = [
            'captured' => $captured_results,
            'equipments' => $equipments,
            'analysts' => $analysts,
        ];

        return response()->json($res);
    }

    public function methodNameFromId($inputObject, $inputKeysStr)
    {
        $inputKeys = explode(',', $inputKeysStr);
        $outputObject = [];
        foreach ($inputKeys as $key) {
            if (isset($inputObject[$key])) {
                $outputObject[$inputObject[$key]] = (int) $key;
            }
        }

        return $outputObject;
    }

    public function cloneBatchInformation(Request $request)
    {
        $batch_config = SystemConfiguration::where('key', 'batch_code_config')->first();
        foreach ($request->batch_code as $batch_code) {
            $batch = SampleHeader::where('batch_code', $batch_code)->first();
            $batch_data = SamplesCategory::where('batch_code', $batch_code)->first();

            $cust_code = str_split($batch_data->crm_code);
            $code = [];
            $loop = 0;
            $cont = [];
            foreach ($cust_code as $cc) {
                if ((int) $cc > 0) {
                    array_push($cont, $loop);
                } elseif (is_string($cc) && $cc != '0') {
                    array_push($code, $cc);
                }
                ++$loop;
            }
            $tt = sizeof($cust_code) - 1;

            $ranges = range($cont[0], $tt);
            $values = [];
            if (sizeof($cont) < 2) {
                array_push($values, '0');
                array_push($values, $cust_code[$cont[0]]);
            } else {
                foreach ($ranges as $r) {
                    array_push($values, $cust_code[$r]);
                }
            }

            $cP = implode('', $code) . $batch_config->value . implode('', $values) . $batch_data->sample_type_code;
            $config_batch_no = SystemConfiguration::where('key', 'batch_start_no')->first();
            if (!isset($config_batch_no->id)) {
                return redirect()->back()->with('error', 'Kindly set the start batch no');
            }
            $last_id = isset(SampleHeader::latest('id')->first()->id) ? SampleHeader::latest('id')->first()->id : 0;
            $batch_no_s = $config_batch_no->value + $last_id + 1;
            $final_no = '';
            if (strlen(strval($batch_no_s)) < 4) {
                $zerosss = str_repeat('0', 4 - strlen(strval($batch_no_s)));
                $final_no = $zerosss . '' . strval($batch_no_s);
            } else {
                $final_no = strval($batch_no_s);
            }
            $new_batch_code = $cP . '' . $final_no;

            $new_batch = $batch->replicate()->fill([
                'updated_at' => '',
                'created_at' => date('Y-m-d H:s:i.u'),
                'batch_code' => $new_batch_code,
                'receipt_date' => getTodayDate(),
                'status' => 'Samples Reception',
                'c_focus_ids_clustered' => "",
                'cluster_amount' => "",
                'cluster_balance' => "",
                'cluster_vat' => "",
                'cluster_amount_paid' => "",
            ]);
            $new_batch->save();
            $samples = SampleDetails::with('captured_results')
                ->where('sample_header_id', $batch->id)->get();
            $relation_analysis = [];

            foreach ($samples as $sample) {
                // $config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
                $sample_data = SamplesCategory::where('id', $sample->id)->first();
                $code = SampleDetails::where('lab_id', $sample_data->main_lab_id)->whereYear('created_at', date('Y'))->orderBy('id', 'DESc')->first()->sample_code;
                $last_sample = substr($code, 9, strlen($code));
                // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                // return response()->json($request->sample_details['lab_id'][$k]);
                $sample_number = intval($last_sample) + 1;
                $new_sample_code = 'S' . date('Y') . $sample_data->main_lab_code . sprintf('%0' . '4' . 'd', $sample_number);

                $new_sample = $sample->replicate()->fill([
                    'sample_code' => $new_sample_code,
                    'sample_header_id' => $new_batch->id,
                    'disposal_date' => \Carbon\Carbon::parse($new_batch->receipt_date)->addMonths(3)->format('Y-m-d'),
                    'sample_no' => sprintf('%0' . '4' . 'd', $sample_number),
                ]);
                $new_sample->save();

                foreach (explode(',', $new_sample->analysis_type_id) as $at_id) {
                    $relation_analysis[] = [
                        'analysis_type_id' => $at_id,
                        'batch_id' => $new_batch->id,
                        'sample_detail_id' => $new_sample->id,
                    ];
                }
                // $this->createDetailAnalysisRelation($new_batch->id, $new_sample->id, explode(',', $new_sample->analysis_type_id));

                $captured = $sample->captured_results;
                foreach ($captured as $c) {
                    $new_captured = $c->replicate()->fill([
                        'sample_header_id' => $new_batch->id,
                        'sample_detail_id' => $new_sample->id,
                        'sample_detail_code' => $new_sample->sample_code,
                        'result' => '',
                        'user_id' => auth()->user()->id,
                        'remark' => '',
                    ]);
                    $new_captured->save();
                    $result = Result::where('captured_result_id', $c->id)->first();
                    if (!$result) {
                        $result = new Result();
                        $result->captured_result_id = $c->id;
                        $result->sample_detail_code = $c->sample_detail_code;
                        $result->sample_detail_id = $c->sample_detail_id;
                        $result->sample_header_id = $c->sample_header_id;
                        $result->analyte_id = $c->analyte_id;
                        $result->analyte_code = $c->analyte_code;
                        $result->analysis_type_id = $c->analysis_type_id;
                        $result->lab_section_id = $c->lab_section_id;
                        $result->parameters_order = $c->parameters_order ?? 0;
                        $result->remark_is_manual = $c->remark_is_manual ?? false;
                        $result->has_no_result_capture = $c->has_no_result_capture ?? false;
                        $result->save();
                    }
                    $new_result = $result->replicate()->fill([
                        'captured_result_id' => $new_captured->id,
                        'sample_header_id' => $new_batch->id,
                        'sample_detail_id' => $new_sample->id,
                        'sample_detail_code' => $new_sample->sample_code,
                        'result' => '',
                        'remarks' => '',
                    ]);
                    $new_result->save();
                }
            }
            SampleAnalysisTypeRelation::insert($relation_analysis);
            $analysis_types_id = SampleAnalysisTypeRelation::where('batch_id', $new_batch->id)->pluck('analysis_type_id')->toArray();
            $analysis_max_report_time = AnalysisType::whereIn('id', $analysis_types_id)->max('reporting_time');
            $analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $analysis_types_id)->max('reporting_time');
            $maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $new_batch->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $new_batch->id;
            $targetDate->date = \Carbon\Carbon::parse($new_batch->receipt_date)->addDays($maxReportingTime);
            $targetDate->save();

            $custodyDetails = [
                'batch_id' => $new_batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $new_batch->status,
                    'tracking_stage' => $new_batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $new_batch->status,
                    'tracking_stage' => $new_batch->sample_tracking_stage,
                ],
            ];
            $this->updateChainofCustody($custodyDetails);
        }

        return redirect()->back()->with('success', 'Cloned batch created successfully');
    }

    public function getStandardValuesDataAjax()
    {
        $values = StandardValue::where('status', 1)->get();

        return response()->json($values);
    }

    public function updateStandardAnalyteLimit(Request $request)
    {
        $standard = Standards::where('code', $request->standard_id)->first();
        $standard_analyte = StandardAnalytes::where('standard_id', $standard->id)->where('analyte_id', $request->analyte_id)->first() ?? new StandardAnalytes();
        $standard_analyte->low = $request->low;
        $standard_analyte->high = $request->high;
        $standard_analyte->standard_value_id = $request->standard_valuetype;
        $standard_analyte->value_type = $request->limit_measure;
        $standard_analyte->standard_is_value = $request->value;
        $standard_analyte->standard_value_type = $request->standard_value_type == 1 ? 'is_range' : 'is_standard_value';
        $standard_analyte->analyte_id = $request->analyte_id;
        $standard_analyte->standard_id = $standard->id;
        $standard_analyte->is_active = 1;
        $standard_analyte->save();
        $value = 'NS';

        $value = $request->standard_value_type == 1 ? $request->low . ' - ' . $request->high : $value;
        $value = $request->standard_value_type == 2 && $request->limit_measure == '' ? StandardValue::find($request->standard_valuetype)->code : $value;
        $value = $request->standard_value_type == 2 && $request->limit_measure != '' ? $request->value . ' ' . $request->limit_measure : $value;
        $format_value = $request->standard_value_type == 2 && $request->limit_measure != '' ? $request->value : $value;

        return response()->json(['format_value' => $value, 'value' => $format_value]);
    }

    public function saveSampleAnalysisDate(Request $request)
    {
        $sample = SampleDetails::where('sample_code', $request->sample_id)->first();
        $analysis_date = SampleAnalysisDates::where('sample_header_id', $request->batch_id)->where('sample_detail_id', $sample->id)->first() ?? new SampleAnalysisDates();
        $decoded = $analysis_date->analysis_dates != '' ? json_decode($analysis_date->analysis_dates, true) : [];
        $prev_dates = [];

        if (is_array($decoded)) {
            foreach ($decoded as $sectionId => $sectionValue) {
                if (is_array($sectionValue)) {
                    $prev_dates[$sectionId] = [
                        'start_date' => $sectionValue['start_date'] ?? $sectionValue['start'] ?? null,
                        'end_date' => $sectionValue['end_date'] ?? $sectionValue['end'] ?? null,
                    ];
                    continue;
                }

                $prev_dates[$sectionId] = [
                    'start_date' => $sectionValue,
                    'end_date' => null,
                ];
            }
        }

        $sectionId = (string) $request->lab_section_id;
        $prev_dates[$sectionId] = [
            'start_date' => $request->start_analysis_date,
            'end_date' => $prev_dates[$sectionId]['end_date'] ?? null,
        ];

        $start_date = '';
        foreach ($prev_dates as $val) {
            $sectionStartDate = is_array($val) ? ($val['start_date'] ?? '') : $val;
            if ($sectionStartDate == '') {
                continue;
            }

            if ($start_date == '') {
                $start_date = $sectionStartDate;
            } else {
                $start_date = $sectionStartDate > $start_date ? $start_date : $sectionStartDate;
            }
        }

        $analysis_date->start_analysis_date = $start_date == '' ? $request->start_analysis_date : $start_date;
        $analysis_date->sample_header_id = $request->batch_id;
        $analysis_date->sample_detail_id = $sample->id;
        // return response()->json($prev_dates);

        $analysis_date->analysis_dates = json_encode($prev_dates);
        $analysis_date->save();

        return response()->json('success');
    }

    public function getSampleIntelabLogsApprovalStatus(Request $request)
    {
        $sample_id = $request->sample_id;
        $sample = SampleDetails::where('sample_code', $sample_id)->first();
        $approval = InterLabLog::where('sample_id', $sample->id)->where('status', 0)->first();

        return response()->json(['approval_status' => isset($approval->id) ? 1 : 0, 'sample' => $sample]);
    }

    public function getSampleResultCapturedNot(Request $request)
    {
        $sample_id = $request->sample_id;
        $sample = SampleDetails::where('sample_code', $sample_id)->first();
        $captured = CapturedResult::where('sample_detail_id', $sample->id)->WhereNotNull('result')->join('analysis_elements as ae', function ($join) {
            $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
            $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
        })->where('ae.active', 1)->where('ae.active', 1)->get()->count();
        $captured_not = CapturedResult::where('sample_detail_id', $sample->id)->WhereNull('result')->join('analysis_elements as ae', function ($join) {
            $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
            $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
        })->where('ae.active', 1)->where('ae.active', 1)->get()->count();

        return response()->json(['captured' => $captured, 'not_captured' => $captured_not]);
    }
    public function addBatchInvoice(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        $batch->invoice_number = $request->invoice_number;
        $batch->invoice_amount = $request->invoice_amount;
        $batch->save();

        return redirect()->back()->with('success', 'Invoice Details added successfully');
    }
    public function markBatchesFinished(Request $request)
    {
        $batches = SampleHeader::whereIn('batch_code', $request->batch_code)->update(['status' => 'Finished Sample']);
        return redirect()->back()->with('success', 'Samples moved to finished samples successfully');
    }
    public function returnFromFinished(Request $request)
    {
        SampleHeader::whereIn('batch_code', $request->batch_code)->update(['status' => 'Sample Approval']);
        return redirect()->back()->with('success', 'Samples moved to Sample Approval successfully');
    }

    public function sendBatchesScheduleAnalysis(Request $request)
    {
        $batch_customers = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('crm_customer_id')->toArray();
        if (sizeof(array_unique($batch_customers)) > 1) {
            return redirect()->back()->with('error', 'Ensure that the selected batches are for the same client');
        }
        $customer = CrmCustomer::find(array_unique($batch_customers)[0]);
        // return response()->json(array_unique($batch_customers));
        $batch_ids = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('id')->toArray();
        $sampletypesIds = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('sample_type_id')->toArray();
        $sampleTypeNames = implode(', ', SampleType::whereIn('id', $sampletypesIds)->pluck('name')->toArray());
        $samples = SampleDetails::whereIn('sample_header_id', $batch_ids)->get();
        $sampleTrs = "";
        foreach ($samples as $sample) {
            $target_date = date('Y-m-d', strtotime($sample->targetDateRelation()));
            $sampleTrs .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample->sample_code) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars(implode(',', $sample->analyteNames())) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>
            </tr>';
        }


        if ($customer->email != '') {
            foreach ($batch_ids as $b_ids) {
                $header = SampleHeader::find($b_ids);
                $header->schedule_sent = 1;
                $header->schedule_analysis_sent = date('Y-m-d');
                $header->schedule_analysis_sender = auth()->user()->id;
                $header->save();
                $schedule_str = 'Schedule Of Analysis Sendoff';
                $schedueDate = \App\SampleDate::where('sample_header_id', $header->id)->where('name', $schedule_str)->first() ?? new
                    \App\SampleDate();
                $schedueDate->name = $schedule_str;
                $schedueDate->sample_header_id = $header->id;
                $schedueDate->date = date('Y-m-d');
                $schedueDate->save();
            }
            $body = '
			<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                <p style="font-size: 12px;">
                    Dear ' . $customer->name . ', <br><br>
                    I hope this message finds you well. <br>
                    We are pleased to confirm that your samples <b>' . strtoupper($sampleTypeNames) . '</b> have been successfully received and assigned following Ref IDs: 
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Test(s) Required</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                    </tr>
                    ' . $sampleTrs . '
                </table>

                <p style="font-size: 12px; margin-top: 15px;">
                    <br>
                    Once analysis is completed, you will receive an update regarding your test results.<br>
                    For any inquiries, please contact us on <b>Fivet@co.ke, /+12345678 </b>. <br>
                    Thank you for the opportunity to serve you. <br><br>
                    Kind regards, <br>
                    FIVET COMPANY LIMITED
                </p>
            </div>';
            notify_user($body, $customer->email, '[FIVET LIMS] Confirmation of Sample Receipt and Schedule of Analysis', false, true, ['dannyagah13@gmanil.com.com']);

            return redirect()->back()->with('success', 'Schedule of analysis sent successfully!');
        } else {
            return redirect()->back()->with('error', 'Kindly set an email to the specified customer');
        }
    }

    public function disposalReportIndex(Request $request)
    {
        $data = [];
        $filter = [];
        if (isset($request->has_filter)) {
            $filter = [
                'date_from' => $request->date_from,
                "date_to" => $request->date_to,
                "customer_id" => $request->customer_id,
                "sample_type_id" => $request->sample_type_id,
                "store_id" => $request->store_id,
            ];
            $data = SamplesCategory::query();
            if (isset($request->date_from) && $request->date_from != '') {
                $data = $data->where('disposal_date', '>=', $request->date_from);
            }
            if (isset($request->date_to) && $request->date_to != '') {
                $data = $data->where('disposal_date', '<=', $request->date_to);
            }
            if (isset($request->customer_id) && $request->customer_id != '' && $request->customer_id != 'All') {
                $data = $data->where('crm_customer_id', $request->customer_id);
            }
            if (isset($request->sample_type_id) && $request->sample_type_id != '' && $request->sample_type_id != 'All') {
                $data = $data->where('sample_type_id', $request->sample_type_id);
            }
            if (isset($request->store_id) && $request->store_id != '' && $request->store_id != 'All') {
                $data = $data->where('store_id', $request->store_id);
            }
            $data = $data->orderBy('disposal_date', 'DESC')->get();
        }
        $sampletypes = SampleType::where('active', 1)->get();
        $customers = CRMCustomer::where('active', 1)->get();
        $stores = getStorageByType('lab_store');
        return view('layouts.lab.reports.disposal', compact('sampletypes', 'customers', 'stores', 'filter', 'data'));
    }
    public function tatReportIndex(Request $request)
    {
        $data = [];
        $filter = [];
        if (isset($request->has_filter)) {
            $filter = [
                "date_from" => $request->date_from,
                "date_to" => $request->date_to,
                "user_id" => $request->user_id,
                'sample_type_id' => $request->sample_type_id,
                'analysis_type_id' => $request->analysis_type_id,

            ];
            $data = TatCapturedView::query();
            if (isset($request->date_from) && $request->date_from != '') {
                $data = $data->where('receipt_date', '>=', $request->date_from);
            }
            if (isset($request->date_to) && $request->date_to != '') {
                $data = $data->where('receipt_date', '<=', $request->date_to);
            }
            if (isset($request->user_id) && $request->user_id != '' && $request->user_id != 'All') {
                $data = $data->where('analyst_id', $request->user_id);
            }
            if (isset($request->sample_type_id) && $request->sample_type_id != '' && $request->sample_type_id != 'All') {
                $data = $data->where('sample_type_id', $request->sample_type_id);
            }
            if (isset($request->analysis_type_id) && $request->analysis_type_id != '' && $request->analysis_type_id != 'All') {
                $data = $data->where('analysis_type_id', $request->analysis_type_id);
            }
            if (isset($request->analyte_id) && $request->analyte_id != '' && $request->analyte_id != 'All') {
                $data = $data->where('analyte_id', $request->analyte_id);
            }

            $data = $data->where('is_complete', 1)->orderBy('created_at', 'ASC')->get();
        }
        $sampletypes = SampleType::where('active', 1)->get();
        $analysts = getActiveUsersByRole('Laboratory Analyst');
        return view('layouts.lab.reports.tat-report', compact('sampletypes', 'analysts', 'filter', 'data'));
    }
    public function getAnalysisTypeAjax($sampletype)
    {
        $analysis = AnalysisType::where('sample_type_id', $sampletype)->where('active', 1)->get();
        return response()->json($analysis);
    }
    public function getAnalyteAjax($analysistype)
    {
        $analytes = Analyte::where('analysis_type_id', $analysistype)->where('active', 1)->get();
        return response()->json($analytes);
    }

    public function getTatDelayedSample()
    {
        $date = \Carbon\Carbon::now();
        $date->addDays(1);
        $headers = SampleDate::join('sample_headers as s', 's.id', '=', 'sample_dates.sample_header_id')->where('sample_dates.date', '<=', $date)->whereIn('s.status', ["Samples En-Route", "Samples Reception", "Samples Request Review", "Samples In Lab", "Sample Verification", "Sample Approval"])->where('name', 'Target Date')->selectRaw('s.*,sample_dates.date as tat_date,date(sample_dates.date) < date(now()) as is_late,date(sample_dates.date) = date(now()) as is_today')->orderBy('tat_date', 'DESC')->get();
        return response()->json($headers);
    }
    public function awaitingApprovalSamples($status)
    {
        // return response()->json($status);
        $headers = BatchLabSectionApprover::join('sample_headers as s', 's.id', '=', 'batch_labsection_approval.batch_id')->join('users as u', 'u.id', '=', 'batch_labsection_approval.user_id')->where('batch_labsection_approval.status', 0)->where('batch_labsection_approval.batch_status', $status)->where('s.isactive', 1)->selectRaw('s.*,u.name as batch_approver')->get();
        return response()->json($headers);
    }
    public function updateTatCaptured()
    {
        $batches = SampleHeader::whereIn('status', ["Sample Verification", "Sample Approval", "Reports In Payment", "Reports for Collection", "Finished Sample"])->pluck('id')->toArray();
        TatCaptured::whereIn('sample_header_id', $batches)->update(['is_complete' => 1]);
        return response()->json('success');
    }

    public function getTatBatchApprovalCounterAjax($status)
    {
        $approval = $headers = BatchLabSectionApprover::join('sample_headers as s', 's.id', '=', 'batch_labsection_approval.batch_id')->join('users as u', 'u.id', '=', 'batch_labsection_approval.user_id')->where('batch_labsection_approval.status', 0)->where('batch_labsection_approval.batch_status', $status)->selectRaw('s.*,u.name as batch_approver')->get()->count();

        $date = \Carbon\Carbon::now();
        $date->addDays(1);
        $atat_count = SampleDate::join('sample_headers as s', 's.id', '=', 'sample_dates.sample_header_id')->where('sample_dates.date', '<=', $date)->whereIn('s.status', ["Samples En-Route", "Samples Reception", "Samples Request Review", "Samples In Lab", "Sample Verification", "Sample Approval"])->where('name', 'Target Date')->selectRaw('s.*,sample_dates.date as tat_date')->get()->count();
        return response()->json(['tat_count' => $atat_count, 'approval_count' => $approval]);
    }

    public function deleteSalesOrder($id)
    {
        InvoiceDetails::where('invoice_id', $id)->delete();
        SampleHeader::where('invoice_id', $id)->update(['invoice_id' => 0]);
        Invoice::find($id)->delete();
        return response()->json(['status' => "success", "message" => "Sales order deleted successfully!"]);
    }
    public function matchCrmCurrency()
    {
        $customers = CRMCustomer::where('zoho_id', '>', 0)->whereNull('currency_id')->get();
        foreach ($customers as $customer) {
            $zoho = ZohoCustomers::find($customer->zoho_id);
            $z_currency = ModulePreConfigs::where('zoho_id', $zoho->currency_id)->first();
            if (isset($z_currency->id)) {
                $customer->currency_id = $z_currency->id;
                $customer->save();
            }
        }
        return response()->json('success');
    }

    public function updateInvoiceDetails(Request $request)
    {
        foreach ($request->details as $detail) {

            $total = $detail['final_price'] * $detail['quantity'];

            if ($detail['invoice_detail_id'] > 0) {

                InvoiceDetails::find($detail['invoice_detail_id'])->update(['quantity' => $detail['quantity'], 'selling_price' => $detail['unit_price'], "final_unit_price" => $detail['final_price'], 'total' => $total, 'discount' => $detail['discount'], 'discount_type' => $detail['discount_type'], 'analysis_title' => $detail['title']]);
                $invoice_detail = InvoiceDetails::with('invoice')->find($detail['invoice_detail_id']);
            } else {
                $invoice = Invoice::find($detail['invoice_id']);
                $item = InventorySubCategories::where('inventory_sub_categories.id', $detail['item_id'])->leftjoin('zoho_items_pricelist', function ($join) use ($invoice) {
                    $join->on('inventory_sub_categories.id', '=', 'zoho_items_pricelist.item_id');
                    $join->on('zoho_items_pricelist.customer_id', '=', DB::raw($invoice->customer_id));
                })->selectRaw('inventory_sub_categories.name as zoho_name,inventory_sub_categories.unit_price,inventory_sub_categories.zoho_item_code,inventory_sub_categories.id as zoho_analysis_type,zoho_items_pricelist.unit_price as unit_price_rate')->first();

                $invoice_detail = new InvoiceDetails();
                $invoice_detail->invoice_id = $detail['invoice_id'];
                $invoice_detail->analysis_type = $detail['item_id'];
                $invoice_detail->quantity = $detail['quantity'];
                $invoice_detail->selling_price = $detail['unit_price'];
                $invoice_detail->final_unit_price = $detail['final_price'];
                $invoice_detail->discount = $detail['discount'];
                $invoice_detail->discount_type = $detail['discount_type'];
                $invoice_detail->analysis_title = $detail['title'];
                $invoice_detail->total = $total;
                $invoice_detail->crm_customer_id = $invoice->customer_id;
                $invoice_detail->analysis_type_name = $item->zoho_name;
                $invoice_detail->sample_detail_id = 0;
                $invoice_detail->sample_header_id = 0;
                $invoice_detail->cost_price = 0;
                $invoice_detail->zoho_item_id = $item->zoho_item_code;
                $invoice_detail->zoho_item_name = $item->zoho_name;
                $invoice_detail->save();
            }


            $check_pl = ZohoPricelist::where('customer_id', $invoice_detail->invoice->customer_id)->where('item_id', $invoice_detail->analysis_type)->first();
            if (isset($check_pl->id)) {
                $check_pl->unit_price = $detail['unit_price'];
                $check_pl->save();
            } else {
                ZohoPricelist::create(["customer_id" => $invoice_detail->invoice->customer_id, "item_id" => $invoice_detail->analysis_type, 'unit_price' => $detail['unit_price']]);
            }
        }
        return response()->json('done');
    }

    public function getInvoiceItemData($invoice_id, $item_id)
    {
        $invoice = Invoice::find($invoice_id);
        $item = InventorySubCategories::where('inventory_sub_categories.id', $item_id)->leftjoin('zoho_items_pricelist', function ($join) use ($invoice) {
            $join->on('inventory_sub_categories.id', '=', 'zoho_items_pricelist.item_id');
            $join->on('zoho_items_pricelist.customer_id', '=', DB::raw($invoice->customer_id));
        })->selectRaw('inventory_sub_categories.name as zoho_name,inventory_sub_categories.unit_price,inventory_sub_categories.zoho_item_code,inventory_sub_categories.id as zoho_analysis_type,zoho_items_pricelist.unit_price as unit_price_rate')->first();

        return response()->json($item);
    }
    public function validateClientBatches(Request $request)
    {
        $clients = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('crm_customer_id')->toArray();
        $res = sizeof(array_unique($clients)) > 1 ? ['error' => "Ensure the batches are from 1 client before proceeding"] : ["client_id" => $clients[0]];
        return response()->json($res);
    }
    public function sendScheduleAjax(Request $request)
    {
        $check_customer_email = SampleHeader::whereIn('batch_code', $request->batch_code)->whereNull('schedule_customer_email')->first();
        if ($check_customer_email) {
            return response()->json(['error' => 'Kindly ensure the selected batches have customer email field']);
        }
        $sampleTrs = "";
        $customer_email = '';
        foreach ($request->batch_code as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();
            $customer_email = $batch->schedule_customer_email;
            if ($batch->schedule_analysis_sent == '') {
                $targetDate = SampleDate::where('sample_header_id', $batch->id)->where('name', 'Target Date')->first();
                $samples = SampleDetails::where('sample_header_id', $batch->id)->pluck('sample_code')->toArray();
                foreach ($samples as $sample) {
                    foreach ($samples as $sample) {
                        $target_date = $targetDate->date;
                        $sampleTrs .= '
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample) . '</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>
                    </tr>';
                    }
                }
                $scheduleDateStr = 'Schedule of Analysis Sendoff Date';
                $scheduleDate = SampleDate::where('sample_header_id', $batch->id)->where('name', $scheduleDateStr)->first() ?? new SampleDate();
                $scheduleDate->name = $scheduleDateStr;
                $scheduleDate->sample_header_id = $batch->id;
                $scheduleDate->date = date('Y-m-d');
                $scheduleDate->save();
            }
        }
        $body = '
            <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                <p style="font-size: 16px;">
                    Dear Esteemed Client, <br><br>
                    We acknowledge receipt of your sample(s) submitted to our laboratory. The sample(s) have been forwarded to our laboratory, and analysis is scheduled to start anytime from now.<br>Sample Information : 
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                    </tr>
                    ' . $sampleTrs . '
                </table>

                <p style="font-size: 16px; margin-top: 15px;">
                    <br>
                    We will keep you updated on the progress report(s).<br>
                    Thank you for the opportunity to serve you.
                </p>
            </div>
            ';
        // return response()->json(['email'=>$customer_email,'body'=>$body,'error'=>'My testing']);
        if ($sampleTrs != "" && $customer_email != '') {
            notify_user($body, $customer_email, '[FIVET LIMS] Schedule Of Analysis ' . implode(',', $request->batch_code), false, true, ['donotreply@FIVET.com']);
            SampleHeader::whereIn('batch_code', $request->batch_code)->update(["schedule_analysis_sent" => date('Y-m-d'), "schedule_analysis_sender" => auth()->user()->id]);
        }
        return response()->json(["customer_email" => $customer_email]);
    }
    public function moveToLabAjax(Request $request)
    {
        $status = 'Samples In Lab';
        foreach ($request->batch_code as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();
            // if ($batch->schedule_analysis_sent == '' && $batch->schedule_customer_email == '') {
            //     return redirect()->back()->with('error', 'Kindly set the customer email under batch information for batch ' . $code);
            // }
            $previousWorkflow = $batch->status;

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

            $this->updateChainofCustody($custodyDetails);
            $batch->status = $status;
            $batch->save();
        }
        return response()->json(['batch_codes' => $request->batch_code, 'process' => 'Complete']);
    }

    public function splitSchoolContacts()
    {
        $contacts = SchoolContacts::all();
        $address_counter = 0;
        foreach ($contacts as $contact) {
            $ad_arr = explode("\n", $contact->address);
            // return response()->json($ad_arr);
            $address_counter = sizeof($ad_arr) > $address_counter ? sizeof($ad_arr) : $address_counter;
            $a_counter = 0;

            $r_address = [];

            foreach ($ad_arr as $ad) {
                if ($ad != '') {
                    $address = explode('-', $ad);
                    $r_address[] = [
                        "address" => $address[0] ?? '',
                        "relation" => $address[1] ?? ''
                    ];
                }
            }
            $contact['refine_address'] = $r_address;
            // return response()->json($contact);
        }
        return view('layouts.lab.sample-workflow.index-other', compact('contacts'));
    }
    private function toScientificNotation($number)
    {
        $exponent = floor(log10(abs($number))); // Get the exponent (power of 10)
        $coefficient = $number / pow(10, $exponent); // Get the coefficient

        // Adjust if coefficient rounds to 10.0
        if (round($coefficient, 1) == 10.0) {
            $coefficient = 1.0;
            $exponent += 1;
        }


        return ['value' => $coefficient, 'to_power' => $exponent, 'scientific' => sprintf("%.1f × 10%s", $coefficient, $this->toSuperscript($exponent))];
    }
    private function toSuperscript($number)
    {
        $superscripts = [
            '0' => '⁰',
            '1' => '¹',
            '2' => '²',
            '3' => '³',
            '4' => '⁴',
            '5' => '⁵',
            '6' => '⁶',
            '7' => '⁷',
            '8' => '⁸',
            '9' => '⁹',
            '-' => '⁻' // Use HTML entity for superscript minus
        ];

        $strNumber = strval($number);
        $superscriptNumber = '';

        foreach (str_split($strNumber) as $digit) {
            $superscriptNumber .= $superscripts[$digit] ?? $digit;
        }

        return $superscriptNumber;
    }
    public function processRawResultsLab(Request $request)
    {
        app(ProcessedResultSyncService::class)->syncBatch((string) $request->batch_id);

        return redirect()->back()->with('success', 'Results processed successfully!');
    }

    public function markQCBatchComplete(Request $request, QcBatchCompletionService $qcBatchCompletionService)
    {
        $batchId = (string) $request->batch_id;
        $batch = SampleHeader::query()->findOrFail($batchId);
        $previousStatus = $batch->status;

        $qcBatchCompletionService->completeBatch($batchId);

        return redirect()->route('sample-workflow', ['status' => $previousStatus])
            ->with('success', 'QC batch completed and analytics processed.');
    }

    /**
     * Get available submission forms for the modal
     */
    public function getAvailableSubmissionForms()
    {
        try {
            $contextRoute = request()->get('context_route');
            $contextRouteCandidates = $this->expandSubmissionFormContextRouteCandidates($contextRoute);
            $hasTargetPagesColumn = Schema::hasColumn('submission_forms', 'target_pages');

            $forms = \App\Models\SubmissionForm::with(['creator', 'sections'])
                ->where('is_published', true)
                ->where('is_active', true)
                ->when(!empty($contextRouteCandidates) && $hasTargetPagesColumn, function ($query) use ($contextRouteCandidates) {
                    $query->where(function ($placementQuery) use ($contextRouteCandidates) {
                        $placementQuery->whereNull('target_pages')
                            ->orWhereJsonLength('target_pages', 0);

                        foreach ($contextRouteCandidates as $candidate) {
                            $placementQuery->orWhereJsonContains('target_pages', $candidate);
                        }
                    });
                })
                ->withCount(['sections', 'instances'])
                ->orderBy('name')
                ->get()
                ->map(function ($form) {
                    return [
                        'id' => $form->id,
                        'name' => $form->name,
                        'description' => $form->description,
                        'sections_count' => $form->sections_count,
                        'instances_count' => $form->instances_count,
                        'created_at' => $form->created_at->format('M d, Y'),
                        'creator' => $form->creator->name ?? 'Unknown'
                    ];
                });

            return response()->json([
                'success' => true,
                'forms' => $forms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load submission forms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new form instance and redirect to fill page
     */
    public function createSubmissionFormInstance(Request $request)
    {
        try {
            $request->validate([
                'submission_form_id' => 'required|exists:submission_forms,id',
                'context_route' => 'nullable|string|max:255'
            ]);

            $submissionForm = \App\Models\SubmissionForm::findOrFail($request->submission_form_id);

            // Check if form is published and active
            if (!$submissionForm->is_published || !$submissionForm->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected form is not available for submission'
                ], 400);
            }

            $contextRoute = $request->get('context_route');
            $contextRouteCandidates = $this->expandSubmissionFormContextRouteCandidates($contextRoute);
            $targetPages = $submissionForm->target_pages ?? [];
            if (!empty($targetPages) && !empty($contextRouteCandidates) && empty(array_intersect($targetPages, $contextRouteCandidates))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected form is not configured for this page.'
                ], 403);
            }

            // Create form instance
            //$formNumber = \App\Services\FormNumberGenerator::generate($submissionForm);
            $instance = \App\Models\SubmissionFormInstance::create([
                'submission_form_id' => $submissionForm->id,
                'submitted_by' => auth()->id(),
                'status' => 'draft',
                'title' => 'New ' . $submissionForm->name . ' Submission',
                //'form_number' => $formNumber['format'],
                //'sequence_number' => $formNumber['sequence_no'],
                'form_number' => null,
                'sequence_number' => null,
                'due_date' => now()->addDays(7), // Default 7 days from now
                'priority' => 'normal'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form instance created successfully',
                'redirect_url' => route('submission-forms.instances.fill', [$submissionForm, $instance])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create form instance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Expand a context route into fallback candidates for target_pages matching.
     * Example: sample-workflow@status=Samples Request Review -> [exact, sample-workflow]
     *
     * @param mixed $contextRoute
     * @return array<int, string>
     */
    private function expandSubmissionFormContextRouteCandidates($contextRoute): array
    {
        if (!is_string($contextRoute) || trim($contextRoute) === '') {
            return [];
        }

        $normalized = trim($contextRoute);
        $candidates = [$normalized];

        if (strpos($normalized, '@status=') !== false) {
            $candidates[] = explode('@status=', $normalized, 2)[0];
        }

        return array_values(array_unique(array_filter($candidates, fn($candidate) => is_string($candidate) && $candidate !== '')));
    }


    public function getAvailableMethods()
    {
        try {
            $methods = AnalysisMethod::where('active', 1)->select('id', 'name', 'code')->get();
            return response()->json($methods);
        } catch (\Exception $e) {
            \Log::error('Error loading methods: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load methods: ' . $e->getMessage()], 500);
        }
    }

    public function saveCaptureResults(Request $request)
    {
        try {
            $captureResults = $request->input('capture_results', []);

            if (empty($captureResults)) {
                return response()->json(['error' => 'No results to save'], 400);
            }

            foreach ($captureResults as $resultData) {
                $capturedResult = CapturedResult::find($resultData['parameter_id']);

                if ($capturedResult) {
                    $capturedResult->result = $resultData['result'];
                    $capturedResult->result_reporting_symbol = $resultData['reporting_symbol'] ?? '';

                    // Validate result against standard and set remark
                    $remark = $this->validateResultAgainstStandard(
                        $resultData['result'],
                        $resultData['standard_limit'] ?? '',
                        $capturedResult->analyte_id
                    );

                    $capturedResult->remark = $remark;
                    $capturedResult->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Results saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save results: ' . $e->getMessage()], 500);
        }
    }

    private function validateResultAgainstStandard($result, $standardLimit, $analyteId)
    {
        // Implement the same validation logic as in the existing system
        // This is a simplified version - you may need to adjust based on your specific validation rules

        if (!is_numeric($result) || !is_numeric($standardLimit)) {
            return 'N/A';
        }

        $resultValue = floatval($result);
        $limitValue = floatval($standardLimit);

        // Simple validation - adjust based on your requirements
        if ($resultValue <= $limitValue) {
            return 'PASS';
        } else {
            return 'FAIL';
        }
    }

    /**
     * Update parameter settings via AJAX
     */
    public function updateParameterSettings(Request $request)
    {
        try {
            $resultId = $request->input('result_id');
            $sampleCode = $request->input('sample_code');
            $analyte = $request->input('analyte');

            $capturedResult = null;

            if ($resultId) {
                $capturedResult = CapturedResult::find($resultId);
            } else {
                $sample = SampleDetails::where('sample_code', $sampleCode)->first();
                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sample not found'], 404);
                }

                $analyteRecord = Analyte::where('code', $analyte)->first();
                if (!$analyteRecord) {
                    return response()->json(['success' => false, 'message' => 'Analyte not found'], 404);
                }

                $capturedResult = new CapturedResult();
                $capturedResult->sample_detail_id = $sample->id;
                $capturedResult->sample_header_id = $sample->sample_header_id;
                $capturedResult->analyte_id = $analyteRecord->id;
                $capturedResult->analysis_type_id = 1; // Default - adjust as needed
                $capturedResult->analyte_code = $analyte;
            }

            $actingUserId = auth()->id() ? (string) auth()->id() : null;
            app(CapturedResultCaptureService::class)->applyOnSave($capturedResult, [
                'reporting_unit_id' => $this->resolveReportingUnitIdForParameterSettings(
                    $request->input('reporting_unit')
                ),
                'method_id' => $this->resolveMethodIdForParameterSettings(
                    $request->input('method_id')
                ),
                'result_reporting_symbol' => $request->input('reporting_symbol', $capturedResult->result_reporting_symbol),
                'analyte_accredited' => $request->input('accredited', 0),
                'analyte_status_contracted' => $request->input('subcontracted', 0),
            ], $actingUserId);

            return response()->json([
                'success' => true,
                'message' => 'Parameter settings updated successfully',
                'result_id' => $capturedResult->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating parameter settings: ' . $e->getMessage()
            ], 500);
        }
    }

    private function normalizeNullableForeignKeyForParameterSettings(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return (string) $value;
    }

    private function resolveMethodIdForParameterSettings(mixed $value): ?string
    {
        $normalized = $this->normalizeNullableForeignKeyForParameterSettings($value);
        if ($normalized === null) {
            return null;
        }

        if (Str::isUuid($normalized)) {
            return $normalized;
        }

        return AnalysisMethod::query()
            ->where('name', $normalized)
            ->orWhere('code', $normalized)
            ->value('id');
    }

    private function resolveReportingUnitIdForParameterSettings(mixed $value): ?string
    {
        $normalized = $this->normalizeNullableForeignKeyForParameterSettings($value);
        if ($normalized === null) {
            return null;
        }

        if (Str::isUuid($normalized)) {
            return $normalized;
        }

        return ReportingUnit::query()->where('name', $normalized)->value('id');
    }

    /**
     * Update standard limit via AJAX
     */
    public function updateStandardLimit(Request $request)
    {
        try {
            $resultId = $request->input('result_id');
            $sampleCode = $request->input('sample_code');
            $analyte = $request->input('analyte');
            $valueType = $request->input('value_type');
            $standardValueId = $request->input('standard_value_id');
            $matrixOperator = $request->input('matrix_operator');
            $matrixValue = $request->input('matrix_value');
            $rangeLow = $request->input('range_low');
            $rangeHigh = $request->input('range_high');
            $legacyStandardValue = $request->input('standard_value');
            $legacyLimitType = $request->input('limit_type');

            // Find or create the captured result
            $capturedResult = null;

            if ($resultId) {
                $capturedResult = CapturedResult::find($resultId);
            } else {
                // Create new captured result if it doesn't exist
                $sample = SampleDetails::where('sample_code', $sampleCode)->first();
                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sample not found'], 404);
                }

                $analyteRecord = Analyte::where('code', $analyte)->first();
                if (!$analyteRecord) {
                    return response()->json(['success' => false, 'message' => 'Analyte not found'], 404);
                }

                $capturedResult = new CapturedResult();
                $capturedResult->sample_detail_id = $sample->id;
                $capturedResult->sample_header_id = $sample->sample_header_id;
                $capturedResult->analyte_id = $analyteRecord->id;
                $capturedResult->analysis_type_id = 1; // Default - adjust as needed
                $capturedResult->analyte_code = $analyte;
            }

            $limitDisplay = app(StandardLimitDisplayService::class);
            $mainValue = $limitDisplay->formatMainValueFromStructuredEditForm([
                'value_type' => $valueType,
                'range_low' => $rangeLow,
                'range_high' => $rangeHigh,
                'standard_value_id' => $standardValueId,
                'matrix_operator' => $matrixOperator,
                'matrix_value' => $matrixValue,
                'standard_value' => $legacyStandardValue,
                'limit_type' => $legacyLimitType,
            ]);

            $capturedResult->main_value = $mainValue;

            if ($capturedResult->result !== null && trim((string) $capturedResult->result) !== '') {
                $remark = app(ResultRemarkService::class)->calculateRemark(
                    $capturedResult,
                    (string) $capturedResult->result,
                    null,
                    $mainValue,
                    $capturedResult->result_reporting_symbol,
                );

                if (in_array($remark, ['PASS', 'FAIL'], true)) {
                    $capturedResult->remark = $remark;
                }
            }

            $capturedResult->save();

            return response()->json([
                'success' => true,
                'message' => 'Standard limit updated successfully',
                'result_id' => $capturedResult->id,
                'standard_limit' => $mainValue,
                'validation_result' => $capturedResult->remark,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating standard limit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update result value via AJAX
     */
    public function updateResult(Request $request)
    {
        try {
            $resultId = $request->input('result_id');
            $sampleCode = $request->input('sample_code');
            $analyte = $request->input('analyte');
            $result = $request->input('result');

            $capturedResult = null;

            if ($resultId) {
                $capturedResult = CapturedResult::find($resultId);
            } else {
                $sample = SampleDetails::where('sample_code', $sampleCode)->first();
                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sample not found'], 404);
                }

                $analyteRecord = Analyte::where('code', $analyte)->first();
                if (!$analyteRecord) {
                    return response()->json(['success' => false, 'message' => 'Analyte not found'], 404);
                }

                $capturedResult = new CapturedResult();
                $capturedResult->sample_detail_id = $sample->id;
                $capturedResult->sample_header_id = $sample->sample_header_id;
                $capturedResult->analyte_id = $analyteRecord->id;
                $capturedResult->analysis_type_id = 1; // Default - adjust as needed
                $capturedResult->analyte_code = $analyte;
            }

            $validationResult = null;
            $attributes = ['result' => $result];

            if ($result !== null && trim((string) $result) !== '' && $capturedResult->main_value) {
                $validationResult = app(ResultRemarkService::class)->calculateRemark(
                    $capturedResult,
                    (string) $result,
                    null,
                    (string) $capturedResult->main_value,
                    $capturedResult->result_reporting_symbol,
                );

                if (in_array($validationResult, ['PASS', 'FAIL'], true)) {
                    $attributes['remark'] = $validationResult;
                }
            }

            $actingUserId = auth()->id() ? (string) auth()->id() : null;
            app(CapturedResultCaptureService::class)->applyOnSave($capturedResult, $attributes, $actingUserId);

            return response()->json([
                'success' => true,
                'message' => 'Result updated successfully',
                'result_id' => $capturedResult->id,
                'validation_result' => $validationResult,
                'standard_limit' => $capturedResult->main_value ?: null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating result: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parameter settings for a result
     */
    public function getParameterSettings($resultId)
    {
        try {
            $capturedResult = CapturedResult::find($resultId);

            if (!$capturedResult) {
                return response()->json(['success' => false, 'message' => 'Result not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'reporting_unit' => $capturedResult->reporting_unit_id,
                    'method_id' => $capturedResult->method_id,
                    'reporting_symbol' => $capturedResult->result_reporting_symbol,
                    'analyst_id' => $capturedResult->operator_id,
                    'accredited' => $capturedResult->analyte_accredited,
                    'subcontracted' => $capturedResult->analyte_status_contracted
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading parameter settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get standard settings for a result
     */
    public function getStandardSettings($resultId)
    {
        try {
            $capturedResult = CapturedResult::find($resultId);

            if (!$capturedResult) {
                return response()->json(['success' => false, 'message' => 'Result not found'], 404);
            }

            $parsed = app(StandardLimitDisplayService::class)
                ->parseStructuredEditFormFromMainValue($capturedResult->main_value);

            return response()->json([
                'success' => true,
                'data' => $parsed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading standard settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update sample data for multiple samples
     */
    public function bulkUpdateSampleData(Request $request)
    {
        $request->validate([
            'sample_ids' => 'required|array|min:1',
            'sample_ids.*' => 'exists:sample_details,id',
            'batch_id' => 'required|exists:sample_headers,id',
            'main_standard' => 'nullable|exists:standards,id',
            'secondary_standard' => 'nullable|exists:standards,id',
            'store_id' => 'nullable|exists:inventory_stores,id',
            'store_slot_id' => 'nullable|exists:inventory_store_slots,id',
            'disposal_date' => 'nullable|date',
        ]);

        try {
            $sampleIds = $request->input('sample_ids');
            $batchId = $request->input('batch_id');

            // Build update data array - only include non-empty values
            $updateData = [];

            if ($request->filled('main_standard')) {
                $updateData['main_standard'] = $request->input('main_standard');
            }

            if ($request->filled('secondary_standard')) {
                $updateData['secondary_standard'] = $request->input('secondary_standard');
            }

            if ($request->filled('store_id')) {
                $updateData['store_id'] = $request->input('store_id');
            }

            if ($request->filled('store_slot_id')) {
                $updateData['store_slot_id'] = $request->input('store_slot_id');
            }

            if ($request->filled('disposal_date')) {
                $updateData['disposal_date'] = $request->input('disposal_date');
            }

            // Only proceed if there's data to update
            if (empty($updateData)) {
                return redirect()->back()->with('error', 'No fields were provided for update.');
            }

            // Update selected samples
            \App\SampleDetails::whereIn('id', $sampleIds)
                ->where('sample_header_id', $batchId)
                ->update($updateData);

            $updatedCount = count($sampleIds);

            // Recalculate Batch Target Date
            $sampleHeader = \App\SampleHeader::find($batchId);
            if ($sampleHeader) {
                // Get all analysis types for this batch
                $batchAnalysisTypeIds = \App\SampleAnalysisTypeRelation::where('batch_id', $batchId)
                    ->pluck('analysis_type_id')
                    ->unique()
                    ->toArray();

                if (!empty($batchAnalysisTypeIds)) {
                    // Calculate max reporting time
                    $analysisMaxReportingTime = \App\AnalysisType::whereIn('id', $batchAnalysisTypeIds)->max('reporting_time') ?? 0;
                    $elementsMaxReportingTime = \App\AnalysisElements::whereIn('analysis_type_id', $batchAnalysisTypeIds)->max('reporting_time') ?? 0;

                    $maxReportingTime = max($analysisMaxReportingTime, $elementsMaxReportingTime);

                    // Update Target Date
                    $targetDateStr = 'Target Date';
                    $targetDate = \App\SampleDate::where('sample_header_id', $batchId)
                        ->where('name', $targetDateStr)
                        ->first() ?? new \App\SampleDate();

                    $targetDate->name = $targetDateStr;
                    $targetDate->sample_header_id = $batchId;
                    // Use receipt_date or fallback to now
                    $baseDate = $sampleHeader->receipt_date ? \Carbon\Carbon::parse($sampleHeader->receipt_date) : now();
                    $targetDate->date = $baseDate->addDays($maxReportingTime);
                    $targetDate->save();

                    \Log::info('Recalculated Target Date for batch after bulk update', [
                        'batch_id' => $batchId,
                        'max_reporting_time' => $maxReportingTime,
                        'new_target_date' => $targetDate->date
                    ]);
                }
            }

            $updatedFields = implode(', ', array_keys($updateData));

            return redirect()->back()->with('success', "Successfully updated {$updatedCount} sample(s). Updated fields: {$updatedFields}");
        } catch (\Exception $e) {
            \Log::error('Bulk update sample data error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Error updating samples: ' . $e->getMessage());
        }
    }

    public function store_attachment_type(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        if (SystemConfiguration::where('key', 'attachment_type')->where('value', $request->value)->exists()) {
            return response()->json(['success' => false, 'message' => 'Attachment Type already exists']);
        }

        $configurationTypeId = app(\App\Services\System\AttachmentTypeResolver::class)
            ->resolveAttachmentConfigurationTypeId();
        if ($configurationTypeId === null) {
            return response()->json(['success' => false, 'message' => 'Attachment Types configuration is not set up in System Configuration.']);
        }

        $config = new SystemConfiguration();
        $config->key = 'attachment_type';
        $config->value = $request->value;
        $config->configuration_type_id = $configurationTypeId;
        $config->save();

        return response()->json([
            'success' => true,
            'id' => $config->id,
            'value' => $config->value
        ]);
    }

    /**
     * Show PDF annotation page
     */
    public function showAnnotationPage($id)
    {
        $attachment = BatchAttachment::findOrFail($id);

        // Verify attachment is a PDF
        if (!str_ends_with(strtolower($attachment->attachment_url), '.pdf')) {
            return redirect()->back()->with('error', 'Only PDF files can be annotated.');
        }

        $batch = \App\SampleHeader::find($attachment->batch_id);

        $user = auth()->user();
        $signatureUrl = null;
        if ($user && $user->electronic_sig) {
            $signatureUrl = $user->electronic_sig;
            if (!filter_var($signatureUrl, FILTER_VALIDATE_URL)) {
                $signatureUrl = asset($signatureUrl);
            }
        }

        return view('layouts.lab.sample-workflow.pdf-annotate', [
            'attachment' => $attachment,
            'batch' => $batch,
            'user' => $user,
            'signatureUrl' => $signatureUrl,
        ]);
    }

    /**
     * Get annotations for a PDF attachment
     */
    public function getAnnotations($id)
    {
        try {
            $annotations = \App\Models\BatchAttachmentAnnotation::where('batch_attachment_id', $id)
                ->orderBy('page_number')
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'annotations' => $annotations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load annotations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete selected annotations
     */
    public function deleteAnnotations(Request $request, $id)
    {
        try {
            $request->validate([
                'annotation_ids' => 'required|array',
                'annotation_ids.*' => 'required|integer|exists:batch_attachment_annotations,id'
            ]);

            // Verify attachment exists
            $attachment = BatchAttachment::findOrFail($id);

            // Delete annotations
            $deletedCount = \App\Models\BatchAttachmentAnnotation::where('batch_attachment_id', $id)
                ->whereIn('id', $request->annotation_ids)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} annotation(s).",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Annotation deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete annotations: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Handle TinyMCE image uploads for annotations
     */
    public function uploadAnnotationImage(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $file = $request->file('file');

            // Validate file type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json(['error' => 'Invalid file type'], 400);
            }

            // Store the file
            $path = Storage::disk('public')->putFile('annotation-images', $file);

            // Return the full URL for TinyMCE
            $location = url(Storage::url($path));

            return response()->json(['location' => $location]);
        } catch (\Exception $e) {
            Log::error('TinyMCE upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Save annotated PDF
     */
    public function saveAnnotatedPdf(Request $request)
    {
        try {
            $request->validate([
                'attachment_id' => 'required|exists:batch_attachments,id',
                'annotations_data' => 'required|json',
                'viewer_scale' => 'nullable|numeric|min:0.25|max:3',
            ]);

            $attachment = BatchAttachment::findOrFail($request->attachment_id);
            $annotationsData = json_decode($request->annotations_data, true);

            // Resolve file path
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

            if (!file_exists($filePath)) {
                throw new \Exception("Source PDF file not found at: " . $filePath);
            }

            // Backup old PDF
            $backupPath = str_replace('.pdf', '_backup_' . time() . '.pdf', $filePath);
            @copy($filePath, $backupPath);

            // Create new PDF using TcpdfFpdi (preserves quality)
            $pdf = new TcpdfFpdi('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('FIVET LIMS');
            $pdf->SetAuthor(auth()->user()->name);
            $pdf->SetTitle($attachment->title . ' (Annotated)');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            // Import existing PDF as template
            $pageCount = $pdf->setSourceFile($filePath);

            // Must match the PDF.js viewer scale used when placing annotations.
            $scale = (float) $request->input('viewer_scale', 1.5);
            if ($scale < 0.25 || $scale > 3) {
                $scale = 1.5;
            }
            $ppi = $scale * 72;
            $pxToMm = 25.4 / $ppi;

            $needsHtmlText = collect($annotationsData ?? [])
                ->contains(fn ($ann) => ($ann['annotation_type'] ?? '') === 'text');

            // Smart-annotation font setup (only needed for leftover HTML text annotations).
            $smartFontFamily = 'times';
            if ($needsHtmlText) {
                $tnrFontFile = null;
                $tnrFontCandidates = [
                    public_path('fonts/TimesNewRoman.ttf'),
                    public_path('assets/fonts/TimesNewRoman.ttf'),
                    storage_path('app/fonts/TimesNewRoman.ttf'),
                    storage_path('app/public/fonts/TimesNewRoman.ttf'),
                ];

                foreach ($tnrFontCandidates as $candidate) {
                    if (is_string($candidate) && file_exists($candidate)) {
                        $tnrFontFile = $candidate;
                        break;
                    }
                }

                if (is_string($tnrFontFile) && $tnrFontFile !== '') {
                    try {
                        $loadedFontName = \TCPDF_FONTS::addTTFfont($tnrFontFile, 'TrueTypeUnicode', '', 96);
                        if (is_string($loadedFontName) && $loadedFontName !== '') {
                            $smartFontFamily = $loadedFontName;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Failed to embed Times New Roman TTF, falling back to TCPDF times', [
                            'tnrFontFile' => $tnrFontFile,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
                $pdf->useTemplate($templateId);

                // Filter annotations for this page
                $pageAnnotations = array_filter($annotationsData, function ($ann) use ($pageNo) {
                    return $ann['page_number'] == $pageNo;
                });

                // Draw border-only boxes last so they appear on top.
                $borderOnlyBoxes = [];

                foreach ($pageAnnotations as $ann) {
                    // Convert pixel coordinates to mm
                    $x = $ann['x_position'] * $pxToMm;
                    $y = $ann['y_position'] * $pxToMm;
                    $w = ($ann['width'] ?? 200) * $pxToMm;
                    $h = ($ann['height'] ?? 30) * $pxToMm;

                    if ($ann['annotation_type'] == 'text') {
                        $html = $ann['htmlContent'] ?? ($ann['content'] ?? '');

                        $fontSize = 8;
                        if (isset($ann['style_data']) && is_array($ann['style_data']) && isset($ann['style_data']['fontSize'])) {
                            $fontSize = (int) $ann['style_data']['fontSize'];
                        }
                        $pdf->SetFont($smartFontFamily, '', $fontSize);
                        $pdf->SetFillColor(255, 255, 255);

                        $border = 1;
                        if (isset($ann['style_data']) && is_array($ann['style_data']) && !empty($ann['style_data']['noBorder'])) {
                            $border = 0;
                        }

                        // Special case: draw a solid border box only (used for smart annotation block)
                        if (isset($ann['style_data']) && is_array($ann['style_data']) && !empty($ann['style_data']['borderOnly'])) {
                            $borderOnlyBoxes[] = compact('x', 'y', 'w', 'h');
                            continue;
                        }

                        $cellWidth = max(10, $w);
                        $minHeight = max(5, $h);

                        // Prefer the client-measured height to avoid a costly measure pass.
                        $measuredHeight = $minHeight;
                        if ($measuredHeight < 5.5) {
                            $pdf->startTransaction();
                            $pdf->writeHTMLCell($cellWidth, 0, $x, $y, $html, 0, 1, false, true, 'L', true);
                            $measuredHeight = max($minHeight, ($pdf->GetY() - $y) + 1.0);
                            $pdf->rollbackTransaction(true);
                        }

                        if ($border) {
                            $pdf->SetDrawColor(0, 0, 0);
                            $pdf->SetLineWidth(0.2);
                            $pdf->Rect($x, $y, $cellWidth, $measuredHeight, 'D');
                        }

                        $pdf->writeHTMLCell($cellWidth, $measuredHeight, $x, $y, $html, 0, 1, false, true, 'L', true);
                    } elseif ($ann['annotation_type'] == 'image') {
                        // Support both legacy 'content' key and newer 'imageData' key
                        $imgData = $ann['imageData'] ?? ($ann['content'] ?? null);
                        if (!$imgData) {
                            continue;
                        }
                        if (str_contains($imgData, 'base64,')) {
                            $imgParts = explode(',', $imgData);
                            $rawData = base64_decode($imgParts[1]);
                            $pdf->Image('@' . $rawData, $x, $y, $w, $h);
                        } else {
                            // If we received a URL/path (e.g. /storage/...png), resolve it to a local file path.
                            $imgPath = $imgData;

                            // Convert absolute URL to path component
                            if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                                $parsed = parse_url($imgPath);
                                $imgPath = $parsed['path'] ?? $imgPath;
                            }

                            // Try public path first (covers /storage symlink)
                            $localPath = public_path(ltrim($imgPath, '/'));
                            if (!file_exists($localPath) && str_starts_with($imgPath, '/storage/')) {
                                // Fallback to storage/app/public
                                $localPath = storage_path('app/public/' . ltrim(substr($imgPath, strlen('/storage/')), '/'));
                            }

                            if (file_exists($localPath)) {
                                try {
                                    $type = strtoupper((string) pathinfo($localPath, PATHINFO_EXTENSION));
                                    $pdf->Image($localPath, $x, $y, $w, $h, $type ?: null);
                                } catch (\Throwable $e) {
                                    Log::warning('PDF annotation image embed failed', [
                                        'imageData' => $imgData,
                                        'localPath' => $localPath,
                                        'type' => $type ?? null,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                                continue;
                            }

                            // Additional fallbacks: storage/app/... (non-public) and public_path with decoded URL
                            $decodedPath = urldecode($imgPath);
                            $altLocalPaths = [
                                // Livewire uploads store signatures on the public disk
                                str_starts_with($decodedPath, '/storage/personnel-signature/')
                                    ? storage_path('app/public/personnel-signature/' . ltrim(substr($decodedPath, strlen('/storage/personnel-signature/')), '/'))
                                    : null,
                                str_starts_with($decodedPath, '/storage/personnel-signature/')
                                    ? storage_path('app/personnel-signature/' . ltrim(substr($decodedPath, strlen('/storage/personnel-signature/')), '/'))
                                    : null,
                                str_starts_with($decodedPath, '/storage/personnel/')
                                    ? storage_path('app/public/personnel/' . ltrim(substr($decodedPath, strlen('/storage/personnel/')), '/'))
                                    : null,
                                str_starts_with($decodedPath, '/storage/personnel/')
                                    ? storage_path('app/personnel/' . ltrim(substr($decodedPath, strlen('/storage/personnel/')), '/'))
                                    : null,
                                storage_path('app/public/' . ltrim(str_replace('/storage/', '', $decodedPath), '/')),
                                storage_path('app/' . ltrim($decodedPath, '/')),
                                public_path(ltrim($decodedPath, '/')),
                            ];
                            $altLocalPaths = array_values(array_filter($altLocalPaths));

                            foreach ($altLocalPaths as $altPath) {
                                if (file_exists($altPath)) {
                                    try {
                                        $type = strtoupper((string) pathinfo($altPath, PATHINFO_EXTENSION));
                                        $pdf->Image($altPath, $x, $y, $w, $h, $type ?: null);
                                    } catch (\Throwable $e) {
                                        Log::warning('PDF annotation image embed failed', [
                                            'imageData' => $imgData,
                                            'localPath' => $altPath,
                                            'type' => $type ?? null,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                    continue 2;
                                }
                            }

                            // Final fallback: if it's a URL, fetch bytes and embed directly.
                            if (filter_var($imgData, FILTER_VALIDATE_URL)) {
                                try {
                                    $raw = @file_get_contents($imgData);
                                    if ($raw !== false && $raw !== '') {
                                        $pdf->Image('@' . $raw, $x, $y, $w, $h);
                                        continue;
                                    }
                                } catch (\Throwable $e) {
                                    // ignore and log below
                                }
                            }

                            Log::warning('PDF annotation image not found', [
                                'imageData' => $imgData,
                                'resolved_path' => $localPath,
                                'alt_paths' => $altLocalPaths ?? [],
                            ]);
                        }
                    }
                }

                if (!empty($borderOnlyBoxes)) {
                    // Faint thin blue border
                    $pdf->SetDrawColor(110, 125, 200);
                    $pdf->SetLineWidth(0.1);
                    foreach ($borderOnlyBoxes as $box) {
                        $pdf->Rect($box['x'], $box['y'], $box['w'], $box['h']);
                    }
                }
            }

            // Save new PDF (overwriting original)
            $pdf->Output($filePath, 'F');

            // Clear annotations from database. 
            // Since they are now "baked" into the PDF, we clear the DB records 
            // to prevent overlapping renderings in the annotator tool.
            BatchAttachmentAnnotation::where('batch_attachment_id', $attachment->id)->delete();

            return redirect()->back()->with('success', 'PDF annotated and saved successfully!');
        } catch (\Exception $e) {
            Log::error('PDF annotation save error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to save annotated PDF: ' . $e->getMessage());
        }
    }


    public function updateStagingDetail(Request $request, $id)
    {
        $staging = \App\Models\SampleDetailStaging::find($id);
        if (!$staging) {
            return redirect()->back()->with('error', 'Staging record not found.');
        }

        $data = $staging->data_json;
        $data['company_sub_unit_name'] = $request->company_sub_unit_name;
        $data['analysis_type_names'] = $request->analysis_type_names;
        $data['quantity'] = $request->quantity;

        $staging->data_json = $data;
        $staging->save();

        return redirect()->back()->with('success', 'Staging detail updated successfully.');
    }

    public function deleteStagingDetail($id)
    {
        $staging = \App\Models\SampleDetailStaging::find($id);
        if ($staging) {
            $staging->delete();
            return redirect()->back()->with('success', 'Staging detail deleted successfully.');
        }
        return redirect()->back()->with('error', 'Staging record not found.');
    }

    public function viewBatchQuotationPdf(\App\SampleHeader $batch)
    {
        return app(\App\Services\Sampleworkflow\BatchWorkflowDocumentAttachmentService::class)
            ->streamQuotationForBatch($batch);
    }

    public function viewAcceptancePdf($id)
    {
        $form = \App\Models\Sampleworkflow\AnalysisAcceptanceForm::findOrFail($id);
        $pdfService = app(\App\Services\Sampleworkflow\AcceptanceFormPdfService::class);
        $url = $pdfService->generatePdfAndStoreAttachment($form);
        return redirect($url);
    }

    public function viewReceiptNotificationPdf($id)
    {
        $form = \App\Models\Sampleworkflow\AnalysisAcceptanceForm::findOrFail($id);
        $pdfService = app(\App\Services\Sampleworkflow\SampleReceiptNotificationService::class);
        $batch = \App\SampleHeader::find($form->sample_header_id);
        $payload = is_array($form->receipt_notification_payload) ? $form->receipt_notification_payload : [];
        $url = $pdfService->generatePdfAndStoreAttachment($batch, $payload, auth()->id());
        return redirect($url);
    }

    public function viewCaseFilePdf($id)
    {
        $form = \App\Models\CaseFileReviewForm::findOrFail($id);
        $batch = \App\SampleHeader::findOrFail($form->batch_id);

        if (!$batch->hasDnaLab()) {
            abort(403, 'Case File Review Form is only available for DNA laboratories.');
        }

        $logoDataUri = $this->resolveLogoAsDataUri();

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        
        $pdf->loadView('batch.attachments.case-file-pdf', [
            'batch' => $batch,
            'form' => $form,
            'logo' => $logoDataUri,
        ]);
        
        // Save PDF to public storage
        $pdfFilename = 'case-file-review-form-batch-' . $batch->id . '.pdf';
        $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
        $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);
        
        \Illuminate\Support\Facades\Storage::disk('public')->put($pdfStoragePath, $pdf->output());

        // Create or update BatchAttachment
        $attachmentTypeId = app(\App\Services\System\AttachmentTypeResolver::class)
            ->resolveOrCreateAttachmentTypeId('Case File');
        $title = 'Case File Review Form (DNA/F/12)';

        $attachment = \App\BatchAttachment::where('batch_id', $batch->id)
            ->where('title', $title)
            ->orderByDesc('created_at')
            ->first();

        if (!$attachment) {
            $attachment = new \App\BatchAttachment();
            $attachment->batch_id = $batch->id;
            $attachment->uploaded_by = auth()->id() ?? 1;
            $attachment->title = $title;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
        }

        $attachment->attachment_type = $attachmentTypeId;
        $attachment->attachment_url = $pdfPublicUrl;
        $attachment->save();
        
        return $pdf->stream('case-file-' . $batch->batch_code . '.pdf');
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
}
