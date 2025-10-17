<?php

namespace App\Livewire\Submissions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmissionFormsManager extends Component
{
    use WithPagination;

    // Modal Management
    public bool $showCaptureModal = false;
    public $availableForms = [];
    public $selectedFormId = null;
    public $selectedFormPreview = null;

    // Filters
    public string $searchTerm = '';
    public string $statusFilter = '';
    public string $priorityFilter = '';
    public string $formTypeFilter = '';

    // Pagination
    public int $perPage = 15;
    public array $perPageOptions = [10, 15, 25, 50, 100];

    // Messages
    public ?string $message = null;
    public string $messageType = 'success';

    /**
     * Lifecycle hook - Initialize component
     */
    public function mount()
    {
        // Load available forms for filter dropdown
        $this->loadAvailableFormsForFilter();
    }

    /**
     * Render the component
     */
    public function render()
    {
        $instances = $this->getInstancesQuery();

        return view('livewire.submissions.submission-forms-manager', [
            'instances' => $instances
        ]);
    }

    /**
     * Get instances query with filters applied
     */
    public function getInstancesQuery()
    {
        $query = SubmissionFormInstance::query()
            ->with(['submissionForm', 'submittedBy'])
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('form_number', 'like', "%{$this->searchTerm}%")
                    ->orWhere('title', 'like', "%{$this->searchTerm}%")
                    ->orWhereHas('submissionForm', function($q) {
                        $q->where('name', 'like', "%{$this->searchTerm}%");
                    });
            });
        }

        // Status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Priority filter
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        // Form type filter
        if ($this->formTypeFilter) {
            $query->where('submission_form_id', $this->formTypeFilter);
        }

        return $query->paginate($this->perPage);
    }

    /**
     * Load available forms for filter dropdown
     */
    private function loadAvailableFormsForFilter()
    {
        $this->availableForms = SubmissionForm::where('is_published', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Open capture modal and load forms
     */
    public function openCaptureModal()
    {
        // Load published forms with full details
        $this->availableForms = SubmissionForm::where('is_published', true)
            ->where('is_active', true)
            ->with('creator')
            ->withCount('sections')
            ->orderBy('name')
            ->get();

        $this->showCaptureModal = true;
    }

    /**
     * Close capture modal
     */
    public function closeCaptureModal()
    {
        $this->showCaptureModal = false;
        $this->selectedFormId = null;
        $this->selectedFormPreview = null;
        
        // Reload forms for filter dropdown
        $this->loadAvailableFormsForFilter();
    }

    /**
     * Handle form selection
     */
    public function updatedSelectedFormId($value)
    {
        if ($value) {
            // Load preview data
            $this->selectedFormPreview = SubmissionForm::with('creator')
                ->withCount('sections')
                ->find($value);
        } else {
            $this->selectedFormPreview = null;
        }
    }

    /**
     * Create form instance and redirect to fill it
     */
    public function createFormInstance()
    {
        // Validate selection
        if (!$this->selectedFormId) {
            $this->message = 'Please select a submission form';
            $this->messageType = 'danger';
            return;
        }

        // Load form
        $submissionForm = SubmissionForm::find($this->selectedFormId);

        if (!$submissionForm || !$submissionForm->is_published || !$submissionForm->is_active) {
            $this->message = 'Selected form is not available';
            $this->messageType = 'danger';
            return;
        }

        try {
            // Create instance
            $instance = SubmissionFormInstance::create([
                'submission_form_id' => $submissionForm->id,
                'submitted_by' => auth()->id(),
                'status' => 'draft',
                'title' => 'New ' . $submissionForm->name . ' Submission',
                'form_number' => $this->generateFormNumber($submissionForm),
                'due_date' => now()->addDays(7),
                'priority' => 'normal'
            ]);

            Log::info('Form instance created', [
                'instance_id' => $instance->id,
                'form_number' => $instance->form_number,
                'user_id' => auth()->id()
            ]);

            // Redirect to fill form page using sample-submissions layout
            return redirect()->route('submission-forms.instances.fill-sample', [
                $submissionForm, 
                $instance
            ]);

        } catch (\Exception $e) {
            $this->message = 'Error creating form instance: ' . $e->getMessage();
            $this->messageType = 'danger';
            
            Log::error('Error creating form instance', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Generate unique form number
     */
    private function generateFormNumber($submissionForm)
    {
        $prefix = strtoupper(substr($submissionForm->name, 0, 3));
        $year = date('Y');
        
        // Get last number for this form in current year
        $lastNumber = SubmissionFormInstance::where('submission_form_id', $submissionForm->id)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return $prefix . '-' . $year . '-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Delete instance
     */
    public function deleteInstance($instanceId)
    {
        try {
            $instance = SubmissionFormInstance::find($instanceId);

            if (!$instance) {
                $this->message = 'Instance not found';
                $this->messageType = 'danger';
                return;
            }

            // Only allow deletion of drafts
            if ($instance->status !== 'draft') {
                $this->message = 'Only draft submissions can be deleted';
                $this->messageType = 'danger';
                return;
            }

            $formNumber = $instance->form_number;
            
            // Delete related values first
            $instance->values()->delete();
            
            // Delete instance
            $instance->delete();

            $this->message = "Form instance {$formNumber} deleted successfully";
            $this->messageType = 'success';

            Log::info('Form instance deleted', [
                'form_number' => $formNumber,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            $this->message = 'Error deleting instance: ' . $e->getMessage();
            $this->messageType = 'danger';
            
            Log::error('Error deleting instance', [
                'error' => $e->getMessage(),
                'instance_id' => $instanceId
            ]);
        }
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->formTypeFilter = '';
        $this->resetPage();
    }

    /**
     * Dismiss message
     */
    public function dismissMessage()
    {
        $this->message = null;
    }

    /**
     * When search term is updated
     */
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    /**
     * When status filter is updated
     */
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * When priority filter is updated
     */
    public function updatedPriorityFilter()
    {
        $this->resetPage();
    }

    /**
     * When form type filter is updated
     */
    public function updatedFormTypeFilter()
    {
        $this->resetPage();
    }

    /**
     * When per page is updated
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Get badge color for status
     */
    public function getStatusBadgeColor($status)
    {
        return match($status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'in_review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary'
        };
    }

    /**
     * Get badge color for priority
     */
    public function getPriorityBadgeColor($priority)
    {
        return match($priority) {
            'low' => 'info',
            'normal' => 'secondary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }
}

