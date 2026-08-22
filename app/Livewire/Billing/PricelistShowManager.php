<?php

namespace App\Livewire\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\CRM\CRMCustomer;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistEmailLog;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\Models\Currency;
use App\Models\System\SystemConfiguration;
use App\SampleAnalysisStage;
use App\SampleType;
use App\Services\Billing\PricelistNumberGenerator;
use App\Services\Billing\PricelistPackageImportService;
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Billing\QuotationPricingResolver;
use App\Services\Billing\PdfTextExtractor;
use App\Livewire\Concerns\WithToastNotifications;
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
    use WithToastNotifications;

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

    /** True while Add Item modal is open (keeps Active default on until user turns it off). */
    public bool $itemModalCreating = false;

    public bool $showItemSampleTypeDropdown = false;

    public bool $showItemAnalysisTypeDropdown = false;

    public string $itemSampleTypeSearch = '';

    public string $itemAnalysisTypeSearch = '';

    public bool $itemParamsSelectAll = false;

    public bool $showImportModal = false;

    public string $importFormat = 'excel';

    public string $importPricingMode = 'per_test';

    public $importFile = null;

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
            'pricelistForm.billing_mode' => 'required|in:package,per_test',
        ];
    }

    protected function itemRules(): array
    {
        $rules = [
            'itemForm.sample_type_id' => 'required|exists:sample_types,id',
            'itemForm.internal_use' => 'boolean',
            'itemForm.external_view' => 'boolean',
            'itemForm.active' => 'boolean',
            'itemForm.is_package' => 'boolean',
            'itemElementRows' => 'required|array|min:1',
            'itemElementRows.*.analysis_element_id' => 'required|exists:analysis_elements,id',
            'itemElementRows.*.included' => 'boolean',
        ];

        if (! empty($this->itemForm['is_package'])) {
            $rules['itemForm.package_cost_price'] = 'required|numeric|min:0';
            $rules['itemForm.package_selling_price'] = 'required|numeric|min:0';
            $rules['itemForm.package_vat'] = 'boolean';

            return $rules;
        }

        $rules['itemElementRows.*.cost_price'] = 'nullable|numeric|min:0';
        $rules['itemElementRows.*.selling_price'] = 'nullable|numeric|min:0';
        $rules['itemElementRows.*.vat'] = 'boolean';

        return $rules;
    }

    public function getItemParamsMaxTatProperty(): ?int
    {
        $tats = collect($this->itemElementRows)
            ->filter(fn (array $row): bool => ! empty($row['included']) && isset($row['tat']) && $row['tat'] !== null)
            ->pluck('tat');

        if ($tats->isEmpty()) {
            return null;
        }

        return (int) $tats->max();
    }

    public function updatedItemParamsSelectAll(bool $value): void
    {
        foreach ($this->itemElementRows as $index => $row) {
            $this->itemElementRows[$index]['included'] = $value;
        }
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

    public function getActiveTaxRegimePercentProperty(): float
    {
        return app(QuotationLineTaxResolver::class)->activeTaxRegimePercent();
    }

    public function getItemsProperty()
    {
        $items = PricelistItem::query()
            ->with([
                'sampleType:id,name,code',
                'analysisType:id,name,code,reporting_time',
                'analysisElement:id,analyte_id,method,reporting_time,analysis_type_id',
                'analysisElement.analyte:id,name,code',
                'analysisElement.mmethod:id,name,code',
                'packageElements.analysisElement:id,analyte_id,method,reporting_time,analysis_type_id',
                'packageElements.analysisElement.analyte:id,name,code',
                'packageElements.analysisElement.mmethod:id,name,code',
            ])
            ->where('pricelist_id', $this->pricelistId)
            ->get();

        $pricingResolver = app(QuotationPricingResolver::class);

        return $items
            ->map(function (PricelistItem $item) use ($pricingResolver) {
                $sampleType = $item->sampleType;
                $analysisType = $item->analysisType;
                $analyte = $item->analysisElement?->analyte;
                $cost = (float) ($item->cost_price ?? 0);
                $selling = (float) ($item->selling_price ?? 0);
                $changedRaw = $item->changed_price;
                $changed = $changedRaw === null || $changedRaw === ''
                    ? $selling
                    : (float) $changedRaw;
                $displaySelling = $changed;
                $profit = $displaySelling - $cost;
                $packageParameters = [];
                if ($item->is_package) {
                    $packageParameters = $item->packageElements
                        ->map(function ($packageElement) use ($pricingResolver): array {
                            $element = $packageElement->analysisElement;
                            $analyte = $element?->analyte;
                            $methodLabel = trim((string) ($element?->mmethod?->code ?? $element?->mmethod?->name ?? ''));

                            return [
                                'id' => (string) ($packageElement->analysis_element_id ?? ''),
                                'name' => (string) ($analyte?->name ?? 'Parameter'),
                                'code' => (string) ($analyte?->code ?? ''),
                                'method_label' => $methodLabel !== '' ? $methodLabel : null,
                                'tat' => $element
                                    ? $pricingResolver->maxTatForElements([(string) $element->id], (string) ($element->analysis_type_id ?? ''))
                                    : null,
                            ];
                        })
                        ->filter(fn (array $row): bool => $row['id'] !== '')
                        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                        ->values()
                        ->all();
                }
                $coveredCount = count($packageParameters);

                $methodLabel = trim((string) ($item->analysisElement?->mmethod?->code ?? $item->analysisElement?->mmethod?->name ?? ''));
                $tat = null;
                if ($item->is_package) {
                    $elementIds = array_column($packageParameters, 'id');
                    $tat = $pricingResolver->maxTatForElements($elementIds, (string) ($item->analysis_id ?? ''));
                } elseif ($item->analysisElement) {
                    $tat = $pricingResolver->maxTatForElements(
                        [(string) $item->analysis_element_id],
                        (string) ($item->analysis_id ?? '')
                    );
                }

                $item->sample_type_name = $sampleType->name ?? null;
                $item->sample_type_code = $sampleType->code ?? null;
                $item->analysis_type_name = $analysisType->name ?? null;
                $item->analysis_type_code = $analysisType->code ?? null;
                $item->analyte_name = $item->is_package
                    ? (string) ($sampleType->name ?? 'Package')
                    : ($analyte?->name ?? 'N/A');
                $item->analyte_code = $item->is_package ? 'PKG' : $analyte?->code;
                $item->display_selling_price = $displaySelling;
                $item->applied_selling_price = $selling;
                $item->profit = $profit;
                $item->profit_margin = $displaySelling > 0 ? (($profit / $displaySelling) * 100) : 0;
                $item->has_pending_change = abs($selling - $changed) > 0.004;
                $item->package_element_count = $coveredCount;
                $item->package_parameters = $packageParameters;
                $item->method_label = $methodLabel !== '' ? $methodLabel : null;
                $item->tat = $tat;

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
        return $this->groupedItems
            ->flatMap(function ($sampleGroup) {
                return collect($sampleGroup->rows ?? []);
            })
            ->values();
    }

    public function getItemPaginatorProperty(): LengthAwarePaginator
    {
        $pages = $this->sampleGroupPages();
        $totalItems = $this->filteredItems->count();
        $lastPage = max(1, $pages->count());
        $page = max(1, min($this->itemsPage, $lastPage));
        $this->itemsPage = $page;

        $currentPageItems = $this->paginatedFilteredItems;

        // Use a stable per-page of 1 relative to group-pages so lastPage()/hasPages() match sample-group pages.
        return new LengthAwarePaginator(
            $currentPageItems->all(),
            $lastPage,
            1,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'itemsPage',
            ]
        );
    }

    public function getItemsPageMetaProperty(): array
    {
        $pages = $this->sampleGroupPages();
        $page = max(1, min($this->itemsPage, max(1, $pages->count())));
        $itemsBefore = $this->countItemsInSampleGroups($pages->take($page - 1));
        $onPage = $this->paginatedFilteredItems->count();
        $total = $this->filteredItems->count();

        return [
            'from' => $total === 0 ? 0 : $itemsBefore + 1,
            'to' => $total === 0 ? 0 : $itemsBefore + $onPage,
            'total' => $total,
        ];
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
        $pages = $this->sampleGroupPages();
        if ($pages->isEmpty()) {
            return collect();
        }

        $page = max(1, min($this->itemsPage, $pages->count()));
        $this->itemsPage = $page;

        return $pages->get($page - 1, collect())->values();
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
        $lastPage = max(1, $this->sampleGroupPages()->count());
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
            ->with('sampleTypeCategory')
            ->select('id', 'code', 'name', 'sample_type_category')
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
                        'valid_till' => filled($this->pricelistForm['valid_till'])
                            ? \Illuminate\Support\Carbon::parse($this->pricelistForm['valid_till'])->toDateString()
                            : null,
                        'status' => $this->pricelistForm['status'],
                        'is_master' => (bool) ($this->pricelistForm['is_master'] ?? false),
                        'active' => (bool) ($this->pricelistForm['active'] ?? true),
                        'billing_mode' => (string) ($this->pricelistForm['billing_mode'] ?? Pricelist::BILLING_MODE_PACKAGE),
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
        $this->itemModalCreating = true;
        $this->resetItemForm();
        $this->itemForm['active'] = true;
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
        $this->itemModalCreating = false;
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
            'is_package' => (bool) $item->is_package,
            'package_cost_price' => (float) ($item->cost_price ?? 0),
            'package_selling_price' => (float) (
                $item->changed_price === null || $item->changed_price === ''
                    ? ($item->selling_price ?? 0)
                    : $item->changed_price
            ),
            'package_vat' => (bool) $item->vat,
        ];

        $this->refreshItemElementRows();

        $this->showItemModal = true;
    }

    public function updatedItemFormIsPackage(): void
    {
        $this->refreshItemElementRows();
    }

    public function setItemPricingMode(bool $isPackage): void
    {
        if ($this->isPackagePricelist() !== $isPackage) {
            return;
        }

        $this->itemForm['is_package'] = $isPackage;

        // Create flow: keep Active on by default when leaving package mode.
        if (! $isPackage && $this->itemModalCreating) {
            $this->itemForm['active'] = true;
            $this->itemForm['external_view'] = true;
            $this->itemForm['internal_use'] = false;
        }

        $this->refreshItemElementRows();
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

    public function updatedItemFormSampleTypeId(?string $value): void
    {
        $sampleTypeId = trim((string) ($value ?? ''));
        $this->itemForm['analysis_id'] = '';
        $this->showItemSampleTypeDropdown = false;
        $this->itemSampleTypeSearch = '';

        if ($sampleTypeId === '') {
            $this->itemElementRows = [];
            $this->itemParamsSelectAll = false;

            return;
        }

        $this->itemForm['sample_type_id'] = $sampleTypeId;
        $this->refreshItemElementRows();
    }

    public function selectItemSampleType(string $id): void
    {
        $this->updatedItemFormSampleTypeId($id);
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
        $this->itemParamsSelectAll = false;
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
        $this->itemForm['is_package'] = $this->isPackagePricelist();
        $this->validate($this->itemRules());

        if (count($this->itemElementRows) === 0) {
            $this->showMessage('No analysis elements found for the selected sample type.', 'danger');
            return;
        }

        $includedRows = collect($this->itemElementRows)
            ->filter(fn (array $row): bool => ! empty($row['included']))
            ->values();

        if ($includedRows->isEmpty()) {
            $this->showMessage('Select at least one parameter.', 'danger');

            return;
        }

        if (empty($this->itemForm['is_package'])) {
            foreach ($includedRows as $row) {
                if (! is_numeric($row['cost_price'] ?? null) || ! is_numeric($row['selling_price'] ?? null)) {
                    $this->showMessage('Cost and selling price are required for each selected parameter.', 'danger');

                    return;
                }
            }
        }

        try {
            DB::transaction(function () use ($includedRows): void {
                if (! empty($this->itemForm['is_package'])) {
                    $this->savePackageItem($includedRows);

                    return;
                }

                $this->saveParameterItems($includedRows);
            });

            $this->showMessage($this->editingItem ? 'Pricelist analysis items updated successfully.' : 'Pricelist analysis items added successfully.', 'success');
            $this->closeItemModal();
        } catch (\Throwable $e) {
            $this->showMessage('Failed to save pricelist item: ' . $e->getMessage(), 'danger');
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $includedRows
     */
    private function savePackageItem(Collection $includedRows): void
    {
        $firstAnalysisId = (string) ($includedRows->first()['analysis_id'] ?? '');
        if ($firstAnalysisId === '') {
            throw new \RuntimeException('Selected parameters are missing an analysis type.');
        }

        $this->itemForm['analysis_id'] = $firstAnalysisId;

        $proposedPrice = (float) ($this->itemForm['package_selling_price'] ?? 0);
        $payload = [
            'analysis_id' => $firstAnalysisId,
            'analysis_element_id' => null,
            'sample_type_id' => $this->itemForm['sample_type_id'],
            'cost_price' => (float) ($this->itemForm['package_cost_price'] ?? 0),
            'changed_price' => $proposedPrice,
            'vat' => (bool) ($this->itemForm['package_vat'] ?? false),
            'internal_use' => (bool) ($this->itemForm['internal_use'] ?? false),
            'external_view' => (bool) ($this->itemForm['external_view'] ?? true),
            'active' => (bool) ($this->itemForm['active'] ?? true),
            'is_package' => true,
            'updated_at' => now(),
        ];

        $existing = null;
        if ($this->editingItemId !== null) {
            $existing = PricelistItem::query()
                ->where('pricelist_id', $this->pricelistId)
                ->where('id', $this->editingItemId)
                ->where('is_package', true)
                ->first();
        }

        if ($existing === null) {
            $existing = PricelistItem::query()
                ->where('pricelist_id', $this->pricelistId)
                ->where('sample_type_id', $this->itemForm['sample_type_id'])
                ->where('is_package', true)
                ->whereNull('analysis_element_id')
                ->first();
        }

        if ($existing) {
            PricelistItem::query()->where('id', $existing->id)->update($payload);
            $packageItemId = (string) $existing->id;
        } else {
            $maxLevel = (int) PricelistItem::query()
                ->where('pricelist_id', $this->pricelistId)
                ->max('level');
            $nextLevel = $maxLevel > 0 ? $maxLevel + 1 : 1;
            $packageItemId = (string) Str::uuid();

            PricelistItem::query()->create(array_merge($payload, [
                'id' => $packageItemId,
                'pricelist_id' => $this->pricelistId,
                'selling_price' => 0,
                'level' => $nextLevel,
                'created_at' => now(),
            ]));
        }

        $includedElementIds = $includedRows
            ->map(fn (array $row): string => (string) ($row['analysis_element_id'] ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        PricelistItemElement::query()
            ->where('pricelist_item_id', $packageItemId)
            ->whereNotIn('analysis_element_id', $includedElementIds)
            ->delete();

        $existingElementIds = PricelistItemElement::query()
            ->where('pricelist_item_id', $packageItemId)
            ->pluck('analysis_element_id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        foreach ($includedElementIds as $elementId) {
            if (in_array($elementId, $existingElementIds, true)) {
                continue;
            }

            PricelistItemElement::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_item_id' => $packageItemId,
                'analysis_element_id' => $elementId,
            ]);
        }

        Pricelist::query()
            ->where('id', $this->pricelistId)
            ->update([
                'status' => 'has-changes',
                'updated_at' => now(),
            ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $includedRows
     */
    private function saveParameterItems(Collection $includedRows): void
    {
        $maxLevel = (int) PricelistItem::query()
            ->where('pricelist_id', $this->pricelistId)
            ->max('level');

        $nextLevel = $maxLevel > 0 ? $maxLevel + 1 : 1;

        foreach ($includedRows as $row) {
            $analysisElementId = (string) ($row['analysis_element_id'] ?? '');
            $analysisId = (string) ($row['analysis_id'] ?? '');
            if ($analysisElementId === '' || $analysisId === '') {
                continue;
            }

            $proposedPrice = (float) ($row['selling_price'] ?? 0);

            $payload = [
                'analysis_id' => $analysisId,
                'analysis_element_id' => $analysisElementId,
                'sample_type_id' => $this->itemForm['sample_type_id'],
                'cost_price' => (float) ($row['cost_price'] ?? 0),
                'changed_price' => $proposedPrice,
                'vat' => (bool) ($row['vat'] ?? false),
                'internal_use' => (bool) ($this->itemForm['internal_use'] ?? false),
                'external_view' => (bool) ($this->itemForm['external_view'] ?? true),
                'active' => (bool) ($this->itemForm['active'] ?? true),
                'is_package' => false,
                'updated_at' => now(),
            ];

            $existing = PricelistItem::query()
                ->where('pricelist_id', $this->pricelistId)
                ->where('sample_type_id', $this->itemForm['sample_type_id'])
                ->where('analysis_id', $analysisId)
                ->where('analysis_element_id', $analysisElementId)
                ->where('is_package', false)
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
    }

    public function openDeleteItemConfirmModal(string $itemId): void
    {
        $item = PricelistItem::query()
            ->with([
                'sampleType:id,name,code',
                'analysisType:id,name,code',
                'analysisElement:id,analyte_id',
                'analysisElement.analyte:id,name,code',
                'packageElements',
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
            'valid_till' => $source?->valid_till?->format('Y-m-d'),
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
                $companyId = function_exists('getUserCompany')
                    ? getUserCompany()
                    : null;
                $numbers = app(PricelistNumberGenerator::class)->next(
                    $companyId !== null ? (string) $companyId : null,
                );

                Pricelist::query()->create([
                    'id' => $newPricelistId,
                    'code' => $numbers['code'],
                    'description' => $this->cloneForm['description'],
                    'currency_id' => $this->cloneForm['currency_id'],
                    'is_master' => (bool) ($this->cloneForm['is_master'] ?? false),
                    'active' => (bool) ($this->cloneForm['active'] ?? true),
                    'billing_mode' => (string) ($this->pricelist->billing_mode ?? Pricelist::BILLING_MODE_PACKAGE),
                    'document_no' => $numbers['document_no'],
                    'revision_number' => '1',
                    'status' => 'no-changes',
                    'valid_till' => $this->cloneForm['valid_till'] ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $items = PricelistItem::query()
                    ->with('packageElements')
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
                    $newItemId = (string) Str::uuid();

                    PricelistItem::query()->create([
                        'id' => $newItemId,
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
                        'is_package' => (bool) $item->is_package,
                        'level' => $level,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($item->is_package) {
                        foreach ($item->coveredElementIds() as $elementId) {
                            PricelistItemElement::query()->create([
                                'id' => (string) Str::uuid(),
                                'pricelist_item_id' => $newItemId,
                                'analysis_element_id' => $elementId,
                            ]);
                        }
                    }

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
        $this->itemModalCreating = false;
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

        // Single toast path (imara) — avoid also firing SweetAlert `notify`.
        $this->imaraToast($toastType, $title, $message);
    }

    private function fillPricelistForm(): void
    {
        $pricelist = $this->pricelist;

        $this->pricelistForm = [
            'description' => $pricelist->description ?? '',
            'currency_id' => $pricelist->currency_id ?? '',
            'valid_till' => $pricelist->valid_till?->format('Y-m-d'),
            'status' => $pricelist->status ?? 'no-changes',
            'is_master' => (bool) ($pricelist->is_master ?? false),
            'active' => (bool) ($pricelist->active ?? true),
            'billing_mode' => (string) ($pricelist->billing_mode ?? Pricelist::BILLING_MODE_PACKAGE),
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
            'is_package' => $this->isPackagePricelist(),
            'package_cost_price' => 0,
            'package_selling_price' => 0,
            'package_vat' => true,
        ];

        $this->itemElementRows = [];
        $this->itemParamsSelectAll = false;
        $this->showItemSampleTypeDropdown = false;
        $this->showItemAnalysisTypeDropdown = false;
        $this->itemSampleTypeSearch = '';
        $this->itemAnalysisTypeSearch = '';
    }

    private function refreshItemElementRows(): void
    {
        $sampleTypeId = (string) ($this->itemForm['sample_type_id'] ?? '');

        if ($sampleTypeId === '') {
            $this->itemElementRows = [];
            $this->itemParamsSelectAll = false;

            return;
        }

        $analysisTypes = AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'reporting_time', 'sample_type_id']);

        if ($analysisTypes->isEmpty()) {
            $this->itemElementRows = [];
            $this->itemParamsSelectAll = false;

            return;
        }

        $analysisTypeIds = $analysisTypes->pluck('id')->all();
        $analysisTypeById = $analysisTypes->keyBy(fn ($type) => (string) $type->id);

        $elements = AnalysisElements::query()
            ->with([
                'analyte:id,name,code',
                'mmethod:id,name,code',
            ])
            ->whereIn('analysis_type_id', $analysisTypeIds)
            ->where(function ($query): void {
                $query->where('active', true)
                    ->orWhereNull('active');
            })
            ->orderBy('level')
            ->get([
                'id',
                'analysis_type_id',
                'analyte_id',
                'method',
                'lab_section_id',
                'reporting_time',
                'level',
                'active',
            ]);

        $labSectionIds = $elements
            ->pluck('lab_section_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->unique()
            ->values()
            ->all();

        $labSectionCodes = SampleAnalysisStage::query()
            ->whereIn('id', $labSectionIds)
            ->get(['id', 'code'])
            ->mapWithKeys(fn ($stage): array => [(string) $stage->id => (string) ($stage->code ?? '')])
            ->all();

        if ($labSectionCodes === [] && $labSectionIds !== []) {
            $labSectionCodes = \App\LabSection::query()
                ->whereIn('id', $labSectionIds)
                ->get(['id', 'code'])
                ->mapWithKeys(fn ($section): array => [(string) $section->id => (string) ($section->code ?? '')])
                ->all();
        }

        $existingItems = PricelistItem::query()
            ->where('pricelist_id', $this->pricelistId)
            ->where('sample_type_id', $sampleTypeId)
            ->where('is_package', false)
            ->get()
            ->keyBy(function ($item) {
                return (string) ($item->analysis_element_id ?? '');
            });

        $packageItem = null;
        if ($this->editingItemId !== null) {
            $packageItem = PricelistItem::query()
                ->with('packageElements')
                ->where('pricelist_id', $this->pricelistId)
                ->where('id', $this->editingItemId)
                ->where('is_package', true)
                ->whereNull('analysis_element_id')
                ->first();
        }

        if ($packageItem === null) {
            $packageItem = PricelistItem::query()
                ->with('packageElements')
                ->where('pricelist_id', $this->pricelistId)
                ->where('sample_type_id', $sampleTypeId)
                ->where('is_package', true)
                ->whereNull('analysis_element_id')
                ->orderBy('level')
                ->first();
        }

        $isPackageMode = ! empty($this->itemForm['is_package']);

        if ($packageItem !== null && $isPackageMode) {
            $changedRaw = $packageItem->changed_price;
            $this->itemForm['package_cost_price'] = (float) ($packageItem->cost_price ?? 0);
            $this->itemForm['package_selling_price'] = $changedRaw === null || $changedRaw === ''
                ? (float) ($packageItem->selling_price ?? 0)
                : (float) $changedRaw;
            $this->itemForm['package_vat'] = (bool) $packageItem->vat;
            $this->itemForm['analysis_id'] = (string) ($packageItem->analysis_id ?? '');

            // Only pull flags when editing an existing package opened via Edit.
            // Create flow keeps Active=true by default (user may still turn it off).
            if (! $this->itemModalCreating
                && $this->editingItemId !== null
                && $this->editingItemId === (string) $packageItem->id
            ) {
                $this->itemForm['internal_use'] = (bool) $packageItem->internal_use;
                $this->itemForm['external_view'] = (bool) $packageItem->external_view;
                $this->itemForm['active'] = (bool) $packageItem->active;
            }

            if ($this->editingItemId === null || $this->editingItemId === (string) $packageItem->id) {
                $this->itemForm['id'] = $packageItem->id;
                $this->editingItemId = (string) $packageItem->id;
                $this->editingItem = true;
            }
        }

        $coveredElementIds = $packageItem?->coveredElementIds() ?? [];
        $editingElementId = null;
        if ($this->editingItem && $this->editingItemId !== null && ! $isPackageMode) {
            $editingElementId = (string) (
                PricelistItem::query()
                    ->where('id', $this->editingItemId)
                    ->value('analysis_element_id') ?? ''
            );
        }

        $pricingResolver = app(QuotationPricingResolver::class);

        $this->itemElementRows = $elements->map(function ($element, int $index) use (
            $existingItems,
            $coveredElementIds,
            $isPackageMode,
            $analysisTypeById,
            $editingElementId,
            $pricingResolver,
            $labSectionCodes
        ) {
            $existing = $existingItems->get((string) $element->id);
            $analyteCode = trim((string) ($element->analyte?->code ?? ''));
            $analyteName = trim((string) ($element->analyte?->name ?? ''));
            $analysisType = $analysisTypeById->get((string) $element->analysis_type_id);
            $methodLabel = trim((string) ($element->mmethod?->code ?? $element->mmethod?->name ?? ''));
            if ($methodLabel === '') {
                $methodLabel = trim((string) ($element->mmethod?->name ?? ''));
            }
            $labSectionCode = trim((string) ($labSectionCodes[(string) ($element->lab_section_id ?? '')] ?? ''));

            $proposedPrice = 0.0;
            if ($existing) {
                $changedRaw = $existing->changed_price;
                $proposedPrice = $changedRaw === null || $changedRaw === ''
                    ? (float) ($existing->selling_price ?? 0)
                    : (float) $changedRaw;
            }

            $included = false;
            if ($isPackageMode) {
                $included = $coveredElementIds === []
                    ? false
                    : in_array((string) $element->id, $coveredElementIds, true);
            } elseif ($editingElementId !== '') {
                $included = (string) $element->id === $editingElementId;
            }

            $tat = $pricingResolver->maxTatForElements([(string) $element->id], (string) ($element->analysis_type_id ?? ''));

            return [
                '_index' => $index,
                'analysis_element_id' => (string) $element->id,
                'analysis_id' => (string) ($element->analysis_type_id ?? ''),
                'analysis_type_name' => (string) ($analysisType?->name ?? 'Analysis'),
                'analysis_type_code' => (string) ($analysisType?->code ?? ''),
                'analyte_label' => trim($analyteCode !== '' ? ($analyteCode . ' - ' . $analyteName) : $analyteName),
                'method_label' => $methodLabel !== '' ? $methodLabel : null,
                'lab_section_code' => $labSectionCode !== '' ? $labSectionCode : null,
                'tat' => $tat,
                'cost_price' => (float) ($existing->cost_price ?? 0),
                'selling_price' => $proposedPrice,
                'vat' => (bool) ($existing->vat ?? true),
                'included' => $included,
            ];
        })->values()->all();

        $this->itemParamsSelectAll = count($this->itemElementRows) > 0
            && collect($this->itemElementRows)->every(fn (array $row): bool => ! empty($row['included']));
    }

    public function openImportModal(): void
    {
        $this->importFormat = 'excel';
        $this->importPricingMode = 'per_package';
        $this->importFile = null;
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->resetValidation();
        $this->dispatch('amspec-import-closed');
    }

    public function submitImport(PricelistPackageImportService $importService, PdfTextExtractor $pdfTextExtractor): void
    {
        $this->validate([
            'importFormat' => 'required|in:excel,pdf',
            'importPricingMode' => 'required|in:per_test,per_package',
            'importFile' => 'required|file|max:20480',
        ]);

        $pricelist = $this->pricelist;
        if (! $pricelist) {
            $this->showMessage('Pricelist not found.', 'danger');

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
                    $this->addError('importFile', $pdfTextExtractor->capability()['hint']);
                    $this->showMessage($pdfTextExtractor->capability()['hint'], 'danger');

                    return;
                }
            } elseif (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
                $this->addError('importFile', 'Please upload an Excel (.xlsx / .xls) or CSV file.');
                $this->showMessage('Please upload an Excel (.xlsx / .xls) or CSV file.', 'danger');

                return;
            }

            $result = $importService->import(
                $pricelist,
                $this->importFile,
                $this->importFormat,
                $this->importPricingMode,
            );

            // Also keep PDF as the published pricelist document when importing from PDF.
            if ($this->importFormat === 'pdf') {
                $rev = ($pricelist->code ?? 'pricelist').'-r'.($pricelist->revision_number ?? '1').'.pdf';
                $stored = $this->importFile->storeAs('pricelist', $rev);
                Pricelist::query()->where('id', $this->pricelistId)->update([
                    'pricelist_file' => urlencode(basename((string) $stored)),
                    'updated_at' => now(),
                ]);
            }

            $created = (int) ($result['created'] ?? 0);
            $updated = (int) ($result['updated'] ?? 0);
            $warnings = $result['warnings'] ?? [];
            $msg = "Imported {$created} new / {$updated} updated pricelist item(s).";
            if ($warnings !== []) {
                $msg .= ' Warnings: '.implode(' ', array_slice($warnings, 0, 5));
                if (count($warnings) > 5) {
                    $msg .= ' (+'.(count($warnings) - 5).' more)';
                }
            }

            $this->showMessage($msg, $created + $updated > 0 ? 'success' : 'danger');
            $this->closeImportModal();
        } catch (\Throwable $e) {
            $this->showMessage('Import failed: '.$e->getMessage(), 'danger');
        }
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

    private function isPackagePricelist(): bool
    {
        $mode = strtolower(trim((string) ($this->pricelist?->billing_mode ?? '')));
        if ($mode === Pricelist::BILLING_MODE_PER_TEST) {
            return false;
        }

        return $mode === '' || $mode === Pricelist::BILLING_MODE_PACKAGE;
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

                return (object) [
                    'sample_type_id' => (string) ($firstSampleItem->sample_type_id ?? ''),
                    'sample_type_name' => $firstSampleItem->sample_type_name ?? 'Unassigned Sample Type',
                    'sample_type_code' => $firstSampleItem->sample_type_code,
                    'total_amount' => (float) $sampleItems->sum(function ($item) {
                        return (float) ($item->display_selling_price ?? $item->selling_price ?? 0);
                    }),
                    'rows' => $sampleItems
                        ->sortBy(function ($item) {
                            return mb_strtolower((string) ($item->analyte_name ?? ''));
                        })
                        ->values(),
                ];
            })
            ->filter(function ($group) {
                return $group->rows->count() > 0;
            })
            ->sortBy(function ($group) {
                return mb_strtolower((string) ($group->sample_type_name ?? ''));
            })
            ->values();
    }

    /**
     * Pack complete sample-type groups into pages without splitting a sample type across pages.
     *
     * @return Collection<int, Collection<int, object>>
     */
    private function sampleGroupPages(): Collection
    {
        $groups = $this->groupItems($this->filteredItems);
        $perPage = max(1, $this->itemsPerPage);
        $pages = collect();
        $current = collect();
        $currentItemCount = 0;

        foreach ($groups as $group) {
            $groupItemCount = $this->countItemsInSampleGroups(collect([$group]));

            if ($current->isNotEmpty() && ($currentItemCount + $groupItemCount) > $perPage) {
                $pages->push($current->values());
                $current = collect();
                $currentItemCount = 0;
            }

            $current->push($group);
            $currentItemCount += $groupItemCount;

            // Oversized groups still occupy a single page so the sample type appears once.
            if ($groupItemCount >= $perPage) {
                $pages->push($current->values());
                $current = collect();
                $currentItemCount = 0;
            }
        }

        if ($current->isNotEmpty()) {
            $pages->push($current->values());
        }

        return $pages->values();
    }

    /**
     * @param  Collection<int, object>|Collection<int, Collection<int, object>>  $groupsOrPages
     */
    private function countItemsInSampleGroups(Collection $groupsOrPages): int
    {
        return (int) $groupsOrPages->sum(function ($group): int {
            if ($group instanceof Collection) {
                return $this->countItemsInSampleGroups($group);
            }

            return (int) collect($group->rows ?? [])->count();
        });
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
        $analyteLabel = (bool) $item->is_package
            ? ('Package ('.count($item->coveredElementIds()).' parameters)')
            : ($analyteCode !== ''
                ? trim($analyteCode . ' - ' . $analyteName)
                : ($analyteName !== '' ? $analyteName : 'N/A'));

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
            'is_package' => (bool) $item->is_package,
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
