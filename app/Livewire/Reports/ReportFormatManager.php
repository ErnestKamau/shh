<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\ReportFormat;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportFormatManager extends Component
{
    use WithPagination;

    // Report Formats Management
    public $editingReportFormat = null;
    public $showReportFormatModal = false;
    
    // Report Format Form
    public $reportFormatForm = [
        'report_name' => '',
        'report_code' => '',
        'is_active' => true
    ];

    // Search and Filter
    public $search = '';
    public $statusFilter = '1'; // Default to active only

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'reportFormatForm.report_name' => 'required|string|max:255',
        'reportFormatForm.report_code' => 'required|string|max:255',
        'reportFormatForm.is_active' => 'boolean',
    ];

    protected $messages = [
        'reportFormatForm.report_name.required' => 'Report format name is required.',
        'reportFormatForm.report_code.required' => 'Report format code is required.',
    ];

    public function mount()
    {
        // Component initialization
    }

    public function getReportFormatsProperty()
    {
        $query = ReportFormat::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('report_name', 'like', '%' . $this->search . '%')
                  ->orWhere('report_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        return $query->orderBy('report_name', 'asc')->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '1'; // Reset to active only
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Report Format Methods
    public function showCreateReportFormatModal()
    {
        $this->resetReportFormatForm();
        $this->showReportFormatModal = true;
    }

    public function showEditReportFormatModal($id)
    {
        $reportFormat = ReportFormat::findOrFail($id);
        $this->reportFormatForm = [
            'report_name' => $reportFormat->report_name,
            'report_code' => $reportFormat->report_code,
            'is_active' => $reportFormat->is_active
        ];
        $this->editingReportFormat = $id;
        $this->showReportFormatModal = true;
    }

    public function saveReportFormat()
    {
        $this->validate([
            'reportFormatForm.report_name' => 'required|string|max:255',
            'reportFormatForm.report_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('report_formats', 'report_code')
                    ->where('company_id', getUserCompany())
                    ->ignore($this->editingReportFormat)
            ],
            'reportFormatForm.is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingReportFormat) {
                $reportFormat = ReportFormat::findOrFail($this->editingReportFormat);
                $reportFormat->update([
                    'report_name' => $this->reportFormatForm['report_name'],
                    'report_code' => $this->reportFormatForm['report_code'],
                    'is_active' => $this->reportFormatForm['is_active'],
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Report format updated successfully!';
            } else {
                ReportFormat::create([
                    'report_name' => $this->reportFormatForm['report_name'],
                    'report_code' => $this->reportFormatForm['report_code'],
                    'is_active' => $this->reportFormatForm['is_active'],
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Report format created successfully!';
            }

            DB::commit();
            $this->closeReportFormatModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteReportFormat($id)
    {
        try {
            DB::beginTransaction();

            $reportFormat = ReportFormat::findOrFail($id);
            
            // Check if any sample types are using this report format
            if ($reportFormat->sampleTypes()->count() > 0) {
                $this->message = 'Cannot delete report format. It is being used by sample types.';
                $this->messageType = 'error';
                return;
            }
            
            $reportFormat->delete();

            DB::commit();
            $this->message = 'Report format deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeReportFormatModal()
    {
        $this->showReportFormatModal = false;
        $this->resetReportFormatForm();
    }

    public function resetReportFormatForm()
    {
        $this->reportFormatForm = [
            'report_name' => '',
            'report_code' => '',
            'is_active' => true
        ];
        $this->editingReportFormat = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.reports.report-format-manager');
    }
}