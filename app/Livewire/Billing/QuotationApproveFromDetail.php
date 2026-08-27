<?php

namespace App\Livewire\Billing;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\QuotationHeader;
use App\Services\Commercial\QuotationApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class QuotationApproveFromDetail extends Component
{
    use WithToastNotifications;

    public string $quotationHeaderId;

    public string $quoteNumber = '';

    public bool $canApprove = false;

    public bool $showApproveAndSendModal = false;

    public bool $showRejectModal = false;

    public string $approvalDecisionComments = '';

    public bool $approveSendEmail = true;

    /** @var list<array{id: string, name: string, email: string, company_unit: string, sampling_location: string, receive_quotations: bool, can_login: bool}> */
    public array $approveRecipientOptions = [];

    /** @var list<string> */
    public array $approveRecipientContactIds = [];

    public function mount(string $quotationHeaderId): void
    {
        $this->quotationHeaderId = $quotationHeaderId;
        $this->refreshCapability();
    }

    public function refreshCapability(): void
    {
        $header = QuotationHeader::query()->find($this->quotationHeaderId);
        $this->quoteNumber = (string) ($header?->quote_number ?? '');
        $this->canApprove = $header !== null
            && app(QuotationApprovalService::class)->canCurrentUserApprove($header);
    }

    #[On('billing-quotation-open-approve')]
    public function openApproveAndSendModal(): void
    {
        $this->refreshCapability();

        $header = QuotationHeader::query()->find($this->quotationHeaderId);
        if ($header === null || ! $this->canApprove) {
            $this->imaraToast('error', 'Not allowed', 'You cannot approve this quotation.');

            return;
        }

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
        $this->approveRecipientOptions = [];
        $this->approveRecipientContactIds = [];
        $this->approvalDecisionComments = '';
    }

    #[On('billing-quotation-open-reject')]
    public function openRejectModal(): void
    {
        $this->refreshCapability();

        $header = QuotationHeader::query()->find($this->quotationHeaderId);
        if ($header === null || ! $this->canApprove) {
            $this->imaraToast('error', 'Not allowed', 'You cannot reject this quotation.');

            return;
        }

        $this->approvalDecisionComments = '';
        $this->showApproveAndSendModal = false;
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
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
            $header = QuotationHeader::query()->find($this->quotationHeaderId);
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

            $this->redirect(route('view_quotation_final', ['id' => $this->quotationHeaderId]), navigate: false);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
            $this->imaraToast('error', 'Could not approve quotation', (string) $message);
        } catch (Throwable $exception) {
            $this->imaraToast('error', 'Could not approve quotation', $exception->getMessage());
        }
    }

    public function confirmApproveOnly(): void
    {
        try {
            $header = QuotationHeader::query()->find($this->quotationHeaderId);
            if ($header === null) {
                throw new \RuntimeException('Quotation not found.');
            }

            app(QuotationApprovalService::class)->approveBilling(
                $header,
                $this->approvalDecisionComments !== '' ? $this->approvalDecisionComments : null,
                true,
            );

            $this->closeApproveAndSendModal();
            $this->imaraToast('success', 'Quotation approved', 'The quotation was approved and moved to Quote Complete.');

            $this->redirect(route('view_quotation_final', ['id' => $this->quotationHeaderId]), navigate: false);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
            $this->imaraToast('error', 'Could not approve quotation', (string) $message);
        } catch (Throwable $exception) {
            $this->imaraToast('error', 'Could not approve quotation', $exception->getMessage());
        }
    }

    public function confirmReject(): void
    {
        try {
            $header = QuotationHeader::query()->find($this->quotationHeaderId);
            if ($header === null) {
                throw new \RuntimeException('Quotation not found.');
            }

            app(QuotationApprovalService::class)->rejectBilling(
                $header,
                $this->approvalDecisionComments,
            );

            $this->closeRejectModal();
            $this->imaraToast('success', 'Quotation rejected', 'The quotation was returned to preparation.');

            $this->redirect(route('add-qoute-details-view', ['id' => $this->quotationHeaderId]), navigate: false);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
            $this->imaraToast('error', 'Could not reject quotation', (string) $message);
        } catch (Throwable $exception) {
            $this->imaraToast('error', 'Could not reject quotation', $exception->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.billing.quotation-approve-from-detail');
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
}
