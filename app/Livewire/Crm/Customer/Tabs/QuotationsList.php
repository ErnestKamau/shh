<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;
use App\QuotationHeader;
use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Throwable;

class QuotationsList extends BaseCrmComponent
{
    use AuthorizesRequests;

    public CRMCustomer $customer;

    public int $perPage = 15;

    public bool $showCreateEnquiryModal = false;

    public ?string $selectedEnquiryQuotationId = null;

    public string $enquiryCreationToken = '';

    /** @var array<string, mixed> */
    public array $enquiryForm = [];

    protected $paginationTheme = 'bootstrap';

    public function mount(CRMCustomer $customer): void
    {
        $this->customer = $customer;
    }

    public function getQuotesProperty(): LengthAwarePaginator
    {
        return QuotationHeader::where('crm_customer_id', $this->customer->id)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function getEnquirySourceQuotationProperty(): ?QuotationHeader
    {
        if ($this->selectedEnquiryQuotationId === null) {
            return null;
        }

        return QuotationHeader::query()
            ->with(['customer', 'details'])
            ->find($this->selectedEnquiryQuotationId);
    }

    public function openCreateEnquiryModal(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.add');
        $this->resetValidation();

        try {
            $quotation = QuotationHeader::query()
                ->with(['details', 'customer'])
                ->where('crm_customer_id', $this->customer->id)
                ->findOrFail($quotationId);
            $service = app(EnquiryFromQuotationService::class);
            $lines = $service->eligibleQuotationLines($quotation);

            $this->selectedEnquiryQuotationId = (string) $quotation->id;
            $this->enquiryCreationToken = (string) Str::uuid();
            $this->enquiryForm = [
                'creation_intent' => EnquiryFromQuotationService::INTENT_PREPARE,
                'number_of_samples' => $service->inferPhysicalSampleCount($lines),
                'reference_number' => '',
                'client_po_number' => '',
                'po_skipped' => false,
                'date_expected' => '',
                'sample_description' => '',
                'enquiry_notes' => '',
            ];
            $this->showCreateEnquiryModal = true;
        } catch (Throwable $exception) {
            $this->showError($exception->getMessage());
        }
    }

    public function closeCreateEnquiryModal(): void
    {
        $this->showCreateEnquiryModal = false;
        $this->selectedEnquiryQuotationId = null;
        $this->enquiryCreationToken = '';
        $this->enquiryForm = [];
        $this->resetValidation();
    }

    public function createEnquiryFromQuotation()
    {
        $this->authorize('laboratory.components.quotation.add');

        $validated = $this->validate([
            'selectedEnquiryQuotationId' => ['required', 'uuid'],
            'enquiryCreationToken' => ['required', 'uuid'],
            'enquiryForm.creation_intent' => ['required', 'in:'.implode(',', EnquiryFromQuotationService::creationIntents())],
            'enquiryForm.number_of_samples' => ['required', 'integer', 'min:1', 'max:10000'],
            'enquiryForm.reference_number' => ['nullable', 'string', 'max:255'],
            'enquiryForm.client_po_number' => ['nullable', 'string', 'max:255'],
            'enquiryForm.po_skipped' => ['nullable', 'boolean'],
            'enquiryForm.date_expected' => ['nullable', 'date'],
            'enquiryForm.sample_description' => ['nullable', 'string', 'max:5000'],
            'enquiryForm.enquiry_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $quotation = QuotationHeader::query()
                ->where('crm_customer_id', $this->customer->id)
                ->findOrFail($validated['selectedEnquiryQuotationId']);
            $enquiry = app(EnquiryFromQuotationService::class)->create(
                $quotation,
                [
                    'number_of_samples' => (int) $validated['enquiryForm']['number_of_samples'],
                    'reference_number' => $validated['enquiryForm']['reference_number'] ?? null,
                    'date_expected' => $validated['enquiryForm']['date_expected'] ?? null,
                    'sample_description' => $validated['enquiryForm']['sample_description'] ?? null,
                    'enquiry_notes' => $validated['enquiryForm']['enquiry_notes'] ?? null,
                    'creation_intent' => $validated['enquiryForm']['creation_intent'],
                    'client_po_number' => $validated['enquiryForm']['client_po_number'] ?? null,
                    'po_skipped' => (bool) ($validated['enquiryForm']['po_skipped'] ?? false),
                ],
                $validated['enquiryCreationToken'],
            );

            $this->closeCreateEnquiryModal();

            $instance = $enquiry->submissionFormInstance;
            if ($instance !== null && $instance->submission_form_id !== null) {
                return redirect()->route('submission-forms.instances.fill', [
                    'submissionForm' => $instance->submission_form_id,
                    'instance' => $instance->id,
                ]);
            }

            $this->showSuccess(
                'Enquiry '.($enquiry->reference_number ?: $enquiry->formatted_number)
                .' was created from quotation '.$quotation->quote_number.'.'
            );
        } catch (Throwable $exception) {
            $this->showError($exception->getMessage());
        }

        return null;
    }

    public function placeholder(): string
    {
        return '<div class="d-flex justify-content-center align-items-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.quotations-list');
    }
}
