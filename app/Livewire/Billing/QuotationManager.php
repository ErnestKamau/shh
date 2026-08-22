<?php

namespace App\Livewire\Billing;

use App\QuotationHeader;
use App\QuotationHeaderView;
use App\SampleAnalysisStage;
use App\Models\CRM\CRMCustomer;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\Billing\PdfTextExtractor;
use App\Services\Billing\QuotationPrepImportService;
use App\Services\Billing\QuotationPricingResolver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Validation\ValidationException;
use App\Services\Commercial\QuotationApprovalService;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCompanyUnit;

class QuotationManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithFileUploads;
    use WithToastNotifications;

    protected $paginationTheme = 'bootstrap';

    public bool $embedded = false;

    public bool $filtersOpen = false;

    public string $search = '';

    public string $customerFilter = '';

    public string $stageFilter = '';

    public string $quotationTypeFilter = '';

    public string $labSectionFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 25;

    /** @var list<int> */
    public array $perPageOptions = [10, 25, 50, 100];

    public $selectedQuotationId = null;

    public bool $showQuotationDetails = false;

    public bool $showCreateModal = false;

    /** @var array<string, mixed> */
    public array $quotationForm = [];

    public string $message = '';

    public string $messageType = 'success';

    public bool $showCustomerDropdown = false;

    public string $customerSearch = '';

    public bool $showImportModal = false;

    public string $importQuotationId = '';

    public string $importFormat = 'excel';

    public string $importPricingMode = 'per_package';

    public $importFile = null;

    public bool $showApproveAndSendModal = false;

    public bool $showRejectModal = false;

    public ?string $approvalQuotationId = null;

    public string $approvalDecisionComments = '';

    public bool $approveSendEmail = true;

    /** @var list<string> */
    public array $approveRecipientContactIds = [];

    /** @var list<array{id: string, name: string, email: string, company_unit: string, sampling_location: string, receive_quotations: bool, can_login: bool}> */
    public array $approveRecipientOptions = [];



    /** @var list<string> */
    private const SORTABLE = [
        'quote_number',
        'customer',
        'status',
        'quote_date',
        'expiring_date',
        'total_amount',
        'created_at',
        'prepared_by_name',
    ];

    public function mount(?string $initialStage = null): void
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->resetQuotationForm();

        $fromRequest = request()->query('stage_filter');
        $stage = $initialStage ?? (is_string($fromRequest) ? $fromRequest : null);
        $this->applyStageFilter($stage);
    }

    #[On('set-quotation-stage')]
    public function onSetQuotationStage(string $stage = ''): void
    {
        $this->applyStageFilter($stage);
    }

    public function setStageFilter(string $stage = ''): void
    {
        $this->applyStageFilter($stage);
    }

    public function toggleFilters(): void
    {
        $this->filtersOpen = ! $this->filtersOpen;
    }

    public function closeFilters(): void
    {
        $this->filtersOpen = false;
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['quote_date', 'expiring_date', 'created_at', 'total_amount'], true)
                ? 'desc'
                : 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStageFilter(): void
    {
        $this->resetPage();
    }

    public function updatedQuotationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedLabSectionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSortField(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function getQuotationsProperty(): LengthAwarePaginator
    {
        if ($this->stageFilter === 'approval-settings') {
            return new LengthAwarePaginator([], 0, (int) $this->perPage, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]);
        }

        $query = QuotationHeaderView::with(['preparedBy', 'labSections'])->where('is_draft', 0);

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term): void {
                $q->where('quote_number', 'like', $term)
                    ->orWhere('customer', 'like', $term)
                    ->orWhere('contact_first', 'like', $term)
                    ->orWhere('contact_last', 'like', $term)
                    ->orWhere('prepared_by_name', 'like', $term);
            });
        }

        if ($this->customerFilter !== '') {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        if ($this->stageFilter !== '') {
            $query->where('status', $this->stageFilter);
        }

        if ($this->quotationTypeFilter !== '') {
            $query->where('quotation_type', $this->quotationTypeFilter);
        }

        if ($this->labSectionFilter !== '') {
            $labSectionId = (string) $this->labSectionFilter;
            $query->whereHas('labSections', function ($q) use ($labSectionId): void {
                $q->where('sample_analysis_stages.id', $labSectionId);
            });
        }

        if ($this->startDate !== '' && $this->endDate !== '') {
            $query->whereBetween('quote_date', [$this->startDate, $this->endDate]);
        } elseif ($this->startDate !== '') {
            $query->whereDate('quote_date', '>=', $this->startDate);
        } elseif ($this->endDate !== '') {
            $query->whereDate('quote_date', '<=', $this->endDate);
        }

        $field = in_array($this->sortField, self::SORTABLE, true) ? $this->sortField : 'created_at';
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($field, $direction)->paginate($this->perPage);
    }

    public function getDraftsProperty()
    {
        return QuotationHeaderView::with('preparedBy')
            ->where('is_draft', 1)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getLabSectionsProperty()
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
     * @return list<string>
     */
    public function getQuotationStagesProperty(): array
    {
        return [
            'Quote In Preparation',
            'Quote In Approval',
            'Quote Complete',
        ];
    }

    /**
     * @return array{all: int, Quote In Preparation: int, Quote In Approval: int, Quote Complete: int}
     */
    public function getStageCountsProperty(): array
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->customerFilter = '';
        $this->quotationTypeFilter = '';
        $this->labSectionFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function viewQuotation($quotationId): void
    {
        $this->selectedQuotationId = $quotationId;
        $this->showQuotationDetails = true;
    }

    public function closeQuotationDetails(): void
    {
        $this->selectedQuotationId = null;
        $this->showQuotationDetails = false;
    }

    public function getSelectedQuotationProperty()
    {
        if ($this->selectedQuotationId) {
            return QuotationHeader::with([
                'customer',
                'contact',
                'details.invoicableItem',
                'details.sampletype',
            ])->find($this->selectedQuotationId);
        }

        return null;
    }

    public function showCreateQuotationModal(): void
    {
        $this->resetQuotationForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetQuotationForm();
    }

    public function resetQuotationForm(): void
    {
        $this->quotationForm = [
            'customer_id' => null,
            'contact_id' => null,
            'quotation_type' => 'Analysis',
            'quote_date' => now()->format('Y-m-d'),
            'expiring_date' => now()->addDays(30)->format('Y-m-d'),
        ];
    }

    public function deleteQuotation($quotationId): void
    {
        try {
            DB::beginTransaction();

            $quotation = QuotationHeader::findOrFail($quotationId);
            $quotation->labSections()->detach();
            $quotation->details()->delete();
            $quotation->delete();

            DB::commit();
            $this->showMessage('Quotation deleted successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showMessage('Error deleting quotation: '.$e->getMessage(), 'danger');
        }
    }

    public function cloneQuotation(string $quotationId)
    {
        return redirect()->route('clone_quotation', ['id' => $quotationId]);
    }

    public function openCreateEnquiryWizard(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.add');
        $this->dispatch('open-create-enquiry-from-quotation', quotationId: $quotationId);
    }

    public function showMessage(string $message, string $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;

        $toastType = match (strtolower($type)) {
            'danger', 'error', 'failed', 'fail' => 'error',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success',
        };

        $title = match ($toastType) {
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            default => 'Notice',
        };

        $this->imaraToast($toastType, $title, $message);
    }

    public function openImportModal(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.edit');
        $header = QuotationHeader::query()->find($quotationId);
        if (! $header || $header->status !== 'Quote In Preparation') {
            $this->showMessage('Import is only available for quotations in preparation.', 'danger');

            return;
        }

        $this->importQuotationId = $quotationId;
        $this->importFormat = 'excel';
        $this->importPricingMode = 'per_package';
        $this->importFile = null;
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importQuotationId = '';
        $this->importFile = null;
        $this->resetValidation();
        $this->dispatch('amspec-import-closed');
    }

    public function submitImport(QuotationPrepImportService $importService, PdfTextExtractor $pdfTextExtractor): void
    {
        $this->authorize('laboratory.components.quotation.edit');
        $this->validate([
            'importQuotationId' => 'required|string',
            'importFormat' => 'required|in:excel,pdf',
            'importPricingMode' => 'required|in:per_test,per_package',
            'importFile' => 'required|file|max:20480',
        ]);

        $header = QuotationHeader::query()->find($this->importQuotationId);
        if (! $header || $header->status !== 'Quote In Preparation') {
            $this->showMessage('Import is only available for quotations in preparation.', 'danger');

            return;
        }

        $extension = strtolower((string) $this->importFile->getClientOriginalExtension());

        try {
            if ($this->importFormat === 'pdf') {
                if ($extension !== 'pdf') {
                    $this->addError('importFile', 'Please upload a PDF file.');
                    $this->showMessage('Please upload a PDF file.', 'danger');

                    return;
                }
                if (! $pdfTextExtractor->isAvailable()) {
                    $this->showMessage($pdfTextExtractor->capability()['hint'], 'danger');

                    return;
                }
            } elseif (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
                $this->addError('importFile', 'Please upload an Excel (.xlsx / .xls) or CSV file.');
                $this->showMessage('Please upload an Excel (.xlsx / .xls) or CSV file.', 'danger');

                return;
            }

            $result = $importService->import(
                $header,
                $this->importFile,
                $this->importFormat,
                $this->importPricingMode === 'per_test'
                    ? QuotationPricingResolver::PRICING_MODE_PER_TEST
                    : QuotationPricingResolver::PRICING_MODE_PER_PACKAGE,
            );

            // Refresh header totals (same formula as prep save).
            $details = $header->details()->get();
            $subTotal = 0.0;
            $taxes = 0.0;
            foreach ($details as $detail) {
                $extended = (int) $detail->quantity * (float) $detail->unit_price;
                $subTotal += $extended;
                if ((float) $detail->tax != 0.0) {
                    $taxes += ((float) $detail->tax / 100) * $extended;
                }
            }
            $header->sub_total = $subTotal;
            $header->tax = $taxes;
            $header->total_amount = $subTotal + $taxes;
            $header->save();

            $created = (int) ($result['created'] ?? 0);
            $warnings = $result['warnings'] ?? [];
            $msg = "Imported {$created} quotation line(s).";
            if ($warnings !== []) {
                $msg .= ' Warnings: '.implode(' ', array_slice($warnings, 0, 3));
            }

            $this->closeImportModal();
            $this->showMessage($msg, $created > 0 ? 'success' : 'danger');
        } catch (\Throwable $e) {
            $this->showMessage('Import failed: '.$e->getMessage(), 'danger');
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }


    public function openApproveAndSendModal(string $quotationId): void
    {
        $header = QuotationHeader::query()->find($quotationId);
        if ($header === null || ! app(QuotationApprovalService::class)->canCurrentUserApprove($header)) {
            $this->imaraToast('error', 'Not allowed', 'You cannot approve this quotation.');

            return;
        }

        $this->approvalQuotationId = (string) $header->id;
        $this->approvalDecisionComments = '';
        $this->approveSendEmail = true;
        $this->approveRecipientOptions = $this->buildApproveRecipientOptions($header);
        $this->approveRecipientContactIds = collect($this->approveRecipientOptions)
            ->filter(static fn (array $row): bool => (bool) ($row['receive_quotations'] ?? false))
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->values()
            ->all();
        if ($this->approveRecipientContactIds === [] && $this->approveRecipientOptions !== []) {
            $this->approveRecipientContactIds = [(string) $this->approveRecipientOptions[0]['id']];
        }
        $this->showRejectModal = false;
        $this->showApproveAndSendModal = true;
    }

    public function closeApproveAndSendModal(): void
    {
        $this->showApproveAndSendModal = false;
        $this->approvalQuotationId = null;
        $this->approveRecipientOptions = [];
        $this->approveRecipientContactIds = [];
        $this->approvalDecisionComments = '';
    }

    public function openRejectModal(string $quotationId): void
    {
        $header = QuotationHeader::query()->find($quotationId);
        if ($header === null || ! app(QuotationApprovalService::class)->canCurrentUserApprove($header)) {
            $this->imaraToast('error', 'Not allowed', 'You cannot reject this quotation.');

            return;
        }

        $this->approvalQuotationId = (string) $header->id;
        $this->approvalDecisionComments = '';
        $this->showApproveAndSendModal = false;
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->approvalQuotationId = null;
        $this->approvalDecisionComments = '';
    }

    public function toggleApproveRecipient(string $contactId): void
    {
        $contactId = (string) $contactId;
        if (in_array($contactId, $this->approveRecipientContactIds, true)) {
            $this->approveRecipientContactIds = array_values(array_filter(
                $this->approveRecipientContactIds,
                static fn (string $id): bool => $id !== $contactId,
            ));

            return;
        }

        $this->approveRecipientContactIds[] = $contactId;
    }

    public function confirmApproveAndSend(): void
    {
        try {
            if ($this->approvalQuotationId === null) {
                return;
            }

            $header = QuotationHeader::query()->find($this->approvalQuotationId);
            if ($header === null) {
                throw new \RuntimeException('Quotation not found.');
            }

            [$sendPortal, $sendEmail] = $this->resolveApproveSendChannels($header);

            app(QuotationApprovalService::class)->sendBillingToCustomer(
                $header,
                $sendPortal,
                $sendEmail,
                $this->approveRecipientContactIds !== [] ? $this->approveRecipientContactIds : null,
                $this->approvalDecisionComments !== '' ? $this->approvalDecisionComments : null,
                true,
            );

            $this->closeApproveAndSendModal();
            $this->imaraToast('success', 'Quotation approved and sent', 'The quotation was approved and sent to the selected contacts.');
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
            $this->imaraToast('error', 'Could not approve quotation', (string) $message);
        } catch (\Throwable $exception) {
            $this->imaraToast('error', 'Could not approve quotation', $exception->getMessage());
        }
    }

    public function confirmReject(): void
    {
        try {
            if ($this->approvalQuotationId === null) {
                return;
            }

            $header = QuotationHeader::query()->find($this->approvalQuotationId);
            if ($header === null) {
                throw new \RuntimeException('Quotation not found.');
            }

            app(QuotationApprovalService::class)->rejectBilling(
                $header,
                $this->approvalDecisionComments,
            );

            $this->closeRejectModal();
            $this->imaraToast('success', 'Quotation rejected', 'The quotation was returned to preparation.');
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
            $this->imaraToast('error', 'Could not reject quotation', (string) $message);
        } catch (\Throwable $exception) {
            $this->imaraToast('error', 'Could not reject quotation', $exception->getMessage());
        }
    }

    /**
     * @return list<array{id: string, name: string, email: string, company_unit: string, sampling_location: string, receive_quotations: bool, can_login: bool}>
     */
    private function buildApproveRecipientOptions(QuotationHeader $header): array
    {
        $customerId = (string) ($header->crm_customer_id ?? '');
        if ($customerId === '') {
            return [];
        }

        $contacts = CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->get();

        $unitIds = $contacts->pluck('crm_company_unit_id')->filter()->unique()->values()->all();
        $unitNamesById = $unitIds === []
            ? collect()
            : CRMCompanyUnit::query()->whereIn('id', $unitIds)->pluck('name', 'id');

        $samplePointsByContact = SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->whereNotNull('contact_id')
            ->whereIn('contact_id', $contacts->pluck('id')->all())
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($point) => (string) $point->contact_id);

        $primaryContactId = (string) ($header->crm_customer_contact_id ?? '');
        $options = [];

        foreach ($contacts as $contact) {
            $email = trim((string) ($contact->email ?? ''));
            if ($email === '' && ! (bool) ($contact->can_login ?? false)) {
                continue;
            }

            $receivesQuotes = (bool) ($contact->receive_quotations ?? false)
                || (string) $contact->id === $primaryContactId
                || (bool) ($contact->is_main_customer_contact ?? false);

            $unitLabel = '—';
            $unitId = (string) ($contact->crm_company_unit_id ?? '');
            if ($unitId !== '' && $unitNamesById->has($unitId)) {
                $unitLabel = (string) $unitNamesById->get($unitId);
            } elseif (trim((string) ($contact->unit_name ?? '')) !== '') {
                $unitLabel = (string) $contact->unit_name;
            }

            $locations = $samplePointsByContact->get((string) $contact->id, collect())
                ->map(fn ($point) => trim((string) ($point->display_name ?? $point->name ?? '')))
                ->filter()
                ->unique()
                ->values();
            $samplingLocation = $locations->isNotEmpty() ? $locations->implode(', ') : '—';

            $name = trim(implode(' ', array_filter([
                (string) ($contact->first_name ?? ''),
                (string) ($contact->middle_name ?? ''),
                (string) ($contact->last_name ?? ''),
            ])));

            $options[] = [
                'id' => (string) $contact->id,
                'name' => $name !== '' ? $name : 'Contact',
                'email' => $email,
                'company_unit' => $unitLabel,
                'sampling_location' => $samplingLocation,
                'receive_quotations' => $receivesQuotes,
                'can_login' => (bool) ($contact->can_login ?? false),
            ];
        }

        return $options;
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function resolveApproveSendChannels(QuotationHeader $header): array
    {
        $selectedCanLogin = collect($this->approveRecipientOptions)
            ->filter(fn (array $row): bool => in_array((string) $row['id'], $this->approveRecipientContactIds, true))
            ->contains(fn (array $row): bool => (bool) ($row['can_login'] ?? false));

        $sendPortal = $selectedCanLogin || $this->customerHasPortalContacts($header);
        $sendEmail = $this->approveSendEmail;

        if (! $sendPortal && ! $sendEmail) {
            throw new \RuntimeException('Enable “Email quotation PDF” or select a portal-capable contact so the quotation can be delivered.');
        }

        return [$sendPortal, $sendEmail];
    }

    private function customerHasPortalContacts(QuotationHeader $header): bool
    {
        $customerId = (string) ($header->crm_customer_id ?? '');
        if ($customerId === '') {
            return false;
        }

        return CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->where('can_login', true)
            ->exists();
    }

    public function render()
    {
        $quotations = $this->quotations;
        $approvalService = app(QuotationApprovalService::class);
        $approvableIds = [];
        foreach ($quotations as $quotation) {
            $header = QuotationHeader::query()->find($quotation->id);
            if ($header !== null && $approvalService->canCurrentUserApprove($header)) {
                $approvableIds[] = (string) $quotation->id;
            }
        }

        return view('livewire.billing.quotation-manager', [
            'quotations' => $quotations,
            'drafts' => $this->drafts,
            'customers' => $this->customers,
            'labSections' => $this->labSections,
            'quotationStages' => $this->quotationStages,
            'selectedQuotation' => $this->selectedQuotation,
            'stageCounts' => $this->stageCounts,
            'approvableQuotationIds' => $approvableIds,
        ]);
    }

    private function applyStageFilter(?string $stage): void
    {
        if ($stage === null || $stage === '' || $stage === 'All Quotations' || $stage === 'all') {
            $this->stageFilter = '';
        } elseif ($stage === 'approval-settings' || in_array($stage, $this->quotationStages, true)) {
            $this->stageFilter = $stage;
        } else {
            return;
        }

        $this->resetPage();
    }
}
