<?php

namespace App\Livewire\Lab;

use App\AnalysisMethod;
use App\Company;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\BulkImportBatch;
use App\Models\CRM\CRMCustomer;
use App\Models\Equipments\Equipment;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\QcSchemeBinding;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\SampleType;
use App\Services\BulkImportService;
use App\Services\Lab\MethodConfigurationResolver;
use App\Standards;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MethodManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filter properties
    public $search = '';
    public $statusFilter = '';
    public $methodTypeFilter = '';
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Modal properties
    public $showMethodModal = false;
    public $editingMethod = null;
    public bool $addToQcWorkflow = false;

    // Form data
    public $methodForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'method_type_id' => '',
        'reference_type_id' => '',
        'based_on_standard_id' => '',
        'qc_scheme_ids' => [],
        'qc_scheme_mode' => 'override',
        'qc_scheme_priority' => 100,
        'qc_condition_equipment_id' => '',
        'qc_condition_crm_customer_id' => '',
        'qc_condition_sample_type_id' => '',
        'active' => true,
    ];

    // Data properties
    public $methodTypes = [];
    public $referenceMethods = [];
    public $ltmMethodTypeId = null;

    protected function normalizeReferenceMethods(): void
    {
        if ($this->referenceMethods instanceof \Illuminate\Support\Collection) {
            return;
        }

        $items = is_array($this->referenceMethods) ? $this->referenceMethods : [];

        $this->referenceMethods = collect($items)
            ->filter(fn ($item) => is_array($item) || is_object($item))
            ->map(fn ($item) => [
                'id' => data_get($item, 'id'),
                'name' => data_get($item, 'name', ''),
            ])
            ->values();
    }

    // Messages
    public $message = '';
    public $messageType = '';

    public bool $showBulkImportModal = false;

    /** @var mixed */
    public $bulkFile = null;

    public bool $replaceExisting = true;

    public string $purgeConfirmation = '';

    protected function rules(): array
    {
        $rules = [
            'methodForm.name' => 'required|string|max:255',
            'methodForm.code' => 'required|string|max:255',
            'methodForm.description' => 'nullable|string',
            'methodForm.method_type_id' => ['required', Rule::exists('system_configurations', 'id')],
            'methodForm.reference_type_id' => ['nullable', 'string', Rule::exists('analysis_methods', 'id')],
            'methodForm.active' => 'boolean',
            'addToQcWorkflow' => 'boolean',
        ];

        if ($this->addToQcWorkflow) {
            $rules['methodForm.based_on_standard_id'] = ['nullable', 'string', Rule::exists('standards', 'id')];
            $rules['methodForm.qc_scheme_ids'] = ['array'];
            $rules['methodForm.qc_scheme_ids.*'] = ['string', Rule::exists('qc_scheme', 'id')];
            $rules['methodForm.qc_scheme_mode'] = ['required', Rule::in([
                QcSchemeBinding::MODE_OVERRIDE,
                QcSchemeBinding::MODE_MERGE,
                QcSchemeBinding::MODE_ADDITIVE,
            ])];
            $rules['methodForm.qc_scheme_priority'] = ['required', 'integer', 'min:1', 'max:1000'];
            $rules['methodForm.qc_condition_equipment_id'] = ['nullable', 'string', Rule::exists('equipment', 'id')];
            $rules['methodForm.qc_condition_crm_customer_id'] = ['nullable', 'string', Rule::exists('crm_customers', 'id')];
            $rules['methodForm.qc_condition_sample_type_id'] = ['nullable', 'string', Rule::exists('sample_types', 'id')];
        }

        return $rules;
    }

    public function mount(): void
    {
        $this->loadStaticData();
    }

    public function hydrate(): void
    {
        $this->normalizeReferenceMethods();
    }

    protected function loadStaticData(): void
    {
        $resolver = app(MethodConfigurationResolver::class);
        $resolver->ensurePointerConfigurations();

        $methodTypeConfig = SystemConfigurationsType::query()
            ->whereIn('configuration_type', ['Method Types', 'Methods Types'])
            ->first();

        $this->methodTypes = $methodTypeConfig
            ? SystemConfiguration::query()
                ->where('configuration_type_id', $methodTypeConfig->id)
                ->where('key', 'method_type')
                ->orderBy('value')
                ->get()
            : collect();

        $this->ltmMethodTypeId = $resolver->resolvePointerConfigValue('method_ltm_id');

        $referenceTypeId = $resolver->resolvePointerConfigValue('method_reference_id');
        if ($referenceTypeId) {
            $this->referenceMethods = AnalysisMethod::query()
                ->where('method_type_id', $referenceTypeId)
                ->where('active', 1)
                ->orderBy('name')
                ->get();
        } else {
            $this->referenceMethods = collect();
        }
    }

    public function updatedMethodFormMethodTypeId(): void
    {
        if (! app(MethodConfigurationResolver::class)->isLaboratoryTestTypeId($this->methodForm['method_type_id'] ?? null)) {
            $this->methodForm['reference_type_id'] = '';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMethodTypeFilter(): void
    {
        $this->resetPage();
    }

    public function getMethodsProperty()
    {
        $query = AnalysisMethod::with(['referencemethod', 'methodtype', 'basedOnStandard', 'qcSchemes']);

        // Apply search filter (case-insensitive for PostgreSQL and MySQL)
        $this->applyCaseInsensitiveSearch($query, ['name', 'code', 'description'], (string) $this->search);

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->where('active', 1);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('active', 0);
        }

        // Apply method type filter
        if ($this->methodTypeFilter) {
            $query->where('method_type_id', $this->methodTypeFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function showCreateMethodModal(): void
    {
        $this->reset(['methodForm', 'editingMethod', 'message']);
        $this->addToQcWorkflow = false;
        $this->methodForm['active'] = true;
        $this->methodForm['qc_scheme_ids'] = [];
        $this->methodForm['qc_scheme_mode'] = QcSchemeBinding::MODE_OVERRIDE;
        $this->methodForm['qc_scheme_priority'] = 100;
        $this->methodForm['qc_condition_equipment_id'] = '';
        $this->methodForm['qc_condition_crm_customer_id'] = '';
        $this->methodForm['qc_condition_sample_type_id'] = '';
        $this->loadStaticData();
        $this->showMethodModal = true;
        $this->dispatch('method-modal-opened');
    }

    public function showEditMethodModal(string $methodId): void
    {
        $this->editingMethod = AnalysisMethod::with('qcSchemes')->find($methodId);
        
        if ($this->editingMethod) {
            $binding = QcSchemeBinding::query()
                ->where('method_id', $this->editingMethod->id)
                ->whereNull('standard_id')
                ->orderByDesc('priority')
                ->first();

            $conditions = is_array($binding?->conditions) ? $binding->conditions : [];

            $this->methodForm = [
                'name' => $this->editingMethod->name,
                'code' => $this->editingMethod->code,
                'description' => $this->editingMethod->description,
                'method_type_id' => (string) ($this->editingMethod->method_type_id ?? ''),
                'reference_type_id' => (string) ($this->editingMethod->reference_type_id ?? ''),
                'based_on_standard_id' => (string) ($this->editingMethod->based_on_standard_id ?? ''),
                'qc_scheme_ids' => $this->editingMethod->qcSchemes
                    ->pluck('id')
                    ->map(static fn ($id) => (string) $id)
                    ->values()
                    ->all(),
                'qc_scheme_mode' => $binding->mode ?? QcSchemeBinding::MODE_OVERRIDE,
                'qc_scheme_priority' => $binding->priority ?? 100,
                'qc_condition_equipment_id' => (string) ($conditions['equipment_id'] ?? ''),
                'qc_condition_crm_customer_id' => (string) ($conditions['crm_customer_id'] ?? ''),
                'qc_condition_sample_type_id' => (string) ($conditions['sample_type_id'] ?? ''),
                'active' => (bool) $this->editingMethod->active,
            ];

            $this->addToQcWorkflow = $this->methodHasQcWorkflowConfiguration();

            $this->loadStaticData();
            $this->showMethodModal = true;
            $this->dispatch('method-modal-opened');
        }
    }

    public function saveMethod(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if (! $this->addToQcWorkflow) {
                $this->clearQcWorkflowFormFields();
            }

            $resolver = app(MethodConfigurationResolver::class);
            $methodTypeId = (string) $this->methodForm['method_type_id'];
            $flags = $resolver->legacyFlagsForTypeId($methodTypeId);
            $referenceTypeId = $resolver->isLaboratoryTestTypeId($methodTypeId)
                ? ($this->methodForm['reference_type_id'] ?: null)
                : null;

            $data = [
                'name' => $this->methodForm['name'],
                'code' => $this->methodForm['code'],
                'description' => $this->methodForm['description'] ?: null,
                'method_type_id' => $methodTypeId,
                'reference_type_id' => $referenceTypeId,
                'based_on_standard_id' => $this->methodForm['based_on_standard_id'] ?: null,
                'active' => ($this->methodForm['active'] ?? true) ? 1 : 0,
                'is_ltm' => $flags['is_ltm'],
                'is_sampling_method' => $flags['is_sampling_method'],
                'company_id' => getUserCompany(),
            ];

            if ($this->editingMethod) {
                $this->editingMethod->update($data);
                $method = $this->editingMethod->fresh();
                $message = 'Analysis Method updated successfully!';
            } else {
                $method = AnalysisMethod::create($data);
                $message = 'Analysis Method created successfully!';
            }

            $method->syncQcSchemes(
                is_array($this->methodForm['qc_scheme_ids'] ?? null) ? $this->methodForm['qc_scheme_ids'] : [],
                [
                    'mode' => $this->methodForm['qc_scheme_mode'] ?? QcSchemeBinding::MODE_OVERRIDE,
                    'priority' => (int) ($this->methodForm['qc_scheme_priority'] ?? 100),
                    'conditions' => [
                        'equipment_id' => $this->methodForm['qc_condition_equipment_id'] ?? '',
                        'crm_customer_id' => $this->methodForm['qc_condition_crm_customer_id'] ?? '',
                        'sample_type_id' => $this->methodForm['qc_condition_sample_type_id'] ?? '',
                    ],
                ]
            );

            DB::commit();

            $this->closeMethodModal();
            $this->setMessage($message, 'success');
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error saving method: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteMethod(string $methodId): void
    {
        try {
            $method = AnalysisMethod::find($methodId);
            
            if ($method) {
                // Check if method has associated analytes
                $analytesCount = $method->analytesCount();
                
                if ($analytesCount > 0) {
                    $this->setMessage('Cannot delete method with associated analytes. Please remove analytes first.', 'error');
                    return;
                }
                
                $method->delete();
                $this->setMessage('Analysis Method deleted successfully!', 'success');
                $this->resetPage();
            }
        } catch (\Exception $e) {
            $this->setMessage('Error deleting method: ' . $e->getMessage(), 'error');
        }
    }

    public function closeMethodModal(): void
    {
        $this->showMethodModal = false;
        $this->addToQcWorkflow = false;
        $this->reset(['methodForm', 'editingMethod']);
        $this->dispatch('method-modal-closed');
    }

    /**
     * True when the loaded method already has QC workflow configuration.
     */
    protected function methodHasQcWorkflowConfiguration(): bool
    {
        if (! empty($this->methodForm['based_on_standard_id'])) {
            return true;
        }

        if (! empty($this->methodForm['qc_scheme_ids'])) {
            return true;
        }

        return filled($this->methodForm['qc_condition_equipment_id'] ?? null)
            || filled($this->methodForm['qc_condition_crm_customer_id'] ?? null)
            || filled($this->methodForm['qc_condition_sample_type_id'] ?? null);
    }

    protected function clearQcWorkflowFormFields(): void
    {
        $this->methodForm['based_on_standard_id'] = '';
        $this->methodForm['qc_scheme_ids'] = [];
        $this->methodForm['qc_scheme_mode'] = QcSchemeBinding::MODE_OVERRIDE;
        $this->methodForm['qc_scheme_priority'] = 100;
        $this->methodForm['qc_condition_equipment_id'] = '';
        $this->methodForm['qc_condition_crm_customer_id'] = '';
        $this->methodForm['qc_condition_sample_type_id'] = '';
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'methodTypeFilter']);
        $this->resetPage();
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function openBulkImportModal(): void
    {
        $this->showBulkImportModal = true;
        $this->bulkFile = null;
        $this->replaceExisting = true;
        $this->purgeConfirmation = '';
        $this->resetValidation(['bulkFile', 'purgeConfirmation']);
    }

    public function closeBulkImportModal(): void
    {
        $this->showBulkImportModal = false;
        $this->bulkFile = null;
        $this->replaceExisting = true;
        $this->purgeConfirmation = '';
        $this->resetValidation(['bulkFile', 'purgeConfirmation']);
    }

    public function downloadBulkImportTemplate()
    {
        return app(BulkImportService::class)->generateTemplate('lab', 'analysis_method');
    }

    public function processBulkImport(): void
    {
        $companyName = $this->resolveCompanyName();
        $rules = [
            'bulkFile' => 'required|file|mimes:xlsx,xls,csv|max:15360',
        ];

        if ($this->replaceExisting) {
            $rules['purgeConfirmation'] = [
                'required',
                'string',
                Rule::in(array_values(array_filter(['DELETE ALL METHODS', $companyName]))),
            ];
        }

        $this->validate($rules, [
            'purgeConfirmation.in' => 'Type DELETE ALL METHODS or your company name exactly to confirm.',
        ]);

        $batch = null;

        try {
            $service = app(BulkImportService::class);
            $batch = $service->createBatch('lab', 'analysis_method');
            $results = $service->processImport(
                $batch,
                $this->bulkFile,
                null,
                $this->replaceExisting
            );

            $this->closeBulkImportModal();
            $this->resetPage();

            if (! ($results['success'] ?? false)) {
                $this->setMessage('Error processing file: '.($results['message'] ?? 'Unknown error'), 'error');

                return;
            }

            $summary = $results['summary'] ?? [];
            $imported = (int) ($summary['imported_rows'] ?? 0);
            $errors = (int) ($summary['error_rows'] ?? 0);

            if ($errors > 0) {
                $errorList = is_array($summary['errors'] ?? null) ? $summary['errors'] : [];
                $errorMessage = implode(' | ', array_map(
                    fn (array $error): string => (string) ($error['message'] ?? 'Unknown error'),
                    array_slice($errorList, 0, 5)
                ));
                if (count($errorList) > 5) {
                    $errorMessage .= ' ... and more';
                }
                $this->setMessage(
                    "{$imported} method(s) imported. {$errors} row(s) failed. Errors: {$errorMessage}",
                    'warning'
                );

                return;
            }

            $this->setMessage("{$imported} method(s) imported successfully.", 'success');
        } catch (\Throwable $e) {
            $this->closeBulkImportModal();

            if ($batch instanceof BulkImportBatch) {
                try {
                    $batch->markAsFailed($e->getMessage());
                } catch (\Throwable) {
                }
            }

            $this->setMessage('Error processing file: '.$e->getMessage(), 'error');
        }
    }

    public function resolveCompanyName(): string
    {
        $companyId = Auth::user()->company_id ?? Auth::user()->inventory_location_id;

        if (! $companyId) {
            return '';
        }

        return (string) (Company::query()->whereKey($companyId)->value('name') ?? '');
    }

    public function render()
    {
        $this->normalizeReferenceMethods();

        $standards = Standards::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_qc_standard']);

        $qcSchemes = QcSchemes::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $equipmentItems = Equipment::query()
            ->where('active', 1)
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'equipment_number']);

        $customers = CRMCustomer::query()
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'code']);

        $sampleTypes = SampleType::query()
            ->without(['analysis_types', 'sample_condition'])
            ->where('active', 1)
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name']);

        return view('livewire.lab.method-manager', [
            'standards' => $standards,
            'qcSchemes' => $qcSchemes,
            'equipmentItems' => $equipmentItems,
            'customers' => $customers,
            'sampleTypes' => $sampleTypes,
        ]);
    }
}
