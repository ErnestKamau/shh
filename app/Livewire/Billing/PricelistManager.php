<?php

namespace App\Livewire\Billing;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Billing\Pricelist;
use App\Models\Currency;
use App\Services\Billing\PdfTextExtractor;
use App\Services\Billing\PricelistCleanupService;
use App\Services\Billing\PricelistNumberGenerator;
use App\Services\Billing\PricelistPackageImportService;
use App\Services\Lab\LabSystemNotificationService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

class PricelistManager extends Component
{
    use WithPagination;
    use WithToastNotifications;
    use WithFileUploads;

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

    public bool $showImportModal = false;

    public string $importPricelistId = '';

    public string $importFormat = 'excel';

    public string $importPricingMode = 'per_package';

    public $importFile = null;

    public bool $showDeletePricelistConfirmModal = false;

    public ?string $pendingDeletePricelistId = null;

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
            'pricelistForm.billing_mode' => 'required|in:package,per_test',
            'pricelistForm.status' => 'nullable|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'pricelistForm.description.required' => 'Description is required.',
            'pricelistForm.currency_id.required' => 'Select a currency from the list (typing alone is not enough).',
            'pricelistForm.currency_id.exists' => 'Select a valid currency from the list.',
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
            'billing_mode' => (string) ($pricelist->billing_mode ?? Pricelist::BILLING_MODE_PACKAGE),
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
        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            $this->toastValidationErrors($exception);

            return;
        }

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
                    'billing_mode' => (string) ($this->pricelistForm['billing_mode'] ?? Pricelist::BILLING_MODE_PACKAGE),
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

            $successMessage = $this->editingPricelist
                ? 'Pricelist updated successfully.'
                : 'Pricelist created successfully.';

            $this->showMessage($successMessage, 'success');
            $this->imaraToast('success', 'Pricelist saved', $successMessage);
            $this->closePricelistModal();
            $this->resetPage();
        } catch (\Throwable $e) {
            $failureMessage = 'Failed to save pricelist: ' . $e->getMessage();
            $this->showMessage($failureMessage, 'danger');
            $this->imaraToast('error', 'Could not save pricelist', $e->getMessage());
        }
    }

    private function toastValidationErrors(ValidationException $exception): void
    {
        $messages = collect($exception->validator->errors()->all())
            ->filter(fn ($message): bool => is_string($message) && trim($message) !== '')
            ->unique()
            ->values();

        $body = $messages->isEmpty()
            ? 'Please fill in all required fields.'
            : $messages->implode(' ');

        $this->imaraToast('error', 'Please fix the form', $body);
    }

    public function openPricelist(string $id)
    {
        return $this->redirect(route('show-pricelist', ['id' => $id]));
    }

    public function openDeletePricelistConfirmModal(string $pricelistId): void
    {
        $exists = Pricelist::query()->whereKey($pricelistId)->exists();

        if (! $exists) {
            $this->showMessage('Pricelist not found.', 'danger');

            return;
        }

        $this->pendingDeletePricelistId = (string) $pricelistId;
        $this->showDeletePricelistConfirmModal = true;
    }

    public function closeDeletePricelistConfirmModal(): void
    {
        $this->showDeletePricelistConfirmModal = false;
        $this->pendingDeletePricelistId = null;
    }

    public function confirmDeletePricelist(PricelistCleanupService $cleanupService): void
    {
        if ($this->pendingDeletePricelistId === null || $this->pendingDeletePricelistId === '') {
            return;
        }

        $pricelistId = $this->pendingDeletePricelistId;

        try {
            $result = $cleanupService->deleteOne($pricelistId);
            $this->closeDeletePricelistConfirmModal();
            $this->resetPage();
            $message = sprintf(
                'Deleted pricelist %s (%d item(s), %d customer assignment(s)).',
                $result['code'],
                $result['items'],
                $result['customers']
            );
            $this->showMessage($message, 'success');
            $this->imaraToast('success', 'Pricelist deleted', $message);
        } catch (\Throwable $e) {
            $this->closeDeletePricelistConfirmModal();
            $this->showMessage('Failed to delete pricelist: '.$e->getMessage(), 'danger');
            $this->imaraToast('error', 'Could not delete pricelist', $e->getMessage());
        }
    }

    public function openImportModal(string $pricelistId): void
    {
        $this->importPricelistId = $pricelistId;
        $this->importFormat = 'excel';
        $this->importPricingMode = 'per_package';
        $this->importFile = null;
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importPricelistId = '';
        $this->importFile = null;
        $this->resetValidation();
        $this->dispatch('amspec-import-closed');
    }

    public function submitImport(PricelistPackageImportService $importService, PdfTextExtractor $pdfTextExtractor): void
    {
        $this->validate([
            'importPricelistId' => 'required|string',
            'importFormat' => 'required|in:excel,pdf',
            'importPricingMode' => 'required|in:per_test,per_package',
            'importFile' => 'required|file|max:20480',
        ]);

        $pricelist = Pricelist::query()->find($this->importPricelistId);
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

            if ($this->importFormat === 'pdf') {
                $rev = ($pricelist->code ?? 'pricelist').'-r'.($pricelist->revision_number ?? '1').'.pdf';
                $stored = $this->importFile->storeAs('pricelist', $rev);
                Pricelist::query()->where('id', $pricelist->id)->update([
                    'pricelist_file' => urlencode(basename((string) $stored)),
                    'updated_at' => now(),
                ]);
            }

            $created = (int) ($result['created'] ?? 0);
            $updated = (int) ($result['updated'] ?? 0);
            $skipped = (int) ($result['skipped'] ?? count($result['warnings'] ?? []));
            $msg = $importService->formatImportSummary($result);
            $title = $importService->formatImportToastTitle($result);
            $toastType = ($created + $updated) === 0
                ? 'danger'
                : ($skipped > 0 ? 'warning' : 'success');

            $this->closeImportModal();
            $this->showMessage($msg, $toastType, $title, $skipped > 0 ? 16000 : 8000);
            $this->notifyImportResult($pricelist, $title, $msg, $result, $skipped > 0 || ($created + $updated) === 0);
        } catch (\Throwable $e) {
            $this->showMessage('Import failed: '.$e->getMessage(), 'danger', 'Pricelist import failed', 10000);
        }
    }

    /**
     * @param  array{created?: int, updated?: int, skipped?: int, warnings?: list<string>}  $result
     */
    private function notifyImportResult(
        Pricelist $pricelist,
        string $title,
        string $message,
        array $result,
        bool $shouldNotify,
    ): void {
        if (! $shouldNotify) {
            return;
        }

        $user = auth()->user();
        if (! $user instanceof User) {
            return;
        }

        app(LabSystemNotificationService::class)->notifyPricelistImportResult(
            $user,
            $pricelist,
            $title,
            $message,
            $result,
        );
        $this->dispatch('lab-notifications-updated');
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

    private function showMessage(string $message, string $type = 'success', ?string $title = null, int $durationMs = 7000): void
    {
        $this->message = $message;
        $this->messageType = $type;

        $toastType = match (strtolower($type)) {
            'danger', 'error', 'failed', 'fail' => 'error',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success',
        };

        $resolvedTitle = $title ?? match ($toastType) {
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            default => 'Notice',
        };

        $this->imaraToast($toastType, $resolvedTitle, $message, $durationMs);
    }

    private function resetPricelistForm(): void
    {
        $this->pricelistForm = [
            'description' => '',
            'currency_id' => '',
            'valid_till' => null,
            'is_master' => false,
            'active' => true,
            'billing_mode' => Pricelist::BILLING_MODE_PACKAGE,
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
