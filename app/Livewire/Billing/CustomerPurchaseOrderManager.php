<?php

namespace App\Livewire\Billing;

use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\CRM\CRMCustomer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerPurchaseOrderManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    /**
     * When set (CRM customer profile tab), the list is fixed to this customer.
     */
    #[Locked]
    public ?string $lockedCustomerId = null;

    public string $search = '';

    public string $customerFilter = '';

    public string $fileFilter = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];

    public bool $filtersOpen = false;

    public function mount(?string $customerId = null): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_VIEW);

        $this->lockedCustomerId = filled($customerId) ? $customerId : null;
    }

    public function toggleFilters(): void
    {
        $this->filtersOpen = ! $this->filtersOpen;
    }

    public function closeFilters(): void
    {
        $this->filtersOpen = false;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'customerFilter', 'fileFilter', 'statusFilter', 'typeFilter', 'startDate', 'endDate', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->customerFilter = '';
        $this->fileFilter = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->filtersOpen = false;
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return collect([
            $this->lockedCustomerId === null ? $this->customerFilter : '',
            $this->fileFilter,
            $this->statusFilter,
            $this->typeFilter,
            $this->startDate,
            $this->endDate,
        ])->filter(static fn (string $value): bool => $value !== '')->count();
    }

    public function getPurchaseOrdersProperty()
    {
        $query = $this->baseQuery()
            ->with([
                'customer',
                'quotation',
                'currency',
                'enquiry.submissionFormInstance.submissionForm',
                'enquiry.batch',
                'uploader',
            ])
            ->withCount('lines')
            ->withSum('lines as ordered_total', 'ordered_qty')
            ->withSum('lines as remaining_total', 'remaining_qty')
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term): void {
                $q->where('po_number', 'ilike', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'ilike', $term))
                    ->orWhereHas('quotation', fn ($qt) => $qt->where('quote_number', 'ilike', $term))
                    ->orWhereHas('enquiry.submissionFormInstance', fn ($i) => $i->where('form_number', 'ilike', $term))
                    ->orWhereHas('enquiry', fn ($e) => $e->where('unique_identification', 'ilike', $term));
            });
        }

        if ($this->fileFilter === 'with_file') {
            $query->whereNotNull('file_path')->where('file_path', '!=', '');
        } elseif ($this->fileFilter === 'without_file') {
            $query->where(function ($q): void {
                $q->whereNull('file_path')->orWhere('file_path', '');
            });
        }

        if ($this->typeFilter !== '' && PurchaseOrderType::tryFrom($this->typeFilter) !== null) {
            $query->where('po_type', $this->typeFilter);
        }

        $this->applyStatusFilter($query, $this->statusFilter);

        if ($this->startDate !== '') {
            $query->whereDate('recorded_at', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('recorded_at', '<=', $this->endDate);
        }

        return $query->paginate($this->perPage);
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::query()
            ->whereIn('id', CustomerPurchaseOrder::query()->select('customer_id')->whereNotNull('customer_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array{active: int, expiring: int, exhausted: int, expired: int, skipped: int}
     */
    public function getSummaryProperty(): array
    {
        $count = function (string $status): int {
            $query = $this->baseQuery();
            $this->applyStatusFilter($query, $status);

            return $query->count();
        };

        return [
            'active' => $count('active'),
            'expiring' => $count('expiring'),
            'exhausted' => $count(PurchaseOrderStatus::Exhausted->value),
            'expired' => $count(PurchaseOrderStatus::Expired->value),
            'skipped' => $count('skipped'),
        ];
    }

    public function getCanCreateProperty(): bool
    {
        return (bool) auth()->user()?->can(CustomerPurchaseOrder::PERMISSION_CREATE);
    }

    public function render()
    {
        return view('livewire.billing.customer-purchase-order-manager', [
            'purchaseOrders' => $this->purchaseOrders,
            'customers' => $this->lockedCustomerId === null ? $this->customers : collect(),
            'summary' => $this->summary,
            'statuses' => PurchaseOrderStatus::cases(),
            'types' => PurchaseOrderType::cases(),
        ]);
    }

    /**
     * @return Builder<CustomerPurchaseOrder>
     */
    private function baseQuery(): Builder
    {
        $query = CustomerPurchaseOrder::query();

        $customerId = $this->lockedCustomerId ?? ($this->customerFilter !== '' ? $this->customerFilter : null);
        if ($customerId !== null) {
            $query->where('customer_id', $customerId);
        }

        return $query;
    }

    /**
     * Status filters follow effectiveStatus(): an active/exhausted PO past valid_to counts as expired.
     *
     * @param  Builder<CustomerPurchaseOrder>  $query
     */
    private function applyStatusFilter(Builder $query, string $status): void
    {
        $today = now()->toDateString();
        $live = [PurchaseOrderStatus::Active->value, PurchaseOrderStatus::Exhausted->value];
        $notPastValidity = static function (Builder $q) use ($today): void {
            $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today);
        };

        match ($status) {
            'skipped' => $query->where('po_skipped', true),
            'active', 'exhausted' => $query->where('po_skipped', false)->where('status', $status)->where($notPastValidity),
            'expiring' => $query->where('po_skipped', false)
                ->where('status', PurchaseOrderStatus::Active->value)
                ->whereNotNull('valid_to')
                ->whereDate('valid_to', '>=', $today)
                ->whereRaw('valid_to <= (CURRENT_DATE + expiry_notice_days)'),
            'expired' => $query->where('po_skipped', false)->where(function (Builder $q) use ($live, $today): void {
                $q->where('status', PurchaseOrderStatus::Expired->value)
                    ->orWhere(function (Builder $inner) use ($live, $today): void {
                        $inner->whereIn('status', $live)->whereDate('valid_to', '<', $today);
                    });
            }),
            'closed', 'cancelled' => $query->where('po_skipped', false)->where('status', $status),
            default => null,
        };
    }
}
