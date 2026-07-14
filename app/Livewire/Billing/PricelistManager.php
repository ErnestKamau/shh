<?php

namespace App\Livewire\Billing;

use App\Models\Billing\Pricelist;
use App\Models\Currency;
use App\Services\Billing\PricelistNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class PricelistManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $currencyFilter = '';
    public $currencyFilterSearch = '';
    public $showCurrencyFilterDropdown = false;
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    public $showPricelistModal = false;
    public $editingPricelist = false;
    public $pricelistForm = [];
    public $currencySearch = '';
    public $showCurrencyDropdown = false;

    public $message = '';
    public $messageType = 'success';

    protected function rules(): array
    {
        return [
            'pricelistForm.description' => 'required|string|max:255',
            'pricelistForm.currency_id' => 'required|exists:currencies,id',
            'pricelistForm.valid_till' => 'nullable|date',
            'pricelistForm.is_master' => 'boolean',
            'pricelistForm.active' => 'boolean',
            'pricelistForm.status' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        $this->resetPricelistForm();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCurrencyFilter(): void
    {
        $this->resetPage();
    }

    public function getPricelistsProperty()
    {
        $query = Pricelist::query()
            ->with(['currency:id,code,description']);

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('description', 'ilike', '%' . $this->search . '%')
                  ->orWhere('document_no', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('active', (bool) $this->statusFilter);
        }

        if ($this->currencyFilter !== '') {
            $query->where('currency_id', $this->currencyFilter);
        }

        $paginator = $query
            ->orderByDesc('is_master')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(function (Pricelist $pricelist) {
                $pricelist->currency_code = $pricelist->currency?->code;
                $pricelist->currency_description = $pricelist->currency?->description;

                return $pricelist;
            })
        );

        return $paginator;
    }

    public function getCurrenciesProperty()
    {
        return Currency::query()
            ->select('id', 'code', 'description')
            ->where('active', true)
            ->orderBy('code')
            ->get();
    }

    public function getFilteredCurrenciesProperty()
    {
        $search = trim((string) $this->currencySearch);

        if ($search === '') {
            return $this->currencies;
        }

        $query = '%' . $search . '%';

        return Currency::query()
            ->select('id', 'code', 'description')
            ->where('active', true)
            ->where(function ($q) use ($query): void {
                $q->where('code', 'ilike', $query)
                    ->orWhere('description', 'ilike', $query);
            })
            ->orderBy('code')
            ->get();
    }

    public function getFilteredCurrencyFilterOptionsProperty()
    {
        $search = trim((string) $this->currencyFilterSearch);

        if ($search === '') {
            return $this->currencies;
        }

        $query = '%' . $search . '%';

        return Currency::query()
            ->select('id', 'code', 'description')
            ->where('active', true)
            ->where(function ($q) use ($query): void {
                $q->where('code', 'ilike', $query)
                    ->orWhere('description', 'ilike', $query);
            })
            ->orderBy('code')
            ->get();
    }

    public function getSelectedCurrencyFilterProperty()
    {
        if (!$this->currencyFilter) {
            return null;
        }

        return Currency::query()
            ->select('id', 'code', 'description')
            ->where('id', $this->currencyFilter)
            ->first();
    }

    public function getSelectedCurrencyProperty()
    {
        $currencyId = $this->pricelistForm['currency_id'] ?? null;
        if (!$currencyId) {
            return null;
        }

        return Currency::query()
            ->select('id', 'code', 'description')
            ->where('id', $currencyId)
            ->first();
    }

    public function getShowAddCurrencyActionProperty(): bool
    {
        [$code] = $this->parseCurrencySearchInput($this->currencySearch);

        if ($code === '') {
            return false;
        }

        return !Currency::query()
            ->where('code', 'ilike', $code)
            ->exists();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->currencyFilter = '';
        $this->currencyFilterSearch = '';
        $this->showCurrencyFilterDropdown = false;
        $this->resetPage();
    }

    public function selectCurrencyFilter(string $currencyId): void
    {
        $this->currencyFilter = $currencyId;
        $this->currencyFilterSearch = '';
        $this->showCurrencyFilterDropdown = false;
        $this->resetPage();
    }

    public function clearCurrencyFilter(): void
    {
        $this->currencyFilter = '';
        $this->currencyFilterSearch = '';
        $this->showCurrencyFilterDropdown = false;
        $this->resetPage();
    }

    public function showCreatePricelistModal(): void
    {
        $this->editingPricelist = false;
        $this->resetPricelistForm();
        $this->currencySearch = '';
        $this->showCurrencyDropdown = false;
        $this->showPricelistModal = true;
    }

    public function showEditPricelistModal(string $id): void
    {
        $pricelist = Pricelist::query()->find($id);
        if (!$pricelist) {
            $this->showMessage('Pricelist not found.', 'danger');
            return;
        }

        $this->editingPricelist = true;
        $this->pricelistForm = [
            'id' => $pricelist->id,
            'description' => $pricelist->description,
            'currency_id' => $pricelist->currency_id,
            'valid_till' => $pricelist->valid_till?->format('Y-m-d'),
            'is_master' => (bool) $pricelist->is_master,
            'active' => (bool) $pricelist->active,
            'status' => $pricelist->status ?: 'no-changes',
        ];

        $this->currencySearch = '';
        $this->showCurrencyDropdown = false;

        $this->showPricelistModal = true;
    }

    public function selectCurrency(string $currencyId): void
    {
        $this->pricelistForm['currency_id'] = $currencyId;
        $this->currencySearch = '';
        $this->showCurrencyDropdown = false;
    }

    public function addCurrencyFromSearch(): void
    {
        [$code, $description] = $this->parseCurrencySearchInput($this->currencySearch);

        if ($code === '') {
            $this->showMessage('Type a currency code first (example: USD).', 'danger');
            return;
        }

        if ($code === '' || strlen($code) > 255) {
            $this->showMessage('Currency code is required and must be under 256 characters.', 'danger');
            return;
        }

        $existing = Currency::query()
            ->where('code', 'ilike', $code)
            ->first();

        if ($existing) {
            $this->selectCurrency((string) $existing->id);
            $this->showMessage('Currency already exists and has been selected.', 'success');
            return;
        }

        try {
            $currency = Currency::create([
                'code' => $code,
                'description' => $description !== '' ? $description : $code,
                'exchange_rate_amt' => 0,
                'currency_factor' => 1,
                'active' => true,
            ]);

            $this->selectCurrency((string) $currency->id);
            $this->showMessage("Currency {$code} was added and selected.", 'success');
        } catch (\Throwable $e) {
            $this->showMessage('Failed to add currency: ' . $e->getMessage(), 'danger');
        }
    }

    private function parseCurrencySearchInput(?string $input): array
    {
        $rawInput = trim((string) $input);
        if ($rawInput === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s*-\s*/', $rawInput, 2);
        $code = strtoupper(trim((string) ($parts[0] ?? '')));
        $description = trim((string) ($parts[1] ?? ''));

        return [$code, $description];
    }

    public function savePricelist(): void
    {
        $this->validate();

        try {
            DB::transaction(function (): void {
                $payload = [
                    'description' => $this->pricelistForm['description'],
                    'currency_id' => $this->pricelistForm['currency_id'],
                    'valid_till' => filled($this->pricelistForm['valid_till'])
                        ? \Illuminate\Support\Carbon::parse($this->pricelistForm['valid_till'])->toDateString()
                        : null,
                    'is_master' => (bool) ($this->pricelistForm['is_master'] ?? false),
                    'active' => (bool) ($this->pricelistForm['active'] ?? true),
                    'status' => $this->pricelistForm['status'] ?: 'no-changes',
                    'updated_at' => now(),
                ];

                if ($this->editingPricelist) {
                    Pricelist::query()
                        ->where('id', $this->pricelistForm['id'])
                        ->update($payload);
                } else {
                    $companyId = function_exists('getUserCompany')
                        ? getUserCompany()
                        : null;
                    $numbers = app(PricelistNumberGenerator::class)->next(
                        $companyId !== null ? (string) $companyId : null,
                    );

                    $payload['id'] = (string) Str::uuid();
                    $payload['code'] = $numbers['code'];
                    $payload['document_no'] = $numbers['document_no'];
                    $payload['revision_number'] = '1';
                    $payload['created_at'] = now();

                    Pricelist::query()->create($payload);
                }
            });

            $this->showMessage($this->editingPricelist ? 'Pricelist updated successfully.' : 'Pricelist created successfully.', 'success');
            $this->closePricelistModal();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->showMessage('Failed to save pricelist: ' . $e->getMessage(), 'danger');
        }
    }

    public function openPricelist(string $id)
    {
        return $this->redirect(route('show-pricelist', ['id' => $id]));
    }

    public function closePricelistModal(): void
    {
        $this->showPricelistModal = false;
        $this->currencySearch = '';
        $this->showCurrencyDropdown = false;
        $this->resetPricelistForm();
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

    private function resetPricelistForm(): void
    {
        $this->pricelistForm = [
            'description' => '',
            'currency_id' => '',
            'valid_till' => null,
            'is_master' => false,
            'active' => true,
            'status' => 'no-changes',
        ];
    }

    public function render()
    {
        return view('livewire.billing.pricelist-manager', [
            'pricelists' => $this->pricelists,
            'currencies' => $this->currencies,
            'filteredCurrencies' => $this->filteredCurrencies,
            'selectedCurrency' => $this->selectedCurrency,
            'showAddCurrencyAction' => $this->showAddCurrencyAction,
            'selectedCurrencyFilter' => $this->selectedCurrencyFilter,
            'filteredCurrencyFilterOptions' => $this->filteredCurrencyFilterOptions,
        ]);
    }
}
