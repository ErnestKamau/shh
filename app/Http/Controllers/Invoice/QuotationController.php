<?php

namespace App\Http\Controllers\Invoice;

use App\CapturedResult;
use App\Lab;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\Models\System\SystemConfiguration;
use App\QuotationHeader;
use App\QuotationDetails;
use App\Result;
use App\SampleAnalysisStage;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use App\SampleHeader;
use App\TaxRegime;
use App\AnalysisType;
use App\AnalysisElements;
use App\Analyte;
use App\InvoicableItem;
use App\ZohoCustomers;
use Illuminate\Http\File;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreateEnquiryFromQuotationRequest;
use App\Http\Requests\Billing\PackageDefaultsRequest;
use Illuminate\Support\Facades\Schema;
use App\Models\CRM\SamplePoint;
use App\QuotationDetailAnalysisSplit;
use App\QuotationHeaderView;
use App\SampleType;
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Billing\QuotationPricingResolver;
use App\Services\Billing\QuotationLabSectionScope;
use App\Services\Billing\QuotationPrepImportService;
use App\Services\Billing\QuotationPricelistPopulateService;
use App\Services\Billing\QuotationReportService;
use App\Services\Billing\QuotationRevisionService;
use App\Exports\Billing\QuotationKpiExport;
use App\Services\Billing\QuotationStatisticsService;
use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use App\Services\Commercial\QuotationApprovalService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Commercial\EnquiryFromQuotationService;
use App\Services\Commercial\EnquiryQuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function __construct(
        private readonly QuotationReportService $quotationReportService,
        private readonly QuotationPricingResolver $quotationPricingResolver,
        private readonly QuotationLineTaxResolver $quotationLineTaxResolver,
        private readonly QuotationStatisticsService $quotationStatisticsService,
        private readonly QuotationRevisionService $quotationRevisionService,
        private readonly EnquiryFromQuotationService $enquiryFromQuotationService,
        private readonly EnquiryQuotationService $enquiryQuotationService,
        private readonly QuotationLabSectionScope $quotationLabSectionScope,
        private readonly AcceptanceFormPricingService $acceptanceFormPricingService,
        private readonly QuotationPrepImportService $quotationPrepImportService,
        private readonly QuotationPricelistPopulateService $quotationPricelistPopulateService,
    ) {
        $this->middleware('auth');
    }

    public function index($stage = false)
    {
        $stagePathFilters = [
            'Quote In Preparation',
            'Quote In Approval',
            'Quote Complete',
            'approval-settings',
        ];

        if ($stage !== false && $stage !== null && $stage !== '') {
            $query = in_array($stage, $stagePathFilters, true)
                ? ['stage_filter' => $stage, 'page_tab' => 'quotations']
                : ['page_tab' => 'quotations'];

            return redirect()->route('quotation-index', $query);
        }

        $customers = CRMCustomer::where('active', 1)->orderBy('name')->get();
        $labSections = $this->activeLabSectionsForQuotation();
        $drafts = QuotationHeaderView::with(['preparedBy'])
            ->where('is_draft', 1)
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();

        $metrics = $this->quotationStatisticsService->getOverviewMetrics();
        $kpiPeriod = $this->resolveKpiPeriodMetrics(request());

        return view('layouts.lab.invoice.quotation-index', compact(
            'customers',
            'drafts',
            'labSections',
            'metrics',
            'kpiPeriod',
        ));
    }

    public function customerLocationOptions(string $customerId): \Illuminate\Http\JsonResponse
    {
        $units = CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (CRMCompanyUnit $unit): array => [
                'id' => (string) $unit->id,
                'name' => (string) $unit->name,
            ])
            ->values()
            ->all();

        $samplePoints = SamplePoint::query()
            ->where('active', 1)
            ->where('crm_customer_id', $customerId)
            ->orderBy('name')
            ->get(['id', 'name', 'crm_company_unit_id'])
            ->map(static function (SamplePoint $point): array {
                return [
                    'id' => (string) $point->id,
                    'name' => (string) ($point->display_name ?? $point->name),
                    'crm_company_unit_id' => $point->crm_company_unit_id ? (string) $point->crm_company_unit_id : '',
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'units' => $units,
            'sample_points' => $samplePoints,
        ]);
    }

    public function createEnquiryFromQuotation(
        CreateEnquiryFromQuotationRequest $request,
    ): \Illuminate\Http\RedirectResponse {
        $validated = $request->validated();
        $quotation = QuotationHeader::query()->findOrFail($validated['quotation_id']);

        try {
            $enquiry = $this->enquiryFromQuotationService->create(
                $quotation,
                [
                    'number_of_samples' => isset($validated['number_of_samples'])
                        ? (int) $validated['number_of_samples']
                        : null,
                    'reference_number' => $validated['reference_number'] ?? null,
                    'date_expected' => $validated['date_expected'] ?? null,
                    'sample_description' => $validated['sample_description'] ?? null,
                    'enquiry_notes' => $validated['enquiry_notes'] ?? null,
                    'creation_intent' => $validated['creation_intent'] ?? EnquiryFromQuotationService::INTENT_PREPARE,
                    'client_po_number' => $validated['client_po_number'] ?? null,
                    'po_skipped' => (bool) ($validated['po_skipped'] ?? false),
                    'source_channel' => $validated['source_channel'] ?? CommercialEnquirySyncService::SOURCE_WALK_IN,
                ],
                $validated['creation_token'],
            );
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $reference = $enquiry->reference_number ?: $enquiry->formatted_number;

        return redirect()
            ->to($enquiry->staffViewUrl())
            ->with(
                'success',
                'Enquiry '.$reference.' was created from quotation '.$quotation->quote_number
                .'. Review the Test Request Form and quotation PDF under Documents, then complete any remaining fields.',
            );
    }

    public function exportQuotationKpi(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $rows = $this->quotationStatisticsService->getKpiDailyRows($startDate, $endDate);
        $filename = sprintf(
            'quotation-kpis-%s-to-%s.xlsx',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        return Excel::download(new QuotationKpiExport($rows), $filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveKpiPeriodMetrics(Request $request): array
    {
        $startDate = $request->filled('kpi_start_date')
            ? Carbon::parse($request->input('kpi_start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('kpi_end_date')
            ? Carbon::parse($request->input('kpi_end_date'))->endOfDay()
            : now()->endOfMonth();

        return $this->quotationStatisticsService->getKpiPeriodMetrics($startDate, $endDate);
    }

    public function filterQuotations(Request $request)
    {
        return redirect()->route('quotation-index');
    }

    /**
     * @return array{all: int, Quote In Preparation: int, Quote In Approval: int, Quote Complete: int}
     */
    private function quotationStageCounts(): array
    {
        return [
            'all' => QuotationHeaderView::query()->where('is_draft', 0)->count(),
            'Quote In Preparation' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote In Preparation')
                ->count(),
            'Quote In Approval' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote In Approval')
                ->count(),
            'Quote Complete' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote Complete')
                ->count(),
        ];
    }

    public function populateQuotationDetailSplit()
    {
        $headers = QuotationHeader::where('quotation_type', 'Analysis')->pluck('id')->toArray();
        $details = QuotationDetails::whereIn('quotation_header_id', $headers)->get();
        foreach ($details as $detail) {
            $idsTypes = array_values(array_filter(array_map('trim', explode(',', (string) $detail->part_no))));
            QuotationDetailAnalysisSplit::syncForDetail((string) $detail->id, $idsTypes);
        }
        return response()->json('done');
    }
    public function add_quotation_header(Request $request)
    {
        $isCreate = ! $request->filled('quote_id');

        $request->validate([
            'currency_id' => ['required', 'uuid', 'exists:currencies,id'],
            'lab_section_ids' => [$isCreate ? 'required' : 'nullable', 'array', $isCreate ? 'min:1' : 'min:0'],
            'lab_section_ids.*' => ['uuid', 'exists:sample_analysis_stages,id'],
        ]);

        if (isset($request->quote_id)) {
            $header = QuotationHeader::find($request->quote_id);
        } else {

            $header = new QuotationHeader();
        }

        $customer = CRMCustomer::where('id', $request->client)->first();
        if (! $customer) {
            return redirect()->back()->with('error', 'Selected client was not found.');
        }

        $header->crm_customer_id = $customer->id;
        if ($request->filled('client_contact')) {
            $header->crm_customer_contact_id = $request->client_contact;
        } elseif (! isset($request->quote_id)) {
            return redirect()->back()->with('error', 'Kindly add contacts to ' . $customer->name . ' customer.');
        }
        $header->quote_date = $request->quotation_date;
        $header->expiring_date = $request->expire_date;
        $header->prepared_by_id = auth()->user()->id;
        $header->currency_id = $request->currency_id;

        if (!isset($request->quote_id)) {
            $header->status = 'Quote In Reception';
        }
        $header->quotation_type = $request->quotation_type;
        $header->subject = $request->input('subject');
        $header->crm_company_unit_id = $request->input('crm_company_unit_id') ?: null;
        $header->sample_point_id = $request->input('sample_point_id') ?: null;
        $header->sampling_location = $request->input('sampling_location');
        $header->laboratory_ref = $request->input('laboratory_ref');
        $header->terms_override = $request->input('terms_override');

        $header->status = 'Quote In Preparation';
        // Independent by default — pricelist binds only when chosen in the commercial modal.
        // Still seed currency from an eligible customer list when the header has none.
        if (empty($header->currency_id)) {
            $eligible = $this->acceptanceFormPricingService->eligiblePricelistsForCustomer(
                $header->crm_customer_id ? (string) $header->crm_customer_id : null
            );
            $currencyDonor = $eligible[0] ?? null;
            if ($currencyDonor !== null && $currencyDonor->currency_id) {
                $header->currency_id = $currencyDonor->currency_id;
            }
        }
        $header->save();
        AmSpecQuotationNumberGenerator::assignIfMissing($header);
        if (! isset($request->quote_id)) {
            $header->is_draft = 0;
        }
        $header->save();
        $this->syncQuotationLabSections($header, $request->input('lab_section_ids', []));
        $this->quotationReportService->ensureHeaderMetadata($header);
        $this->quotationReportService->seedDefaultTermsOfSale($header);
        $this->quotationReportService->seedDefaultStructuredTerms($header);
        // return response()->json($header,200);

        return redirect()->route('add-qoute-details-view', ['id' => $header->id, 'stage' => $header->status]);
    }

    public function getCurrencyByCode(Request $request)
    {
        $currencyCode = $request->input('currency_code');

        if (!$currencyCode) {
            return response()->json(['error' => 'Currency code is required'], 400);
        }

        $currency = Currency::where('code', $currencyCode)->first();

        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        return response()->json([
            'id' => $currency->id,
            'code' => $currency->code,
            'description' => $currency->description
        ]);
    }
    public function view_quote_header_detail($id, $stage = false)
    {
        // return response()->json('test');
        $customers = CRMCustomer::all();
        $header = QuotationHeader::with(['preparedBy', 'customer', 'labSections'])->find($id);
        AmSpecQuotationNumberGenerator::assignIfMissing($header);
        $header = $header->fresh(['preparedBy', 'customer', 'labSections']);
        $sample_types = $this->quotationLabSectionScope->sampleTypesForQuotation($header);

        $pricelist = $this->quotationPricingResolver->resolvePricelist($header->crm_customer_id, $header);
        $pricelist_items = $pricelist
            ? $pricelist->items()->where('active', 1)->orderBy('level')->get()
            : collect();
        $pricelistChooser = $this->acceptanceFormPricingService->buildPricelistChooserPayload(
            $header->crm_customer_id ? (string) $header->crm_customer_id : null,
            $header->pricelist_id ? (string) $header->pricelist_id : ($pricelist?->id ? (string) $pricelist->id : null),
        );
        $details = QuotationDetails::where('quotation_header_id', $id)->get();
        $count = 1;

        foreach ($details as $detail) {
            if ($header->quotation_type == 'Analysis') {

                $detail_sub_analytes = $this->splitCommaSeparatedIds($detail->subcontracted_analytes);
                $detail_accredited_analytes = $this->splitCommaSeparatedIds($detail->accredited_analytes);
                $detail_part_no = $this->splitCommaSeparatedIds($detail->part_no);

                $analytes_ac = $this->resolveAnalyteNamesFromElementIds($detail_accredited_analytes);
                $analyte_sa = $this->resolveAnalyteNamesFromElementIds($detail_sub_analytes);
                $analyte_sub_acc = $this->resolveAnalyteNamesFromElementIds(
                    $this->splitCommaSeparatedIds($detail->sub_acc_analytes)
                );
                $default_a = $this->resolveAnalyteNamesFromElementIds(
                    $this->splitCommaSeparatedIds($detail->default_analytes)
                );
                $part_no = $this->resolveAnalysisTypeNames($detail_part_no);

                $detail['sub_analytes'] = $analyte_sa;
                $detail['acc_analytes'] = $analytes_ac;
                $detail['sub_acc'] = $analyte_sub_acc;
                $detail['default'] = $default_a;
                $detail['part_no_final'] = implode(',', $part_no);
                $sampleType = getSampleTypeByID($detail->sample_type);
                $detail['sample_type_name'] = $sampleType?->name ?? '';

                $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
                $detail->tat = $this->quotationPricingResolver->maxTatForElements(
                    $elementIds,
                    (string) $detail->part_no
                );
            }

            $detail['count'] = $count;
            ++$count;
            // return response()->json($detail,200);
        }
        if (in_array($header->status, ['Quote Complete', 'Quote In Approval'], true)) {
            return redirect()->route('view_quotation_final', ['id' => $header->id, 'stage' => $header->status]);
        }



        // return view('layouts.lab.invoice.quotation-show', compact('header', 'pricelist', 'pricelist_items', 'customers', 'details','sample_types '));
        $this->quotationReportService->normalizeStoredTerms($header);
        $header = $header->fresh(['preparedBy', 'customer', 'labSections']);
        $termsOfSale = $this->quotationReportService->resolveTermsOfSale($header);
        $users = getAllUsers();
        // return response()->json($sample_types);

        $samplePoints = SamplePoint::query()
            ->where('active', 1)
            ->where('crm_customer_id', $header->crm_customer_id)
            ->orderBy('name')
            ->get();

        $companyUnits = CRMCompanyUnit::query()
            ->where('crm_customer_id', $header->crm_customer_id)
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $currencies = Currency::query()->orderBy('code')->get();
        $structuredTermsConfig = $this->quotationReportService->resolveStructuredTermsConfig();
        $structuredTerms = $this->quotationReportService->resolveStructuredTerms($header);
        $revisionFamily = $this->quotationRevisionService->collectRevisionFamily($header);
        $linkedEnquiryEngagements = $this->enquiryQuotationService->engagementsForQuotation($header);
        $accountPaymentOptions = $this->quotationReportService->accountPaymentOptions();
        $labSections = $this->activeLabSectionsForQuotation();
        $canApproveQuotation = app(QuotationApprovalService::class)->canCurrentUserApprove($header);
        $skipsQuotationApproval = app(QuotationApprovalService::class)->skipsApproval();

        return view('layouts.lab.invoice.quotation-show', compact('header', 'pricelist', 'pricelist_items', 'pricelistChooser', 'customers', 'details', 'sample_types', 'termsOfSale', 'users', 'samplePoints', 'companyUnits', 'currencies', 'structuredTermsConfig', 'structuredTerms', 'revisionFamily', 'linkedEnquiryEngagements', 'accountPaymentOptions', 'labSections', 'canApproveQuotation', 'skipsQuotationApproval'));
        // return response()->json($pricelist_items,200);
    }
    public function change_quotation_workflow($id, $stage)
    {

        $header = QuotationHeader::find($id);

        if (isset($header->id)) {
            if (in_array($header->status, ['Quote Complete', 'Quote In Approval']) && in_array($stage, ['Quote In Preparation', 'Quote In Approval'])) {
                $header->is_complete = 0;
                $header->is_draft = 0;
                $header->is_print = 0;

                $header->is_approved = 0;
            }
            if (in_array($header->status, ['Quote In Preparation', 'Quote In Approval']) && in_array($stage, ['Quote Complete'])) {
                return redirect()->back()->with('error', 'Quote ' . $header->quote_number . ' has not being approved');
            }

            $previous = $header->status;
            $header->status = $stage;
            if ($stage == 'Quote Complete') {
                $header->approved_by = auth()->user()->id;
                $header->is_complete = 1;
                $header->is_draft = 0;
                $this->enquiryQuotationService->markPriorRevisionSuperseded($header->fresh() ?? $header);
            } else {
                $header->is_complete = 0;
                $header->is_draft = 0;
                $header->is_print = 0;
                $header->is_approved = 0;
            }




            $header->save();
        }
        // return response()->json($header,200);
        // return response()->json($previous);
        return redirect()->route('quotation-index', $previous)->with('success', 'Quotation Moved to ' . $stage . ' successfully.');
    }
    public function approve_workflow(Request $request)
    {
        $header = QuotationHeader::find($request->header_id);

        $res_approve = getUserById($request->user_id);
        $company = getActiveCompany();
        $prepared = getUserById($header->prepared_by_id);
        if ($header->status == 'Quote In Preparation') {
            $subject = '[' . $company->name . '] Quote ' . $header->quote_number . ' Approval Notification.';
            $message = 'Hi ' . $res_approve->name . ', Please approve for me Quote ' . $header->quote_number . '. From ' . $prepared->name;
            if (isset($request->notification)) {

                $notify = notify_user($message, $res_approve->email, $subject);
            }
            if (isset($request->send_message)) {
                $send = sendTextMessage($res_approve->phone, $message);
                if ($send == 'error') {
                    return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                }
            }
            if (!isset($res_approve->id)) {
                return redirect()->back()->with('error', 'Kindly Provide the approve before sending for approval!');
            }
            $header->approved_by = $res_approve->id;
        }
        if ($header->status == 'Quote In Approval') {
            $subject = '[' . $company->name . '] Quote ' . $header->quote_number . ' Approval Notification.';
            $message = 'Hi ' . $prepared->name . ', Quote ' . $header->quote_number . ' has been approved successfully.';
            if (isset($request->notification)) {

                $notify = notify_user($message, $prepared->email, $subject);
            }
            if (isset($request->send_message)) {
                $send = sendTextMessage($prepared->phone, $message);
                if ($send == 'error') {
                    return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                }
            }
            $header->is_complete = 1;
            $header->is_approved = 1;
        }
        $previous = $header->status;
        $header->status = $request->stage;
        $header->save();
        return redirect()->route('quotation-index', $previous)->with('success', 'Quotation Moved to ' . $request->stage . ' successfully.');
    }
    public function add_quotation_detail(Request $request, $id)
    {
        $request->validate([
            'currency_id' => ['required', 'uuid', 'exists:currencies,id'],
            'service_delivery' => ['nullable', 'string'],
            'payments_account_id' => ['nullable', 'uuid'],
            'payments_custom' => ['nullable', 'string'],
            'quote_specification' => ['nullable', 'string'],
            'additional_info' => ['nullable', 'string'],
            'payment_info' => ['nullable', 'string'],
        ]);

        $header = QuotationHeader::find($id);

        $termsOverride = $request->input('terms_override', $header->terms_override);
        if ($termsOverride === 'null' || $termsOverride === '') {
            $termsOverride = null;
        }

        $header->service_delivery = $request->input('service_delivery');
        $header->payments = $this->resolvePaymentsValue($request);
        $header->quote_specification = $request->input('quote_specification');
        $header->additional_info = $request->input('additional_info');
        $header->payment_info = $request->input('payment_info');
        $header->currency_id = $request->currency_id;
        $header->subject = $request->input('subject', $header->subject);
        $header->crm_company_unit_id = $request->input('crm_company_unit_id', $header->crm_company_unit_id) ?: null;
        $header->sample_point_id = $request->input('sample_point_id', $header->sample_point_id) ?: null;
        $header->sampling_location = $request->input('sampling_location', $header->sampling_location);
        $header->laboratory_ref = $request->input('laboratory_ref', $header->laboratory_ref);
        $header->terms_override = $termsOverride;
        if ((string) $header->quotation_type === 'Analysis') {
            $header->show_loq_column = $request->boolean('show_loq_column');
            $header->show_mu_column = $request->boolean('show_mu_column');
        }

        // Structured Commercial Terms are hidden on the prep form; preserve existing values.
        if ($request->has('structured_terms') && is_array($request->input('structured_terms'))) {
            $structuredTerms = [];
            foreach (array_keys(QuotationReportService::STRUCTURED_TERM_DEFINITIONS) as $key) {
                $structuredTerms[$key] = (string) $request->input('structured_terms.'.$key, '');
            }
            $header->structured_terms = $structuredTerms;
        }

        $header->save();
        $this->quotationReportService->ensureHeaderMetadata($header);

        $hasNewGeneralLines = is_array($request->part_no) && count(array_filter($request->part_no, fn ($value) => $value !== '' && $value !== null)) > 0;
        $hasNewAnalysisLines = is_array($request->sample_type) && count(array_filter($request->sample_type, fn ($value) => $value !== '' && $value !== null)) > 0;

        if ($header->quotation_type == 'General') {
            $count = 0;
            if (! $hasNewGeneralLines) {
                return redirect()->back()->with('success', 'Quotation configuration saved successfully.');
            }
            foreach ($request->part_no as $id) {
                $detail = new QuotationDetails();
                $detail->item_name = $request->item[$count];
                $detail->unit_price = $request->unit_price[$count];
                // return response()->json($request->unit_price);
                $detail->tax = $request->tax[$count];
                $detail->part_no = $request->part_no[$count];
                $detail->quantity = $request->quantity[$count];
                $detail->description = $request->description[$count];
                if (isset($request->photo[$count]) && $request->photo[$count] != '') {
                    $path = $request->photo[$count]->path();
                    $file = Storage::putFile('quote_items', new File($path));
                    $file = explode('/', $file);
                    $fname = '/storage/quote_items/' . urlencode(end($file));
                    $detail->photo_url = (string) $fname;
                }
                // return response()->json($request->analysis_id,200);
                $detail->quotation_header_id = $header->id;

                $detail->save();
                ++$count;
            }
        } else {
            // return response()->json($request->all(),200);

            // Analysis quotation lines: Amspec package math —
            // Total = quantity (no. of samples) × unit_price ONCE per sample package.
            // Selected tests stay on that line for LOQ/MU/method/PDF; they are not billed per test
            // unless pricing_mode is explicitly per_test (see QuotationPricingResolver).
            $count = 0;
            if (! $hasNewAnalysisLines) {
                return redirect()->back()->with('success', 'Quotation configuration saved successfully.');
            }
            foreach ($request->sample_type as $id) {
                $unitPrice = (float) $request->unit_price[$count];
                $taxRate = (float) ($request->tax[$count] ?? 0);
                $elementIds = array_values(array_unique(array_filter(explode(',', implode(',', [
                    $request->default_analytes[$count] ?? '',
                    $request->accreditted_analytes[$count] ?? '',
                    $request->sub_analytes[$count] ?? '',
                    $request->sub_acc[$count] ?? '',
                ])))));

                $analysisTypeIds = array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) ($request->part_number_final[$count] ?? ''))
                )));

                // Analysis Type column removed: derive from selected elements when hidden CSV is empty.
                if ($analysisTypeIds === [] && $elementIds !== []) {
                    $analysisTypeIds = AnalysisElements::query()
                        ->whereIn('id', $elementIds)
                        ->pluck('analysis_type_id')
                        ->map(fn ($id) => trim((string) $id))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                $partNumberFinal = $analysisTypeIds !== []
                    ? implode(',', $analysisTypeIds)
                    : (string) ($request->part_number_final[$count] ?? '');

                try {
                    $this->quotationLabSectionScope->assertAnalysisTypesAllowed($header, $analysisTypeIds);
                    $this->quotationLabSectionScope->assertElementsAllowed($header, $elementIds);
                } catch (\RuntimeException $exception) {
                    return redirect()->back()->withInput()->with('error', $exception->getMessage());
                }

                $loqOverrides = [];
                $loqJson = (string) ($request->element_loq_json[$count] ?? '');
                if ($loqJson !== '') {
                    $decoded = json_decode($loqJson, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $elementId => $loqValue) {
                            $loqOverrides[(string) $elementId] = (string) $loqValue;
                        }
                    }
                }

                $normalizedRows = $this->quotationPricingResolver->normalizeManualDetailRows(
                    $header,
                    $request->sample_type[$count],
                    $partNumberFinal,
                    $elementIds,
                    (int) $request->quantity[$count],
                    $unitPrice,
                    $taxRate,
                    (string) ($request->accreditted_analytes[$count] ?? ''),
                    (string) ($request->sub_analytes[$count] ?? ''),
                    (string) ($request->default_analytes[$count] ?? ''),
                    (string) ($request->sub_acc[$count] ?? ''),
                    (string) ($request->pricing_mode[$count] ?? QuotationPricingResolver::PRICING_MODE_AUTO),
                    $loqOverrides,
                    '',
                    '',
                    (string) ($request->quantity_required[$count] ?? ''),
                );

                foreach ($normalizedRows as $rowPayload) {
                    $detail = new QuotationDetails();
                    $detail->sample_type = $rowPayload['sample_type'];
                    $detail->unit_price = $rowPayload['unit_price'];
                    $detail->tax = $rowPayload['tax'];
                    $detail->part_no = $rowPayload['part_no'];
                    $detail->quantity = $rowPayload['quantity'];
                    $detail->quantity_required = $rowPayload['quantity_required'] ?? null;
                    $detail->accredited_analytes = $rowPayload['accredited_analytes'];
                    $detail->subcontracted_analytes = $rowPayload['subcontracted_analytes'] ?? '';
                    $detail->default_analytes = $rowPayload['default_analytes'];
                    $detail->sub_acc_analytes = $rowPayload['sub_acc_analytes'];
                    $detail->is_package = (bool) ($rowPayload['is_package'] ?? false);
                    $detail->loq = (string) ($rowPayload['loq'] ?? '');
                    $detail->mu_percent = (string) ($rowPayload['mu_percent'] ?? '');
                    $detail->show_loq_analytes = null;
                    $detail->show_mu_analytes = null;
                    $detail->test_method = (string) ($rowPayload['test_method'] ?? '');
                    $detail->tat = $rowPayload['tat'] ?? null;
                    $detail->description = (string) ($rowPayload['description'] ?? '');
                    $detail->quotation_header_id = $header->id;

                    $analysisTypeIds = array_filter(explode(',', (string) $rowPayload['part_no']));
                    $this->quotationPricingResolver->persistInvoicableItemOnDetail(
                        $detail,
                        $analysisTypeIds[0] ?? null
                    );
                    $detail->save();

                    QuotationDetailAnalysisSplit::syncForDetail(
                        (string) $detail->id,
                        $analysisTypeIds
                    );
                }

                ++$count;
            }

        }
        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        $sub_total = [];
        $taxes = [];
        foreach ($details as $detail) {
            $detail['extended_price'] = (int) $detail->quantity * (float) $detail->unit_price;

            array_push($sub_total, (float) $detail->extended_price);
            if ($detail->tax != 0) {
                $tax = (int) $detail->tax / 100 * (float) $detail->unit_price * (int) $detail->quantity;
                // return response()->json($tax);
                array_push($taxes, $tax);
            }
        }
        $sub_total_num = array_sum($sub_total);
        $tax_num = array_sum($taxes);
        // return response()->json($header[0]['tax']);
        $total_price_num = array_sum($sub_total) + array_sum($taxes);
        $header->sub_total = $sub_total_num;
        $header->tax = $tax_num;
        $header->total_amount = $total_price_num;
        $header->save();
        return redirect()->back()->with('success', 'Analysis added successfully!');
    }
    public function edit_quotation_header(Request $request, $id)
    {
        $request->validate([
            'lab_section_ids' => ['nullable', 'array'],
            'lab_section_ids.*' => ['uuid', 'exists:sample_analysis_stages,id'],
        ]);

        $header = QuotationHeader::find($id);
        $customer = CRMCustomer::where('name', $request->client)->first();

        $header->crm_customer_id = $customer->id;
        if ($request->filled('client_contact')) {
            $header->crm_customer_contact_id = $request->client_contact;
        }
        $header->quote_date = $request->quotation_date;
        $header->quotation_type = $request->quotation_type;
        $header->expiring_date = $request->expire_date;
        $header->subject = $request->input('subject', $header->subject);
        $header->crm_company_unit_id = $request->input('crm_company_unit_id', $header->crm_company_unit_id) ?: null;
        $header->sample_point_id = $request->input('sample_point_id', $header->sample_point_id) ?: null;
        $header->sampling_location = $request->input('sampling_location', $header->sampling_location);
        $header->laboratory_ref = $request->input('laboratory_ref', $header->laboratory_ref);

        if ($request->filled('currency_id')) {
            $header->currency_id = $request->currency_id;
        }

        // Do not auto-bind a customer pricelist on header save — bind only via commercial modal.
        if (empty($header->currency_id)) {
            $eligible = $this->acceptanceFormPricingService->eligiblePricelistsForCustomer(
                $header->crm_customer_id ? (string) $header->crm_customer_id : null
            );
            $currencyDonor = $eligible[0] ?? null;
            if ($currencyDonor !== null && $currencyDonor->currency_id) {
                $header->currency_id = $currencyDonor->currency_id;
            }
        }
        $header->save();
        if ($request->has('lab_section_ids')) {
            $this->syncQuotationLabSections($header, $request->input('lab_section_ids', []));
        }
        $this->quotationReportService->ensureHeaderMetadata($header);

        return redirect()->back()->with('success', 'Quotation updated successfully');
    }
    public function view_quotation_final($id, $stage = false)
    {
        $hd = QuotationHeader::findOrFail($id);

        $this->recalculateQuotationTotals($hd);
        $hd->refresh();

        $currency = $hd->currency_id ? Currency::find($hd->currency_id) : null;
        $batchGenerateSelect = Schema::hasColumn('quotation_headers', 'is_batch_generate')
            ? 'quotation_headers.is_batch_generate'
            : '0 as is_batch_generate';
        $header = QuotationHeader::join('crm_customers', 'crm_customers.id', '=', 'quotation_headers.crm_customer_id')
            ->join('crm_customer_contacts', 'crm_customer_contacts.id', '=', 'quotation_headers.crm_customer_contact_id')
            ->join('users', DB::raw('users.id::text'), '=', DB::raw('quotation_headers.prepared_by_id::text'))
            ->leftJoin('module_pre_configs as tc', function ($join): void {
                $join->whereRaw('tc.id::text = users.position');
            })
            ->where('quotation_headers.id', $id)
            ->selectRaw('quotation_headers.service_delivery,quotation_headers.payments,quotation_headers.payment_info,quotation_headers.quotation_type,quotation_headers.additional_info,quotation_headers.quote_specification,quotation_headers.quote_number,quotation_headers.upload_url,quotation_headers.is_print,quotation_headers.id,quotation_headers.quote_date,quotation_headers.expiring_date,quotation_headers.email_to_customer,quotation_headers.is_draft,quotation_headers.is_complete,quotation_headers.is_approved,quotation_headers.status,quotation_headers.approved_by,quotation_headers.email_to_customer,quotation_headers.total_amount,quotation_headers.sub_total,quotation_headers.tax,crm_customers.name,crm_customers.postal_address,crm_customers.physical_address,crm_customer_contacts.first_name,crm_customer_contacts.middle_name,crm_customer_contacts.last_name,crm_customer_contacts.email,crm_customer_contacts.mobile,
                                users.name as prepared_by,tc.name as position,users.email as prepared_by_email,users.phone,'.$batchGenerateSelect)
            ->get();

        $header[0]['total_price'] = $hd->total_amount;
        $header[0]['sub_total'] = $hd->sub_total;
        $header[0]['tax'] = $hd->tax;

        $labs = Lab::where('active', 1)->get();
        $reportViewData = $this->quotationReportService->buildViewData($hd);
        $revisionFamily = $this->quotationRevisionService->collectRevisionFamily($hd);
        $linkedEnquiryEngagements = $this->enquiryQuotationService->engagementsForQuotation($hd);
        $quotationHeader = $hd;
        $canApproveQuotation = app(QuotationApprovalService::class)->canCurrentUserApprove($hd);

        return view('layouts.lab.invoice.quotation-doc', array_merge(
            compact('header', 'currency', 'labs', 'revisionFamily', 'linkedEnquiryEngagements', 'quotationHeader', 'canApproveQuotation'),
            $reportViewData
        ));
    }

    public function previewQuotation(string $id)
    {
        return $this->streamQuotationPdf($id);
    }

    public function publicReportView(string $id, string $token)
    {
        $header = QuotationHeader::findOrFail($id);

        if (! hash_equals($this->quotationReportService->publicReportToken($header), $token)) {
            abort(403, 'Invalid quotation report link.');
        }

        $this->recalculateQuotationTotals($header);
        $data = $this->quotationReportService->buildViewData($header->fresh());

        return view('billing.quotations.amspec.public-report', $data);
    }

    public function streamQuotationPdf(string $id)
    {
        $header = QuotationHeader::findOrFail($id);
        AmSpecQuotationNumberGenerator::assignIfMissing($header);
        $header = $header->fresh();

        if ($header->details()->count() === 0) {
            return redirect()->back()->with('error', 'Add at least one line item before previewing the quotation.');
        }

        $this->recalculateQuotationTotals($header);

        return $this->quotationReportService->streamPdf($header->fresh());
    }

    public function edit_quotation_detail(Request $request)
    {
        // return response()->json($request->all(),200);
        $detail = QuotationDetails::find($request->detail_id);
        if (!isset($detail->id)) {
            return redirect()->back()->with('error', 'No Quotation analysis with the specified id!');
        }
        $header = QuotationHeader::find($detail->quotation_header_id);
        if ($header->quotation_type == 'General') {
            $detail->item_name = $request->item;
            $detail->part_no = $request->part_no;
            $detail->description = $request->description;
            if ($request->hasFile('photo')) {
                $path = $request->photo->path();
                $file = Storage::putFile('quote_items', new File($path));
                $file = explode('/', $file);
                $fname = '/storage/quote_items/' . urlencode(end($file));
                $detail->photo_url = (string) $fname;
            }
        } else {
            $partNoInput = $request->input('part_no', $detail->part_no);
            $partNoCsv = is_array($partNoInput)
                ? implode(',', array_values(array_filter(array_map('strval', $partNoInput))))
                : (string) $partNoInput;

            $detail->part_no = $partNoCsv;
            $detail->accredited_analytes = isset($request->accreditted_analytes) ? $request->accreditted_analytes : $detail->accredited_analytes;
            $detail->subcontracted_analytes = isset($request->sub_analytes) ? $request->sub_analytes : $detail->subcontracted_analytes;
            $detail->sub_acc_analytes = isset($request->sub_acc) ? $request->sub_acc : $detail->sub_acc_analytes;
            $detail->default_analytes = isset($request->default_analytes) ? $request->default_analytes : $detail->default_analytes;
            $detail->show_loq_analytes = null;
            $detail->show_mu_analytes = null;
            $analysis_types_ids = array_values(array_filter(array_map('trim', explode(',', $partNoCsv))));

            try {
                $this->quotationLabSectionScope->assertAnalysisTypesAllowed($header, $analysis_types_ids);
                $this->quotationLabSectionScope->assertElementsAllowed($header, array_values(array_unique(array_filter(array_merge(
                    $this->splitCommaSeparatedIds((string) $detail->default_analytes),
                    $this->splitCommaSeparatedIds((string) $detail->accredited_analytes),
                    $this->splitCommaSeparatedIds((string) $detail->subcontracted_analytes),
                    $this->splitCommaSeparatedIds((string) $detail->sub_acc_analytes),
                )))));
            } catch (\RuntimeException $exception) {
                return redirect()->back()->withInput()->with('error', $exception->getMessage());
            }

            QuotationDetailAnalysisSplit::syncForDetail((string) $detail->id, $analysis_types_ids);

            $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
            $computedTat = $this->quotationPricingResolver->maxTatForElements($elementIds, $partNoCsv);
            $requestTat = $request->input('tat');
            $detail->tat = $computedTat ?? (is_numeric($requestTat) && (int) $requestTat > 0 ? (int) $requestTat : null);
        }
        $unitPrice = (float) $request->unit_price;
        $taxRate = (float) ($request->tax ?? 0);

        if ($header->quotation_type !== 'General') {
            $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
            if ($unitPrice <= 0) {
                $unitPrice = $this->quotationPricingResolver->suggestLineUnitPrice(
                    $header,
                    (string) $detail->sample_type,
                    (string) $detail->part_no,
                    $elementIds,
                );
            }

            $suggestion = $this->quotationPricingResolver->suggestManualLinePricing(
                $header,
                (string) $detail->sample_type,
                (string) $detail->part_no,
                $elementIds,
            );
            $taxRate = (float) ($suggestion['tax'] ?? 0);
        }

        $detail->unit_price = $unitPrice;
        $detail->tax = $taxRate;
        $detail->quantity = $request->quantity;
        if ($request->has('quantity_required')) {
            $qtyRequired = trim((string) $request->input('quantity_required', ''));
            $detail->quantity_required = $qtyRequired !== '' ? $qtyRequired : null;
        }
        $detail->save();

        $this->recalculateQuotationTotals($header);

        return redirect()->back()->with('success', 'Quotation detail edited successfully!');
    }
    public function delete_quotation_detail($id)
    {
        $detail = QuotationDetails::find($id);
        if (!isset($detail->id)) {
            return redirect()->back()->with('error', 'No Quotation analysis with the specified id!');
        }
        $detail->delete();
        return redirect()->back()->with('success', 'Quotation analysis deleted successfully!');
    }
    public function delete_quotation(Request $request, $id)
    {
        if ($request->header_id != $id) {
            return redirect()->back()->with('error', 'Quotation Id does not match the passed Quotation ID!');
        }
        $header = QuotationHeader::find($id);
        if (!isset($header->id)) {
            return redirect()->back()->with('error', 'There is no Quotation with the specified ID');
        }
        $header->labSections()->detach();
        $header->delete();
        return redirect()->route('quotation-index')->with('success', 'Quotation deleted successfully!');
    }
    public function save_draft(Request $request, $id)
    {
        $header = QuotationHeader::find($id);
        $header->is_draft = 1;
        $header->save();
        return redirect()->route('quotation-index')->with('success', 'Quotation ' . $header->quote_number . ' saved as draft successfully');
    }
    public function save_quotation_final(Request $request, $id)
    {
        $header = QuotationHeader::find($id);
        $header->status = 'Quote Complete';
        // return response()->json((int) $id,200);
        $header->tax = (int) $request->tax;
        $header->sub_total = (int) $request->sub_total;
        $header->total_amount = (int) $request->total;
        $header->is_draft = 0;
        $header->is_complete = 1;
        $header->save();
        return redirect()->back()->with('success', 'Quotation ' . $header->quote_number . ' saved successfully');
    }
    public function clone_quotation($id)
    {
        $header = QuotationHeader::with('labSections')->find($id);
        $header_clone = new QuotationHeader();
        $header_clone->crm_customer_id = $header->crm_customer_id;

        $header_clone->crm_customer_contact_id = $header->crm_customer_contact_id;
        $header_clone->quote_date = $header->quote_date;
        $header_clone->status = $header->status;
        $header_clone->expiring_date = $header->expiring_date;
        $header_clone->prepared_by_id = auth()->user()->id;

        $header_clone->pricelist_id = $header->pricelist_id
            ?? $this->quotationPricingResolver->resolvePricelist($header->crm_customer_id)?->id;
        $header_clone->save();

        AmSpecQuotationNumberGenerator::assignIfMissing($header_clone);
        $header_clone->is_draft = 1;
        $header_clone->save();

        $header_clone->labSections()->sync(
            $header->labSections->pluck('id')->all()
        );

        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        foreach ($details as $detail) {
            $new = new QuotationDetails();
            $new->sample_type = $detail->sample_type;
            $new->unit_price = $detail->unit_price;
            // return response()->json($request->unit_price);
            $new->tax = $detail->tax;
            $new->part_no = $detail->part_no;
            $new->quantity = $detail->quantity;
            $new->quantity_required = $detail->quantity_required;
            $new->analyte_id = $detail->analyte_id;
            // return response()->json($request->analysis_id,200);
            $new->quotation_header_id = $header_clone->id;

            $new->save();
        }
        return redirect()->route('add-qoute-details-view', ['id' => $header_clone->id]);
    }

    public function create_quotation_revision(string $id)
    {
        $priorHeader = QuotationHeader::findOrFail($id);

        if ($priorHeader->details()->count() === 0) {
            return redirect()->back()->with('error', 'Add at least one line item before creating a revision.');
        }

        $revision = $this->quotationRevisionService->createRevision($priorHeader);

        return redirect()
            ->route('add-qoute-details-view', ['id' => $revision->id])
            ->with('success', 'Revision '.$revision->revision_number.' created as '.$revision->quote_number.'.');
    }

    public function redirect_from_docs($id, $stage)
    {
        $header = QuotationHeader::find($id);
        // return response()->json($stage);
        $header->status = $stage;
        $header->is_complete = 0;
        $header->save();
        return redirect()->route('add-qoute-details-view', ['id' => $header->id, 'stage' => $header->status]);
    }
    public function print_quotation(Request $request, $id)
    {
        ini_set('max_execution_time', 300);
        $header = QuotationHeader::findOrFail($id);
        AmSpecQuotationNumberGenerator::assignIfMissing($header);
        $header = $header->fresh();

        if ($header->details()->count() === 0) {
            return redirect()->back()->with('error', 'Add at least one line item before generating the quotation PDF.');
        }

        $this->recalculateQuotationTotals($header);
        $stored = $this->quotationReportService->storePdf($header->fresh());

        return $stored;
    }
    public function upload_quotation(Request $request, $id)
    {
        // Billing-only send: stamps header delivery markers. Per-enquiry send/accept
        // still lives on enquiry_quotations when enquiries are linked to this quote.
        $header = QuotationHeader::find($id);
        // return response()->json($request->all(),200);

        $contact = getCrmCustomerContactById($header->crm_customer_contact_id);
        $name = $contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name;
        $company = getActiveCompany();
        $message = 'Quotation ' . $header->quote_number . ' has been to you from ' . $company->name . '. <br>Kindly find it attached below. Contact us if you need any clarification. ';
        $body = 'Hi ' . $name . ', <br>' . $message . '<br>Regards, <br>' . $company->name . '.';
        $subject = '[' . $company->name . '] Quotation ' . $header->quote_number . '.';
        $file = \storage_path() . '/app' . $header->upload_url;
        $notify = notify_user($body, $contact->email, $subject, $file);
        $header->email_to_customer = getTodayDate();
        if ($header->sent_to_customer_at === null) {
            $header->sent_to_customer_at = now();
        }
        $header->save();

        return redirect()->back()->with('success', 'Quotation uploaded and sent to client successfully');
    }

    public function suggestManualLinePricing(Request $request, string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);

        $elementIds = array_values(array_unique(array_filter(array_map(
            'trim',
            explode(',', (string) $request->input('element_ids', ''))
        ))));

        $analysisTypeIds = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $request->input('analysis_type_ids', ''))
        )));

        try {
            $this->quotationLabSectionScope->assertAnalysisTypesAllowed($header, $analysisTypeIds);
            $this->quotationLabSectionScope->assertElementsAllowed($header, $elementIds);
        } catch (\RuntimeException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        $suggestion = $this->quotationPricingResolver->suggestManualLinePricing(
            $header,
            $request->input('sample_type_id'),
            (string) $request->input('analysis_type_ids', ''),
            $elementIds,
            (string) $request->input('pricing_mode', QuotationPricingResolver::PRICING_MODE_AUTO),
        );

        return response()->json($suggestion);
    }

    /**
     * Eligible pricelists for the quotation customer (chooser data for prep UI).
     * needs_choice=true when multiple lists → shaking icon / modal (UI by other agent).
     */
    public function eligiblePricelists(string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);
        $payload = $this->acceptanceFormPricingService->buildPricelistChooserPayload(
            $header->crm_customer_id ? (string) $header->crm_customer_id : null,
            $header->pricelist_id ? (string) $header->pricelist_id : null,
        );

        return response()->json($payload);
    }

    /**
     * Bind the quotation to one eligible pricelist (user choice from chooser modal).
     */
    public function selectPricelist(\Illuminate\Http\Request $request, string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);
        $pricelistId = (string) $request->input('pricelist_id', '');
        $eligible = $this->acceptanceFormPricingService->eligiblePricelistsForCustomer(
            $header->crm_customer_id ? (string) $header->crm_customer_id : null
        );
        $match = collect($eligible)->first(fn ($p) => (string) $p->id === $pricelistId);
        if ($match === null) {
            return response()->json(['error' => 'Selected pricelist is not assigned to this customer.'], 422);
        }

        $header->pricelist_id = $match->id;
        if (empty($header->currency_id) && $match->currency_id) {
            $header->currency_id = $match->currency_id;
        }
        $header->save();

        $populate = $this->quotationPricelistPopulateService->appendFromPricelist($header, $match);
        if ($populate['created'] > 0) {
            $this->recalculateQuotationTotals($header);
        }

        $billingMode = (string) ($match->billing_mode ?? $populate['billing_mode'] ?? 'package');
        $pricingMode = $billingMode === 'per_test' ? 'per_test' : 'per_package';

        return response()->json([
            'ok' => true,
            'pricelist_id' => (string) $match->id,
            'code' => (string) ($match->code ?? ''),
            'description' => (string) ($match->description ?? ''),
            'billing_mode' => $billingMode,
            'pricing_mode' => $pricingMode,
            'lines_created' => (int) $populate['created'],
            'reload' => $populate['created'] > 0,
        ]);
    }

    public function detachPricelist(\Illuminate\Http\Request $request, string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);
        $header->pricelist_id = null;
        $header->save();

        return response()->json([
            'ok' => true,
            'pricelist_id' => null,
        ]);
    }

    public function bulkDeleteQuotationDetails(\Illuminate\Http\Request $request, string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);
        $request->validate([
            'detail_ids' => 'required|array|min:1',
            'detail_ids.*' => 'uuid',
        ]);

        $ids = collect($request->input('detail_ids', []))
            ->map(fn ($detailId): string => (string) $detailId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $deleted = QuotationDetails::query()
            ->where('quotation_header_id', $header->id)
            ->whereIn('id', $ids)
            ->delete();

        $this->recalculateQuotationTotals($header);

        return response()->json([
            'ok' => true,
            'deleted' => (int) $deleted,
        ]);
    }

    /**
     * Import quotation prep lines from Excel or PDF (logic only; UI by other agent).
     */
    public function importPrepLines(\Illuminate\Http\Request $request, string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,pdf|max:20480',
            'format' => 'nullable|in:excel,pdf',
            'pricing_mode' => 'nullable|in:per_package,per_test,auto',
        ]);

        $format = (string) $request->input('format', '');
        if ($format === '') {
            $ext = strtolower($request->file('file')->getClientOriginalExtension());
            $format = $ext === 'pdf' ? 'pdf' : 'excel';
        }

        try {
            $result = $this->quotationPrepImportService->import(
                $header,
                $request->file('file'),
                $format,
                (string) $request->input('pricing_mode', QuotationPricingResolver::PRICING_MODE_PER_PACKAGE),
            );
        } catch (\RuntimeException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'pdf_import' => $this->quotationPrepImportService->pdfImportCapability(),
            ], 422);
        }

        $this->recalculateQuotationTotals($header);

        return response()->json(array_merge($result, [
            'pdf_import' => $this->quotationPrepImportService->pdfImportCapability(),
        ]));
    }

    /**
     * Whether PDF (pdftotext) import is available on this host; Excel always is.
     */
    public function importPrepCapability()
    {
        return response()->json([
            'excel' => true,
            'pdf' => $this->quotationPrepImportService->pdfImportCapability(),
        ]);
    }

    public function packageDefaults(PackageDefaultsRequest $request, string $id)
    {
        $header = QuotationHeader::query()->findOrFail($id);

        $analysisTypeIds = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $request->input('analysis_type_ids', ''))
        )));

        try {
            $this->quotationLabSectionScope->assertAnalysisTypesAllowed($header, $analysisTypeIds);
        } catch (\RuntimeException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        $defaults = $this->quotationPricingResolver->resolvePackageDefaults(
            $header,
            $request->input('sample_type_id'),
            (string) $request->input('analysis_type_ids', ''),
        );

        if (! empty($defaults['found']) && is_array($defaults['element_ids'] ?? null)) {
            $allowed = $this->quotationLabSectionScope->filterAllowedElementIds(
                $header,
                $defaults['element_ids'],
            );
            $allowedSet = array_flip($allowed);
            $defaults['element_ids'] = $allowed;
            $defaults['accredited_ids'] = array_values(array_filter(
                $defaults['accredited_ids'] ?? [],
                static fn ($id): bool => isset($allowedSet[(string) $id]),
            ));
            $defaults['default_ids'] = array_values(array_filter(
                $defaults['default_ids'] ?? [],
                static fn ($id): bool => isset($allowedSet[(string) $id]),
            ));
            $defaults['parameters'] = array_values(array_filter(
                $defaults['parameters'] ?? [],
                static fn (array $row): bool => isset($allowedSet[(string) ($row['id'] ?? '')]),
            ));
            if ($allowed === []) {
                $defaults['found'] = false;
                $defaults['hint'] = 'No package parameters fall under this quotation\'s lab section(s).';
            }
        }

        return response()->json($defaults);
    }

    public function updateElementLoq(Request $request)
    {
        $validated = $request->validate([
            'element_id' => ['required', 'uuid', 'exists:analysis_elements,id'],
            'loq' => ['nullable', 'string', 'max:50'],
        ]);

        $element = AnalysisElements::query()->findOrFail($validated['element_id']);
        $loq = trim((string) ($validated['loq'] ?? ''));

        if ($loq === '' || ! is_numeric($loq)) {
            $element->hod = null;
        } else {
            $element->hod = (float) $loq;
        }

        $element->save();

        return response()->json([
            'ok' => true,
            'element_id' => (string) $element->id,
            'loq' => $loq === '' || ! is_numeric($loq)
                ? ''
                : rtrim(rtrim(number_format((float) $loq, 6, '.', ''), '0'), '.'),
        ]);
    }

    public function get_quotation_detail($id)
    {
        $detail = QuotationDetails::find($id);
        if (isset($detail->id)) {
            $sample_types = SampleType::find($detail->sample_type);
            $header = QuotationHeader::query()->find($detail->quotation_header_id);
            $analysisQuery = AnalysisType::query()->where('sample_type_id', $detail->sample_type);
            if ($header !== null) {
                $analysisQuery = $this->quotationLabSectionScope->constrainAnalysisTypes($analysisQuery, $header);
            }
            $analysis_data = $analysisQuery->get();
            $detail_sub_analytes = $this->splitCommaSeparatedIds($detail->subcontracted_analytes);
            $detail_accredited_analytes = $this->splitCommaSeparatedIds($detail->accredited_analytes);
            $detail_part_no = $this->splitCommaSeparatedIds($detail->part_no);

            $analytes_ac = $this->resolveAnalyteNamesFromElementIds($detail_accredited_analytes);
            $analyte_sa = $this->resolveAnalyteNamesFromElementIds($detail_sub_analytes);
            $analyte_sub_acc = $this->resolveAnalyteNamesFromElementIds(
                $this->splitCommaSeparatedIds($detail->sub_acc_analytes)
            );
            $default_a = $this->resolveAnalyteNamesFromElementIds(
                $this->splitCommaSeparatedIds($detail->default_analytes)
            );
            $part_no = $this->resolveAnalysisTypeNames($detail_part_no);

            $detail['sub_analytes'] = $analyte_sa;
            $detail['acc_analytes'] = $analytes_ac;
            $detail['sub_acc'] = $analyte_sub_acc;
            $detail['default'] = $default_a;
            $detail['part_no_final'] = implode(',', $part_no);
            $detail['part_no_value'] = $detail_part_no;
            $sampleType = getSampleTypeByID($detail->sample_type);
            $detail['sample_type_name'] = $sampleType?->name ?? '';
        }
        $final = [];
        $final['analysis_data'] = $analysis_data ?? collect();
        $final['detail'] = $detail;
        return $final;
    }
    public function convertQuoteToBatch(Request $request)
    {
        $quote = QuotationHeader::with(['details', 'contact', 'customer'])->find($request->quote_id);
        $sample_type_arr = array_unique(QuotationDetails::where('quotation_header_id', $quote->id)->pluck('sample_type')->toArray());
        $sampletypes = SampleType::whereIn('id', $sample_type_arr)->get();
        $batch_config = SystemConfiguration::where('key', 'batch_code_config')->first();
        if (!isset($batch_config->id)) {
            return redirect()->back()->with('error', 'Kindly add batch_code_config configuration');
        }
        $unit = CRMCompanyUnit::where('crm_customer_id', $quote->crm_customer_id)->where('name', $quote->contact->unit_name)->first();

        $account = SystemConfiguration::find($quote->customer->account_status);
        if (isset($account->id)) {
            $account_status = $account->key;
            if ($account->key == 'Suspended') {
                return redirect()->back()->with('error', 'The customer is currently suspended!');
            }
        } else {
            $account_status = 'N/a';
        }
        foreach ($sampletypes as $sample_type) {
            $cust_code = str_split($quote->customer->code);
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

            $cP = 'BA' . $batch_config->value . implode('', $values) . $sample_type->code;
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
            $batch_code = $cP . '' . $final_no;



            $insert_batch = [
                'receipt_date' => date('Y-m-d'),
                'date_collected' => date('Y-m-d'),
                'crm_customer_id' => $quote->crm_customer_id,
                'crm_unit_name' => $quote->contact->unit_name,
                'sample_type_id' => $sample_type->id,
                'reference_number' => $quote->quote_number,
                'quote_no' => $quote->quote_number,
                'batch_code' => $batch_code,
                'crm_contact_id' => $quote->contact->id,
                'status' => 'Samples Reception',
                'crm_unit_id' => $unit->id,
                'lab_capable' => 1,
                'client_instruction_clear' => 1,
                'current_account_status' => $account_status,
                'receiving_officer' => auth()->user()->id,
                'quote_id' => $quote->id,

            ];
            $batch = SampleHeader::create($insert_batch);
            $analysis_max_report_time = 0;
            $analytes_max_report_time = 0;

            foreach ($quote->details as $detail) {
                if ($detail->sample_type == $sample_type->id) {
                    $analysis_types_arr = QuotationDetailAnalysisSplit::where('quotation_detail_id', $detail->id)->pluck('analysis_type_id')->toArray();
                    foreach (range(1, $detail->quantity) as $no_samples) {
                        $lab = Lab::find($request->lab_id);
                        $code = SampleDetails::orderBy('id', 'DESC')->first();
                        $last_sample = isset($code->id) ? $code->sample_no : $lab->start_sample_no;

                        // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                        // return response()->json($request->sample_details['lab_id'][$k]);
                        $sample_number = intval($last_sample) + 1;
                        // $sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

                        $sample_code = 'S' . date('Y') . $lab->code . $sample_type->code . sprintf('%0' . '4' . 'd', $sample_number);
                        $sample_no = sprintf('%0' . '4' . 'd', $sample_number);
                        $report_number = 'LR/' . $sample_type->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%0' . '4' . 'd', $sample_number);
                        $insert_sample = [
                            'sample_code' => $sample_code,
                            'sample_no' => $sample_no,
                            'report_number' => $report_number,
                            'analysis_type_id' => implode(',', $analysis_types_arr),
                            'lab_id' => $lab->id,
                            'sample_header_id' => $batch->id,
                            'disposal_date' => \Carbon\Carbon::parse($batch->receipt_date)->addDays(14)->format('Y-m-d'),
                        ];
                        $sample = SampleDetails::create($insert_sample);
                        $this->createDetailAnalysisRelation($batch->id, $sample->id, explode(',', $sample->analysis_type_id));

                        $analysis_elements = AnalysisElements::with(['analyte'])->whereIn('id', explode(',', $detail->default_analytes))->get();
                        foreach ($analysis_elements as $element) {
                            $insert_captured = [
                                "sample_detail_code" => $sample->sample_code,
                                "sample_detail_id" => $sample->id,
                                "sample_header_id" => $batch->id,
                                "analyte_id" => $element->analyte_id,
                                "analysis_type_id" => $element->analysis_type_id,
                                "analyte_code" => $element->analyte->code,
                                "equipment_id" => $element->equipment_id,
                                "method_id" => $element->method_id,
                                "reporting_unit_id" => $element->reporting_unit,
                                "user_id" => Auth()->user()->id,
                                "operator_id" => $element->operator_id,
                                "ltm_method_id" => $element->ltm_method_id,
                                "analyte_accredited" => $element->non_accredited,
                                "analyte_status_contracted" => $lab->is_external ?? 0,
                                "lab_section_id" => $element->lab_section_id,
                                "parameters_order" => $element->level,
                                "remark_is_manual" => $element->remark_is_manual
                            ];
                            $captured = CapturedResult::create($insert_captured);
                            $insert_result = [
                                'captured_result_id' => $captured->id,
                                "sample_detail_id" => $sample->id,
                                'sample_detail_code' => $sample->sample_code,
                                "sample_header_id" => $batch->id,
                                "analyte_id" => $element->analyte_id,
                                "analysis_type_id" => $element->analysis_type_id,
                                "analyte_code" => $element->analyte->code,
                                "unit_code" => $element->reporting_unit,
                                "reporting_symbol" => $element->reporting_symbol,
                                "recheck" => 0,
                                "analyte_status_contracted" => $lab->is_external ?? 0,
                                "lab_section_id" => $element->lab_section_id,
                                "parameters_order" => $element->level,
                                "remark_is_manual" => $element->remark_is_manual

                            ];
                            $result = Result::create($insert_result);
                        }

                        $analysis_max_report_time = AnalysisType::whereIn('id', $analysis_types_arr)->max('reporting_time');
                        $analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $analysis_types_arr)->max('reporting_time');
                        $maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
                    }
                }
            }
            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $batch->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $batch->id;
            $targetDate->date = \Carbon\Carbon::parse($batch->receipt_date)->addDays($maxReportingTime);
            $targetDate->save();

            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';

            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
            $batch->days_of_analysis = $maxReportingTime;
            $batch->sample_tracking_stage = $stage->id;
            $batch->save();

        }

        if (Schema::hasColumn('quotation_headers', 'is_batch_generate')) {
            QuotationHeader::find($request->quote_id)?->update(['is_batch_generate' => 1]);
        }

        return redirect()->back()->with('success', 'Batch created successfully');


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

    /**
     * @return list<string>
     */
    private function splitCommaSeparatedIds(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (string $id): string => trim($id), explode(',', $value)),
            static fn (string $id): bool => $id !== ''
        ));
    }

    /**
     * @param  list<string>  $elementIds
     * @return list<string>
     */
    private function resolveAnalyteNamesFromElementIds(array $elementIds): array
    {
        $names = [];

        foreach ($elementIds as $elementId) {
            $analyte = AnalysisElements::find($elementId);
            if ($analyte === null || ! isset($analyte->id)) {
                continue;
            }

            $an = getAnalyteByID($analyte->analyte_id);
            if ($an !== null && isset($an->name)) {
                $names[] = $an->name;
            }
        }

        return $names;
    }

    /**
     * @param  list<string>  $analysisTypeIds
     * @return list<string>
     */
    private function resolveAnalysisTypeNames(array $analysisTypeIds): array
    {
        $names = [];

        foreach ($analysisTypeIds as $analysisTypeId) {
            $analysis = AnalysisType::find($analysisTypeId);
            if ($analysis !== null && isset($analysis->id)) {
                $names[] = $analysis->name;
            }
        }

        return $names;
    }

    /**
     * Persist Account Settings label, or custom "Other" text, into payments.
     */
    private function resolvePaymentsValue(Request $request): ?string
    {
        $accountId = trim((string) $request->input('payments_account_id', ''));
        $custom = trim((string) $request->input('payments_custom', ''));

        if ($accountId === '') {
            return $custom !== '' ? $custom : null;
        }

        $options = $this->quotationReportService->accountPaymentOptions();
        foreach ($options as $option) {
            if ((string) ($option['id'] ?? '') !== $accountId) {
                continue;
            }

            if (! empty($option['allows_custom'])) {
                return $custom !== '' ? $custom : null;
            }

            $label = trim((string) ($option['label'] ?? ''));

            return $label !== '' ? $label : null;
        }

        return $custom !== '' ? $custom : null;
    }

    private function recalculateQuotationTotals(QuotationHeader $header): void
    {
        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        $subTotal = 0.0;
        $taxes = 0.0;

        foreach ($details as $detail) {
            $extended = (int) $detail->quantity * (float) $detail->unit_price;
            $subTotal += $extended;

            if ($detail->tax != 0) {
                $taxes += (int) $detail->tax / 100 * (float) $detail->unit_price * (int) $detail->quantity;
            }
        }

        $header->sub_total = $subTotal;
        $header->tax = $taxes;
        $header->total_amount = $subTotal + $taxes;
        $header->save();
    }

    /**
     * Active lab sections available for quotation assignment (SampleAnalysisStage).
     *
     * @return \Illuminate\Support\Collection<int, SampleAnalysisStage>
     */
    private function activeLabSectionsForQuotation()
    {
        return SampleAnalysisStage::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * @param  array<int, mixed>|null  $labSectionIds
     */
    private function syncQuotationLabSections(QuotationHeader $header, ?array $labSectionIds): void
    {
        $ids = SampleAnalysisStage::filterExistingIds($labSectionIds ?? []);
        $header->labSections()->sync($ids);
    }
}
