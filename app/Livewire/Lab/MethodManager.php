<?php

namespace App\Livewire\Lab;

use App\AnalysisMethod;
use App\Models\CRM\CRMCustomer;
use App\Models\Equipments\Equipment;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\QcSchemeBinding;
use App\Models\System\SystemConfiguration;
use App\SampleType;
use App\Standards;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class MethodManager extends Component
{
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

    protected function rules(): array
    {
        return [
            'methodForm.name' => 'required|string|max:255',
            'methodForm.code' => 'required|string|max:255',
            'methodForm.description' => 'required|string',
            'methodForm.method_type_id' => 'nullable|integer',
            'methodForm.reference_type_id' => 'nullable|integer',
            'methodForm.based_on_standard_id' => ['nullable', 'string', Rule::exists('standards', 'id')],
            'methodForm.qc_scheme_ids' => ['array'],
            'methodForm.qc_scheme_ids.*' => ['string', Rule::exists('qc_scheme', 'id')],
            'methodForm.qc_scheme_mode' => ['required', Rule::in([
                QcSchemeBinding::MODE_OVERRIDE,
                QcSchemeBinding::MODE_MERGE,
                QcSchemeBinding::MODE_ADDITIVE,
            ])],
            'methodForm.qc_scheme_priority' => ['required', 'integer', 'min:1', 'max:1000'],
            'methodForm.qc_condition_equipment_id' => ['nullable', 'string', Rule::exists('equipment', 'id')],
            'methodForm.qc_condition_crm_customer_id' => ['nullable', 'string', Rule::exists('crm_customers', 'id')],
            'methodForm.qc_condition_sample_type_id' => ['nullable', 'string', Rule::exists('sample_types', 'id')],
            'methodForm.active' => 'boolean',
        ];
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
        // Load method types from system configuration
        $this->methodTypes = SystemConfiguration::where('key', 'method_type')->get();
        
        // Get LTM method type ID
        $ltmConfig = SystemConfiguration::where('key', 'method_ltm_id')->first();
        $this->ltmMethodTypeId = $ltmConfig ? $ltmConfig->value : null;
        
        // Load reference methods
        $referenceConfig = SystemConfiguration::where('key', 'method_reference_id')->first();
        if ($referenceConfig) {
            $this->referenceMethods = AnalysisMethod::where('method_type_id', $referenceConfig->value)
                ->where('active', 1)
                ->get();
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

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

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
        $this->methodForm['active'] = true;
        $this->methodForm['qc_scheme_ids'] = [];
        $this->methodForm['qc_scheme_mode'] = QcSchemeBinding::MODE_OVERRIDE;
        $this->methodForm['qc_scheme_priority'] = 100;
        $this->methodForm['qc_condition_equipment_id'] = '';
        $this->methodForm['qc_condition_crm_customer_id'] = '';
        $this->methodForm['qc_condition_sample_type_id'] = '';
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
                'method_type_id' => $this->editingMethod->method_type_id,
                'reference_type_id' => $this->editingMethod->reference_type_id,
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
            
            $this->showMethodModal = true;
            $this->dispatch('method-modal-opened');
        }
    }

    public function saveMethod(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->methodForm['name'],
                'code' => $this->methodForm['code'],
                'description' => $this->methodForm['description'],
                'method_type_id' => $this->methodForm['method_type_id'] ?: null,
                'reference_type_id' => $this->methodForm['reference_type_id'] ?: null,
                'based_on_standard_id' => $this->methodForm['based_on_standard_id'] ?: null,
                'active' => $this->methodForm['active'] ? 1 : 0,
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
                $analytesCount = $method->analytes()->count();
                
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
        $this->reset(['methodForm', 'editingMethod']);
        $this->dispatch('method-modal-closed');
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

