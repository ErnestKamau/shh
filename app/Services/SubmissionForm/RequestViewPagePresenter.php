<?php

namespace App\Services\SubmissionForm;

use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use Illuminate\Support\Str;

class RequestViewPagePresenter
{
    public const STAGE_REQUESTED = 'Requested';

    public const STAGE_QUOTATION_IN_PROGRESS = 'Quotation In Progress';

    public const STAGE_QUOTATION_SENT = 'Quotation Sent';

    public const STAGE_QUOTATION_UNDER_REVIEW = 'Quotation Under Review';

    public const STAGE_QUOTATION_ACCEPTED = 'Quotation Accepted';

    public const STAGE_READY_FOR_RECEPTION = 'Ready for Reception';

    public const STAGE_SAMPLE_INTEGRITY_CHECK = 'Sample Integrity Check';

    /** @deprecated In Review was removed; acceptance runs on the Sample Integrity Check page. */
    public const STAGE_IN_REVIEW = 'In Review';

    public const STAGE_ACCEPTED = 'Accepted';

    /** @var list<string> */
    public const ENQUIRY_PROGRESS_STAGES = [
        self::STAGE_REQUESTED,
        self::STAGE_QUOTATION_IN_PROGRESS,
        self::STAGE_QUOTATION_SENT,
        self::STAGE_QUOTATION_UNDER_REVIEW,
        self::STAGE_QUOTATION_ACCEPTED,
        self::STAGE_READY_FOR_RECEPTION,
        self::STAGE_ACCEPTED,
    ];

    /** @var list<string> */
    private const SKIP_SECTION_TITLES = [
        'submit & sign',
        'trf storage',
    ];

    /** @var list<string> */
    private const CONTACT_ONLY_FIELD_NAMES = [
        'contact_person',
        'crm_contact_id',
    ];

    /** @var list<string> */
    private const PASTEL_TINTS = ['sand', 'sky', 'sage', 'lavender', 'mist'];

    /** @var array<string, list<string>> */
    private const REQUEST_INFO_FIELD_ALIASES = [
        'client_name' => ['customer_name', 'client_name', 'customer', 'client'],
        'site' => [
            'company_unit_id',
            'company_unit',
            'site_name',
            'site',
            'customer_site',
            'unit_name',
            'crm_unit_name',
        ],
        'address' => ['customer_address', 'address', 'physical_address', 'postal_address'],
        'tel_fax' => ['tel_fax_no', 'tel_fax', 'customer_phone', 'phone', 'telephone', 'phone_number', 'telephone_number'],
        'mobile' => ['mobile_number', 'mobile', 'customer_mobile'],
        'email' => ['customer_email', 'email', 'email_address'],
        'contact_name' => ['contact_person', 'customer_contact_name', 'contact_name'],
        'contact_email' => ['contact_email', 'customer_contact_email'],
        'contact_phone' => ['contact_phone', 'customer_contact_phone', 'contact_telephone'],
        'number_of_samples' => ['number_of_samples', 'no_of_samples', 'sample_count'],
        'received_by' => ['lab_received_by', 'received_by', 'receiving_officer'],
        'customer_representative' => [
            'customer_representative_name',
            'customer_rep_name',
            'customer_representative',
            'customer_rep_contact',
        ],
    ];

    /** @var list<string> */
    private const REQUEST_INFO_CONSUMED_NAMES = [
        'customer_name', 'client_name', 'customer', 'client',
        'company_unit_id', 'company_unit', 'site_name', 'site', 'customer_site', 'unit_name', 'crm_unit_name',
        'customer_address', 'address', 'physical_address', 'postal_address',
        'tel_fax_no', 'tel_fax', 'customer_phone', 'phone', 'telephone', 'phone_number', 'telephone_number',
        'mobile_number', 'mobile', 'customer_mobile',
        'customer_email', 'email', 'email_address',
        'contact_person', 'customer_contact_name', 'contact_name',
        'contact_email', 'customer_contact_email',
        'contact_phone', 'customer_contact_phone', 'contact_telephone',
        'number_of_samples', 'no_of_samples', 'sample_count',
        'lab_received_by', 'received_by', 'receiving_officer',
        'lab_received_datetime', 'lab_sample_condition',
        'customer_representative_name', 'customer_rep_name', 'customer_representative', 'customer_rep_contact',
        'crm_contact_id', 'crm_customer_id', 'remarks',
    ];

    /** @var list<string> */
    private const SOURCE_CHANNEL_LABELS = [
        'walk_in' => 'Walk-in',
        'portal' => 'Portal',
        'offline' => 'Offline',
        'scheduled' => 'Scheduled',
    ];

    public function __construct(
        private readonly SubmissionFormInstance $instance,
        private readonly SubmissionForm $submissionForm,
        private readonly ?SampleSubmissionRequest $commercialEnquiry,
        private readonly ?string $trfPdfUrl = null,
        private readonly bool $canCreateSamples = false,
        private readonly bool $linkedBatchesOutOfSyncWithForm = false,
        private readonly bool $showSampleCollectionLabel = false,
        private readonly bool $isTrfForm = false,
    ) {}

    /**
     * @return array{
     *     request_number: ?string,
     *     form_name: string,
     *     enquiry_stage: ?string,
     *     has_enquiry: bool,
     *     quotation_content_stale: bool
     * }
     */
    public function header(): array
    {
        $stage = $this->enquiryDisplayStatus();

        // Prefer TRF document control number (e.g. TRFW007/26) over commercial REQ-… on the header.
        $requestNumber = $this->nonEmptyString(
            $this->instance->getDocumentControlNumber() ?? $this->instance->form_number
        );

        if ($requestNumber === null && $this->commercialEnquiry !== null) {
            $requestNumber = $this->commercialEnquiry->formatted_number
                ?? ($this->commercialEnquiry->request_number !== null
                    ? 'REQ-'.str_pad((string) $this->commercialEnquiry->request_number, 4, '0', STR_PAD_LEFT)
                    : null);
        }

        return [
            'request_number' => $requestNumber,
            'form_name' => (string) $this->submissionForm->name,
            'enquiry_stage' => $stage,
            'has_enquiry' => $this->commercialEnquiry !== null,
            'quotation_content_stale' => $this->commercialEnquiry?->quotation_content_stale_at !== null,
        ];
    }

    public function enquiryDisplayStatus(): ?string
    {
        if ($this->commercialEnquiry === null) {
            return null;
        }

        if ($this->isAccepted()) {
            return self::STAGE_ACCEPTED;
        }

        // Past physical check-in (legacy In Review) still awaits Accept Samples.
        if ($this->isPastReception()) {
            return self::STAGE_READY_FOR_RECEPTION;
        }

        $raw = (string) ($this->commercialEnquiry->status ?? '');
        $commercial = $this->commercialEnquiry->commercialStatus();

        if ($commercial === 'Sales Order Created' || $raw === 'Received at Lab') {
            return self::STAGE_READY_FOR_RECEPTION;
        }

        return match ($raw) {
            'submitted', 'Submitted' => self::STAGE_REQUESTED,
            SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND, 'Pending Quotation' => self::STAGE_QUOTATION_IN_PROGRESS,
            SampleSubmissionRequest::STATUS_REQUESTED => self::STAGE_REQUESTED,
            SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS => self::STAGE_QUOTATION_IN_PROGRESS,
            SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL => self::STAGE_QUOTATION_IN_PROGRESS,
            SampleSubmissionRequest::STATUS_QUOTATION_SENT => self::STAGE_QUOTATION_SENT,
            SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW => self::STAGE_QUOTATION_UNDER_REVIEW,
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED => self::STAGE_QUOTATION_ACCEPTED,
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION => self::STAGE_READY_FOR_RECEPTION,
            SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK => self::STAGE_SAMPLE_INTEGRITY_CHECK,
            SampleSubmissionRequest::STATUS_IN_REVIEW => self::STAGE_READY_FOR_RECEPTION,
            'received_at_lab' => self::STAGE_ACCEPTED,
            default => $commercial !== '' ? $commercial : ($raw !== '' ? $raw : null),
        };
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array{
     *     customer: ?array{title: string, tint: string, fields: list<array{label: string, value: string, name: ?string, element_type: string, icon: string}>, contact: ?array{name: ?string, email: ?string, phone: ?string}},
     *     sections: list<array{id: string, title: string, tint: string, fields: list<array{label: string, value: string, name: ?string, element_type: string, icon: string}>}>
     * }
     */
    public function sectionCards(array $formData): array
    {
        $customerCard = null;
        $sections = [];
        $tintIndex = 1;

        foreach ($formData['sections'] ?? [] as $section) {
            $title = trim((string) ($section['title'] ?? ''));
            $titleKey = strtolower($title);

            if ($this->shouldSkipSection($section, $titleKey)) {
                continue;
            }

            if ($this->isCustomerDetailsSection($titleKey)) {
                $customerCard = $this->buildCustomerCard($section);
                continue;
            }

            $fields = $this->filledFieldsFromSection($section);
            if ($fields === []) {
                continue;
            }

            $sections[] = [
                'id' => (string) ($section['id'] ?? uniqid('section_', true)),
                'title' => $title !== '' ? $title : 'Details',
                'tint' => self::PASTEL_TINTS[$tintIndex % count(self::PASTEL_TINTS)],
                'fields' => $fields,
            ];
            $tintIndex++;
        }

        return [
            'customer' => $customerCard,
            'sections' => $sections,
        ];
    }

    /**
     * Batch-info-style request summary: fixed fields first, then other filled TRF fields, then remarks.
     *
     * @param  array<string, mixed>  $formData
     * @param  list<array<string, mixed>>  $sampleLines
     * @return array{
     *     fields: list<array{label: string, value: string, name: ?string}>,
     *     remarks: ?string
     * }
     */
    public function requestInfoCard(array $formData, array $sampleLines = []): array
    {
        $indexed = $this->indexFilledFieldsByName($formData);
        $contact = $this->resolveContact(array_values($indexed));

        $clientName = $this->firstFilledAlias($indexed, 'client_name')
            ?? $this->nonEmptyString($this->commercialEnquiry?->customer?->name ?? $this->instance->crmCustomer?->name);

        $address = $this->firstFilledAlias($indexed, 'address');
        $telFax = $this->firstFilledAlias($indexed, 'tel_fax');
        $mobile = $this->firstFilledAlias($indexed, 'mobile');
        $email = $this->firstFilledAlias($indexed, 'email') ?? ($contact['email'] ?? null);
        $contactName = $this->firstFilledAlias($indexed, 'contact_name') ?? ($contact['name'] ?? null);
        $contactEmail = $this->firstFilledAlias($indexed, 'contact_email') ?? ($contact['email'] ?? null);
        $contactPhone = $this->firstFilledAlias($indexed, 'contact_phone') ?? ($contact['phone'] ?? null);

        $sampleType = $this->instance->selectedSampleTypeName();
        if ($sampleType === null) {
            $names = $this->instance->getFormSampleTypeNames();
            $sampleType = $names[0] ?? null;
        }

        $numberOfSamples = $this->resolvePhysicalSampleCount($indexed, $sampleLines);

        $hasBeenPhysicallyReceived = filled($this->commercialEnquiry?->received_by_full_name)
            || filled($this->commercialEnquiry?->received_by_date);

        $receivedBy = null;
        if ($this->commercialEnquiry === null || $hasBeenPhysicallyReceived) {
            $receivedBy = $this->firstFilledAlias($indexed, 'received_by')
                ?? $this->nonEmptyString($this->commercialEnquiry?->received_by_full_name ?? null);
        }

        $customerRepresentative = $this->firstFilledAlias($indexed, 'customer_representative')
            ?? $contactName;

        $fixed = [
            ['label' => 'Client name', 'value' => $clientName, 'name' => 'client_name'],
            ['label' => 'Address', 'value' => $address, 'name' => 'address'],
            ['label' => 'Tel / Fax no.', 'value' => $telFax, 'name' => 'tel_fax'],
            ['label' => 'Mobile number', 'value' => $mobile, 'name' => 'mobile'],
            ['label' => 'Email', 'value' => $email, 'name' => 'email'],
            ['label' => 'Customer contact name', 'value' => $contactName, 'name' => 'contact_name'],
            ['label' => 'Customer contact email', 'value' => $contactEmail, 'name' => 'contact_email'],
            ['label' => 'Customer contact phone', 'value' => $contactPhone, 'name' => 'contact_phone'],
            ['label' => 'Sample type', 'value' => $sampleType, 'name' => 'sample_type'],
            ['label' => 'Number of samples', 'value' => $numberOfSamples, 'name' => 'number_of_samples'],
            ['label' => 'Received by', 'value' => $receivedBy, 'name' => 'received_by'],
            ['label' => 'Customer representative', 'value' => $customerRepresentative, 'name' => 'customer_representative'],
        ];

        $fields = [];
        foreach ($fixed as $field) {
            $value = $this->nonEmptyString($field['value']);
            if ($value === null) {
                continue;
            }
            $fields[] = [
                'label' => $field['label'],
                'value' => $value,
                'name' => $field['name'],
            ];
        }

        foreach ($this->dynamicFilledFields($formData) as $field) {
            $fields[] = $field;
        }

        return [
            'fields' => $fields,
            'remarks' => $this->extractRemarks($formData),
        ];
    }

    /**
     * Context rail for the lab-console request view (no remarks).
     *
     * @param  array<string, mixed>  $formData
     * @param  list<array<string, mixed>>  $sampleLines
     * @return array{
     *     identity: list<array{label: string, value: string, name: string}>,
     *     contact: list<array{label: string, value: string, name: string}>,
     *     when: list<array{label: string, value: string, name: string}>,
     *     refs: list<array{label: string, value: string, name: string}>,
     *     sample_collection: list<array{label: string, value: string, name: ?string}>,
     *     documents: list<array{key: string, label: string, icon: string, href: string, available: bool}>,
     *     more_fields: list<array{label: string, value: string, name: ?string}>
     * }
     */
    public function contextRail(array $formData, array $sampleLines = []): array
    {
        $indexed = $this->indexFilledFieldsByName($formData);
        $info = $this->requestInfoCard($formData, $sampleLines);

        $clientName = $this->firstFilledAlias($indexed, 'client_name')
            ?? $this->nonEmptyString($this->commercialEnquiry?->customer?->name ?? $this->instance->crmCustomer?->name);

        $companyUnitLabel = $this->resolveCompanyUnitLabel($indexed);

        $identity = [];
        if ($clientName !== null) {
            $identity[] = ['label' => 'Client', 'value' => $clientName, 'name' => 'client_name'];
        }
        if ($companyUnitLabel !== null) {
            $identity[] = ['label' => 'Company unit', 'value' => $companyUnitLabel, 'name' => 'company_unit'];
        }

        $when = [];

        // References: Job / Sample nos. after they exist (accepted quotes). TRF form no. lives in the header.
        $refs = [];

        $this->instance->loadMissing(['batches.samples']);

        $jobCodes = $this->instance->batches
            ->pluck('batch_code')
            ->filter(fn ($code): bool => $this->nonEmptyString($code) !== null)
            ->unique()
            ->values()
            ->all();
        if ($jobCodes !== []) {
            $refs[] = [
                'label' => count($jobCodes) === 1 ? 'Job no.' : 'Job nos.',
                'value' => implode(', ', $jobCodes),
                'name' => 'job_numbers',
            ];
        }

        $sampleCodes = $this->instance->batches
            ->flatMap(fn ($batch) => $batch->relationLoaded('samples') ? $batch->samples : collect())
            ->pluck('sample_code')
            ->filter(fn ($code): bool => $this->nonEmptyString($code) !== null)
            ->unique()
            ->values()
            ->all();
        if ($sampleCodes !== []) {
            $refs[] = [
                'label' => count($sampleCodes) === 1 ? 'Sample no.' : 'Sample nos.',
                'value' => implode(', ', $sampleCodes),
                'name' => 'sample_numbers',
            ];
        }

        $contact = [];
        $enquiryContact = $this->commercialEnquiry?->contact;
        $rawContactPerson = $this->firstFilledAlias($indexed, 'contact_name');
        $resolvedContact = $enquiryContact;
        if ($rawContactPerson !== null && Str::isUuid($rawContactPerson)) {
            if ($enquiryContact === null || (string) $enquiryContact->id !== $rawContactPerson) {
                $resolvedContact = \App\Models\CRM\CustomerContact::query()->find($rawContactPerson) ?? $enquiryContact;
            }
        }
        $enquiryContactName = null;
        if ($resolvedContact !== null) {
            $enquiryContactName = $this->nonEmptyString(trim(
                (string) ($resolvedContact->first_name ?? '').' '
                .(string) ($resolvedContact->middle_name ?? '').' '
                .(string) ($resolvedContact->last_name ?? '')
            ));
        }
        $contactName = $enquiryContactName;
        if ($contactName === null && $rawContactPerson !== null && ! Str::isUuid($rawContactPerson)) {
            $contactName = $rawContactPerson;
        }
        $contactEmail = $this->firstFilledAlias($indexed, 'contact_email')
            ?? $this->firstFilledAlias($indexed, 'email')
            ?? $this->nonEmptyString($resolvedContact?->email ?? null);
        $contactPhone = $this->firstFilledAlias($indexed, 'contact_phone')
            ?? $this->firstFilledAlias($indexed, 'mobile')
            ?? $this->nonEmptyString($resolvedContact?->mobile ?? $resolvedContact?->telephone ?? null);

        if ($contactName !== null) {
            $contact[] = [
                'label' => 'Contact person',
                'value' => $contactName,
                'name' => 'contact_name',
                'email' => $contactEmail,
            ];
        }
        if ($contactPhone !== null) {
            $contact[] = ['label' => 'Mobile no.', 'value' => $contactPhone, 'name' => 'contact_phone'];
        }

        $sampleCollection = $this->sampleCollectionRailFields($formData);
        $documents = $this->railDocuments();

        $railNames = array_values(array_filter(array_merge(
            array_column($identity, 'name'),
            array_column($contact, 'name'),
            array_column($when, 'name'),
            array_column($refs, 'name'),
            array_map(
                static fn (array $field): string => strtolower(trim((string) ($field['name'] ?? ''))),
                $sampleCollection
            ),
            self::REQUEST_INFO_FIELD_ALIASES['client_name'],
            self::REQUEST_INFO_FIELD_ALIASES['site'],
            self::REQUEST_INFO_FIELD_ALIASES['contact_name'],
            self::REQUEST_INFO_FIELD_ALIASES['contact_email'],
            self::REQUEST_INFO_FIELD_ALIASES['contact_phone'],
            self::REQUEST_INFO_FIELD_ALIASES['email'],
            self::REQUEST_INFO_FIELD_ALIASES['mobile'],
            ['company_unit', 'sampling_location', 'sample_type', 'number_of_samples'],
        )));

        $moreFields = [];
        foreach ($info['fields'] as $field) {
            $name = strtolower(trim((string) ($field['name'] ?? '')));
            if ($name !== '' && in_array($name, $railNames, true)) {
                continue;
            }
            if ($name === 'remarks') {
                continue;
            }
            $moreFields[] = [
                'label' => $field['label'],
                'value' => $field['value'],
                'name' => $field['name'] ?? null,
            ];
        }

        return [
            'identity' => $identity,
            'contact' => $contact,
            'when' => $when,
            'refs' => $refs,
            'sample_collection' => $sampleCollection,
            'documents' => $documents,
            'more_fields' => $moreFields,
        ];
    }

    /**
     * Default work-canvas tab for a cold load (no ?tab= / no prior user choice).
     */
    public function defaultCanvasTab(): string
    {
        $stage = $this->enquiryDisplayStatus();

        return match ($stage) {
            self::STAGE_QUOTATION_SENT,
            self::STAGE_QUOTATION_UNDER_REVIEW => 'tests',
            default => 'tests',
        };
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return list<array{label: string, value: string, name: ?string}>
     */
    private function sampleCollectionRailFields(array $formData): array
    {
        $fields = [];

        foreach ($formData['sections'] ?? [] as $section) {
            $titleKey = strtolower(trim((string) ($section['title'] ?? '')));
            if ($titleKey !== 'sample collection data') {
                continue;
            }

            foreach ($this->filledFieldsFromSection($section) as $field) {
                $name = strtolower(trim((string) ($field['name'] ?? '')));
                if (in_array($name, SubmissionFormSchemaHelper::miscellaneousTrfFieldNames(), true)) {
                    continue;
                }

                $fields[] = [
                    'label' => $field['label'],
                    'value' => $field['value'],
                    'name' => $field['name'] ?? null,
                ];
            }
        }

        return $fields;
    }

    private function sourceChannelLabel(): ?string
    {
        $raw = strtolower(trim((string) (
            $this->commercialEnquiry?->source_channel
            ?? $this->instance->source_channel
            ?? ''
        )));

        if ($raw === '') {
            return null;
        }

        return self::SOURCE_CHANNEL_LABELS[$raw]
            ?? Str::of($raw)->replace('_', ' ')->title()->toString();
    }

    /**
     * @return list<array{key: string, label: string, icon: string, href: string, available: bool}>
     */
    private function railDocuments(): array
    {
        $documents = [];

        if ($this->isTrfForm) {
            $pdfService = app(\App\Services\Sampleworkflow\TestRequestFormPdfService::class);
            $primaryPdfExists = \Illuminate\Support\Facades\Storage::disk('public')
                ->exists($pdfService->resolveStoragePath($this->instance));
            $href = $this->trfPdfUrl
                ?? route('test-request-form.pdf', $this->instance->id);

            $documents[] = [
                'key' => 'trf_pdf',
                'label' => 'TRF PDF',
                'icon' => 'mdi-file-pdf-box',
                'href' => $href,
                'available' => $primaryPdfExists || filled($this->trfPdfUrl),
            ];
        }

        $quotation = $this->commercialEnquiry?->currentQuotation;
        if ($quotation !== null) {
            $documents[] = [
                'key' => 'quotation',
                'label' => 'Quotation',
                'icon' => 'mdi-file-document-outline',
                'href' => route('quotation.preview.pdf', ['id' => $quotation->id]),
                'available' => true,
            ];
        }

        $batch = $this->instance->batches->first();
        if ($batch instanceof SampleHeader && ! empty($batch->invoice_id)) {
            $documents[] = [
                'key' => 'invoice',
                'label' => 'Invoice',
                'icon' => 'mdi-receipt',
                'href' => route('invoice-sample-header', $batch->id),
                'available' => true,
            ];
        }

        return $documents;
    }

    private function displayableLabel(mixed $value): string
    {
        $candidates = is_array($value)
            ? $value
            : (preg_split('/\s*,\s*/', (string) $value) ?: []);

        $labels = [];

        foreach ($candidates as $candidate) {
            $label = trim((string) $candidate);
            if ($label === '' || Str::isUuid($label) || in_array($label, $labels, true)) {
                continue;
            }

            $labels[] = $label;
        }

        return implode(', ', $labels);
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @return array{
     *     samples: list<array{
     *         number: int,
     *         customer_sample_id: string,
     *         sample_type: string,
     *         analysis_type: string,
     *         sample_quantity: string,
     *         sampling_point: string,
     *         test_category: string,
     *         production_date: string,
     *         expiry_date: string,
     *         batch_number: string,
     *         sample_description: string,
     *         sample_description_html: string,
     *         has_description: bool,
     *         test_codes: list<string>,
     *         parameter_groups: list<array{analysis_type: string, parameters: list<array{code: string, name: string}>}>,
     *         extra_columns: list<array{label: string, value: string}>,
     *         details: list<array{label: string, value: string}>
     *     }>,
     *     count: int,
     *     extra_column_labels: list<string>,
     *     columns: list<array{key: string, label: string, inline?: bool}>,
     *     variant: string
     * }
     */
    public function testSamplesCard(array $sampleLines): array
    {
        $variant = $this->resolveTrfTableVariant();
        $grouped = [];
        // Multi-pickers store selections flat, so labels can arrive as CSV with unresolved ids mixed in.

        foreach ($sampleLines as $line) {
            $typeName = $this->displayableLabel($line['sample_type_name'] ?? $line['sample_type_id'] ?? '');
            $analysisName = $this->displayableLabel($line['analysis_type_name'] ?? $line['analysis_type_id'] ?? '');
            $sampleId = trim((string) ($line['customer_sample_id'] ?? ''));
            $testCodes = $this->resolveTestCodesForLine($line);
            $quantity = $this->formatSampleQuantity($line);
            $parameterGroups = $this->resolveParameterGroupsForLine($line);
            $descriptionHtml = trim((string) ($line['sample_description'] ?? ''));
            $descriptionPlain = trim(strip_tags($descriptionHtml));

            $hasType = $typeName !== '' && strcasecmp($typeName, 'N/A') !== 0;
            $hasAnalysis = $analysisName !== '' && strcasecmp($analysisName, 'N/A') !== 0;

            if ($sampleId === '' && ! $hasType && ! $hasAnalysis && $testCodes === [] && $quantity === '—') {
                continue;
            }

            $groupKey = implode('|', [
                mb_strtolower($sampleId !== '' ? $sampleId : 'row-'.(string) ($line['row_index'] ?? count($grouped))),
                mb_strtolower($hasType ? $typeName : ''),
                mb_strtolower($hasAnalysis ? $analysisName : ''),
            ]);

            $samplingPoint = $this->resolveLineSamplingPoint($line);
            $testCategory = $this->testCategoryLabel($line);
            $productionDate = trim((string) ($line['production_date'] ?? ''));
            $expiryDate = trim((string) (($line['expiration_date'] ?? null) ?: ($line['expiry_date'] ?? '')));
            $batchNumber = trim((string) ($line['batch_number'] ?? ''));
            $sampleTemp = $this->resolveLineSampleTemp($line);
            $extraColumns = $this->buildSampleExtraColumns($line, $variant);

            if (! isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'row_index' => (int) ($line['row_index'] ?? count($grouped)),
                    'customer_sample_id' => $sampleId !== '' ? $sampleId : '—',
                    'sample_type' => $hasType ? $typeName : '—',
                    'analysis_type' => $hasAnalysis ? $analysisName : '—',
                    'analysis_types' => $hasAnalysis ? [$analysisName] : [],
                    'sample_quantity' => $quantity,
                    'sampling_point' => $samplingPoint !== '' ? $samplingPoint : '—',
                    'test_category' => $testCategory !== '' ? $testCategory : '—',
                    'production_date' => $productionDate !== '' ? $productionDate : '—',
                    'expiry_date' => $expiryDate !== '' ? $expiryDate : '—',
                    'batch_number' => $batchNumber !== '' ? $batchNumber : '—',
                    'sample_temp' => $sampleTemp !== '' ? $sampleTemp : '—',
                    'sample_description' => $descriptionPlain !== '' ? $descriptionPlain : '—',
                    'sample_description_html' => $descriptionHtml,
                    'has_description' => $descriptionPlain !== '',
                    'test_codes' => [],
                    'parameter_groups' => $parameterGroups,
                    'extra_columns' => $extraColumns,
                    'details' => $this->buildSampleDetailFields($line, $hasType ? $typeName : '—', $hasAnalysis ? $analysisName : '—', $quantity),
                ];
            } else {
                if ($grouped[$groupKey]['sample_quantity'] === '—' && $quantity !== '—') {
                    $grouped[$groupKey]['sample_quantity'] = $quantity;
                }
                if ($hasAnalysis && ! in_array($analysisName, $grouped[$groupKey]['analysis_types'], true)) {
                    $grouped[$groupKey]['analysis_types'][] = $analysisName;
                    $grouped[$groupKey]['analysis_type'] = implode(', ', $grouped[$groupKey]['analysis_types']);
                }
                if ($grouped[$groupKey]['sampling_point'] === '—' && $samplingPoint !== '') {
                    $grouped[$groupKey]['sampling_point'] = $samplingPoint;
                }
                if ($grouped[$groupKey]['test_category'] === '—' && $testCategory !== '') {
                    $grouped[$groupKey]['test_category'] = $testCategory;
                }
                if ($grouped[$groupKey]['production_date'] === '—' && $productionDate !== '') {
                    $grouped[$groupKey]['production_date'] = $productionDate;
                }
                if ($grouped[$groupKey]['expiry_date'] === '—' && $expiryDate !== '') {
                    $grouped[$groupKey]['expiry_date'] = $expiryDate;
                }
                if ($grouped[$groupKey]['batch_number'] === '—' && $batchNumber !== '') {
                    $grouped[$groupKey]['batch_number'] = $batchNumber;
                }
                if ($grouped[$groupKey]['sample_temp'] === '—' && $sampleTemp !== '') {
                    $grouped[$groupKey]['sample_temp'] = $sampleTemp;
                }
                if (! $grouped[$groupKey]['has_description'] && $descriptionPlain !== '') {
                    $grouped[$groupKey]['sample_description'] = $descriptionPlain;
                    $grouped[$groupKey]['sample_description_html'] = $descriptionHtml;
                    $grouped[$groupKey]['has_description'] = true;
                }
                $grouped[$groupKey]['parameter_groups'] = $this->mergeParameterGroups(
                    $grouped[$groupKey]['parameter_groups'],
                    $parameterGroups
                );
                $grouped[$groupKey]['extra_columns'] = $this->mergeExtraColumns(
                    $grouped[$groupKey]['extra_columns'],
                    $extraColumns
                );
                $grouped[$groupKey]['details'] = $this->mergeDetailFields(
                    $grouped[$groupKey]['details'],
                    $this->buildSampleDetailFields($line, $hasType ? $typeName : '—', $hasAnalysis ? $analysisName : '—', $quantity)
                );
            }

            foreach ($testCodes as $code) {
                if (! in_array($code, $grouped[$groupKey]['test_codes'], true)) {
                    $grouped[$groupKey]['test_codes'][] = $code;
                }
            }
        }

        $extraColumnLabels = [];
        foreach ($grouped as $sample) {
            foreach ($sample['extra_columns'] as $column) {
                if (! in_array($column['label'], $extraColumnLabels, true)) {
                    $extraColumnLabels[] = $column['label'];
                }
            }
        }

        $samples = [];
        $number = 1;
        foreach (array_values($grouped) as $sample) {
            $detailFields = $sample['details'];
            if ($sample['test_codes'] !== []) {
                $detailFields = $this->upsertDetailField($detailFields, 'Tests', implode(', ', $sample['test_codes']));
            }

            $extraByLabel = [];
            foreach ($sample['extra_columns'] as $column) {
                $extraByLabel[$column['label']] = $column['value'];
            }
            $alignedExtra = [];
            foreach ($extraColumnLabels as $label) {
                $alignedExtra[] = [
                    'label' => $label,
                    'value' => $extraByLabel[$label] ?? '—',
                ];
            }

            $samples[] = [
                'number' => $number++,
                'row_index' => $sample['row_index'],
                'customer_sample_id' => $sample['customer_sample_id'],
                'sample_type' => $sample['sample_type'],
                'analysis_type' => $sample['analysis_type'],
                'sample_quantity' => $sample['sample_quantity'],
                'sampling_point' => $sample['sampling_point'],
                'test_category' => $sample['test_category'],
                'production_date' => $sample['production_date'],
                'expiry_date' => $sample['expiry_date'],
                'batch_number' => $sample['batch_number'],
                'sample_temp' => $sample['sample_temp'] ?? '—',
                'sample_description' => $sample['sample_description'],
                'sample_description_html' => $sample['sample_description_html'],
                'has_description' => $sample['has_description'],
                'test_codes' => $sample['test_codes'],
                'parameter_groups' => $sample['parameter_groups'],
                'extra_columns' => $alignedExtra,
                'details' => $detailFields,
            ];
        }

        $columns = $this->testsTableColumns($variant, $extraColumnLabels);

        return [
            'samples' => $samples,
            'count' => count($samples),
            'extra_column_labels' => $extraColumnLabels,
            'columns' => $columns,
            'variant' => $variant,
        ];
    }

    public function canEditSampleRows(): bool
    {
        return ! $this->isPastReception();
    }

    /**
     * @return list<array{key: string, label: string, inline?: bool}>
     */
    private function testsTableColumns(string $variant, array $extraColumnLabels): array
    {
        $columns = [
            ['key' => 'actions', 'label' => 'Actions'],
            ['key' => 'sample_type', 'label' => 'Sample type'],
            ['key' => 'analysis_type', 'label' => 'Analysis types'],
            ['key' => 'sample_description', 'label' => 'Sample description', 'inline' => true],
            ['key' => 'sample_quantity', 'label' => 'Sample quantity'],
            ['key' => 'sampling_point', 'label' => 'Sampling point'],
        ];

        if ($variant === 'water') {
            $columns[] = ['key' => 'test_requirements', 'label' => 'TEST REQUIREMENTS'];
            $columns[] = ['key' => 'sample_temp', 'label' => 'SampleTemp(°C)'];
        } else {
            $columns[] = ['key' => 'test_category', 'label' => 'Test category'];
            $columns[] = ['key' => 'production_date', 'label' => 'Production date'];
            $columns[] = ['key' => 'expiry_date', 'label' => 'Expiry date'];
            $columns[] = ['key' => 'batch_number', 'label' => 'Batch number'];
        }

        foreach ($extraColumnLabels as $label) {
            $columns[] = ['key' => 'extra:'.mb_strtolower($label), 'label' => $label];
        }

        return $columns;
    }

    private function resolveTrfTableVariant(): string
    {
        $code = strtoupper(trim((string) ($this->submissionForm->document_code ?? '')));

        if (in_array($code, [TrfDocumentCodeForSampleType::WATER, TrfDocumentCodeForSampleType::WASTE_WATER], true)) {
            return 'water';
        }

        return 'default';
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveLineSamplingPoint(array $line): string
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        $candidates = [
            $line['sampling_point'] ?? null,
            $line['location'] ?? null,
            $attributes['sampling_point_manual'] ?? null,
            $attributes['sampling_point'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveLineSampleTemp(array $line): string
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        foreach ([
            $line['field_sample_temp'] ?? null,
            $attributes['field_sample_temp'] ?? null,
            $attributes['sample_temp'] ?? null,
            $attributes['sample_temperature'] ?? null,
        ] as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * Test category is a checkbox group, so it is stored as a slug CSV.
     *
     * @param  array<string, mixed>  $line
     */
    private function testCategoryLabel(array $line): string
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        foreach ([
            $line['parameter_category'] ?? null,
            $line['test_requirements'] ?? null,
            $line['test_category'] ?? null,
            $attributes['test_category'] ?? null,
            $attributes['test_requirements'] ?? null,
        ] as $candidate) {
            $label = SubmissionFormSchemaHelper::testCategoryLabel($candidate);
            if ($label !== '') {
                return $label;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function formatSampleQuantity(array $line): string
    {
        $qty = trim((string) ($line['sample_quantity'] ?? ''));
        $unit = trim((string) ($line['sample_quantity_unit'] ?? ''));

        if ($qty === '' && $unit === '') {
            return '—';
        }

        if ($qty !== '' && $unit !== '') {
            return $qty.' '.$unit;
        }

        return $qty !== '' ? $qty : $unit;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<array{label: string, value: string}>
     */
    private function buildSampleDetailFields(array $line, string $sampleType, string $analysisType, string $quantity): array
    {
        $fields = [
            ['label' => 'Customer sample ID', 'value' => trim((string) ($line['customer_sample_id'] ?? ''))],
            ['label' => 'Sample quantity', 'value' => $quantity === '—' ? '' : $quantity],
            ['label' => 'Sample temp (°C)', 'value' => $this->lineAttributeOrField($line, ['field_sample_temp', 'sample_temp', 'sample_temperature'])],
            ['label' => 'State of sample', 'value' => trim((string) ($line['state_of_sample'] ?? ''))],
            ['label' => 'Batch number', 'value' => trim((string) ($line['batch_number'] ?? ''))],
            ['label' => 'Production date', 'value' => trim((string) ($line['production_date'] ?? ''))],
            ['label' => 'Expiration date', 'value' => trim((string) ($line['expiration_date'] ?? ''))],
            ['label' => 'Sampling point / location', 'value' => trim((string) (($line['sampling_point'] ?? null) ?: ($line['location'] ?? '')))],
            ['label' => 'Test category', 'value' => $this->testCategoryLabel($line)],
            ['label' => 'Sample type', 'value' => $sampleType === '—' ? '' : $sampleType],
            ['label' => 'Analysis type', 'value' => $analysisType === '—' ? '' : $analysisType],
            ['label' => 'Sample description', 'value' => trim(strip_tags((string) ($line['sample_description'] ?? '')))],
            ['label' => 'Sample condition', 'value' => trim((string) ($line['sample_condition'] ?? ''))],
        ];

        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        foreach ($attributes as $key => $value) {
            if (in_array($key, ['analysis_element_ids', 'test_requirements', 'parameters', 'test_category', 'food_sample_type'], true)) {
                continue;
            }
            if (is_array($value)) {
                continue;
            }
            $trimmed = trim((string) $value);
            if ($trimmed === '') {
                continue;
            }
            $fields[] = [
                'label' => ucwords(str_replace('_', ' ', (string) $key)),
                'value' => $trimmed,
            ];
        }

        return array_values(array_filter(
            $fields,
            fn (array $field): bool => $field['value'] !== '' && strcasecmp($field['value'], 'N/A') !== 0
        ));
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  list<string>  $keys
     */
    private function lineAttributeOrField(array $line, array $keys): string
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        foreach ($keys as $key) {
            if (! empty($line[$key])) {
                return trim((string) $line[$key]);
            }
            if (! empty($attributes[$key])) {
                return trim((string) $attributes[$key]);
            }
        }

        return '';
    }

    /**
     * @param  list<array{label: string, value: string}>  $existing
     * @param  list<array{label: string, value: string}>  $incoming
     * @return list<array{label: string, value: string}>
     */
    private function mergeDetailFields(array $existing, array $incoming): array
    {
        foreach ($incoming as $field) {
            $existing = $this->upsertDetailField($existing, $field['label'], $field['value']);
        }

        return $existing;
    }

    /**
     * @param  list<array{label: string, value: string}>  $fields
     * @return list<array{label: string, value: string}>
     */
    private function upsertDetailField(array $fields, string $label, string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return $fields;
        }

        foreach ($fields as $index => $field) {
            if (strcasecmp($field['label'], $label) === 0) {
                $fields[$index]['value'] = $value;

                return $fields;
            }
        }

        $fields[] = ['label' => $label, 'value' => $value];

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function resolveTestCodesForLine(array $line): array
    {
        $ids = [];
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        if (! empty($attributes['analysis_element_ids']) && is_array($attributes['analysis_element_ids'])) {
            foreach ($attributes['analysis_element_ids'] as $id) {
                $id = trim((string) $id);
                if ($id !== '') {
                    $ids[] = $id;
                }
            }
        }

        if ($ids === [] && ! empty($line['analysis_element_id'])) {
            $ids[] = (string) $line['analysis_element_id'];
        }

        $codes = [];

        if ($ids !== []) {
            $elements = \App\AnalysisElements::query()
                ->with('analyte')
                ->whereIn('id', array_values(array_unique($ids)))
                ->get();

            foreach ($elements as $element) {
                $code = trim((string) ($element->analyte?->code ?? ''));
                if ($code === '') {
                    $code = trim((string) ($element->analyte?->name ?? ''));
                }
                if ($code !== '' && ! in_array($code, $codes, true)) {
                    $codes[] = $code;
                }
            }
        }

        if ($codes === []) {
            $parameter = trim((string) ($line['parameter_label'] ?? ''));
            if ($parameter !== '' && strcasecmp($parameter, 'N/A') !== 0) {
                foreach (preg_split('/\s*,\s*/', $parameter) ?: [] as $token) {
                    $token = trim((string) $token);
                    if ($token !== '' && ! in_array($token, $codes, true)) {
                        $codes[] = $token;
                    }
                }
            }
        }

        return $codes;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<array{analysis_type: string, parameters: list<array{code: string, name: string}>}>
     */
    private function resolveParameterGroupsForLine(array $line): array
    {
        $ids = [];
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        if (! empty($attributes['analysis_element_ids']) && is_array($attributes['analysis_element_ids'])) {
            foreach ($attributes['analysis_element_ids'] as $id) {
                $id = trim((string) $id);
                if ($id !== '') {
                    $ids[] = $id;
                }
            }
        }

        if ($ids === [] && ! empty($line['analysis_element_id'])) {
            $ids[] = (string) $line['analysis_element_id'];
        }

        $groups = [];

        if ($ids !== []) {
                $elements = \App\AnalysisElements::query()
                ->with(['analyte', 'analysis_type', 'mmethod', 'ltmethod', 'labSection'])
                ->whereIn('id', array_values(array_unique($ids)))
                ->get();

            foreach ($elements as $element) {
                $analysisType = trim((string) ($element->analysis_type?->name ?? $line['analysis_type_name'] ?? 'Parameters'));
                if ($analysisType === '') {
                    $analysisType = 'Parameters';
                }

                $name = trim((string) ($element->analyte?->name ?? ''));
                $reportDisplay = trim((string) ($element->report_display_name ?? ''));
                if ($reportDisplay === '') {
                    $reportDisplay = trim((string) ($element->analyte?->plainReportDisplay() ?? $element->analyte?->code ?? ''));
                }
                $methodName = trim((string) ($element->mmethod?->name ?? $element->ltmethod?->name ?? ''));
                $reportingUnit = trim((string) ($element->reporting_unit ?? ''));
                $tat = $element->reporting_time !== null && $element->reporting_time !== ''
                    ? (string) $element->reporting_time
                    : '';
                $loq = $element->hod !== null && $element->hod !== ''
                    ? (string) $element->hod
                    : '';

                if ($name === '' && $reportDisplay === '') {
                    continue;
                }

                if (! isset($groups[$analysisType])) {
                    $groups[$analysisType] = [
                        'analysis_type' => $analysisType,
                        'parameters' => [],
                    ];
                }

                $groups[$analysisType]['parameters'][] = [
                    'name' => $name !== '' ? $name : $reportDisplay,
                    'report_display_name' => $reportDisplay !== '' ? $reportDisplay : '—',
                    'method' => $methodName !== '' ? $methodName : '—',
                    'reporting_unit' => $reportingUnit !== '' ? $reportingUnit : '—',
                    'tat' => $tat !== '' ? $tat.'d' : '—',
                    'loq' => $loq !== '' ? $loq : '—',
                    // Legacy keys kept for older Alpine markup.
                    'code' => $reportDisplay !== '' ? $reportDisplay : $name,
                ];
            }
        }

        if ($groups === []) {
            $fallbackType = trim((string) ($line['analysis_type_name'] ?? 'Parameters'));
            if ($fallbackType === '') {
                $fallbackType = 'Parameters';
            }
            $codes = $this->resolveTestCodesForLine($line);
            if ($codes !== []) {
                $groups[$fallbackType] = [
                    'analysis_type' => $fallbackType,
                    'parameters' => array_map(
                        static fn (string $code): array => ['code' => $code, 'name' => $code],
                        $codes
                    ),
                ];
            }
        }

        return array_values($groups);
    }

    /**
     * @param  list<array{analysis_type: string, parameters: list<array{code: string, name: string}>}>  $existing
     * @param  list<array{analysis_type: string, parameters: list<array{code: string, name: string}>}>  $incoming
     * @return list<array{analysis_type: string, parameters: list<array{code: string, name: string}>}>
     */
    private function mergeParameterGroups(array $existing, array $incoming): array
    {
        $indexed = [];
        foreach ($existing as $group) {
            $indexed[$group['analysis_type']] = $group;
        }

        foreach ($incoming as $group) {
            $type = $group['analysis_type'];
            if (! isset($indexed[$type])) {
                $indexed[$type] = $group;
                continue;
            }

            foreach ($group['parameters'] as $parameter) {
                $exists = false;
                foreach ($indexed[$type]['parameters'] as $known) {
                    if (($known['code'] ?? '') === ($parameter['code'] ?? '')) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    $indexed[$type]['parameters'][] = $parameter;
                }
            }
        }

        return array_values($indexed);
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<array{label: string, value: string}>
     */
    private function buildSampleExtraColumns(array $line, string $variant = 'default'): array
    {
        $knownKeys = [
            'analysis_element_ids', 'test_requirements', 'parameters', 'test_category', 'food_sample_type',
            'sample_temp', 'sample_temperature', 'field_sample_temp', 'sampling_point_manual',
            'contact', 'contact_person', 'contact_name', 'crm_contact_id', 'customer_contact',
        ];
        $knownLabels = [
            'Customer sample ID', 'Sample quantity', 'Sample temp (°C)', 'SampleTemp(°C)', 'State of sample', 'Batch number',
            'Production date', 'Expiration date', 'Sampling point / location', 'Sampling point', 'Test category', 'Sample type',
            'Analysis type', 'Sample description', 'Sample condition', 'Tests', 'Field Sample Temp',
            'Contact', 'Contact Person', 'Contact Name', 'Customer Contact',
        ];

        $columns = [];
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        foreach ($attributes as $key => $value) {
            if (in_array($key, $knownKeys, true) || is_array($value)) {
                continue;
            }
            $trimmed = trim((string) $value);
            if ($trimmed === '' || strcasecmp($trimmed, 'N/A') === 0) {
                continue;
            }
            $label = ucwords(str_replace('_', ' ', (string) $key));
            if (in_array($label, $knownLabels, true)) {
                continue;
            }
            $columns[] = ['label' => $label, 'value' => $trimmed];
        }

        return $columns;
    }

    /**
     * @param  list<array{label: string, value: string}>  $existing
     * @param  list<array{label: string, value: string}>  $incoming
     * @return list<array{label: string, value: string}>
     */
    private function mergeExtraColumns(array $existing, array $incoming): array
    {
        foreach ($incoming as $column) {
            $found = false;
            foreach ($existing as $index => $known) {
                if (strcasecmp($known['label'], $column['label']) === 0) {
                    if (($known['value'] === '' || $known['value'] === '—') && $column['value'] !== '') {
                        $existing[$index]['value'] = $column['value'];
                    }
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $existing[] = $column;
            }
        }

        return $existing;
    }

    /**
     * @param  array<string, array{label: string, value: string, name: ?string, element_type: string, icon: string}>  $indexed
     * @param  list<array<string, mixed>>  $sampleLines
     */
    private function resolvePhysicalSampleCount(array $indexed, array $sampleLines): ?string
    {
        $lineCount = count($sampleLines);
        $enquiryCount = $this->commercialEnquiryPhysicalSampleCount();
        $headerCount = $this->parsePositiveInt($this->firstFilledAlias($indexed, 'number_of_samples'));

        $resolved = max(
            $lineCount,
            $enquiryCount ?? 0,
            $headerCount ?? 0,
        );

        if ($resolved <= 0) {
            return null;
        }

        return (string) $resolved;
    }

    private function commercialEnquiryPhysicalSampleCount(): ?int
    {
        if ($this->commercialEnquiry === null) {
            return null;
        }

        $configCount = count(
            is_array($this->commercialEnquiry->enquiry_sample_configuration)
                ? $this->commercialEnquiry->enquiry_sample_configuration
                : [],
        );
        $lineCount = count(
            is_array($this->commercialEnquiry->sample_lines)
                ? $this->commercialEnquiry->sample_lines
                : [],
        );
        $stored = (int) ($this->commercialEnquiry->number_of_samples ?? 0);

        $count = max($stored, $configCount, $lineCount);

        return $count > 0 ? $count : null;
    }

    private function parsePositiveInt(?string $value): ?int
    {
        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        $parsed = (int) $value;

        return $parsed > 0 ? $parsed : null;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, array{label: string, value: string, name: ?string, element_type: string, icon: string}>
     */
    private function indexFilledFieldsByName(array $formData): array
    {
        $indexed = [];

        foreach ($formData['sections'] ?? [] as $section) {
            foreach ($this->filledFieldsFromSection($section) as $field) {
                $name = strtolower(trim((string) ($field['name'] ?? '')));
                if ($name === '' || isset($indexed[$name])) {
                    continue;
                }
                $indexed[$name] = $field;
            }
        }

        return $indexed;
    }

    /**
     * @param  array<string, array{label: string, value: string, name: ?string, element_type: string, icon: string}>  $indexed
     */
    private function firstFilledAlias(array $indexed, string $aliasKey): ?string
    {
        foreach (self::REQUEST_INFO_FIELD_ALIASES[$aliasKey] ?? [] as $name) {
            if (! empty($indexed[$name]['value'])) {
                return $this->nonEmptyString($indexed[$name]['value']);
            }
        }

        return null;
    }

    /**
     * @param  array<string, array{label: string, value: string, name: ?string, element_type: string, icon: string}>  $indexed
     */
    private function resolveCompanyUnitLabel(array $indexed): ?string
    {
        $raw = $this->firstFilledAlias($indexed, 'site');
        if ($raw === null) {
            $enquiryUnitId = $this->nonEmptyString($this->commercialEnquiry?->crm_company_unit_id ?? null);
            $raw = $enquiryUnitId;
        }

        if ($raw === null) {
            return null;
        }

        if (Str::isUuid($raw)) {
            $name = \App\Models\CRM\CRMCompanyUnit::query()->whereKey($raw)->value('name');

            return $this->nonEmptyString($name) ?? $raw;
        }

        return $raw;
    }

    /**
     * @param  array<string, array{label: string, value: string, name: ?string, element_type: string, icon: string}>  $indexed
     * @param  array<string, mixed>  $formData
     */
    private function resolveSamplingLocationLabel(array $indexed, array $formData): ?string
    {
        $raw = $this->nonEmptyString($indexed['sampling_location']['value'] ?? null);
        if ($raw === null) {
            foreach ($this->sampleCollectionRailFields($formData) as $field) {
                if (($field['name'] ?? '') === 'sampling_location') {
                    $raw = $this->nonEmptyString($field['value'] ?? null);
                    break;
                }
            }
        }

        if ($raw === null) {
            return null;
        }

        if (Str::isUuid($raw)) {
            $point = \App\Models\CRM\SamplePoint::query()->find($raw);

            return $this->nonEmptyString($point?->display_name ?? $point?->name) ?? $raw;
        }

        return $raw;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return list<array{label: string, value: string, name: ?string}>
     */
    private function dynamicFilledFields(array $formData): array
    {
        $fields = [];
        $seenNames = [];

        foreach ($formData['sections'] ?? [] as $section) {
            $titleKey = strtolower(trim((string) ($section['title'] ?? '')));
            if ($this->shouldSkipSection($section, $titleKey)) {
                continue;
            }

            foreach ($this->filledFieldsFromSection($section) as $field) {
                $name = strtolower(trim((string) ($field['name'] ?? '')));
                if ($name !== '' && in_array($name, self::REQUEST_INFO_CONSUMED_NAMES, true)) {
                    continue;
                }
                if ($name !== '' && isset($seenNames[$name])) {
                    continue;
                }
                if ($name !== '') {
                    $seenNames[$name] = true;
                }

                $fields[] = [
                    'label' => $field['label'],
                    'value' => $field['value'],
                    'name' => $field['name'],
                ];
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function extractRemarks(array $formData): ?string
    {
        foreach ($formData['sections'] ?? [] as $section) {
            $titleKey = strtolower(trim((string) ($section['title'] ?? '')));
            if ($titleKey !== 'submit & sign') {
                continue;
            }

            foreach ($this->filledFieldsFromSection($section) as $field) {
                $name = strtolower(trim((string) ($field['name'] ?? '')));
                if ($name === 'remarks' || str_contains(strtolower($field['label']), 'remark')) {
                    return $this->nonEmptyString($field['value']);
                }
            }
        }

        foreach ($formData['sections'] ?? [] as $section) {
            foreach ($this->filledFieldsFromSection($section) as $field) {
                $name = strtolower(trim((string) ($field['name'] ?? '')));
                if ($name === 'remarks') {
                    return $this->nonEmptyString($field['value']);
                }
            }
        }

        return null;
    }

    /**
     * @return array{
     *     primary: ?array{key: string, label: string, icon: string, type: string, wire: ?string, href: ?string, confirm: ?string},
     *     secondary: list<array{key: string, label: string, icon: string, type: string, wire: ?string, href: ?string, confirm: ?string}>,
     *     danger: ?array{key: string, label: string, icon: string, type: string, wire: ?string, href: ?string, confirm: ?string}
     * }
     */
    public function nextStepActions(string $workflowBoardStatus): array
    {
        $stage = $this->enquiryDisplayStatus();
        $secondary = [];
        $primary = null;

        $trfActions = $this->trfActions();
        $quotation = $this->viewQuotationAction();
        $invoice = $this->viewInvoiceAction();
        $batch = $this->viewBatchAction();
        $label = $this->sampleLabelAction();
        $createJob = $this->createJobAction();
        $applyBatches = $this->applyBatchesAction();

        if ($stage === null) {
            $primary = null;
            foreach (array_filter([$quotation, $invoice, ...$trfActions, $batch, $label, $createJob, $applyBatches]) as $action) {
                if ($primary !== null && ($action['key'] ?? null) === ($primary['key'] ?? null)) {
                    continue;
                }
                if (in_array($action['key'] ?? null, ['view_quotation', 'view_trf_pdf'], true)) {
                    continue;
                }
                $secondary[] = $action;
            }

            return [
                'primary' => $primary,
                'secondary' => $secondary,
                'danger' => $this->rejectAction(),
            ];
        }

        $commercialActions = $this->commercialActionsForStage($stage, $workflowBoardStatus);

        if ($commercialActions['primary'] !== null) {
            $primary = $commercialActions['primary'];
        }

        foreach ($commercialActions['secondary'] as $action) {
            $secondary[] = $action;
        }

        foreach (array_filter([$quotation, $invoice, ...$trfActions, $batch, $label, $createJob, $applyBatches]) as $action) {
            if ($primary !== null && ($action['key'] ?? null) === ($primary['key'] ?? null)) {
                continue;
            }
            if ($this->actionListContainsKey($secondary, $action['key'])) {
                continue;
            }
            if (in_array($action['key'] ?? null, ['view_quotation', 'view_trf_pdf'], true)) {
                continue;
            }
            if (! $this->isActionAllowedInStage($action['key'], $stage)) {
                continue;
            }
            $secondary[] = $action;
        }

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'danger' => $this->rejectAction(),
        ];
    }

    private function isAccepted(): bool
    {
        if ($this->commercialEnquiry === null) {
            return false;
        }

        $raw = (string) ($this->commercialEnquiry->status ?? '');
        if ($raw === 'received_at_lab' || $this->commercialEnquiry->commercialStatus() === 'Accepted') {
            return true;
        }

        return $this->instance->analysisAcceptanceForms->contains(
            fn ($form): bool => (string) $form->status === AnalysisAcceptanceForm::STATUS_COMPLETED
        );
    }

    private function isPastReception(): bool
    {
        if ($this->commercialEnquiry === null) {
            return false;
        }

        if (! empty($this->commercialEnquiry->sample_header_id)) {
            return true;
        }

        $instanceStatus = strtolower((string) $this->instance->status);
        if (in_array($instanceStatus, ['received', 'in_review', 'in_additional_info', 'approved', 'rejected', 'complete'], true)) {
            return in_array((string) $this->commercialEnquiry->status, [
                SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
                SampleSubmissionRequest::STATUS_IN_REVIEW,
                'Received at Lab',
                'received_at_lab',
            ], true)
                || $this->instance->batches->isNotEmpty()
                || $this->instance->analysisAcceptanceForms->isNotEmpty();
        }

        if ($this->instance->batches->contains(fn ($batch) => (string) $batch->status === 'Samples Request Review')) {
            return true;
        }

        if ($this->instance->analysisAcceptanceForms->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private function shouldSkipSection(array $section, string $titleKey): bool
    {
        if (in_array($titleKey, self::SKIP_SECTION_TITLES, true)) {
            return true;
        }

        if (($section['section_type'] ?? null) === 'rows_section') {
            return true;
        }

        foreach ($section['element_holders'] ?? [] as $holder) {
            if (($holder['holder_type'] ?? null) === 'rows') {
                return true;
            }
        }

        return false;
    }

    private function isCustomerDetailsSection(string $titleKey): bool
    {
        return $titleKey === 'customer details' || $titleKey === 'crm customer';
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array{title: string, tint: string, fields: list<array{label: string, value: string, name: ?string, element_type: string, icon: string}>, contact: ?array{name: ?string, email: ?string, phone: ?string}}|null
     */
    private function buildCustomerCard(array $section): ?array
    {
        $allFields = $this->filledFieldsFromSection($section);
        $contact = $this->resolveContact($allFields);

        $customerFields = [];
        foreach ($allFields as $field) {
            $name = strtolower((string) ($field['name'] ?? ''));
            if (in_array($name, self::CONTACT_ONLY_FIELD_NAMES, true)) {
                continue;
            }

            if ($contact !== null && $this->fieldDuplicatesContact($field, $contact)) {
                continue;
            }

            $customerFields[] = $field;
        }

        if ($customerFields === [] && $contact === null) {
            return null;
        }

        return [
            'title' => 'Customer',
            'tint' => 'sand',
            'fields' => $customerFields,
            'contact' => $contact,
        ];
    }

    /**
     * @param  list<array{label: string, value: string, name: ?string, element_type: string, icon: string}>  $fields
     * @return array{name: ?string, email: ?string, phone: ?string}|null
     */
    private function resolveContact(array $fields): ?array
    {
        $name = null;
        $email = null;
        $phone = null;

        $crmContact = $this->commercialEnquiry?->contact;
        if ($crmContact !== null) {
            $name = $this->nonEmptyString(trim(implode(' ', array_filter([
                $crmContact->first_name ?? null,
                $crmContact->middle_name ?? null,
                $crmContact->last_name ?? null,
            ]))));
            $email = $this->nonEmptyString($crmContact->email ?? null);
            $phone = $this->nonEmptyString($crmContact->mobile ?? $crmContact->telephone ?? null);
        }

        foreach ($fields as $field) {
            $fieldName = strtolower((string) ($field['name'] ?? ''));
            $value = $field['value'];

            if ($name === null && in_array($fieldName, ['contact_person', 'customer_representative_name'], true)) {
                $name = $value;
            }
            if ($email === null && in_array($fieldName, ['customer_email', 'contact_email', 'email'], true)) {
                $email = $value;
            }
            if ($phone === null && in_array($fieldName, ['customer_phone', 'mobile_number', 'contact_phone', 'telephone'], true)) {
                $phone = $value;
            }
        }

        if ($name === null && $email === null && $phone === null) {
            return null;
        }

        return [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ];
    }

    /**
     * @param  array{label: string, value: string, name: ?string, element_type: string, icon: string}  $field
     * @param  array{name: ?string, email: ?string, phone: ?string}  $contact
     */
    private function fieldDuplicatesContact(array $field, array $contact): bool
    {
        $value = strtolower(trim($field['value']));
        $name = strtolower((string) ($field['name'] ?? ''));

        if (in_array($name, ['contact_person', 'customer_email', 'customer_phone', 'mobile_number'], true)) {
            foreach (['name', 'email', 'phone'] as $key) {
                $contactValue = strtolower(trim((string) ($contact[$key] ?? '')));
                if ($contactValue !== '' && $contactValue === $value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $section
     * @return list<array{label: string, value: string, name: ?string, element_type: string, icon: string}>
     */
    private function filledFieldsFromSection(array $section): array
    {
        $fields = [];

        foreach ($section['element_holders'] ?? [] as $holder) {
            if (($holder['holder_type'] ?? null) === 'rows') {
                continue;
            }

            foreach ($holder['elements'] ?? [] as $element) {
                $display = $this->elementDisplayValue($element);
                if ($this->isEmptyDisplayValue($display)) {
                    continue;
                }

                $elementType = (string) ($element['element_type'] ?? 'text');
                if ($elementType === 'signature') {
                    continue;
                }

                $fields[] = [
                    'label' => (string) ($element['label'] ?? $element['name'] ?? 'Field'),
                    'value' => $display,
                    'name' => isset($element['name']) ? (string) $element['name'] : null,
                    'element_type' => $elementType,
                    'icon' => $this->iconForField($element),
                ];
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $element
     */
    private function elementDisplayValue(array $element): string
    {
        $saved = $element['saved_values'][0] ?? null;
        if (! is_array($saved)) {
            return '';
        }

        $display = $saved['display_value'] ?? $saved['value'] ?? '';

        if (is_array($display)) {
            return implode(', ', array_filter(array_map('strval', $display)));
        }

        return trim((string) $display);
    }

    private function isEmptyDisplayValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' || strcasecmp($trimmed, 'N/A') === 0;
    }

    /**
     * @param  array<string, mixed>  $element
     */
    private function iconForField(array $element): string
    {
        $name = strtolower((string) ($element['name'] ?? ''));
        $type = (string) ($element['element_type'] ?? '');

        if (str_contains($name, 'email')) {
            return 'mdi-email-outline';
        }
        if (str_contains($name, 'phone') || str_contains($name, 'mobile') || str_contains($name, 'tel')) {
            return 'mdi-phone-outline';
        }
        if (str_contains($name, 'address')) {
            return 'mdi-map-marker-outline';
        }
        if (str_contains($name, 'date') || $type === 'date') {
            return 'mdi-calendar';
        }
        if (str_contains($name, 'time') || $type === 'time') {
            return 'mdi-clock-outline';
        }
        if (str_contains($name, 'location') || str_contains($name, 'sampling_location')) {
            return 'mdi-map-marker-radius-outline';
        }
        if (in_array($type, ['checkbox', 'radio', 'select'], true)) {
            return 'mdi-check-circle-outline';
        }
        if (in_array($type, ['file', 'camera_photo', 'image_upload'], true)) {
            return 'mdi-paperclip';
        }

        return 'mdi-information-outline';
    }

    private function nonEmptyString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return array{primary: ?array<string, mixed>, secondary: list<array<string, mixed>>}
     */
    private function commercialActionsForStage(string $stage, string $workflowBoardStatus): array
    {
        $primary = null;
        $secondary = [];

        $earlyQuote = in_array($stage, [
            self::STAGE_REQUESTED,
            self::STAGE_QUOTATION_IN_PROGRESS,
            self::STAGE_QUOTATION_SENT,
            self::STAGE_QUOTATION_UNDER_REVIEW,
        ], true);

        if ($earlyQuote) {
            $walkIn = $stage === self::STAGE_QUOTATION_SENT
                && strtolower((string) ($this->commercialEnquiry?->source_channel ?? '')) === 'walk_in';
            $processEnquiryLabel = $stage === self::STAGE_QUOTATION_UNDER_REVIEW
                ? 'Review quotation'
                : 'Process enquiry';

            if ($walkIn) {
                $primary = $this->action('record_walk_in_acceptance', 'Record quotation acceptance', 'mdi-check-decagram', 'wire', 'recordWalkInQuotationAcceptance');
                $secondary[] = $this->action('process_enquiry', $processEnquiryLabel, 'mdi-file-chart-outline', 'wire', 'openProcessEnquiry');
            } else {
                $primary = $this->action('process_enquiry', $processEnquiryLabel, 'mdi-file-chart-outline', 'wire', 'openProcessEnquiry');
            }

            $syncAction = $this->syncRequestQuotationAction();
            if ($syncAction !== null) {
                $secondary[] = $syncAction;
            }
        } elseif ($stage === self::STAGE_QUOTATION_ACCEPTED) {
            $primary = $this->action('record_po', 'Record PO', 'mdi-file-document-edit-outline', 'wire', 'openPoCaptureModal');
        } elseif ($stage === self::STAGE_READY_FOR_RECEPTION) {
            $primary = $this->action(
                'receive_samples',
                'Receive Samples',
                'mdi-package-down',
                'wire',
                'openAcceptSampleWizard'
            );
        }

        return [
            'primary' => $primary,
            'secondary' => $secondary,
        ];
    }

    /**
     * @return ?array{key: string, label: string, icon: string, type: string, wire: ?string, href: ?string, confirm: ?string, target_blank: bool}
     */
    private function syncRequestQuotationAction(): ?array
    {
        if ($this->commercialEnquiry === null) {
            return null;
        }

        $syncService = app(\App\Services\Commercial\EnquiryQuotationContentSyncService::class);
        if (! $syncService->canSync($this->commercialEnquiry)) {
            return null;
        }

        return $this->action(
            'sync_request_quotation',
            'Sync Request Quotation',
            'mdi-sync',
            'wire',
            'syncRequestQuotation',
            null,
            'Rebuild this request\'s tests/parameters from the linked quotation? Staff-entered TRF answers will be kept.',
        );
    }

    private function isActionAllowedInStage(string $key, string $stage): bool
    {
        $blockedByStage = match ($stage) {
            self::STAGE_READY_FOR_RECEPTION => [
                'process_enquiry',
                'record_po',
                'record_walk_in_acceptance',
                'send_for_review',
                'accept_samples',
            ],
            self::STAGE_SAMPLE_INTEGRITY_CHECK => [
                'process_enquiry',
                'record_po',
                'record_walk_in_acceptance',
                'send_for_review',
                'receive_samples',
                'accept_samples',
            ],
            self::STAGE_ACCEPTED => [
                'process_enquiry',
                'record_po',
                'record_walk_in_acceptance',
                'receive_samples',
                'send_for_review',
                'accept_samples',
            ],
            self::STAGE_QUOTATION_ACCEPTED => [
                'process_enquiry',
                'receive_samples',
                'send_for_review',
                'accept_samples',
            ],
            self::STAGE_REQUESTED,
            self::STAGE_QUOTATION_IN_PROGRESS,
            self::STAGE_QUOTATION_SENT,
            self::STAGE_QUOTATION_UNDER_REVIEW => [
                'record_po',
                'receive_samples',
                'send_for_review',
                'accept_samples',
                'create_job',
            ],
            default => [],
        };

        return ! in_array($key, $blockedByStage, true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function trfActions(): array
    {
        if (! $this->isTrfForm) {
            return [];
        }

        $pdfService = app(\App\Services\Sampleworkflow\TestRequestFormPdfService::class);
        $primaryPdfExists = \Illuminate\Support\Facades\Storage::disk('public')
            ->exists($pdfService->resolveStoragePath($this->instance));

        $actions = [
            $this->action(
                'generate_trf',
                $primaryPdfExists ? 'Regenerate Test Request Form' : 'Generate Test Request Form',
                'mdi-file-document-outline',
                'wire',
                'openGenerateTrfOrientationModal'
            ),
            $this->action(
                'view_trf_pdf',
                'Test Request Form',
                'mdi-file-pdf-box',
                'href',
                null,
                route('test-request-form.pdf', $this->instance->id),
                null,
                true
            ),
        ];

        if ($primaryPdfExists || $this->trfPdfUrl) {
            $actions[] = $this->action('send_trf', 'Send Test Request Form', 'mdi-email-send-outline', 'wire', 'sendTrfPdfToCustomer');
        }

        return $actions;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function viewQuotationAction(): ?array
    {
        $quotation = $this->commercialEnquiry?->currentQuotation;
        if ($quotation === null) {
            return null;
        }

        return $this->action(
            'view_quotation',
            'View quotation',
            'mdi-file-pdf-box',
            'href',
            null,
            route('quotation.preview.pdf', ['id' => $quotation->id]),
            null,
            true
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function viewInvoiceAction(): ?array
    {
        $batch = $this->instance->batches->first();
        if (! $batch instanceof SampleHeader || empty($batch->invoice_id)) {
            return null;
        }

        return $this->action(
            'view_invoice',
            'View invoice',
            'mdi-receipt',
            'href',
            null,
            route('invoice-sample-header', $batch->id),
            null,
            true
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function viewBatchAction(): ?array
    {
        $batch = $this->instance->batches->first();
        if ($batch === null) {
            return null;
        }

        return $this->action(
            'view_batch',
            'View sample batch',
            'mdi-flask',
            'href',
            null,
            route('view-batch-details', [
                'batch' => $batch->id,
                'client' => 0,
                'portal' => 0,
                'status' => $batch->status,
            ])
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sampleLabelAction(): ?array
    {
        if (! $this->showSampleCollectionLabel) {
            return null;
        }

        return $this->action(
            'sample_label',
            'Print labels',
            'mdi-printer',
            'modal',
            null,
            null,
            null,
            false,
            '#print-sample-labels-modal'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function createJobAction(): ?array
    {
        if (! $this->canCreateSamples) {
            return null;
        }

        return $this->action('create_job', 'Create job / batch', 'mdi-flask', 'create_samples');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function applyBatchesAction(): ?array
    {
        if ($this->instance->batches->isEmpty() || ! $this->linkedBatchesOutOfSyncWithForm) {
            return null;
        }

        return $this->action(
            'apply_batches',
            'Apply form to linked batches',
            'mdi-sync',
            'form_post',
            null,
            route('submission-forms.instances.apply-to-batches', $this->instance->id),
            'Update all linked batches from the current saved form data?'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function rejectAction(): ?array
    {
        $status = strtolower((string) $this->instance->status);
        if (in_array($status, ['rejected', 'cancelled', 'approved'], true)) {
            return null;
        }

        return $this->action(
            'reject',
            'Reject request',
            'mdi-close-circle-outline',
            'wire',
            'openRejectWizard'
        );
    }

    /**
     * @return array{key: string, label: string, icon: string, type: string, wire: ?string, href: ?string, confirm: ?string, target_blank: bool}
     */
    private function action(
        string $key,
        string $label,
        string $icon,
        string $type,
        ?string $wire = null,
        ?string $href = null,
        ?string $confirm = null,
        bool $targetBlank = false,
        ?string $modal = null,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'type' => $type,
            'wire' => $wire,
            'href' => $href,
            'confirm' => $confirm,
            'target_blank' => $targetBlank,
            'modal' => $modal,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $actions
     */
    private function actionListContainsKey(array $actions, string $key): bool
    {
        foreach ($actions as $action) {
            if (($action['key'] ?? null) === $key) {
                return true;
            }
        }

        return false;
    }
}
