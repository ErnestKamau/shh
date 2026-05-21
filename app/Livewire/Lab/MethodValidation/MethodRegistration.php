<?php

namespace App\Livewire\Lab\MethodValidation;

use Livewire\Component;
use App\AnalysisMethod;
use App\Company;
use App\Models\System\SystemConfigurationsType;
use App\Models\System\SystemConfiguration;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class MethodRegistration extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showAdvancedFilters = false;
    
    // Advanced Filters
    public $filters = [
        'method_type' => '',
        'active_status' => '',
        'is_ltm' => '',
        'company' => '',
        'created_date_from' => '',
        'created_date_to' => '',
        'created_date_year' => '',
        'created_date_month' => '',
        'updated_date_from' => '',
        'updated_date_to' => '',
        'updated_date_year' => '',
        'updated_date_month' => ''
    ];

    // Data Properties
    public $methodTypes = [];
    public $companies = [];
    public $availableYears = [];
    public $availableMonths = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => []],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 25],
        'showAdvancedFilters' => ['except' => false]
    ];

    public function mount()
    {
        $this->loadMethodTypes();
        $this->loadCompanies();
        $this->loadFilterOptions();
    }

    public function render()
    {
        $methods = $this->getFilteredMethods();
        
        return view('livewire.lab.method-validation.method-registration', [
            'methods' => $methods
        ]);
    }

    public function getFilteredMethods()
    {
        $query = AnalysisMethod::query();

        // Only show methods that have been sent for validation
        $query->where('validation_status', 'sent_for_validation');

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply filters
        if ($this->filters['method_type']) {
            $query->where('method_type_id', $this->filters['method_type']);
        }

        if ($this->filters['active_status'] !== '') {
            $query->where('active', $this->filters['active_status']);
        }

        if ($this->filters['is_ltm'] !== '') {
            $query->where('is_ltm', $this->filters['is_ltm']);
        }

        if ($this->filters['company']) {
            $query->where('company_id', $this->filters['company']);
        }


        // Date filters
        if ($this->filters['created_date_from']) {
            $query->whereDate('created_at', '>=', $this->filters['created_date_from']);
        }

        if ($this->filters['created_date_to']) {
            $query->whereDate('created_at', '<=', $this->filters['created_date_to']);
        }

        if ($this->filters['created_date_year']) {
            $query->whereYear('created_at', $this->filters['created_date_year']);
        }

        if ($this->filters['created_date_month']) {
            $query->whereMonth('created_at', $this->filters['created_date_month']);
        }

        if ($this->filters['updated_date_from']) {
            $query->whereDate('updated_at', '>=', $this->filters['updated_date_from']);
        }

        if ($this->filters['updated_date_to']) {
            $query->whereDate('updated_at', '<=', $this->filters['updated_date_to']);
        }

        if ($this->filters['updated_date_year']) {
            $query->whereYear('updated_at', $this->filters['updated_date_year']);
        }

        if ($this->filters['updated_date_month']) {
            $query->whereMonth('updated_at', $this->filters['updated_date_month']);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        // Get results with sample header relationship and add computed fields
        $methods = $query->with('sampleHeader')->paginate($this->perPage);
        
        foreach ($methods as $method) {
            $method->method_type_name = $this->getMethodTypeName($method->method_type_id);
            $method->reference_method_name = $this->getReferenceMethodName($method->reference_type_id);
        }

        return $methods;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filters = [
            'method_type' => '',
            'active_status' => '',
            'is_ltm' => '',
            'company' => '',
            'created_date_from' => '',
            'created_date_to' => '',
            'created_date_year' => '',
            'created_date_month' => '',
            'updated_date_from' => '',
            'updated_date_to' => '',
            'updated_date_year' => '',
            'updated_date_month' => ''
        ];
        $this->resetPage();
        session()->flash('info', 'Filters cleared');
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function updatedFilters($value, $key)
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    private function loadMethodTypes()
    {
        $methodTypeConfig = SystemConfigurationsType::where('configuration_type', 'Methods Types')->first();
        if ($methodTypeConfig) {
            $this->methodTypes = SystemConfiguration::where('configuration_type_id', $methodTypeConfig->id)
                ->pluck('value', 'id')
                ->toArray();
        }
    }

    private function loadCompanies()
    {
        $this->companies = Company::pluck('name', 'id')->toArray();
    }

    public function loadFilterOptions()
    {
        // Load years from created_at and updated_at
        $years = collect(
            AnalysisMethod::where('validation_status', 'sent_for_validation')
                ->whereNotNull('created_at')
                ->select(DB::raw('EXTRACT(YEAR FROM created_at)::integer as year'))
                ->distinct()
                ->pluck('year')
        );
        $years = $years->merge(
            AnalysisMethod::where('validation_status', 'sent_for_validation')
                ->whereNotNull('updated_at')
                ->select(DB::raw('EXTRACT(YEAR FROM updated_at)::integer as year'))
                ->distinct()
                ->pluck('year')
        );
        $this->availableYears = $years->unique()->sort()->toArray();
        
        // Load months
        $this->availableMonths = [
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];
    }

    private function getMethodTypeName($methodTypeId)
    {
        return $this->methodTypes[$methodTypeId] ?? 'Not Set';
    }

    private function getReferenceMethodName($referenceTypeId)
    {
        if (!$referenceTypeId) {
            return '-';
        }
        
        $referenceMethod = AnalysisMethod::find($referenceTypeId);
        return $referenceMethod ? $referenceMethod->name : '-';
    }

    public function getMethodTypeBadgeClass($methodTypeName)
    {
        $methodTypeName = strtolower($methodTypeName ?? '');
        
        return match(true) {
            str_contains($methodTypeName, 'spectroscopy') => 'badge-primary',
            str_contains($methodTypeName, 'kjeldahl') => 'badge-success',
            str_contains($methodTypeName, 'colorimetric') => 'badge-warning',
            str_contains($methodTypeName, 'calculated') => 'badge-info',
            str_contains($methodTypeName, 'gravimetric') => 'badge-secondary',
            str_contains($methodTypeName, 'titration') => 'badge-danger',
            str_contains($methodTypeName, 'chromatography') => 'badge-primary',
            str_contains($methodTypeName, 'microbiological') => 'badge-success',
            str_contains($methodTypeName, 'physical') => 'badge-info',
            str_contains($methodTypeName, 'chemical') => 'badge-warning',
            str_contains($methodTypeName, 'instrumental') => 'badge-primary',
            str_contains($methodTypeName, 'manual') => 'badge-secondary',
            str_contains($methodTypeName, 'automated') => 'badge-success',
            str_contains($methodTypeName, 'reference') => 'badge-info',
            str_contains($methodTypeName, 'standard') => 'badge-success',
            str_contains($methodTypeName, 'sampling') => 'badge-warning',
            str_contains($methodTypeName, 'laboratory') || str_contains($methodTypeName, 'test') => 'badge-primary',
            default => 'badge-dark'
        };
    }

    public function getValidationStatusBadgeClass($status)
    {
        switch ($status) {
            case 'sent_for_validation':
                return 'badge-info';
            case 'in_validation':
                return 'badge-primary';
            case 'validated':
                return 'badge-success';
            case 'validation_failed':
                return 'badge-danger';
            case 'released':
                return 'badge-success';
            default:
                return 'badge-light';
        }
    }

    public function getValidationStatusText($status)
    {
        switch ($status) {
            case 'sent_for_validation':
                return 'Sent for Validation';
            case 'in_validation':
                return 'In Validation';
            case 'validated':
                return 'Validated';
            case 'validation_failed':
                return 'Validation Failed';
            case 'released':
                return 'Released';
            default:
                return 'Unknown';
        }
    }

    public function viewDetails($id)
    {
        // Redirect to method details page
        return redirect()->route('method-validation.registration.show', $id);
    }

    public function viewValidationRequest($id)
    {
        // Show validation request details
        session()->flash('info', 'View validation request functionality will be implemented here!');
    }
}
