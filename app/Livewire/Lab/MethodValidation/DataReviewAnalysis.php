<?php

namespace App\Livewire\Lab\MethodValidation;

use Livewire\Component;
use App\AnalysisMethod;
use App\Company;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\System\SystemConfigurationsType;
use App\Models\System\SystemConfiguration;
use Livewire\WithPagination;

class DataReviewAnalysis extends Component
{
    use AppliesCaseInsensitiveSearch;
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
        'company' => '',
        'created_date_from' => '',
        'created_date_to' => ''
    ];

    // Data Properties
    public $methodTypes = [];
    public $companies = [];

    // Approval modal properties
    public $showApprovalModal = false;
    public $selectedMethodId;
    public $selectedAction;
    public $approvalAction = '';
    public $approvalReason = '';
    public $sendNotification = false;

    // Return sample modal properties
    public $showReturnSampleModal = false;
    public $selectedReturnMethodId;
    public $selectedReturnAction;
    public $returnAction = '';
    public $returnReason = '';
    public $sendReturnNotification = false;


    public function mount()
    {
        $this->loadMethodTypes();
        $this->loadCompanies();
    }

    public function render()
    {
        $methodsInValidation = $this->getMethodsWithTestingInfo();

        return view('livewire.lab.method-validation.data-review-analysis', [
            'methodsInValidation' => $methodsInValidation
        ]);
    }

    public function getFilteredMethods()
    {
        $query = AnalysisMethod::query();

        // Only show methods that are in validation status
        $query->where('validation_status', 'in_validation');

        // Apply search
        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code', 'description'], (string) $this->search);
        }

        // Apply filters
        if ($this->filters['method_type']) {
            $query->where('method_type_id', $this->filters['method_type']);
        }

        if ($this->filters['active_status'] !== '') {
            $query->where('active', $this->filters['active_status']);
        }

        if ($this->filters['company']) {
            $query->where('company_id', $this->filters['company']);
        }

        if ($this->filters['created_date_from']) {
            $query->whereDate('created_at', '>=', $this->filters['created_date_from']);
        }

        if ($this->filters['created_date_to']) {
            $query->whereDate('created_at', '<=', $this->filters['created_date_to']);
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
            'company' => '',
            'created_date_from' => '',
            'created_date_to' => ''
        ];
        $this->resetPage();
        session()->flash('info', 'Filters cleared');
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function updatedFilters()
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
            case 'returned_to_lab':
                return 'badge-warning';
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
            case 'returned_to_lab':
                return 'Returned to Lab';
            case 'released':
                return 'Released';
            default:
                return 'Unknown';
        }
    }

    public function openApprovalModal($methodId, $action)
    {
        // Check if user has permission to approve methods
        if (!auth()->user()->checkApproveMethodsRole()) {
            session()->flash('error', 'You do not have permission to approve or reject methods.');
            return;
        }
        
        $this->selectedMethodId = $methodId;
        $this->selectedAction = $action;
        $this->approvalAction = $action;
        $this->approvalReason = '';
        $this->sendNotification = false;

        $method = \App\AnalysisMethod::find($methodId);
        $actionText = $action === 'approve' ? 'Approve' : 'Reject';
        
        $this->dispatch('showApprovalModal', [
            'title' => "{$actionText} Method Validation",
            'message' => "Please confirm your decision to {$action} the method '{$method->name}'."
        ]);
    }

    public function submitApproval()
    {
        // Double-check permission before processing
        if (!auth()->user()->checkApproveMethodsRole()) {
            session()->flash('error', 'You do not have permission to approve or reject methods.');
            return;
        }
        
        $this->validate([
            'approvalAction' => 'required|in:approve,reject',
            'approvalReason' => 'required|string|min:10'
        ], [
            'approvalAction.required' => 'Please select an action.',
            'approvalAction.in' => 'Invalid action selected.',
            'approvalReason.required' => 'Please provide a reason.',
            'approvalReason.min' => 'Reason must be at least 10 characters.'
        ]);

        try {
            $method = \App\AnalysisMethod::find($this->selectedMethodId);
            
            if (!$method) {
                session()->flash('error', 'Method not found.');
                return;
            }

            // Update method status based on action
            if ($this->approvalAction === 'approve') {
                $method->validation_status = 'validated';
                $message = 'Method approved successfully.';
                $messageType = 'success';
            } else {
                $method->validation_status = 'validation_failed';
                $message = 'Method rejected successfully.';
                $messageType = 'warning';
            }

            $method->save();

            // Log the approval/rejection
            \Log::info("Method validation {$this->approvalAction}d", [
                'method_id' => $this->selectedMethodId,
                'method_name' => $method->name,
                'action' => $this->approvalAction,
                'reason' => $this->approvalReason,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            // Log notification request (email notification can be implemented later)
            \Log::info("Method approval action completed", [
                'method_id' => $this->selectedMethodId,
                'method_name' => $method->name,
                'action' => $this->approvalAction
            ]);

            $this->resetApprovalModal();
            $this->dispatch('hideApprovalModal');
            session()->flash($messageType, $message);

        } catch (\Exception $e) {
            \Log::error("Method approval failed: " . $e->getMessage());
            session()->flash('error', 'Failed to process approval. Please try again.');
        }
    }

    public function resetApprovalModal()
    {
        $this->selectedMethodId = null;
        $this->selectedAction = null;
        $this->approvalAction = '';
        $this->approvalReason = '';
        $this->sendNotification = false;
    }

    public function closeApprovalModal()
    {
        $this->resetApprovalModal();
        $this->dispatch('hideApprovalModal');
    }

    public function openReturnSampleModal($methodId)
    {
        // Check if user has permission to return samples
        if (!auth()->user()->checkApproveMethodsRole()) {
            session()->flash('error', 'You do not have permission to return samples to lab.');
            return;
        }
        
        $this->selectedReturnMethodId = $methodId;
        $this->selectedReturnAction = 'return_to_lab';
        $this->returnAction = 'return_to_lab';
        $this->returnReason = '';
        $this->sendReturnNotification = false;

        $method = \App\AnalysisMethod::find($methodId);
        
        $this->dispatch('showReturnSampleModal', [
            'title' => 'Return Sample to Lab',
            'message' => "Please confirm your decision to return the sample for method '{$method->name}' back to the lab."
        ]);
    }

    public function submitReturnSample()
    {
        // Double-check permission before processing
        if (!auth()->user()->checkApproveMethodsRole()) {
            session()->flash('error', 'You do not have permission to return samples to lab.');
            return;
        }
        
        $this->validate([
            'returnAction' => 'required|in:return_to_lab',
            'returnReason' => 'required|string|min:10'
        ], [
            'returnAction.required' => 'Please select an action.',
            'returnAction.in' => 'Invalid action selected.',
            'returnReason.required' => 'Please provide a reason.',
            'returnReason.min' => 'Reason must be at least 10 characters.'
        ]);

        try {
            $method = \App\AnalysisMethod::find($this->selectedReturnMethodId);
            
            if (!$method) {
                session()->flash('error', 'Method not found.');
                return;
            }

            // Get the sample header associated with this method
            $sampleHeader = $method->sampleHeader;
            
            if (!$sampleHeader) {
                session()->flash('error', 'No sample header found for this method.');
                return;
            }

            // Update method validation status to indicate it's back in validation
            $method->validation_status = 'in_validation';
            $method->save();

            // Update sample header status back to lab analysis
            $sampleHeader->status = 'Samples In Lab';
            $sampleHeader->save();

            // Update chain of custody
            $this->updateChainOfCustody($sampleHeader->id, $this->returnReason);

            // Log the return action
            \Log::info("Sample returned to lab for continued validation", [
                'method_id' => $this->selectedReturnMethodId,
                'method_name' => $method->name,
                'sample_header_id' => $sampleHeader->id,
                'return_reason' => $this->returnReason,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            // Send notification if requested
            if ($this->sendReturnNotification) {
                // TODO: Implement email notification to lab personnel
                \Log::info("Email notification requested for sample return", [
                    'method_id' => $this->selectedReturnMethodId,
                    'method_name' => $method->name,
                    'sample_header_id' => $sampleHeader->id
                ]);
            }

            $this->resetReturnSampleModal();
            $this->dispatch('hideReturnSampleModal');
            session()->flash('success', 'Sample has been returned to the lab successfully.');

        } catch (\Exception $e) {
            \Log::error("Sample return failed: " . $e->getMessage());
            session()->flash('error', 'Failed to return sample to lab. Please try again.');
        }
    }

    private function updateChainOfCustody($sampleHeaderId, $reason)
    {
        try {
            // Close the current chain of custody entry
            \App\ChainOfCustody::where('sample_header_id', $sampleHeaderId)
                ->whereNull('moved_out_date')
                ->update([
                    'moved_out_date' => \Carbon\Carbon::now(),
                    'moved_out_by' => \Auth::user()->id,
                    'comments' => 'Sample returned to lab for continued validation. Reason: ' . $reason
                ]);

            // Create new chain of custody entry for lab analysis
            $custody = new \App\ChainOfCustody;
            $custody->workflow_stage = 'Samples In Lab';
            $custody->tracking_stage_id = 1; // Assuming 1 is the lab analysis stage
            $custody->moved_in_by = \Auth::user()->id;
            $custody->sample_header_id = $sampleHeaderId;
            $custody->comments = 'Sample returned from data review for continued validation. Return reason: ' . $reason;
            $custody->save();

            return true;
        } catch (\Exception $e) {
            \Log::error("Chain of custody update failed: " . $e->getMessage());
            return false;
        }
    }

    public function resetReturnSampleModal()
    {
        $this->selectedReturnMethodId = null;
        $this->selectedReturnAction = null;
        $this->returnAction = '';
        $this->returnReason = '';
        $this->sendReturnNotification = false;
    }

    public function closeReturnSampleModal()
    {
        $this->resetReturnSampleModal();
        $this->dispatch('hideReturnSampleModal');
    }
    
    /**
     * Detect testing option from validation info stored in sample header
     */
    private function getTestingOption($method)
    {
        if (!$method->sampleHeader) {
            return 'lab_and_reference'; // Default fallback
        }
        
        try {
            $validationInfo = json_decode($method->sampleHeader->how_sample_was_obtained, true);
            return $validationInfo['testing_option'] ?? 'lab_and_reference';
        } catch (\Exception $e) {
            \Log::warning('Could not parse validation info for method ' . $method->id);
            return 'lab_and_reference'; // Default fallback
        }
    }
    
    /**
     * Get validation info from sample header
     */
    private function getValidationInfo($method)
    {
        if (!$method->sampleHeader) {
            return null;
        }
        
        try {
            return json_decode($method->sampleHeader->how_sample_was_obtained, true);
        } catch (\Exception $e) {
            \Log::warning('Could not parse validation info for method ' . $method->id);
            return null;
        }
    }
    
    /**
     * Enhanced method retrieval with testing option detection
     */
    public function getMethodsWithTestingInfo()
    {
        $methods = $this->getFilteredMethods();
        
        foreach ($methods as $method) {
            $method->testing_option = $this->getTestingOption($method);
            $method->validation_info = $this->getValidationInfo($method);
            $method->has_reference_results = ($method->testing_option === 'lab_with_reference_results');
        }
        
        return $methods;
    }
}

