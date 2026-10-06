<?php

namespace App\Livewire\Billing;

use App\Livewire\Concerns\WithToastNotifications;
use App\QuotationHeader;
use App\Services\Commercial\QuotationApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;
use Throwable;

class QuotationSendForApproval extends Component
{
    use WithToastNotifications;

    public string $quotationHeaderId;

    public bool $showModal = false;

    public bool $notifyEmail = true;

    public bool $notifyInApp = true;

    /** @var list<array{id: string, name: string, email: string}> */
    public array $approverOptions = [];

    public string $quoteNumber = '';

    public function mount(string $quotationHeaderId): void
    {
        $this->quotationHeaderId = $quotationHeaderId;
        $header = QuotationHeader::query()->find($quotationHeaderId);
        $this->quoteNumber = (string) ($header?->quote_number ?? '');
        $this->refreshApproverOptions();
    }

    #[On('billing-quotation-open-send-for-approval')]
    public function openModal(): void
    {
        if (app(QuotationApprovalService::class)->skipsApproval()) {
            $this->confirm();

            return;
        }

        $this->refreshApproverOptions();

        if ($this->approverOptions === []) {
            $this->imaraToast(
                'error',
                'No approvers configured',
                'Configure the quotation approval role under Billing → Approval configuration.',
            );

            return;
        }

        $this->notifyEmail = true;
        $this->notifyInApp = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function confirm(): void
    {
        try {
            $header = QuotationHeader::query()->find($this->quotationHeaderId);
            if ($header === null) {
                throw new RuntimeException('Quotation not found.');
            }

            $approvalService = app(QuotationApprovalService::class);
            $skippedApproval = $approvalService->skipsApproval();

            if ($skippedApproval) {
                $approvalService->completeWithoutApproval($header);
            } else {
                if (! $this->notifyEmail && ! $this->notifyInApp) {
                    throw ValidationException::withMessages([
                        'approvalNotify' => 'Choose at least one notification channel (email or in-app).',
                    ]);
                }

                $approverId = $this->approverOptions[0]['id'] ?? null;
                if ($approverId === null) {
                    throw new RuntimeException('No approvers configured.');
                }

                $this->notifyInApp = true;

                $approvalService->submitBillingForApproval(
                    $header,
                    (string) $approverId,
                    $this->notifyEmail,
                    null,
                    $this->notifyInApp,
                );
            }

            $this->showModal = false;
            if ($skippedApproval) {
                $this->imaraToast(
                    'success',
                    'Quotation complete',
                    'Quotation '.$this->quoteNumber.' is complete and ready to send.',
                );
            } else {
                $this->imaraToast(
                    'success',
                    'Sent for approval',
                    'Quotation '.$this->quoteNumber.' was sent for approval.',
                );
            }

            $this->dispatch('lab-notifications-updated');

            $this->redirect(route('add-qoute-details-view', ['id' => $this->quotationHeaderId]), navigate: false);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
            $this->imaraToast('error', 'Could not complete quotation', (string) $message);
        } catch (Throwable $exception) {
            $this->imaraToast('error', 'Could not complete quotation', $exception->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.billing.quotation-send-for-approval');
    }

    private function refreshApproverOptions(): void
    {
        $this->approverOptions = app(QuotationApprovalService::class)
            ->eligibleApprovers()
            ->map(static fn ($user): array => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
                'email' => (string) ($user->email ?? ''),
            ])
            ->values()
            ->all();
    }
}
