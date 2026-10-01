<?php

namespace App\Livewire\Commercial;

use App\Http\Requests\Commercial\ChangeEnquiryPurchaseOrderRequest;
use App\Livewire\Concerns\CapturesEnquiryPurchaseOrder;
use App\Livewire\Concerns\ValidatesWithFormRequest;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\EnquiryPurchaseOrderChange;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\EnquiryPurchaseOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * PO coverage strip for an enquiry at reception (Integrity Check), with an audited "Change PO" action.
 */
class EnquiryPurchaseOrderCoverage extends Component
{
    use CapturesEnquiryPurchaseOrder;
    use ValidatesWithFormRequest;
    use WithFileUploads;

    #[Locked]
    public string $enquiryId = '';

    #[Locked]
    public bool $readOnly = false;

    public bool $showChangeModal = false;

    public bool $showHistory = false;

    public string $clientPoNumber = '';

    public string $poRuleMessage = '';

    public string $changeReason = '';

    public $poDocument = null;

    public string $errorMessage = '';

    public string $successMessage = '';

    public function mount(string $enquiryId, bool $readOnly = false): void
    {
        $this->enquiryId = $enquiryId;
        $this->readOnly = $readOnly;
    }

    #[Computed]
    public function enquiry(): ?SampleSubmissionRequest
    {
        return SampleSubmissionRequest::query()->with('customer')->find($this->enquiryId);
    }

    /**
     * @return array{requirement: string, purchase_order: ?CustomerPurchaseOrder, requested: int, covered: int, uncovered: int, reserved: int, rows: list<array{key: string, label: string, requested: int, covered: int, uncovered: int, reason: ?string}>}|null
     */
    #[Computed]
    public function coverage(): ?array
    {
        $enquiry = $this->enquiry;
        if ($enquiry === null || ! EnquiryPurchaseOrderService::enabled()) {
            return null;
        }

        return app(EnquiryPurchaseOrderService::class)->coverage($enquiry);
    }

    #[Computed]
    public function canChange(): bool
    {
        $enquiry = $this->enquiry;

        return ! $this->readOnly
            && $enquiry !== null
            && Gate::allows(CustomerPurchaseOrder::PERMISSION_CHANGE_AT_RECEPTION)
            && app(EnquiryPurchaseOrderService::class)->canChangeAtReception($enquiry);
    }

    #[Computed]
    public function canViewPurchaseOrders(): bool
    {
        return Gate::allows(CustomerPurchaseOrder::PERMISSION_VIEW);
    }

    /**
     * @return Collection<int, EnquiryPurchaseOrderChange>
     */
    #[Computed]
    public function recentChanges(): Collection
    {
        return EnquiryPurchaseOrderChange::query()
            ->where('sample_submission_request_id', $this->enquiryId)
            ->with('changer')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
    }

    public function openChangeModal(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->resetErrorBag();

        if (! $this->canChange) {
            $this->errorMessage = 'The PO can only be changed while the samples are at reception, before the job is created.';

            return;
        }

        $enquiry = $this->enquiry;
        $this->clientPoNumber = blank($enquiry->customer_purchase_order_id) ? (string) ($enquiry->client_po_number ?? '') : '';
        $this->changeReason = '';
        $this->poDocument = null;
        $this->hydratePoCapture($enquiry);
        $this->poRuleMessage = $this->poCaptureRuleMessage('');
        $this->showChangeModal = true;
    }

    public function closeChangeModal(): void
    {
        $this->showChangeModal = false;
        $this->clientPoNumber = '';
        $this->changeReason = '';
        $this->poDocument = null;
        $this->poRuleMessage = '';
        $this->resetPoCapture();
        $this->resetErrorBag();
    }

    public function submitChange(): void
    {
        $this->errorMessage = '';
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CHANGE_AT_RECEPTION);

        $enquiry = $this->enquiry;
        if ($enquiry === null || ! $this->canChange) {
            $this->errorMessage = 'The PO can only be changed while the samples are at reception, before the job is created.';
            $this->closeChangeModal();

            return;
        }

        $payload = $this->poCapturePayload($this->clientPoNumber);

        $this->validateWithFormRequest(
            ChangeEnquiryPurchaseOrderRequest::class,
            $payload + [
                'file' => $this->poDocument,
                'reason' => $this->changeReason,
            ],
        );

        try {
            $change = app(EnquiryPurchaseOrderService::class)->changeAtReception(
                $enquiry,
                $payload,
                $payload['mode'] === EnquiryPurchaseOrderService::MODE_SINGLE ? $this->poDocument : null,
                $this->changeReason,
                (string) auth()->id(),
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->errorMessage = 'The PO could not be changed: '.$exception->getMessage();

            return;
        }

        $this->closeChangeModal();
        unset($this->enquiry, $this->coverage, $this->canChange, $this->recentChanges);

        $this->successMessage = 'PO changed. This request is now on '.($change->to_po_number ?: 'no PO').'.';
        $this->dispatch('enquiry-purchase-order-changed', enquiryId: $this->enquiryId);
    }

    public function render(): View
    {
        return view('livewire.commercial.enquiry-purchase-order-coverage');
    }
}
