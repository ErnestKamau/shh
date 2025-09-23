<?php

namespace App\Livewire\Ratings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RatingHeader;
use App\Models\RatingDetail;
use Illuminate\Validation\Rule;

class RatingManager extends Component
{
    use WithPagination;

    // Rating Headers Management
    public $editingRatingHeader = null;
    public $showRatingHeaderModal = false;
    
    // Rating Header Form
    public $ratingHeaderForm = [
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
        'ratingHeaderForm.name' => 'required|string|max:255',
        'ratingHeaderForm.description' => 'nullable|string',
    ];

    protected $messages = [
        'ratingHeaderForm.name.required' => 'Rating header name is required.',
        'ratingHeaderForm.name.max' => 'Rating header name cannot exceed 255 characters.',
    ];

    public function mount()
    {
        $this->loadRatingHeaders();
    }

    public function loadRatingHeaders()
    {
        // This will be handled by the computed property
    }

    public function getRatingHeadersProperty()
    {
        return RatingHeader::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->withCount('ratingDetails')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function showCreateRatingHeaderModal()
    {
        $this->resetForm();
        $this->showRatingHeaderModal = true;
    }

    public function showEditRatingHeaderModal($ratingHeaderId)
    {
        $ratingHeader = RatingHeader::findOrFail($ratingHeaderId);
        
        $this->editingRatingHeader = $ratingHeaderId;
        $this->ratingHeaderForm = [
            'name' => $ratingHeader->name,
            'description' => $ratingHeader->description,
        ];
        $this->showRatingHeaderModal = true;
    }

    public function saveRatingHeader()
    {
        $this->validate();

        try {
            $this->loading = true;

            if ($this->editingRatingHeader) {
                $ratingHeader = RatingHeader::findOrFail($this->editingRatingHeader);
                $ratingHeader->update($this->ratingHeaderForm);
                $this->message = 'Rating header updated successfully!';
            } else {
                RatingHeader::create($this->ratingHeaderForm);
                $this->message = 'Rating header created successfully!';
            }

            $this->messageType = 'success';
            $this->resetForm();
            $this->showRatingHeaderModal = false;

        } catch (\Exception $e) {
            $this->message = 'An error occurred: ' . $e->getMessage();
            $this->messageType = 'error';
        } finally {
            $this->loading = false;
        }
    }

    public function deleteRatingHeader($ratingHeaderId)
    {
        try {
            $ratingHeader = RatingHeader::findOrFail($ratingHeaderId);
            $ratingHeader->delete();
            
            $this->message = 'Rating header deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'An error occurred: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function resetForm()
    {
        $this->editingRatingHeader = null;
        $this->ratingHeaderForm = [
            'name' => '',
            'description' => '',
        ];
        $this->resetErrorBag();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.ratings.rating-manager', [
            'ratingHeaders' => $this->ratingHeaders,
        ])->layout('livewire.layout.lab-app', [
            'componentType' => 'ratings',
            'pageTitle' => 'Rating Hub - Rating Headers Management'
        ]);
    }
}