<?php

namespace App\Livewire\Billing;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\CRM\CRMCustomer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerPurchaseOrderManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $customerFilter = '';

    public string $fileFilter = '';

    public string $statusFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];

    public bool $filtersOpen = false;

    public function mount(): void
    {
        $this->startDate = now()->subMonths(3)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function toggleFilters(): void
    {
        $this->filtersOpen = ! $this->filtersOpen;
    }

    public function closeFilters(): void
    {
        $this->filtersOpen = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFileFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->customerFilter = '';
        $this->fileFilter = '';
        $this->statusFilter = '';
        $this->startDate = now()->subMonths(3)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->filtersOpen = false;
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return collect([
            $this->customerFilter,
            $this->fileFilter,
            $this->statusFilter,
            $this->startDate !== now()->subMonths(3)->format('Y-m-d') ? $this->startDate : '',
            $this->endDate !== now()->format('Y-m-d') ? $this->endDate : '',
        ])->filter(static fn (string $value): bool => $value !== '')->count();
    }

    public function getPurchaseOrdersProperty()
    {
        $query = CustomerPurchaseOrder::query()
            ->with([
                'customer',
                'quotation',
                'enquiry.submissionFormInstance.submissionForm',
                'enquiry.batch',
                'uploader',
            ])
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

        if ($this->customerFilter !== '') {
            $query->where('customer_id', $this->customerFilter);
        }

        if ($this->fileFilter === 'with_file') {
            $query->whereNotNull('file_path')->where('file_path', '!=', '');
        } elseif ($this->fileFilter === 'without_file') {
            $query->where(function ($q): void {
                $q->whereNull('file_path')->orWhere('file_path', '');
            });
        }

        if ($this->statusFilter === 'skipped') {
            $query->where('po_skipped', true);
        } elseif ($this->statusFilter === 'recorded') {
            $query->where('po_skipped', false);
        }

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
     * @return array{total: int, recorded: int, skipped: int, with_file: int}
     */
    public function getSummaryProperty(): array
    {
        $base = CustomerPurchaseOrder::query();

        if ($this->startDate !== '') {
            $base->whereDate('recorded_at', '>=', $this->startDate);
        }
        if ($this->endDate !== '') {
            $base->whereDate('recorded_at', '<=', $this->endDate);
        }

        return [
            'total' => (clone $base)->count(),
            'recorded' => (clone $base)->where('po_skipped', false)->count(),
            'skipped' => (clone $base)->where('po_skipped', true)->count(),
            'with_file' => (clone $base)->whereNotNull('file_path')->where('file_path', '!=', '')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.billing.customer-purchase-order-manager', [
            'purchaseOrders' => $this->purchaseOrders,
            'customers' => $this->customers,
            'summary' => $this->summary,
        ]);
    }
}
