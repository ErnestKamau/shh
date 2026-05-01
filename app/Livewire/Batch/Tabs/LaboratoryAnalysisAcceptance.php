<?php

namespace App\Livewire\Batch\Tabs;

use App\AnalysisType;
use App\BatchAttachment;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class LaboratoryAnalysisAcceptance extends Component
{
    public SampleHeader $batch;

    public int $currentPart = 1;

    public bool $readOnly = false;

    public array $form = [
        'customer_name' => '',
        'customer_address' => '',
        'customer_email' => '',
        'number_of_samples' => 0,
        'type_of_samples' => '',
        'date_of_sampling' => '',
        'date' => '',
        'tel' => '',
        'mode_of_work' => 'Normal',
        'deviation_answer' => '',
        'customer_certification_text' => 'I certify that the above request is correct.',
        'customer_name_certified' => '',
        'customer_signature' => '',
        'customer_date' => '',
        'conformity_request' => '',
        'manager_capability' => '',
        'laboratory_name' => '',
        'laboratory_manager_name' => '',
        'manager_signature' => '',
        'manager_date' => '',
    ];

    /**
     * @var array<int, array{analysis_type_id: string, label: string, price: float, selected: bool}>
     */
    public array $parameters = [];

    public ?string $attachmentUrl = null;

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;

        $this->hydrateDefaults();
        $this->loadRequestedParameters();
        $this->loadExistingDraft();
        $this->loadExistingAttachment();

        if (Auth::user() && (int) Auth::user()->is_client === 1) {
            $this->readOnly = true;
        }
    }

    public function setPart(int $part): void
    {
        $this->currentPart = max(1, min(4, $part));
    }

    public function nextPart(): void
    {
        $this->setPart($this->currentPart + 1);
    }

    public function previousPart(): void
    {
        $this->setPart($this->currentPart - 1);
    }

    public function saveDraft(): void
    {
        if ($this->readOnly) {
            return;
        }

        $this->persistDraftPayload();
        session()->flash('success', 'Laboratory Analysis Acceptance draft saved.');
    }

    public function submitForm(): void
    {
        if ($this->readOnly) {
            return;
        }

        $this->validate($this->rules(), $this->messages());

        if (count($this->acceptedParameters()) < 1) {
            session()->flash('error', 'Select at least one accepted parameter before submitting the form.');
            return;
        }

        $payload = $this->buildPayload();
        $draftPath = $this->draftStoragePath();
        Storage::disk('public')->put($draftPath, json_encode($payload, JSON_PRETTY_PRINT));

        $pdfFilename = 'laboratory-analysis-acceptance-batch-' . $this->batch->id . '.pdf';
        $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
        $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('batch.attachments.laboratory-analysis-acceptance-pdf', [
            'batch' => $this->batch,
            'form' => $this->form,
            'requestedParameters' => $this->parameters,
            'acceptedParameters' => $this->acceptedParameters(),
            'rejectedParameters' => $this->rejectedParameters(),
            'acceptedTotal' => $this->acceptedTotal,
        ]);

        Storage::disk('public')->put($pdfStoragePath, $pdf->output());

        $attachmentTypeId = $this->resolveAcceptanceAttachmentTypeId();
        $title = 'Laboratory Analysis Acceptance Form (GCLA/F/03)';

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

        $this->attachmentUrl = $pdfPublicUrl;
        $this->dispatch('attachmentsUpdated');

        session()->flash('success', 'Laboratory Analysis Acceptance form submitted and attached to this batch.');
    }

    public function getAcceptedTotalProperty(): float
    {
        return round(array_sum(array_map(static fn ($row) => (float) ($row['price'] ?? 0), $this->acceptedParameters())), 2);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.laboratory-analysis-acceptance', [
            'acceptedParameters' => $this->acceptedParameters(),
            'rejectedParameters' => $this->rejectedParameters(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function rules(): array
    {
        return [
            'form.customer_name' => 'required|string|max:255',
            'form.customer_email' => 'nullable|email|max:255',
            'form.date' => 'required|date',
            'form.mode_of_work' => 'required|in:Normal,Express',
            'form.deviation_answer' => 'required|in:Yes,No',
            'form.customer_name_certified' => 'required|string|max:255',
            'form.customer_signature' => 'required|string',
            'form.customer_date' => 'required|date',
            'form.conformity_request' => 'required|in:requested,not_requested',
            'form.manager_capability' => 'required|in:has,has_not',
            'form.laboratory_name' => 'required|string|max:255',
            'form.laboratory_manager_name' => 'required|string|max:255',
            'form.manager_signature' => 'required|string',
            'form.manager_date' => 'required|date',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'form.customer_signature.required' => 'Customer signature is required.',
            'form.manager_signature.required' => 'Laboratory manager signature is required.',
        ];
    }

    private function hydrateDefaults(): void
    {
        $this->batch->loadMissing(['client', 'sampleSubmissionRequest.customer', 'sampleSubmissionRequest.contact', 'sample_type']);

        $request = $this->batch->sampleSubmissionRequest;
        $customer = $request?->customer ?: $this->batch->client;
        $contact = $request?->contact;

        $customerName = trim((string) ($customer?->name ?? ''));
        $customerAddress = trim((string) ($customer?->postal_address ?? $request?->physical_address ?? ''));
        $customerEmail = trim((string) ($contact?->email ?? $request?->email ?? $customer?->email ?? ''));

        $contactName = trim((string) ($contact
            ? trim(($contact->first_name ?? '') . ' ' . ($contact->middle_name ?? '') . ' ' . ($contact->last_name ?? ''))
            : ($request?->submitting_officer_full_name ?? $customerName)));

        $tel = trim((string) ($request?->mobile_telephone_no ?? $request?->office_telephone_no ?? $customer?->telephone1 ?? ''));

        $dateOfSampling = $request?->date_of_seizure ? $request->date_of_seizure->format('Y-m-d') : '';

        $this->form['customer_name'] = $customerName;
        $this->form['customer_address'] = $customerAddress;
        $this->form['customer_email'] = $customerEmail;
        $this->form['number_of_samples'] = (int) $this->batch->samples()->count();
        $this->form['type_of_samples'] = (string) ($this->batch->sample_type?->name ?? '');
        $this->form['date_of_sampling'] = $dateOfSampling;
        $this->form['date'] = now()->format('Y-m-d');
        $this->form['tel'] = $tel;
        $this->form['mode_of_work'] = strtolower((string) $this->batch->priority) === 'express' ? 'Express' : 'Normal';

        $this->form['customer_name_certified'] = $contactName !== '' ? $contactName : $customerName;
        $this->form['customer_date'] = now()->format('Y-m-d');
        $this->form['laboratory_name'] = $this->resolveLaboratoryName();
        $this->form['laboratory_manager_name'] = trim((string) (Auth::user()->name ?? ''));
        $this->form['manager_date'] = now()->format('Y-m-d');
    }

    private function resolveLaboratoryName(): string
    {
        $firstSample = $this->batch->samples()->with('lab')->first();
        if ($firstSample && $firstSample->lab) {
            return (string) $firstSample->lab->name;
        }

        return (string) (config('app.name') ?? '');
    }

    private function loadRequestedParameters(): void
    {
        $requested = [];
        $seen = [];

        $request = $this->batch->sampleSubmissionRequest;
        if ($request) {
            $request->loadMissing('requestedAnalyses');

            foreach ($request->requestedAnalyses as $ra) {
                $analysisType = $this->resolveAnalysisTypeFromRequested((string) ($ra->analysis_key ?? ''), (string) ($ra->analysis_label ?? ''));

                if (! $analysisType) {
                    $key = 'label:' . Str::lower(trim((string) $ra->analysis_label));
                    if ($key === 'label:' || isset($seen[$key])) {
                        continue;
                    }

                    $seen[$key] = true;
                    $requested[] = [
                        'analysis_type_id' => '',
                        'label' => (string) $ra->analysis_label,
                        'price' => 0.0,
                        'selected' => false,
                    ];
                    continue;
                }

                $key = 'id:' . (string) $analysisType->id;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $requested[] = [
                    'analysis_type_id' => (string) $analysisType->id,
                    'label' => (string) $analysisType->name,
                    'price' => $this->resolveParameterPrice((string) $analysisType->id),
                    'selected' => false,
                ];
            }
        }

        $sampleDetails = $this->batch->samples()->get(['analysis_type_id']);
        foreach ($sampleDetails as $detail) {
            $ids = array_filter(array_map('trim', explode(',', (string) $detail->analysis_type_id)));
            foreach ($ids as $id) {
                $analysisType = AnalysisType::find($id);
                if (! $analysisType) {
                    continue;
                }

                $key = 'id:' . (string) $analysisType->id;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $requested[] = [
                    'analysis_type_id' => (string) $analysisType->id,
                    'label' => (string) $analysisType->name,
                    'price' => $this->resolveParameterPrice((string) $analysisType->id),
                    'selected' => false,
                ];
            }
        }

        $this->parameters = array_values($requested);
    }

    private function resolveAnalysisTypeFromRequested(string $analysisKey, string $analysisLabel): ?AnalysisType
    {
        $analysisKey = trim($analysisKey);
        $analysisLabel = trim($analysisLabel);

        if ($analysisKey !== '') {
            $found = AnalysisType::where('id', $analysisKey)
                ->orWhere('code', $analysisKey)
                ->first();

            if ($found) {
                return $found;
            }
        }

        if ($analysisLabel !== '') {
            return AnalysisType::where('name', $analysisLabel)
                ->orWhere('short_name', $analysisLabel)
                ->first();
        }

        return null;
    }

    private function resolveParameterPrice(string $analysisTypeId): float
    {
        if ($analysisTypeId === '') {
            return 0.0;
        }

        $customerId = $this->batch->crm_customer_id;
        $sampleTypeId = $this->batch->sample_type_id;

        $pricelistId = DB::table('pricelist_customers')
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->value('pricelist_id');

        if ($pricelistId) {
            $item = DB::table('pricelist_items')
                ->where('pricelist_id', $pricelistId)
                ->where('sample_type_id', $sampleTypeId)
                ->where('analysis_id', $analysisTypeId)
                ->first();

            if (! $item && is_numeric($analysisTypeId)) {
                $item = DB::table('pricelist_items')
                    ->where('pricelist_id', $pricelistId)
                    ->where('sample_type_id', $sampleTypeId)
                    ->where('analysis_id', (int) $analysisTypeId)
                    ->first();
            }

            if ($item) {
                $price = $item->changed_price ?? $item->selling_price ?? 0;
                return round((float) $price, 2);
            }
        }

        $invoicePrice = DB::table('invoice_details')
            ->where('sample_header_id', $this->batch->id)
            ->where('analysis_type', $analysisTypeId)
            ->value('selling_price');

        if ($invoicePrice !== null) {
            return round((float) $invoicePrice, 2);
        }

        return 0.0;
    }

    private function loadExistingDraft(): void
    {
        $path = $this->draftStoragePath();
        if (! Storage::disk('public')->exists($path)) {
            return;
        }

        $decoded = json_decode((string) Storage::disk('public')->get($path), true);
        if (! is_array($decoded)) {
            return;
        }

        $savedForm = $decoded['form'] ?? null;
        if (is_array($savedForm)) {
            $this->form = array_merge($this->form, $savedForm);
        }

        $savedParameters = $decoded['parameters'] ?? null;
        if (is_array($savedParameters)) {
            $this->parameters = collect($this->parameters)
                ->map(function (array $row) use ($savedParameters) {
                    foreach ($savedParameters as $saved) {
                        if (($saved['analysis_type_id'] ?? '') !== ''
                            && ($saved['analysis_type_id'] ?? '') === ($row['analysis_type_id'] ?? '')) {
                            $row['selected'] = (bool) ($saved['selected'] ?? false);
                            $row['price'] = (float) ($saved['price'] ?? $row['price']);
                            return $row;
                        }

                        if (($saved['analysis_type_id'] ?? '') === ''
                            && Str::lower((string) ($saved['label'] ?? '')) === Str::lower((string) ($row['label'] ?? ''))
                        ) {
                            $row['selected'] = (bool) ($saved['selected'] ?? false);
                            $row['price'] = (float) ($saved['price'] ?? $row['price']);
                            return $row;
                        }
                    }

                    return $row;
                })
                ->values()
                ->all();
        }
    }

    private function loadExistingAttachment(): void
    {
        $existing = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', 'Laboratory Analysis Acceptance Form (GCLA/F/03)')
            ->orderByDesc('created_at')
            ->first();

        $this->attachmentUrl = $existing?->attachment_url;
    }

    private function persistDraftPayload(): void
    {
        Storage::disk('public')->put(
            $this->draftStoragePath(),
            json_encode($this->buildPayload(), JSON_PRETTY_PRINT)
        );
    }

    private function draftStoragePath(): string
    {
        return 'batch-attachments/laboratory-analysis-acceptance-batch-' . $this->batch->id . '.json';
    }

    /**
     * @return array{form: array<string, mixed>, parameters: array<int, array<string, mixed>>}
     */
    private function buildPayload(): array
    {
        return [
            'form' => $this->form,
            'parameters' => array_values($this->parameters),
        ];
    }

    /**
     * @return array<int, array{analysis_type_id: string, label: string, price: float, selected: bool}>
     */
    private function acceptedParameters(): array
    {
        return array_values(array_filter($this->parameters, static fn ($row) => (bool) ($row['selected'] ?? false)));
    }

    /**
     * @return array<int, array{analysis_type_id: string, label: string, price: float, selected: bool}>
     */
    private function rejectedParameters(): array
    {
        return array_values(array_filter($this->parameters, static fn ($row) => ! ((bool) ($row['selected'] ?? false))));
    }

    private function resolveAcceptanceAttachmentTypeId(): ?int
    {
        $label = 'Laboratory Analysis Acceptance Form';

        $existingId = SystemConfiguration::query()->where('key', 'attachment_type')
            ->where('value', $label)
            ->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        $typeConfig = SystemConfiguration::query()->where('key', 'attachment_type_config_id')->first();
        if (! $typeConfig) {
            return null;
        }

        $newConfig = new SystemConfiguration();
        $newConfig->key = 'attachment_type';
        $newConfig->value = $label;
        $newConfig->configuration_type_id = $typeConfig->id;
        $newConfig->save();

        return (int) $newConfig->id;
    }
}
