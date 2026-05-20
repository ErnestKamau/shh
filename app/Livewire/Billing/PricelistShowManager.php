<?php

namespace App\Livewire\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\CRM\CRMCustomer;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistEmailLog;
use App\Models\Billing\PricelistItem;
use App\Models\Currency;
use App\Models\System\SystemConfiguration;
use App\SampleType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class PricelistShowManager extends Component
{
    use WithFileUploads;

    public string $pricelistId;
    public string $activeTab = 'customer-assignment';
    public string $customerAssignmentTab = 'assigned-customers';
    public $print = null;

    public $message = '';
    public $messageType = 'success';

    public $pricelistForm = [];
    public $selectedCustomerId = '';
    public $customerSearch = '';
    public array $customerPickerIds = [];

    public $emailSubject = '';
    public $emailMessage = '';
    public array $emailCustomerIds = [];
    public string $emailLogFilter = 'all';

    public $showItemModal = false;
    public $editingItem = false;
    public $itemForm = [];
    public array $itemElementRows = [];
    public ?string $editingItemId = null;

    public bool $showItemSampleTypeDropdown = false;

    public bool $showItemAnalysisTypeDropdown = false;

    public string $itemSampleTypeSearch = '';

    public string $itemAnalysisTypeSearch = '';

    public $selectedItemIds = [];
    public $showCloneModal = false;
    public $cloneForm = [];
    public string $itemCommitFilter = 'all';

    public string $itemSearch = '';

    /** @var array<int, string> */
    public array $itemSampleTypeFilterIds = [];

    /** @var array<int, string> */
    public array $itemAnalysisTypeFilterIds = [];

    /** @var array<int, string> */
    public array $itemAnalysisElementFilterIds = [];

    public string $itemActiveFilter = '';

    public string $itemVatFilter = '';

    public string $itemInternalUseFilter = '';

    public string $itemExternalViewFilter = '';

    public int $itemsPerPage = 25;

    public int $itemsPage = 1;

    /** @var array<int, int> */
    public array $itemsPerPageOptions = [25, 50, 75, 100];

    public bool $showItemAdvancedFilters = false;

    public string $itemFilterSampleTypeSearch = '';

    public bool $showItemFilterSampleTypeDropdown = false;

    public string $itemFilterAnalysisTypeSearch = '';

    public bool $showItemFilterAnalysisTypeDropdown = false;

    public string $itemFilterAnalysisElementSearch = '';

    public bool $showItemFilterAnalysisElementDropdown = false;

    public bool $showDeleteItemConfirmModal = false;

    public ?string $pendingDeleteItemId = null;

    /** @var array<string, mixed>|null */
    public ?array $pendingDeleteItemPreview = null;

    public function mount(string $pricelistId, $print = null): void
    {
        $this->pricelistId = $pricelistId;
        $this->print = $print;
        $this->fillPricelistForm();
        $this->initializeEmailComposer();
        $this->resetItemForm();
        $this->resetCloneForm();
    }

    protected function pricelistRules(): array
    {
        return [
            'pricelistForm.description' => 'required|string|max:255',
            'pricelistForm.currency_id' => 'required|exists:currencies,id',
            'pricelistForm.valid_till' => 'nullable|date',
            'pricelistForm.status' => 'required|string|max:50',
            'pricelistForm.is_master' => 'boolean',
            'pricelistForm.active' => 'boolean',
        ];
    }

    protected function itemRules(): array
    {
        return [
            'itemForm.analysis_id' => 'required|exists:analysis_types,id',
            'itemForm.sample_type_id' => 'required|exists:sample_types,id',
            'itemElementRows' => 'required|array|min:1',
            'itemElementRows.*.analysis_element_id' => 'required|exists:analysis_elements,id',
            'itemElementRows.*.cost_price' => 'required|numeric|min:0',
            'itemElementRows.*.selling_price' => 'required|numeric|min:0',
            'itemElementRows.*.vat' => 'boolean',
            'itemForm.internal_use' => 'boolean',
            'itemForm.external_view' => 'boolean',
            'itemForm.active' => 'boolean',
        ];
    }

    protected function cloneRules(): array
    {
        return [
            'cloneForm.description' => 'required|string|max:255',
            'cloneForm.currency_id' => 'required|exists:currencies,id',
            'cloneForm.valid_till' => 'nullable|date',
            'cloneForm.is_master' => 'boolean',
            'cloneForm.active' => 'boolean',
            'selectedItemIds' => 'required|array|min:1',
        ];
    }

    protected function emailRules(): array
    {
        return [
            'emailSubject' => 'required|string|max:255',
            'emailMessage' => 'nullable|string|max:5000',
            'emailCustomerIds' => 'required|array|min:1',
        ];
    }

    public function getPricelistProperty()
    {
        $pricelist = Pricelist::query()
            ->with(['currency:id,code,description'])
            ->find($this->pricelistId);

        if (!$pricelist) {
            return null;
        }

        $pricelist->currency_code = $pricelist->currency?->code;
        $pricelist->currency_description = $pricelist->currency?->description;

        return $pricelist;
    }

    public function getItemsProperty()
    {
        $items = PricelistItem::query()
            ->with([
                'sampleType:id,name,code',
                'analysisType:id,name,code',
                'analysisElement:id,analyte_id',
                'analysisElement.analyte:id,name,code',
            ])
            ->where('pricelist_id', $this->pricelistId)
            ->get();

        return $items
            ->map(function (PricelistItem $item) {
                $sampleType = $item->sampleType;
                $analysisType = $item->analysisType;
                $analyte = $item->analysisElement?->analyte;
                $cost = (float) ($item->cost_price ?? 0);
                $selling = (float) ($item->selling_price ?? 0);
                $changedRaw = $item->changed_price;
                $changed = $changedRaw === null || $changedRaw === ''
                    ? $selling
                    : (float) $changedRaw;
                $profit = $selling - $cost;

                $item->sample_type_name = $sampleType->name ?? null;
                $item->sample_type_code = $sampleType->code ?? null;
                $item->analysis_type_name = $analysisType->name ?? null;
                $item->analysis_type_code = $analysisType->code ?? null;
                $item->analyte_name = $analyte?->name ?? 'N/A';
                $item->analyte_code = $analyte?->code;
                $item->profit = $profit;
                $item->profit_margin = $selling > 0 ? (($profit / $selling) * 100) : 0;
                $item->has_pending_change = abs($selling - $changed) > 0.004;

                return $item;
            })
            ->sortBy(function ($item) {
                return [
                    $item->level ?? PHP_INT_MAX,
                    optional($item->created_at)->getTimestamp() ?? 0,
                ];
            })
            ->values();
    }

    public function getFilteredItemsProperty(): Collection
    {
        return $this->items->filter(function ($item): bool {
            if ($this->itemCommitFilter === 'pending') {
                if (!(bool) ($item->has_pending_change ?? false)) {
                    return false;
                }
            } elseif ($this->itemCommitFilter === 'applied') {
                if ((bool) ($item->has_pending_change ?? false)) {
                    return false;
                }
            }

            if (! $this->itemMatchesSearch($item)) {
                return false;
            }

            if ($this->itemSampleTypeFilterIds !== []
                && ! in_array((string) ($item->sample_type_id ?? ''), $this->itemSampleTypeFilterIds, true)) {
                return false;
            }

            if ($this->itemAnalysisTypeFilterIds !== []
                && ! in_array((string) ($item->analysis_id ?? ''), $this->itemAnalysisTypeFilterIds, true)) {
                return false;
            }

            if ($this->itemAnalysisElementFilterIds !== []
                && ! in_array((string) ($item->analysis_element_id ?? ''), $this->itemAnalysisElementFilterIds, true)) {
                return false;
            }

            if (! $this->itemMatchesFlagFilter($item, $this->itemActiveFilter, 'active')) {
                return false;
            }

            if (! $this->itemMatchesFlagFilter($item, $this->itemVatFilter, 'vat')) {
                return false;
            }

            if (! $this->itemMatchesFlagFilter($item, $this->itemInternalUseFilter, 'internal_use')) {
                return false;
            }

            if (! $this->itemMatchesFlagFilter($item, $this->itemExternalViewFilter, 'external_view')) {
                return false;
            }

            return true;
        })->values();
    }

    public function getPaginatedFilteredItemsProperty(): Collection
    {
        $filtered = $this->filteredItems;
        $perPage = max(1, $this->itemsPerPage);
        $page = $this->resolvedItemsPage($filtered->count(), $perPage);

        return $filtered->forPage($page, $perPage)->values();
    }

    public function getItemPaginatorProperty(): LengthAwarePaginator
    {
        $filtered = $this->filteredItems;
        $perPage = max(1, $this->itemsPerPage);
        $total = $filtered->count();
        $page = $this->resolvedItemsPage($total, $perPage);

        return new LengthAwarePaginator(
            $this->paginatedFilteredItems->all(),
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'itemsPage',
            ]
        );
    }

    public function getHasActiveItemFiltersProperty(): bool
    {
        return $this->itemSearch !== ''
            || $this->itemSampleTypeFilterIds !== []
            || $this->itemAnalysisTypeFilterIds !== []
            || $this->itemAnalysisElementFilterIds !== []
            || $this->itemActiveFilter !== ''
            || $this->itemVatFilter !== ''
            || $this->itemInternalUseFilter !== ''
            || $this->itemExternalViewFilter !== '';
    }

    public function getItemFilterSampleTypesProperty(): Collection
    {
        $sampleTypeIds = $this->items
            ->pluck('sample_type_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($sampleTypeIds === []) {
            return collect();
        }

        return SampleType::query()
            ->select('id', 'code', 'name')
            ->whereIn('id', $sampleTypeIds)
            ->orderBy('name')
            ->get();
    }

    public function getFilteredItemFilterSampleTypesProperty(): Collection
    {
        $types = $this->itemFilterSampleTypes;
        $query = mb_strtolower(trim($this->itemFilterSampleTypeSearch));
        if ($query === '') {
            return $types;
        }

        return $types->filter(function ($sampleType) use ($query): bool {
            return str_contains(mb_strtolower((string) ($sampleType->name ?? '')), $query)
                || str_contains(mb_strtolower((string) ($sampleType->code ?? '')), $query);
        })->values();
    }

    public function getCanFilterByAnalysisTypeProperty(): bool
    {
        return $this->itemSampleTypeFilterIds !== [];
    }

    public function getCanFilterByParameterProperty(): bool
    {
        return $this->itemAnalysisTypeFilterIds !== [];
    }

    public function getItemFilterAnalysisTypesProperty(): Collection
    {
        if ($this->itemSampleTypeFilterIds === []) {
            return collect();
        }

        $analysisIds = $this->items
            ->filter(fn ($item) => in_array((string) ($item->sample_type_id ?? ''), $this->itemSampleTypeFilterIds, true))
            ->pluck('analysis_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($analysisIds === []) {
            return collect();
        }

        return AnalysisType::query()
            ->select('id', 'code', 'name', 'sample_type_id')
            ->whereIn('id', $analysisIds)
            ->whereIn('sample_type_id', $this->itemSampleTypeFilterIds)
            ->orderBy('name')
            ->get();
    }

    public function getFilteredItemFilterAnalysisTypesProperty(): Collection
    {
        $types = $this->itemFilterAnalysisTypes;
        $query = mb_strtolower(trim($this->itemFilterAnalysisTypeSearch));
        if ($query === '') {
            return $types;
        }

        return $types->filter(function ($analysisType) use ($query): bool {
            return str_contains(mb_strtolower((string) ($analysisType->name ?? '')), $query)
                || str_contains(mb_strtolower((string) ($analysisType->code ?? '')), $query);
        })->values();
    }

    public function getItemFilterAnalysisElementsProperty(): Collection
    {
        if ($this->itemSampleTypeFilterIds === [] || $this->itemAnalysisTypeFilterIds === []) {
            return collect();
        }

        $itemsQuery = $this->items
            ->filter(fn ($item) => in_array((string) ($item->sample_type_id ?? ''), $this->itemSampleTypeFilterIds, true))
            ->filter(fn ($item) => in_array((string) ($item->analysis_id ?? ''), $this->itemAnalysisTypeFilterIds, true));

        $elementIds = $itemsQuery
            ->pluck('analysis_element_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($elementIds === []) {
            return collect();
        }

        return AnalysisElements::query()
            ->with(['analyte:id,name,code'])
            ->whereIn('id', $elementIds)
            ->orderBy('level')
            ->get()
            ->map(function (AnalysisElements $element): object {
                $code = trim((string) ($element->analyte?->code ?? ''));
                $name = trim((string) ($element->analyte?->name ?? ''));

                return (object) [
                    'id' => (string) $element->id,
                    'label' => $code !== '' ? ($code . ' - ' . $name) : ($name !== '' ? $name : 'Element'),
                ];
            })
            ->values();
    }

    public function getFilteredItemFilterAnalysisElementsProperty(): Collection
    {
        $elements = $this->itemFilterAnalysisElements;
        $query = mb_strtolower(trim($this->itemFilterAnalysisElementSearch));
        if ($query === '') {
            return $elements;
        }

        return $elements->filter(function ($element) use ($query): bool {
            return str_contains(mb_strtolower((string) ($element->label ?? '')), $query);
        })->values();
    }

    public function getGroupedItemsProperty(): Collection
    {
        return $this->groupItems($this->paginatedFilteredItems);
    }

    public function setItemCommitFilter(string $filter): void
    {
        if (!in_array($filter, ['all', 'pending', 'applied'], true)) {
            return;
        }

        $this->itemCommitFilter = $filter;
        $this->resetItemsPage();
    }

    public function toggleItemAdvancedFilters(): void
    {
        $this->showItemAdvancedFilters = ! $this->showItemAdvancedFilters;
    }

    public function clearItemFilters(): void
    {
        $this->itemSearch = '';
        $this->itemSampleTypeFilterIds = [];
        $this->itemAnalysisTypeFilterIds = [];
        $this->itemAnalysisElementFilterIds = [];
        $this->itemActiveFilter = '';
        $this->itemVatFilter = '';
        $this->itemInternalUseFilter = '';
        $this->itemExternalViewFilter = '';
        $this->itemFilterSampleTypeSearch = '';
        $this->itemFilterAnalysisTypeSearch = '';
        $this->itemFilterAnalysisElementSearch = '';
        $this->showItemFilterSampleTypeDropdown = false;
        $this->showItemFilterAnalysisTypeDropdown = false;
        $this->showItemFilterAnalysisElementDropdown = false;
        $this->resetItemsPage();
    }

    public function resetItemsPage(): void
    {
        $this->itemsPage = 1;
    }

    public function goToItemsPage(int $page): void
    {
        $perPage = max(1, $this->itemsPerPage);
        $total = $this->filteredItems->count();
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $this->itemsPage = max(1, min($page, $lastPage));
    }

    public function previousItemsPage(): void
    {
        $this->goToItemsPage($this->itemsPage - 1);
    }

    public function nextItemsPage(): void
    {
        $this->goToItemsPage($this->itemsPage + 1);
    }

    public function toggleItemFilterSampleType(string $id): void
    {
        $id = (string) $id;
        if (in_array($id, $this->itemSampleTypeFilterIds, true)) {
            $this->itemSampleTypeFilterIds = array_values(array_filter(
                $this->itemSampleTypeFilterIds,
                fn (string $selectedId): bool => $selectedId !== $id
            ));
        } else {
            $this->itemSampleTypeFilterIds[] = $id;
        }

        $this->syncDependentItemFilters();
        $this->resetItemsPage();
    }

    public function removeItemFilterSampleType(string $id): void
    {
        $id = (string) $id;
        $this->itemSampleTypeFilterIds = array_values(array_filter(
            $this->itemSampleTypeFilterIds,
            fn (string $selectedId): bool => $selectedId !== $id
        ));
        $this->syncDependentItemFilters();
        $this->resetItemsPage();
    }

    public function toggleItemFilterAnalysisType(string $id): void
    {
        if (! $this->canFilterByAnalysisType) {
            return;
        }

        $id = (string) $id;
        if (in_array($id, $this->itemAnalysisTypeFilterIds, true)) {
            $this->itemAnalysisTypeFilterIds = array_values(array_filter(
                $this->itemAnalysisTypeFilterIds,
                fn (string $selectedId): bool => $selectedId !== $id
            ));
        } else {
            $this->itemAnalysisTypeFilterIds[] = $id;
        }

        $this->syncParameterFiltersAfterAnalysisTypeChange();
        $this->resetItemsPage();
    }

    public function removeItemFilterAnalysisType(string $id): void
    {
        $id = (string) $id;
        $this->itemAnalysisTypeFilterIds = array_values(array_filter(
            $this->itemAnalysisTypeFilterIds,
            fn (string $selectedId): bool => $selectedId !== $id
        ));
        $this->syncParameterFiltersAfterAnalysisTypeChange();
        $this->resetItemsPage();
    }

    public function toggleItemFilterAnalysisElement(string $id): void
    {
        if (! $this->canFilterByParameter) {
            return;
        }

        $id = (string) $id;
        if (in_array($id, $this->itemAnalysisElementFilterIds, true)) {
            $this->itemAnalysisElementFilterIds = array_values(array_filter(
                $this->itemAnalysisElementFilterIds,
                fn (string $selectedId): bool => $selectedId !== $id
            ));
        } else {
            $this->itemAnalysisElementFilterIds[] = $id;
        }

        $this->resetItemsPage();
    }

    public function removeItemFilterAnalysisElement(string $id): void
    {
        $id = (string) $id;
        $this->itemAnalysisElementFilterIds = array_values(array_filter(
            $this->itemAnalysisElementFilterIds,
            fn (string $selectedId): bool => $selectedId !== $id
        ));
        $this->resetItemsPage();
    }

    public function updatedItemSearch(): void
    {
        $this->resetItemsPage();
    }

    public function updatedItemSampleTypeFilterIds(): void
    {
        $this->syncDependentItemFilters();
        $this->resetItemsPage();
    }

    public function updatedItemAnalysisTypeFilterIds(): void
    {
        $this->syncParameterFiltersAfterAnalysisTypeChange();
        $this->resetItemsPage();
    }

    public function updatedItemAnalysisElementFilterIds(): void
    {
        $this->resetItemsPage();
    }

    public function updatedItemActiveFilter(): void
    {
        $this->resetItemsPage();
    }

    public function updatedItemVatFilter(): void
    {
        $this->resetItemsPage();
    }

    public function updatedItemInternalUseFilter(): void
    {
        $this->resetItemsPage();
    }

    public function updatedItemExternalViewFilter(): void
    {
        $this->resetItemsPage();
    }

    public function updatedItemsPerPage(): void
    {
        $this->resetItemsPage();
    }

    public function getAssignedCustomersProperty()
    {
        $assignments = PricelistCustomer::query()
            ->with(['customer:id,code,name,email,telephone1,active,customer_type,account_status'])
            ->where('pricelist_id', $this->pricelistId)
            ->get();

        if ($assignments->isEmpty()) {
            return collect();
        }

        $accountStatusIds = $assignments
            ->pluck('customer.account_status')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $accountStatusKeys = $accountStatusIds === []
            ? collect()
            : SystemConfiguration::query()
                ->whereIn('id', $accountStatusIds)
                ->pluck('key', 'id');

        return $assignments
            ->map(function (PricelistCustomer $assignment) use ($accountStatusKeys) {
                $customer = $assignment->customer;
                if (!$customer) {
                    return null;
                }

                return (object) [
                    'assignment_id' => $assignment->id,
                    'customer_id' => (string) $customer->id,
                    'customer_code' => $customer->code,
                    'customer_name' => $customer->name,
                    'email' => $customer->email,
                    'telephone1' => $customer->telephone1,
                    'active' => (bool) $customer->active,
                    'customer_type' => $customer->customer_type,
                    'account_setting' => $this->formatAccountSettingLabel(
                        $accountStatusKeys->get((string) $customer->account_status)
                    ),
                ];
            })
            ->filter()
            ->sortBy(function ($row) {
                return mb_strtolower((string) ($row->customer_name ?? ''));
            })
            ->values();
    }

    public function getAvailableCustomersProperty()
    {
        $assignedCustomerIds = PricelistCustomer::query()
            ->where('pricelist_id', $this->pricelistId)
            ->pluck('customer_id');

        $customers = CRMCustomer::query()
            ->where('active', true)
            ->where(function ($query): void {
                $query->where('is_hidden', false)
                    ->orWhereNull('is_hidden');
            })
            ->when($assignedCustomerIds->isNotEmpty(), function ($query) use ($assignedCustomerIds): void {
                $query->whereNotIn('id', $assignedCustomerIds->all());
            })
            ->get(['id', 'code', 'name', 'email', 'telephone1', 'customer_type'])
            ->map(function (CRMCustomer $customer) {
                return (object) [
                    'id' => (string) $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'telephone1' => $customer->telephone1,
                    'customer_type' => $customer->customer_type,
                ];
            });

        $search = mb_strtolower(trim((string) $this->customerSearch));
        if ($search !== '') {
            $customers = $customers->filter(function ($customer) use ($search) {
                return str_contains(mb_strtolower((string) ($customer->name ?? '')), $search)
                    || str_contains(mb_strtolower((string) ($customer->code ?? '')), $search)
                    || str_contains(mb_strtolower((string) ($customer->email ?? '')), $search);
            });
        }

        return $customers
            ->sortBy(function ($customer) {
                return mb_strtolower((string) ($customer->name ?? ''));
            })
            ->take(20)
            ->values();
    }

    public function getCustomerPickerChipsProperty()
    {
        if (count($this->customerPickerIds) === 0) {
            return collect();
        }

        return CRMCustomer::query()
            ->whereIn('id', $this->customerPickerIds)
            ->get(['id', 'code', 'name', 'email'])
            ->map(function (CRMCustomer $customer) {
                return (object) [
                    'id' => (string) $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'email' => $customer->email,
                ];
            })
            ->sortBy(function ($customer) {
                return mb_strtolower((string) ($customer->name ?? ''));
            })
            ->values();
    }

    public function getEmailCustomersProperty()
    {
        return $this->assignedCustomers
            ->filter(function ($customer) {
                return !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL);
            })
            ->values();
    }

    public function getRecentEmailLogsProperty()
    {
        return PricelistEmailLog::query()
            ->with([
                'customer:id,code,name',
                'sender:id,name',
            ])
            ->where('pricelist_id', $this->pricelistId)
            ->when(in_array($this->emailLogFilter, ['sent', 'failed'], true), function ($query): void {
                $query->where('status', $this->emailLogFilter);
            })
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(function (PricelistEmailLog $log) {
                $log->customer_code = $log->customer?->code;
                $log->customer_name_ref = $log->customer?->name;
                $log->sender_name = $log->sender?->name;

                return $log;
            });
    }

    public function getEmailLogCountsProperty(): array
    {
        $base = PricelistEmailLog::query()
            ->where('pricelist_id', $this->pricelistId);

        return [
            'all' => (clone $base)->count(),
            'sent' => (clone $base)->where('status', 'sent')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
        ];
    }

    public function getSampleTypesProperty()
    {
        return SampleType::query()
            ->select('id', 'code', 'name')
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAnalysisTypesProperty()
    {
        return AnalysisType::query()
            ->select('id', 'code', 'name')
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAvailableAnalysisTypesProperty()
    {
        if (empty($this->itemForm['sample_type_id'])) {
            return $this->analysisTypes;
        }

        return AnalysisType::query()
            ->select('id', 'code', 'name')
            ->where('active', true)
            ->where('sample_type_id', $this->itemForm['sample_type_id'])
            ->orderBy('name')
            ->get();
    }

    public function getFilteredItemSampleTypesProperty()
    {
        $types = $this->sampleTypes;
        $query = mb_strtolower(trim($this->itemSampleTypeSearch));
        if ($query === '') {
            return $types;
        }

        return $types->filter(function ($st) use ($query) {
            return str_contains(mb_strtolower((string) ($st->name ?? '')), $query)
                || str_contains(mb_strtolower((string) ($st->code ?? '')), $query);
        })->values();
    }

    public function getFilteredItemAnalysisTypesProperty()
    {
        $types = $this->availableAnalysisTypes;
        $query = mb_strtolower(trim($this->itemAnalysisTypeSearch));
        if ($query === '') {
            return $types;
        }

        return $types->filter(function ($at) use ($query) {
            return str_contains(mb_strtolower((string) ($at->name ?? '')), $query)
                || str_contains(mb_strtolower((string) ($at->code ?? '')), $query);
        })->values();
    }

    public function getCurrenciesProperty()
    {
        return Currency::query()
            ->select('id', 'code', 'description')
            ->where('active', true)
            ->orderBy('code')
            ->get();
    }

    public function getSummaryProperty(): array
    {
        $items = $this->items;
        $assignedCustomers = $this->assignedCustomers;

        return [
            'total_items' => $items->count(),
            'active_items' => $items->where('active', true)->count(),
            'changed_items' => $items->filter(function ($item) {
                return round((float) ($item->selling_price ?? 0), 2) !== round((float) ($item->changed_price ?? 0), 2);
            })->count(),
            'assigned_customers' => $assignedCustomers->count(),
        ];
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setCustomerAssignmentTab(string $tab): void
    {
        if (!in_array($tab, ['assigned-customers', 'email-pricelist', 'recent-sends'], true)) {
            return;
        }

        $this->customerAssignmentTab = $tab;
    }

    public function savePricelist(): void
    {
        $this->validate($this->pricelistRules());

        $pricelist = $this->pricelist;
        if (!$pricelist) {
            $this->showMessage('Pricelist not found.', 'danger');
            return;
        }

        $newCurrencyId = $this->pricelistForm['currency_id'];
        $oldCurrencyId = $pricelist->currency_id;

        try {
            DB::transaction(function () use ($pricelist, $newCurrencyId, $oldCurrencyId): void {
                Pricelist::query()
                    ->where('id', $this->pricelistId)
                    ->update([
                        'description' => $this->pricelistForm['description'],
                        'currency_id' => $newCurrencyId,
                        'valid_till' => $this->pricelistForm['valid_till'] ?: null,
                        'status' => $this->pricelistForm['status'],
                        'is_master' => (bool) ($this->pricelistForm['is_master'] ?? false),
                        'active' => (bool) ($this->pricelistForm['active'] ?? true),
                        'updated_at' => now(),
                    ]);

                if ($oldCurrencyId !== $newCurrencyId && function_exists('convert_currency')) {
                    $items = PricelistItem::query()
                        ->where('pricelist_id', $this->pricelistId)
                        ->get();

                    foreach ($items as $item) {
                        PricelistItem::query()
                            ->where('id', $item->id)
                            ->update([
                                'cost_price' => convert_currency((float) ($item->cost_price ?? 0), $oldCurrencyId, $newCurrencyId),
                                'selling_price' => convert_currency((float) ($item->selling_price ?? 0), $oldCurrencyId, $newCurrencyId),
                                'changed_price' => convert_currency((float) ($item->changed_price ?? 0), $oldCurrencyId, $newCurrencyId),
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

            $this->fillPricelistForm();
            $this->showMessage('Pricelist details updated successfully.', 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Failed to update pricelist: ' . $e->getMessage(), 'danger');
        }
    }

    public function assignCustomer(?string $customerId = null): void
    {
        $customerId = $customerId ?: $this->selectedCustomerId;

        if (!$customerId) {
            $this->showMessage('Select a customer to assign.', 'danger');
            return;
        }

        $exists = CRMCustomer::query()->where('id', $customerId)->exists();
        if (!$exists) {
            $this->showMessage('Selected customer was not found.', 'danger');
            return;
        }

        $alreadyAssigned = PricelistCustomer::query()
            ->where('pricelist_id', $this->pricelistId)
            ->where('customer_id', $customerId)
            ->exists();

        if ($alreadyAssigned) {
            $this->showMessage('Customer is already assigned to this pricelist.', 'danger');
            return;
        }

        try {
            PricelistCustomer::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $this->pricelistId,
                'customer_id' => $customerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->selectedCustomerId = '';
            $this->customerSearch = '';
            if (!in_array($customerId, $this->emailCustomerIds, true)) {
                $this->emailCustomerIds[] = $customerId;
            }
            $this->showMessage('Customer assigned successfully.', 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Failed to assign customer: ' . $e->getMessage(), 'danger');
        }
    }

    public function addCustomerChip(string $customerId): void
    {
        if (!in_array($customerId, $this->customerPickerIds, true)) {
            $this->customerPickerIds[] = $customerId;
        }

        $this->selectedCustomerId = '';
        $this->customerSearch = '';
    }

    public function removeCustomerChip(string $customerId): void
    {
        $this->customerPickerIds = array_values(array_filter(
            $this->customerPickerIds,
            fn ($id) => (string) $id !== (string) $customerId
        ));
    }

    public function clearCustomerChips(): void
    {
        $this->customerPickerIds = [];
    }

    public function assignCustomerChips(): void
    {
        if (count($this->customerPickerIds) === 0) {
            $this->showMessage('Select at least one customer chip to assign.', 'danger');
            return;
        }

        $assigned = 0;

        try {
            foreach ($this->customerPickerIds as $customerId) {
                $alreadyAssigned = PricelistCustomer::query()
                    ->where('pricelist_id', $this->pricelistId)
                    ->where('customer_id', $customerId)
                    ->exists();

                if ($alreadyAssigned) {
                    continue;
                }

                PricelistCustomer::query()->create([
                    'id' => (string) Str::uuid(),
                    'pricelist_id' => $this->pricelistId,
                    'customer_id' => $customerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!in_array($customerId, $this->emailCustomerIds, true)) {
                    $this->emailCustomerIds[] = $customerId;
                }

                $assigned++;
            }

            $this->customerPickerIds = [];

            if ($assigned > 0) {
                $this->showMessage($assigned . ' customer(s) assigned successfully.', 'success');
            } else {
                $this->showMessage('All selected customers are already assigned.', 'danger');
            }
        } catch (\Throwable $e) {
            $this->showMessage('Failed to assign selected customers: ' . $e->getMessage(), 'danger');
        }
    }

    public function removeCustomer(string $assignmentId): void
    {
        try {
            $customerId = PricelistCustomer::query()
                ->where('id', $assignmentId)
                ->where('pricelist_id', $this->pricelistId)
                ->value('customer_id');

            PricelistCustomer::query()
                ->where('id', $assignmentId)
                ->where('pricelist_id', $this->pricelistId)
                ->delete();

            if ($customerId) {
                $this->emailCustomerIds = array_values(array_filter(
                    $this->emailCustomerIds,
                    fn ($id) => (string) $id !== (string) $customerId
                ));
            }

            $this->showMessage('Customer assignment removed.', 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Failed to remove customer assignment: ' . $e->getMessage(), 'danger');
        }
    }

    public function toggleEmailCustomer(string $customerId): void
    {
        if (in_array($customerId, $this->emailCustomerIds, true)) {
            $this->emailCustomerIds = array_values(array_filter(
                $this->emailCustomerIds,
                fn ($id) => (string) $id !== (string) $customerId
            ));
            return;
        }

        $this->emailCustomerIds[] = $customerId;
    }

    public function selectAllEmailCustomers(): void
    {
        $this->emailCustomerIds = $this->emailCustomers
            ->pluck('customer_id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function clearEmailCustomers(): void
    {
        $this->emailCustomerIds = [];
    }

    public function setEmailLogFilter(string $filter): void
    {
        if (!in_array($filter, ['all', 'sent', 'failed'], true)) {
            return;
        }

        $this->emailLogFilter = $filter;
    }

    public function sendPricelistEmail(): void
    {
        $this->validate($this->emailRules());

        $pricelist = $this->pricelist;
        if (!$pricelist) {
            $this->showMessage('Pricelist not found.', 'danger');
            return;
        }

        $customers = $this->emailCustomers
            ->whereIn('customer_id', $this->emailCustomerIds)
            ->values();

        if ($customers->count() === 0) {
            $this->showMessage('No valid customer email recipients selected.', 'danger');
            return;
        }

        $company = function_exists('getCompanyDetails') ? (getCompanyDetails() ?: []) : [];
        $companyName = $company['name'] ?? config('app.name', 'LIMS');

        $attachmentPath = false;
        $attachmentName = null;

        $sent = 0;
        $failed = 0;
        $senderId = Auth::id();

        foreach ($customers as $customer) {
            try {
                $messageBody = trim((string) $this->emailMessage) !== ''
                    ? $this->emailMessage
                    : 'We have revised our pricelist. Please find the pricelist attached.';

                $body = 'Hi ' . $customer->customer_name . ',<br><br>'
                    . $messageBody . '<br><br>Regards,<br><br>' . $companyName;

                notify_user($body, $customer->email, $this->emailSubject, $attachmentPath);
                $sent++;

                $this->logEmailAttempt(
                    customerId: $customer->customer_id,
                    customerName: $customer->customer_name,
                    recipientEmail: $customer->email,
                    subject: $this->emailSubject,
                    status: 'sent',
                    senderId: $senderId,
                    attachmentName: $attachmentName,
                    hasAttachment: (bool) $attachmentPath,
                    errorMessage: null
                );
            } catch (\Throwable $e) {
                $failed++;

                $this->logEmailAttempt(
                    customerId: $customer->customer_id,
                    customerName: $customer->customer_name,
                    recipientEmail: $customer->email,
                    subject: $this->emailSubject,
                    status: 'failed',
                    senderId: $senderId,
                    attachmentName: $attachmentName,
                    hasAttachment: (bool) $attachmentPath,
                    errorMessage: $e->getMessage()
                );
            }
        }

        if ($sent > 0 && $failed === 0) {
            $this->showMessage('Pricelist email sent to ' . $sent . ' customer(s).', 'success');
            return;
        }

        if ($sent > 0) {
            $this->showMessage('Pricelist email sent to ' . $sent . ' customer(s), failed for ' . $failed . '.', 'danger');
            return;
        }

        $this->showMessage('Failed to send pricelist emails.', 'danger');
    }

    public function retryEmailLog(string $logId): void
    {
        $log = PricelistEmailLog::query()
            ->where('id', $logId)
            ->where('pricelist_id', $this->pricelistId)
            ->first();

        if (!$log) {
            $this->showMessage('Email log entry not found.', 'danger');
            return;
        }

        if (empty($log->recipient_email) || !filter_var($log->recipient_email, FILTER_VALIDATE_EMAIL)) {
            $this->showMessage('Log entry has an invalid recipient email.', 'danger');
            return;
        }

        $company = function_exists('getCompanyDetails') ? (getCompanyDetails() ?: []) : [];
        $companyName = $company['name'] ?? config('app.name', 'LIMS');

        $recipientName = $log->customer_name ?: 'Customer';
        $messageBody = trim((string) $this->emailMessage) !== ''
            ? $this->emailMessage
            : 'We have revised our pricelist. Please find the pricelist attached.';

        $body = 'Hi ' . $recipientName . ',<br><br>'
            . $messageBody . '<br><br>Regards,<br><br>' . $companyName;

        $attachmentPath = false;
        $attachmentName = null;

        if ($log->has_attachment && !empty($log->attachment_name)) {
            $candidate = storage_path('app/pricelist/' . $log->attachment_name);
            if (file_exists($candidate)) {
                $attachmentPath = $candidate;
                $attachmentName = $log->attachment_name;
            }
        }

        $senderId = Auth::id();

        try {
            notify_user($body, $log->recipient_email, $log->subject, $attachmentPath);

            $this->logEmailAttempt(
                customerId: $log->customer_id,
                customerName: $recipientName,
                recipientEmail: $log->recipient_email,
                subject: $log->subject,
                status: 'sent',
                senderId: $senderId,
                attachmentName: $attachmentName,
                hasAttachment: (bool) $attachmentPath,
                errorMessage: null
            );

            $this->showMessage('Retry email sent successfully.', 'success');
        } catch (\Throwable $e) {
            $this->logEmailAttempt(
                customerId: $log->customer_id,
                customerName: $recipientName,
                recipientEmail: $log->recipient_email,
                subject: $log->subject,
                status: 'failed',
                senderId: $senderId,
                attachmentName: $attachmentName,
                hasAttachment: (bool) $attachmentPath,
                errorMessage: $e->getMessage()
            );

            $this->showMessage('Retry failed: ' . $e->getMessage(), 'danger');
        }
    }

    public function showCreateItemModal(): void
    {
        $this->editingItem = false;
        $this->editingItemId = null;
        $this->resetItemForm();
        $this->showItemModal = true;
    }

    public function showEditItemModal(string $itemId): void
    {
        $item = PricelistItem::query()->where('id', $itemId)->first();
        if (!$item) {
            $this->showMessage('Pricelist item not found.', 'danger');
            return;
        }

        $this->editingItem = true;
        $this->editingItemId = (string) $item->id;
        $this->showItemSampleTypeDropdown = false;
        $this->showItemAnalysisTypeDropdown = false;
        $this->itemSampleTypeSearch = '';
        $this->itemAnalysisTypeSearch = '';
        $this->itemForm = [
            'id' => $item->id,
            'analysis_id' => $item->analysis_id,
            'sample_type_id' => $item->sample_type_id,
            'internal_use' => (bool) $item->internal_use,
            'external_view' => (bool) $item->external_view,
            'active' => (bool) $item->active,
        ];

        $this->refreshItemElementRows();

        $this->showItemModal = true;
    }

    public function setItemSampleTypeDropdown(bool $open): void
    {
        $this->showItemSampleTypeDropdown = $open;
        if ($open) {
            $this->showItemAnalysisTypeDropdown = false;
        }
    }

    public function setItemAnalysisTypeDropdown(bool $open): void
    {
        if ($open && empty($this->itemForm['sample_type_id'])) {
            return;
        }

        $this->showItemAnalysisTypeDropdown = $open;
        if ($open) {
            $this->showItemSampleTypeDropdown = false;
        }
    }

    public function selectItemSampleType(string $id): void
    {
        $this->itemForm['sample_type_id'] = $id;
        $this->showItemSampleTypeDropdown = false;
        $this->itemSampleTypeSearch = '';

        if (!empty($this->itemForm['analysis_id'])) {
            $analysisType = AnalysisType::query()
                ->where('id', $this->itemForm['analysis_id'])
                ->first(['id', 'sample_type_id']);

            if (!$analysisType || (string) $analysisType->sample_type_id !== (string) $id) {
                $this->itemForm['analysis_id'] = '';
            }
        }

        $this->refreshItemElementRows();
    }

    public function selectItemAnalysisType(string $id): void
    {
        $this->itemForm['analysis_id'] = $id;
        $this->showItemAnalysisTypeDropdown = false;
        $this->itemAnalysisTypeSearch = '';
        $this->refreshItemElementRows();
    }

    public function clearItemSampleType(): void
    {
        $this->itemForm['sample_type_id'] = '';
        $this->itemForm['analysis_id'] = '';
        $this->itemElementRows = [];
        $this->itemSampleTypeSearch = '';
        $this->showItemSampleTypeDropdown = false;
    }

    public function clearItemAnalysisType(): void
    {
        $this->itemForm['analysis_id'] = '';
        $this->itemElementRows = [];
        $this->itemAnalysisTypeSearch = '';
        $this->showItemAnalysisTypeDropdown = false;
    }

    public function saveItem(): void
    {
        $this->validate($this->itemRules());

        if (count($this->itemElementRows) === 0) {
            $this->showMessage('No analysis elements found for the selected analysis type.', 'danger');
            return;
        }

        try {
            DB::transaction(function (): void {
                $maxLevel = (int) PricelistItem::query()
                    ->where('pricelist_id', $this->pricelistId)
                    ->max('level');

                $nextLevel = $maxLevel > 0 ? $maxLevel + 1 : 1;

                foreach ($this->itemElementRows as $row) {
                    $analysisElementId = (string) ($row['analysis_element_id'] ?? '');
                    if ($analysisElementId === '') {
                        continue;
                    }

                    $proposedPrice = (float) ($row['selling_price'] ?? 0);

                    $payload = [
                        'analysis_id' => $this->itemForm['analysis_id'],
                        'analysis_element_id' => $analysisElementId,
                        'sample_type_id' => $this->itemForm['sample_type_id'],
                        'cost_price' => (float) ($row['cost_price'] ?? 0),
                        'changed_price' => $proposedPrice,
                        'vat' => (bool) ($row['vat'] ?? false),
                        'internal_use' => (bool) ($this->itemForm['internal_use'] ?? false),
                        'external_view' => (bool) ($this->itemForm['external_view'] ?? true),
                        'active' => (bool) ($this->itemForm['active'] ?? true),
                        'updated_at' => now(),
                    ];

                    $existing = PricelistItem::query()
                        ->where('pricelist_id', $this->pricelistId)
                        ->where('sample_type_id', $this->itemForm['sample_type_id'])
                        ->where('analysis_id', $this->itemForm['analysis_id'])
                        ->where('analysis_element_id', $analysisElementId)
                        ->first();

                    if ($existing) {
                        PricelistItem::query()
                            ->where('id', $existing->id)
                            ->update($payload);

                        continue;
                    }

                    $payload = array_merge($payload, [
                        'id' => (string) Str::uuid(),
                        'pricelist_id' => $this->pricelistId,
                        'selling_price' => 0,
                        'level' => $nextLevel,
                        'created_at' => now(),
                    ]);

                    PricelistItem::query()->create($payload);
                    $nextLevel++;
                }

                Pricelist::query()
                    ->where('id', $this->pricelistId)
                    ->update([
                        'status' => 'has-changes',
                        'updated_at' => now(),
                    ]);
            });

            $this->showMessage($this->editingItem ? 'Pricelist analysis items updated successfully.' : 'Pricelist analysis items added successfully.', 'success');
            $this->closeItemModal();
        } catch (\Throwable $e) {
            $this->showMessage('Failed to save pricelist item: ' . $e->getMessage(), 'danger');
        }
    }

    public function openDeleteItemConfirmModal(string $itemId): void
    {
        $item = PricelistItem::query()
            ->with([
                'sampleType:id,name,code',
                'analysisType:id,name,code',
                'analysisElement:id,analyte_id',
                'analysisElement.analyte:id,name,code',
            ])
            ->where('pricelist_id', $this->pricelistId)
            ->where('id', $itemId)
            ->first();

        if (! $item) {
            $this->showMessage('Pricelist item not found.', 'danger');

            return;
        }

        $this->pendingDeleteItemId = $itemId;
        $this->pendingDeleteItemPreview = $this->previewFromPricelistItem($item);
        $this->showDeleteItemConfirmModal = true;
    }

    public function closeDeleteItemConfirmModal(): void
    {
        $this->showDeleteItemConfirmModal = false;
        $this->pendingDeleteItemId = null;
        $this->pendingDeleteItemPreview = null;
    }

    public function confirmDeleteItem(): void
    {
        if ($this->pendingDeleteItemId === null) {
            return;
        }

        $itemId = $this->pendingDeleteItemId;

        try {
            $deleted = PricelistItem::query()
                ->where('pricelist_id', $this->pricelistId)
                ->where('id', $itemId)
                ->delete();

            if ($deleted === 0) {
                $this->showMessage('Pricelist item not found.', 'danger');
            } else {
                $this->selectedItemIds = array_values(array_filter(
                    $this->selectedItemIds,
                    fn ($id) => $id !== $itemId
                ));
                $this->showMessage('Pricelist item removed.', 'success');
            }
        } catch (\Throwable $e) {
            $this->showMessage('Failed to remove item: ' . $e->getMessage(), 'danger');
        } finally {
            $this->closeDeleteItemConfirmModal();
        }
    }

    public function moveItem(string $itemId, string $direction): void
    {
        try {
            DB::transaction(function () use ($itemId, $direction): void {
                $item = PricelistItem::query()->where('id', $itemId)->first();
                if (!$item) {
                    return;
                }

                $currentLevel = (int) ($item->level ?? 1);
                $targetLevel = $direction === 'up' ? max(1, $currentLevel - 1) : $currentLevel + 1;

                $sibling = PricelistItem::query()
                    ->where('pricelist_id', $this->pricelistId)
                    ->where('level', $targetLevel)
                    ->first();

                if ($sibling) {
                    PricelistItem::query()->where('id', $sibling->id)->update([
                        'level' => $currentLevel,
                        'updated_at' => now(),
                    ]);
                }

                PricelistItem::query()->where('id', $itemId)->update([
                    'level' => $targetLevel,
                    'updated_at' => now(),
                ]);
            });

            $this->showMessage('Item order updated.', 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Failed to move item: ' . $e->getMessage(), 'danger');
        }
    }

    public function applyPriceChanges(): void
    {
        try {
            DB::transaction(function (): void {
                PricelistItem::query()
                    ->where('pricelist_id', $this->pricelistId)
                    ->get()
                    ->each(function (PricelistItem $item): void {
                        $item->update([
                            'selling_price' => $item->changed_price,
                            'updated_at' => now(),
                        ]);
                    });

                $pricelist = Pricelist::query()->where('id', $this->pricelistId)->first();
                $rev = (int) ($pricelist->revision_number ?? 1);

                Pricelist::query()
                    ->where('id', $this->pricelistId)
                    ->update([
                        'status' => 'no-changes',
                        'revision_number' => (string) ($rev + 1),
                        'updated_at' => now(),
                    ]);
            });

            $this->showMessage('Price changes applied successfully.', 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Failed to apply price changes: ' . $e->getMessage(), 'danger');
        }
    }

    public function showCloneModal(): void
    {
        if (count($this->selectedItemIds) === 0) {
            $this->showMessage('Select at least one item to clone.', 'danger');
            return;
        }

        $source = $this->pricelist;
        $this->cloneForm = [
            'description' => $source?->description ? ($source->description . ' (Copy)') : 'Pricelist Copy',
            'currency_id' => $source?->currency_id ?? '',
            'valid_till' => $source?->valid_till,
            'is_master' => false,
            'active' => true,
        ];

        $this->showCloneModal = true;
    }

    public function cloneSelectedItems(): void
    {
        $this->validate($this->cloneRules());

        try {
            $newPricelistId = null;

            DB::transaction(function () use (&$newPricelistId): void {
                $newPricelistId = (string) Str::uuid();

                Pricelist::query()->create([
                    'id' => $newPricelistId,
                    'code' => $this->generatePricelistCode(),
                    'description' => $this->cloneForm['description'],
                    'currency_id' => $this->cloneForm['currency_id'],
                    'is_master' => (bool) ($this->cloneForm['is_master'] ?? false),
                    'active' => (bool) ($this->cloneForm['active'] ?? true),
                    'document_no' => 'DOC-',
                    'revision_number' => '1',
                    'status' => 'no-changes',
                    'valid_till' => $this->cloneForm['valid_till'] ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $items = PricelistItem::query()
                    ->whereIn('id', $this->selectedItemIds)
                    ->get();

                $items = $items
                    ->sortBy(function ($item) {
                        return [
                            $item->level ?? PHP_INT_MAX,
                            optional($item->created_at)->getTimestamp() ?? 0,
                        ];
                    })
                    ->values();

                $level = 1;
                foreach ($items as $item) {
                    PricelistItem::query()->create([
                        'id' => (string) Str::uuid(),
                        'pricelist_id' => $newPricelistId,
                        'analysis_id' => $item->analysis_id,
                        'analysis_element_id' => $item->analysis_element_id,
                        'sample_type_id' => $item->sample_type_id,
                        'cost_price' => $item->cost_price,
                        'selling_price' => $item->selling_price,
                        'changed_price' => $item->changed_price,
                        'vat' => (bool) $item->vat,
                        'internal_use' => (bool) $item->internal_use,
                        'external_view' => (bool) $item->external_view,
                        'active' => (bool) $item->active,
                        'level' => $level,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $level++;
                }
            });

            $this->showMessage('Pricelist clone created successfully.', 'success');
            $this->selectedItemIds = [];
            $this->showCloneModal = false;

            if ($newPricelistId) {
                $this->redirect(route('show-pricelist', ['id' => $newPricelistId]), navigate: true);
            }
        } catch (\Throwable $e) {
            $this->showMessage('Failed to clone pricelist: ' . $e->getMessage(), 'danger');
        }
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->editingItem = false;
        $this->editingItemId = null;
        $this->resetItemForm();
        $this->resetValidation();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    private function showMessage(string $message, string $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    private function fillPricelistForm(): void
    {
        $pricelist = $this->pricelist;

        $this->pricelistForm = [
            'description' => $pricelist->description ?? '',
            'currency_id' => $pricelist->currency_id ?? '',
            'valid_till' => $pricelist->valid_till ?? null,
            'status' => $pricelist->status ?? 'no-changes',
            'is_master' => (bool) ($pricelist->is_master ?? false),
            'active' => (bool) ($pricelist->active ?? true),
        ];
    }

    private function initializeEmailComposer(): void
    {
        $company = function_exists('getCompanyDetails') ? (getCompanyDetails() ?: []) : [];
        $companyName = $company['name'] ?? config('app.name', 'LIMS');

        $this->emailSubject = '[' . $companyName . '] ' . date('Y') . ' Pricelist';
        $this->emailMessage = 'We have revised our pricelist. Please find the pricelist attached.';

        $this->emailCustomerIds = $this->emailCustomers
            ->pluck('customer_id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    private function logEmailAttempt(
        ?string $customerId,
        string $customerName,
        string $recipientEmail,
        string $subject,
        string $status,
        ?string $senderId,
        ?string $attachmentName,
        bool $hasAttachment,
        ?string $errorMessage
    ): void {
        PricelistEmailLog::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $this->pricelistId,
            'customer_id' => $customerId,
            'sent_by' => $senderId,
            'customer_name' => $customerName,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'has_attachment' => $hasAttachment,
            'attachment_name' => $attachmentName,
            'status' => $status,
            'error_message' => $errorMessage,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resetItemForm(): void
    {
        $this->itemForm = [
            'analysis_id' => '',
            'sample_type_id' => '',
            'internal_use' => false,
            'external_view' => true,
            'active' => true,
        ];

        $this->itemElementRows = [];
        $this->showItemSampleTypeDropdown = false;
        $this->showItemAnalysisTypeDropdown = false;
        $this->itemSampleTypeSearch = '';
        $this->itemAnalysisTypeSearch = '';
    }

    private function refreshItemElementRows(): void
    {
        $sampleTypeId = (string) ($this->itemForm['sample_type_id'] ?? '');
        $analysisId = (string) ($this->itemForm['analysis_id'] ?? '');

        if ($sampleTypeId === '' || $analysisId === '') {
            $this->itemElementRows = [];
            return;
        }

        $analysisType = AnalysisType::query()
            ->where('id', $analysisId)
            ->first(['id', 'sample_type_id']);

        if (!$analysisType || (string) $analysisType->sample_type_id !== $sampleTypeId) {
            $this->itemElementRows = [];
            return;
        }

        $elements = AnalysisElements::query()
            ->with(['analyte:id,name,code'])
            ->where('analysis_type_id', $analysisId)
            ->where(function ($query): void {
                $query->where('active', true)
                    ->orWhereNull('active');
            })
            ->orderBy('level')
            ->get(['id', 'analysis_type_id', 'analyte_id', 'level', 'active']);

        $existingItems = PricelistItem::query()
            ->where('pricelist_id', $this->pricelistId)
            ->where('sample_type_id', $sampleTypeId)
            ->where('analysis_id', $analysisId)
            ->get()
            ->keyBy(function ($item) {
                return (string) ($item->analysis_element_id ?? '');
            });

        $this->itemElementRows = $elements->map(function ($element) use ($existingItems) {
            $existing = $existingItems->get((string) $element->id);
            $analyteCode = trim((string) ($element->analyte?->code ?? ''));
            $analyteName = trim((string) ($element->analyte?->name ?? ''));

            $proposedPrice = 0.0;
            if ($existing) {
                $changedRaw = $existing->changed_price;
                $proposedPrice = $changedRaw === null || $changedRaw === ''
                    ? (float) ($existing->selling_price ?? 0)
                    : (float) $changedRaw;
            }

            return [
                'analysis_element_id' => (string) $element->id,
                'analyte_label' => trim($analyteCode !== '' ? ($analyteCode . ' - ' . $analyteName) : $analyteName),
                'cost_price' => (float) ($existing->cost_price ?? 0),
                'selling_price' => $proposedPrice,
                'vat' => (bool) ($existing->vat ?? false),
            ];
        })->values()->all();
    }

    private function resetCloneForm(): void
    {
        $this->cloneForm = [
            'description' => '',
            'currency_id' => '',
            'valid_till' => null,
            'is_master' => false,
            'active' => true,
        ];
    }

    private function generatePricelistCode(): string
    {
        if (function_exists('getNamingConventionCode')) {
            try {
                return (string) getNamingConventionCode('Pricelist', false, 'PL-');
            } catch (\Throwable $e) {
                // Fallback when naming convention helper is unavailable.
            }
        }

        return 'PL-' . now()->format('ymdHis');
    }

    private function formatAccountSettingLabel(?string $raw): string
    {
        $normalized = strtoupper(trim((string) $raw));

        return match ($normalized) {
            'POSTPAID', 'POST PAID' => 'Post Paid',
            'PREPAID', 'PRE PAID' => 'Prepaid',
            default => $normalized !== '' ? ucwords(strtolower(str_replace('_', ' ', $normalized))) : 'Not set',
        };
    }

    private function groupItems(Collection $items): Collection
    {
        return $items
            ->groupBy(function ($item) {
                return (string) ($item->sample_type_id ?? '');
            })
            ->map(function ($sampleItems) {
                $firstSampleItem = $sampleItems->first();

                $analysisGroups = $sampleItems
                    ->groupBy(function ($item) {
                        return (string) ($item->analysis_id ?? '');
                    })
                    ->map(function ($analysisItems) {
                        $firstAnalysisItem = $analysisItems->first();

                        return (object) [
                            'analysis_id' => (string) ($firstAnalysisItem->analysis_id ?? ''),
                            'analysis_type_name' => $firstAnalysisItem->analysis_type_name ?? 'Unassigned Analysis',
                            'analysis_type_code' => $firstAnalysisItem->analysis_type_code,
                            'total_amount' => (float) $analysisItems->sum(function ($item) {
                                return (float) ($item->selling_price ?? 0);
                            }),
                            'rows' => $analysisItems,
                        ];
                    })
                    ->sortBy(function ($group) {
                        return mb_strtolower((string) ($group->analysis_type_name ?? ''));
                    })
                    ->values();

                return (object) [
                    'sample_type_id' => (string) ($firstSampleItem->sample_type_id ?? ''),
                    'sample_type_name' => $firstSampleItem->sample_type_name ?? 'Unassigned Sample Type',
                    'sample_type_code' => $firstSampleItem->sample_type_code,
                    'analysis_groups' => $analysisGroups,
                ];
            })
            ->filter(function ($group) {
                return $group->analysis_groups->count() > 0;
            })
            ->sortBy(function ($group) {
                return mb_strtolower((string) ($group->sample_type_name ?? ''));
            })
            ->values();
    }

    private function itemMatchesSearch(mixed $item): bool
    {
        $query = mb_strtolower(trim($this->itemSearch));
        if ($query === '') {
            return true;
        }

        $haystacks = [
            (string) ($item->analyte_name ?? ''),
            (string) ($item->analyte_code ?? ''),
            (string) ($item->analysis_type_name ?? ''),
            (string) ($item->analysis_type_code ?? ''),
            (string) ($item->sample_type_name ?? ''),
            (string) ($item->sample_type_code ?? ''),
        ];

        foreach ($haystacks as $haystack) {
            if ($haystack !== '' && str_contains(mb_strtolower($haystack), $query)) {
                return true;
            }
        }

        return false;
    }

    private function itemMatchesFlagFilter(mixed $item, string $filter, string $field): bool
    {
        if ($filter === '') {
            return true;
        }

        return (bool) ($item->{$field} ?? false) === ($filter === '1');
    }

    private function resolvedItemsPage(int $total, int $perPage): int
    {
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);

        return max(1, min($this->itemsPage, $lastPage));
    }

    private function syncDependentItemFilters(): void
    {
        if ($this->itemSampleTypeFilterIds === []) {
            $this->itemAnalysisTypeFilterIds = [];
            $this->itemAnalysisElementFilterIds = [];
            $this->showItemFilterAnalysisTypeDropdown = false;
            $this->showItemFilterAnalysisElementDropdown = false;

            return;
        }

        $validAnalysisIds = $this->itemFilterAnalysisTypes
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->itemAnalysisTypeFilterIds = array_values(array_filter(
            $this->itemAnalysisTypeFilterIds,
            fn (string $selectedId): bool => in_array($selectedId, $validAnalysisIds, true)
        ));

        $this->syncParameterFiltersAfterAnalysisTypeChange();
    }

    private function syncParameterFiltersAfterAnalysisTypeChange(): void
    {
        if ($this->itemAnalysisTypeFilterIds === []) {
            $this->itemAnalysisElementFilterIds = [];
            $this->showItemFilterAnalysisElementDropdown = false;

            return;
        }

        $validElementIds = $this->itemFilterAnalysisElements
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->itemAnalysisElementFilterIds = array_values(array_filter(
            $this->itemAnalysisElementFilterIds,
            fn (string $selectedId): bool => in_array($selectedId, $validElementIds, true)
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromPricelistItem(PricelistItem $item): array
    {
        $sampleType = $item->sampleType;
        $analysisType = $item->analysisType;
        $analyte = $item->analysisElement?->analyte;

        $analyteCode = trim((string) ($analyte?->code ?? ''));
        $analyteName = trim((string) ($analyte?->name ?? ''));
        $analyteLabel = $analyteCode !== ''
            ? trim($analyteCode . ' - ' . $analyteName)
            : ($analyteName !== '' ? $analyteName : 'N/A');

        $selling = (float) ($item->selling_price ?? 0);
        $changedRaw = $item->changed_price;
        $changed = $changedRaw === null || $changedRaw === ''
            ? $selling
            : (float) $changedRaw;
        $hasPendingChange = abs($selling - $changed) > 0.004;

        return [
            'analyte' => $analyteLabel,
            'sample_type' => (string) ($sampleType->name ?? '—'),
            'sample_type_code' => (string) ($sampleType->code ?? ''),
            'analysis_type' => (string) ($analysisType->name ?? '—'),
            'analysis_type_code' => (string) ($analysisType->code ?? ''),
            'cost_price' => number_format((float) ($item->cost_price ?? 0), 2),
            'applied_price' => number_format($selling, 2),
            'changed_price' => number_format($changed, 2),
            'has_pending_change' => $hasPendingChange,
            'commit_state' => $hasPendingChange ? 'Pending' : 'Applied',
            'vat' => (bool) ($item->vat ?? false) ? 'Yes' : 'No',
            'active' => (bool) ($item->active ?? false) ? 'Active' : 'Inactive',
        ];
    }

    public function render()
    {
        return view('livewire.billing.pricelist-show-manager', [
            'pricelist' => $this->pricelist,
            'items' => $this->items,
            'filteredItemsCount' => $this->filteredItems->count(),
            'groupedItems' => $this->groupedItems,
            'itemPaginator' => $this->itemPaginator,
            'hasActiveItemFilters' => $this->hasActiveItemFilters,
            'itemFilterSampleTypes' => $this->itemFilterSampleTypes,
            'filteredItemFilterSampleTypes' => $this->filteredItemFilterSampleTypes,
            'itemFilterAnalysisTypes' => $this->itemFilterAnalysisTypes,
            'filteredItemFilterAnalysisTypes' => $this->filteredItemFilterAnalysisTypes,
            'itemFilterAnalysisElements' => $this->itemFilterAnalysisElements,
            'filteredItemFilterAnalysisElements' => $this->filteredItemFilterAnalysisElements,
            'canFilterByAnalysisType' => $this->canFilterByAnalysisType,
            'canFilterByParameter' => $this->canFilterByParameter,
            'summary' => $this->summary,
            'assignedCustomers' => $this->assignedCustomers,
            'availableCustomers' => $this->availableCustomers,
            'customerPickerChips' => $this->customerPickerChips,
            'emailCustomers' => $this->emailCustomers,
            'emailLogCounts' => $this->emailLogCounts,
            'recentEmailLogs' => $this->recentEmailLogs,
            'sampleTypes' => $this->sampleTypes,
            'analysisTypes' => $this->analysisTypes,
            'availableAnalysisTypes' => $this->availableAnalysisTypes,
            'currencies' => $this->currencies,
        ]);
    }
}
