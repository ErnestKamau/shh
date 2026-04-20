<?php

namespace App\Livewire;

use App\Exports\UncertaintyBudgetsExport;
use App\UncertaintyBudget;
use App\Analyte;
use App\AnalysisMethod;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UncertaintyBudgetsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showAdvancedFilters = false;
    public $filters = [
        'analyte_name' => '',
        'analyte_code' => '',
        'method_name' => '',
        'coverage_factor_k' => '',
        'confidence_level' => '',
        'created_date_from' => '',
        'created_date_to' => '',
        'created_date_year' => '',
        'created_date_month' => '',
        'updated_date_from' => '',
        'updated_date_to' => '',
        'updated_date_year' => '',
        'updated_date_month' => '',
        'active' => ''
    ];
    
    // Available filter options (populated from database)
    public $availableAnalyteNames = [];
    public $availableAnalyteCodes = [];
    public $availableMethodNames = [];
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
        $this->loadFilterOptions();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $key)
    {
        $this->resetPage();
    }


    public function loadFilterOptions()
    {
        $companyId = getUserCompany();
        
        // Load analyte names and codes
        $this->availableAnalyteNames = UncertaintyBudget::join('analytes', 'uncertainty_budgets.analyte_id', '=', 'analytes.id')
            ->where('uncertainty_budgets.company_id', $companyId)
            ->where('uncertainty_budgets.active', true)
            ->distinct()
            ->pluck('analytes.name')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        $this->availableAnalyteCodes = UncertaintyBudget::join('analytes', 'uncertainty_budgets.analyte_id', '=', 'analytes.id')
            ->where('uncertainty_budgets.company_id', $companyId)
            ->where('uncertainty_budgets.active', true)
            ->distinct()
            ->pluck('analytes.code')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        // Load method names from method_ids column
        $this->availableMethodNames = [];
        $budgets = UncertaintyBudget::where('company_id', $companyId)
            ->where('active', true)
            ->whereNotNull('method_ids')
            ->get();
            
        foreach($budgets as $budget) {
            if ($budget->method_ids) {
                $methodIds = explode(',', $budget->method_ids);
                $methods = AnalysisMethod::whereIn('id', $methodIds)->get();
                foreach($methods as $method) {
                    if (!in_array($method->name, $this->availableMethodNames)) {
                        $this->availableMethodNames[] = $method->name;
                    }
                }
            }
        }
        sort($this->availableMethodNames);

        // Load years and months
        $this->availableYears = UncertaintyBudget::where('company_id', $companyId)
            ->where('active', true)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();

        $this->availableMonths = [
            ['value' => '01', 'label' => 'January'],
            ['value' => '02', 'label' => 'February'],
            ['value' => '03', 'label' => 'March'],
            ['value' => '04', 'label' => 'April'],
            ['value' => '05', 'label' => 'May'],
            ['value' => '06', 'label' => 'June'],
            ['value' => '07', 'label' => 'July'],
            ['value' => '08', 'label' => 'August'],
            ['value' => '09', 'label' => 'September'],
            ['value' => '10', 'label' => 'October'],
            ['value' => '11', 'label' => 'November'],
            ['value' => '12', 'label' => 'December']
        ];
    }

    public function getUncertaintyBudgets()
    {
        $query = UncertaintyBudget::with(['analyte', 'creator'])
            ->where('company_id', getUserCompany())
            ->where('active', true);

        // Apply search
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('analyte', function($analyteQuery) use ($searchTerm) {
                    $analyteQuery->where('name', 'like', $searchTerm)
                                ->orWhere('code', 'like', $searchTerm);
                })
                ->orWhereRaw('EXISTS (
                    SELECT 1 FROM analysis_methods 
                    WHERE FIND_IN_SET(analysis_methods.id, uncertainty_budgets.method_ids) > 0 
                    AND (analysis_methods.name LIKE ? OR analysis_methods.code LIKE ?)
                )', [$searchTerm, $searchTerm]);
            });
        }

        // Apply advanced filters
        if ($this->filters['analyte_name']) {
            $query->whereHas('analyte', function($q) {
                $q->where('name', 'like', '%' . $this->filters['analyte_name'] . '%');
            });
        }

        if ($this->filters['analyte_code']) {
            $query->whereHas('analyte', function($q) {
                $q->where('code', 'like', '%' . $this->filters['analyte_code'] . '%');
            });
        }

        if ($this->filters['method_name']) {
            $query->whereRaw('EXISTS (
                SELECT 1 FROM analysis_methods 
                WHERE FIND_IN_SET(analysis_methods.id, uncertainty_budgets.method_ids) > 0 
                AND analysis_methods.name LIKE ?
            )', ['%' . $this->filters['method_name'] . '%']);
        }

        if ($this->filters['coverage_factor_k']) {
            $query->where('coverage_factor_k', $this->filters['coverage_factor_k']);
        }

        if ($this->filters['confidence_level']) {
            $query->where('confidence_level', $this->filters['confidence_level']);
        }

        if ($this->filters['active'] !== '') {
            $query->where('active', $this->filters['active']);
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

        return $query->orderBy($this->sortField, $this->sortDirection);
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

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function clearFilters()
    {
        $this->filters = [
            'analyte_name' => '',
            'analyte_code' => '',
            'method_name' => '',
            'coverage_factor_k' => '',
            'confidence_level' => '',
            'created_date_from' => '',
            'created_date_to' => '',
            'created_date_year' => '',
            'created_date_month' => '',
            'updated_date_from' => '',
            'updated_date_to' => '',
            'updated_date_year' => '',
            'updated_date_month' => '',
            'active' => ''
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function exportToExcel()
    {
        $budgets = $this->getUncertaintyBudgets()->get();
        $filename = 'uncertainty_budgets_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new UncertaintyBudgetsExport($budgets), $filename);
    }

    public function exportToPdf()
    {
        // Implementation for PDF export
        // This would typically use a PDF generation package
        return redirect()->back()->with('info', 'PDF export functionality will be implemented.');
    }


    public function delete($id)
    {
        $budget = UncertaintyBudget::where('id', $id)
            ->where('company_id', getUserCompany())
            ->first();

        if ($budget) {
            $budget->update(['active' => false]);
            session()->flash('success', 'Uncertainty budget deactivated successfully.');
        }
    }

    public function render()
    {
        $uncertaintyBudgets = $this->getUncertaintyBudgets()->paginate($this->perPage);

        return view('livewire.uncertainty-budgets-table', [
            'uncertaintyBudgets' => $uncertaintyBudgets
        ]);
    }
}
