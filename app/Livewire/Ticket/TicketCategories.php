<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use App\Models\CRM\TicketCategory;
use Illuminate\Validation\ValidationException;

class TicketCategories extends Component
{
    public $categories = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    
    // Form data
    public $form = [
        'id' => null,
        'name' => '',
        'description' => '',
    ];
    
    public $deleteCategoryId = null;

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.description' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'form.name.required' => 'Category name is required.',
        'form.name.max' => 'Category name may not be greater than 255 characters.',
        'form.description.max' => 'Description may not be greater than 1000 characters.',
    ];

    public function mount(): void
    {
        $this->loadCategories();
    }

    /**
     * Load all categories
     */
    private function loadCategories(): void
    {
        $this->categories = TicketCategory::orderBy('name')->get();
    }

    /**
     * Open create modal
     */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    /**
     * Open edit modal
     */
    public function openEditModal(int $id): void
    {
        $category = TicketCategory::findOrFail($id);
        $this->form = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description ?? '',
        ];
        $this->showEditModal = true;
    }

    /**
     * Open delete modal
     */
    public function openDeleteModal(int $id): void
    {
        $this->deleteCategoryId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Close all modals
     */
    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->deleteCategoryId = null;
    }

    /**
     * Reset form
     */
    private function resetForm(): void
    {
        $this->form = [
            'id' => null,
            'name' => '',
            'description' => '',
        ];
        $this->resetErrorBag();
    }

    /**
     * Store a new category
     */
    public function store(): void
    {
        $this->validate([
            'form.name' => 'required|string|max:255|unique:ticket_categories,name',
        ], [
            'form.name.unique' => 'This category name already exists.',
        ]);

        TicketCategory::create([
            'name' => $this->form['name'],
            'description' => $this->form['description'],
            'active' => 1,
        ]);

        $this->loadCategories();
        $this->closeModals();
        
        $this->dispatch('category-created');
    }

    /**
     * Update a category
     */
    public function update(): void
    {
        $this->validate([
            'form.name' => 'required|string|max:255|unique:ticket_categories,name,' . $this->form['id'],
        ], [
            'form.name.unique' => 'This category name already exists.',
        ]);

        $category = TicketCategory::findOrFail($this->form['id']);
        $category->update([
            'name' => $this->form['name'],
            'description' => $this->form['description'],
        ]);

        $this->loadCategories();
        $this->closeModals();
        
        $this->dispatch('category-updated');
    }

    /**
     * Toggle category active status
     */
    public function toggleStatus(int $id): void
    {
        $category = TicketCategory::findOrFail($id);
        
        // Check if category is used by any tickets when trying to deactivate
        $ticketCount = $category->tickets()->count();
        if ($ticketCount > 0 && $category->active) {
            $errorMessage = "Cannot deactivate category. It is used by {$ticketCount} ticket(s).";
            $this->addError('toggle', $errorMessage);
            $this->dispatch('error-shown');
            return;
        }

        // Toggle the status
        $newStatus = !$category->active;
        $category->update([
            'active' => $newStatus,
        ]);

        $this->loadCategories();
    }

    /**
     * Delete a category (if not used)
     */
    public function delete(): void
    {
        if (!$this->deleteCategoryId) {
            return;
        }

        $category = TicketCategory::findOrFail($this->deleteCategoryId);
        
        // Check if category is used by any tickets
        $ticketCount = $category->tickets()->count();
        if ($ticketCount > 0) {
            $this->addError('delete', "Cannot delete category. It is used by {$ticketCount} ticket(s).");
            return;
        }

        $category->delete();
        $this->loadCategories();
        $this->closeModals();
        
        $this->dispatch('category-deleted');
    }

    public function render()
    {
        return view('livewire.ticket.ticket-categories');
    }
}

