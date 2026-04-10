<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketCategory;
use App\Models\CRM\TicketStatus;
use App\Models\CRM\TicketPriority;
use App\Models\CRM\TicketChangeHistory;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaintattachment;
use App\Services\DeveloperSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Validation\ValidationException;

class CreateTicket extends Component
{
    use WithFileUploads;

    public $ticket_category_id = '';
    public $description = '';
    public $screenshots = [];
    public $files = [];

    // Available categories
    public $categories = [];

    // Modal state
    public $showCategoryModal = false;
    public $newCategoryName = '';
    public $newCategoryDescription = '';

    protected $rules = [
        'ticket_category_id' => 'required|exists:ticket_categories,id',
        'description' => 'required|string|max:5000',
        'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt|max:2048',
    ];

    protected $messages = [
        'ticket_category_id.required' => 'Please select a ticket category.',
        'description.required' => 'Please provide a description of your issue.',
        'screenshots.*.image' => 'Screenshots must be image files.',
        'screenshots.*.max' => 'Each screenshot must be less than 2MB.',
        'files.*.max' => 'Each file must be less than 2MB.',
    ];

    public function mount(): void
    {
        $this->loadCategories();
    }

    /**
     * Load available categories
     */
    private function loadCategories(): void
    {
        $this->categories = TicketCategory::active()->orderBy('name')->get();
    }

    /**
     * Remove a screenshot
     */
    public function removeScreenshot(int $index): void
    {
        unset($this->screenshots[$index]);
        $this->screenshots = array_values($this->screenshots);
    }

    /**
     * Remove a file
     */
    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    /**
     * Create a new category
     */
    public function createCategory(): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255|unique:ticket_categories,name',
            'newCategoryDescription' => 'nullable|string|max:1000',
        ], [
            'newCategoryName.required' => 'Category name is required.',
            'newCategoryName.unique' => 'This category name already exists.',
        ]);

        $category = TicketCategory::create([
            'name' => $this->newCategoryName,
            'description' => $this->newCategoryDescription,
            'active' => 1,
        ]);

        // Reload categories
        $this->loadCategories();

        // Select the new category
        $this->ticket_category_id = $category->id;

        // Reset modal
        $this->showCategoryModal = false;
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';

        $this->dispatch('category-created', category: $category);
    }

    /**
     * Close category modal
     */
    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';
    }

    /**
     * Store the ticket
     */
    public function store()
    {
        $this->validate();

        $user = Auth::user();

        // Generate ticket number
        $ticketCount = Complaint::withTrashed()->count() + 1;
        $ticketNoStr = strval($ticketCount);
        if (strlen($ticketNoStr) < 6) {
            $diff = 6 - strlen($ticketNoStr);
            $zero = str_repeat("0", $diff);
            $ticketNo = "TKT" . $zero . $ticketNoStr;
        } else {
            $ticketNo = "TKT" . $ticketNoStr;
        }

        // Generate complaint_id (for backward compatibility)
        $complaintCount = Complaint::withTrashed()->count() + 1;
        $complaintIdStr = strval($complaintCount);
        if (strlen($complaintIdStr) < 4) {
            $diff = 4 - strlen($complaintIdStr);
            $zero = str_repeat("0", $diff);
            $complaintId = "COMP" . $zero . $complaintIdStr;
        } else {
            $complaintId = "COMP" . $complaintIdStr;
        }

        // Get default priority from database
        $defaultPriority = TicketPriority::active()
            ->where('value', 'medium')
            ->first();

        $priorityValue = $defaultPriority ? $defaultPriority->value : 'medium';

        // Get default status (Open) from database
        $defaultStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%Open%')
                    ->orWhere('name', 'like', '%Draft%');
            })
            ->orderBy('workflow_value')
            ->first();

        if (!$defaultStatus) {
            throw ValidationException::withMessages([
                'ticket_category_id' => ['Default ticket status not found in the system. Please contact an administrator.'],
            ]);
        }

        $workflowValue = $defaultStatus->workflow_value;

        // Create ticket
        $ticket = new Complaint();
        $ticket->complaint_id = $complaintId;
        $ticket->ticket_no = $ticketNo;
        $ticket->ticket_category_id = $this->ticket_category_id;
        $ticket->description = $this->description;
        $ticket->priority = $priorityValue;
        $ticket->received_from = $user->name;
        $ticket->registered_by = $user->name;
        $ticket->created_by = $user->name;
        $ticket->date = now();
        $ticket->time_created = now();
        $ticket->complaint_workflow = $workflowValue;
        $ticket->submitted_from = 'ticketing_system';
        $ticket->client_id = $user->client_id ?? 0;
        $ticket->raised_by = $user->name;
        $ticket->save();

        // Log change history
        TicketChangeHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'change_type' => 'created',
            'field_name' => 'ticket',
            'old_value' => null,
            'new_value' => 'Ticket created',
            'created_at' => now(),
        ]);

        // Chain of custody
        $chainCustody = new Chain_of_Custody_Complaint();
        $chainCustody->complaint_id = $ticket->id;
        $chainCustody->action = "Create ticket";
        $chainCustody->action_taker_id = $user->id;
        $chainCustody->workflow_stage = $ticket->complaint_workflow;
        $chainCustody->save();

        // Handle file uploads
        $this->handleFileUploads($ticket->id);

        // Sync to developer app
        try {
            $syncService = app(DeveloperSyncService::class);
            $synced = $syncService->syncNewTicket($ticket->fresh());

            if (!$synced) {
                // Sync failed but ticket was created locally
                session()->flash('warning', 'Ticket created locally but sync to developer app failed. It will be retried automatically.');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync ticket after creation', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail ticket creation if sync fails
        }

        // Reset form
        $this->reset(['ticket_category_id', 'description', 'screenshots', 'files']);

        // Set success message and redirect to ticket show page
        session()->flash('success', 'Ticket created successfully! Your ticket number is: ' . $ticketNo);
        return $this->redirect(route('tickets.show', $ticket->id), navigate: true);
    }

    /**
     * Handle file uploads (screenshots and files)
     */
    private function handleFileUploads(int $ticketId): void
    {
        $user = Auth::user();

        // Handle screenshots
        if (!empty($this->screenshots)) {
            foreach ($this->screenshots as $index => $screenshot) {
                if (!$screenshot || !$screenshot->isValid()) {
                    continue;
                }

                try {
                    $path = $screenshot->store('tickets/screenshots', 'public');
                    $fname = '/storage/' . $path;

                    Complaintattachment::create([
                        'complaint_id' => $ticketId,
                        'title' => 'Screenshot ' . ($index + 1),
                        'type' => 'screenshot',
                        'file_type' => 'screenshot',
                        'file_path' => $fname,
                        'file_size' => $screenshot->getSize() / 1024, // Size in KB
                        'posted_by' => $user->name,
                        'description' => 'Screenshot uploaded with ticket',
                        'is_delete' => false,
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Ticket {$ticketId}: Failed to upload screenshot - " . $e->getMessage());
                }
            }
        }

        // Handle files
        if (!empty($this->files)) {
            foreach ($this->files as $index => $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                try {
                    $path = $file->store('tickets/files', 'public');
                    $fname = '/storage/' . $path;

                    Complaintattachment::create([
                        'complaint_id' => $ticketId,
                        'title' => $file->getClientOriginalName(),
                        'type' => 'document',
                        'file_type' => 'document',
                        'file_path' => $fname,
                        'file_size' => $file->getSize() / 1024, // Size in KB
                        'posted_by' => $user->name,
                        'description' => 'File uploaded with ticket',
                        'is_delete' => false,
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Ticket {$ticketId}: Failed to upload file - " . $e->getMessage());
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.ticket.create-ticket');
    }
}

