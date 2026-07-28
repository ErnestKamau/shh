<?php

namespace App\Livewire\Remedies;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\RemedyHeader;
use App\Models\RemedyDetail;
use Illuminate\Validation\Rule;

class RemedyManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    // Remedy Headers Management
    public $editingRemedyHeader = null;
    public $showRemedyHeaderModal = false;
    
    // Remedy Header Form
    public $remedyHeaderForm = [
        'name' => '',
        'description' => '',
    ];

    // Search and Filter
    public $search = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'remedyHeaderForm.name' => 'required|string|max:255',
        'remedyHeaderForm.description' => 'nullable|string',
    ];

    protected $messages = [
        'remedyHeaderForm.name.required' => 'Remedy name is required.',
        'remedyHeaderForm.name.max' => 'Remedy name cannot exceed 255 characters.',
    ];

    public function mount()
    {
        $this->loadRemedyHeaders();
    }

    public function loadRemedyHeaders()
    {
        // This will be handled by the computed property
    }

    public function getRemedyHeadersProperty()
    {
        return RemedyHeader::query()
            ->when($this->search, function ($query) {
                $this->applyCaseInsensitiveSearch($query, ['name', 'description'], (string) $this->search);
            })
            ->withCount('remedyDetails')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function showCreateRemedyHeaderModal()
    {
        $this->resetForm();
        $this->showRemedyHeaderModal = true;
    }

    public function showEditRemedyHeaderModal($remedyHeaderId)
    {
        $remedyHeader = RemedyHeader::findOrFail($remedyHeaderId);
        $this->remedyHeaderForm = [
            'name' => $remedyHeader->name,
            'description' => $remedyHeader->description,
        ];
        $this->editingRemedyHeader = $remedyHeader;
        $this->showRemedyHeaderModal = true;
    }

    public function saveRemedyHeader()
    {
        $this->validate();

        try {
            $data = $this->remedyHeaderForm;

            if ($this->editingRemedyHeader) {
                $this->editingRemedyHeader->update($data);
                $this->message = 'Remedy updated successfully!';
            } else {
                RemedyHeader::create($data);
                $this->message = 'Remedy created successfully!';
            }

            $this->messageType = 'success';
            $this->showRemedyHeaderModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving remedy: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteRemedyHeader($remedyHeaderId)
    {
        try {
            $remedyHeader = RemedyHeader::findOrFail($remedyHeaderId);
            $remedyHeader->delete();
            $this->message = 'Remedy deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error deleting remedy: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function cloneRemedyHeader($remedyHeaderId)
    {
        try {
            $originalRemedy = RemedyHeader::with('remedyDetails')->findOrFail($remedyHeaderId);
            
            // Create cloned remedy header
            $clonedRemedy = RemedyHeader::create([
                'name' => $originalRemedy->name . ' (Copy)',
                'description' => $originalRemedy->description,
            ]);

            // Clone all remedy details
            foreach ($originalRemedy->remedyDetails as $detail) {
                RemedyDetail::create([
                    'remedy_header_id' => $clonedRemedy->id,
                    'antibiotic' => $detail->antibiotic,
                    'sensitivity' => $detail->sensitivity,
                    'dimension' => $detail->dimension,
                    'comments' => $detail->comments,
                ]);
            }

            $this->message = 'Remedy cloned successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error cloning remedy: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function closeRemedyHeaderModal()
    {
        $this->showRemedyHeaderModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->remedyHeaderForm = [
            'name' => '',
            'description' => '',
        ];
        $this->editingRemedyHeader = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.remedies.remedy-manager', [
            'remedyHeaders' => $this->remedyHeaders,
        ])->layout('layouts.lab.app');
    }
}

