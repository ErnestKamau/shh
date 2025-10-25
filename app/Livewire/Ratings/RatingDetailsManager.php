<?php

namespace App\Livewire\Ratings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RatingHeader;
use App\Models\RatingDetail;
use Illuminate\Validation\Rule;

class RatingDetailsManager extends Component
{
    use WithPagination;

    // Rating Header
    public $ratingHeaderId;
    public $ratingHeader;

    // Rating Details Management
    public $editingRatingDetail = null;
    public $showRatingDetailModal = false;
    
    // Rating Detail Form
    public $ratingDetailForm = [
        'key' => '',
        'label' => '',
        'interpretation' => '',
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
        'ratingDetailForm.key' => 'required|string|max:50',
        'ratingDetailForm.label' => 'required|string|max:255',
        'ratingDetailForm.interpretation' => 'required|string|max:1000',
    ];

    protected $messages = [
        'ratingDetailForm.key.required' => 'Rating key is required.',
        'ratingDetailForm.key.max' => 'Rating key cannot exceed 50 characters.',
        'ratingDetailForm.label.required' => 'Rating label is required.',
        'ratingDetailForm.label.max' => 'Rating label cannot exceed 255 characters.',
        'ratingDetailForm.interpretation.required' => 'Rating interpretation is required.',
        'ratingDetailForm.interpretation.max' => 'Rating interpretation cannot exceed 1000 characters.',
    ];

    public function mount($ratingHeaderId)
    {
        $this->ratingHeaderId = $ratingHeaderId;
        $this->ratingHeader = RatingHeader::findOrFail($ratingHeaderId);
    }

    public function getRatingDetailsProperty()
    {
        return RatingDetail::query()
            ->where('rating_header_id', $this->ratingHeaderId)
            ->when($this->search, function ($query) {
                $query->where('key', 'like', '%' . $this->search . '%')
                      ->orWhere('label', 'like', '%' . $this->search . '%')
                      ->orWhere('interpretation', 'like', '%' . $this->search . '%');
            })
            ->orderBy('key')
            ->paginate($this->perPage);
    }

    public function showCreateRatingDetailModal()
    {
        $this->resetForm();
        $this->showRatingDetailModal = true;
    }

    public function showEditRatingDetailModal($ratingDetailId)
    {
        $ratingDetail = RatingDetail::findOrFail($ratingDetailId);
        
        $this->editingRatingDetail = $ratingDetailId;
        $this->ratingDetailForm = [
            'key' => $ratingDetail->key,
            'label' => $ratingDetail->label,
            'interpretation' => $ratingDetail->interpretation,
        ];
        $this->showRatingDetailModal = true;
    }

    public function saveRatingDetail()
    {
        $this->validate();

        try {
            $this->loading = true;

            if ($this->editingRatingDetail) {
                $ratingDetail = RatingDetail::findOrFail($this->editingRatingDetail);
                $ratingDetail->update($this->ratingDetailForm);
                $this->message = 'Rating detail updated successfully!';
            } else {
                RatingDetail::create([
                    'rating_header_id' => $this->ratingHeaderId,
                    'key' => $this->ratingDetailForm['key'],
                    'label' => $this->ratingDetailForm['label'],
                    'interpretation' => $this->ratingDetailForm['interpretation'],
                ]);
                $this->message = 'Rating detail created successfully!';
            }

            $this->messageType = 'success';
            $this->resetForm();
            $this->showRatingDetailModal = false;

        } catch (\Exception $e) {
            $this->message = 'An error occurred: ' . $e->getMessage();
            $this->messageType = 'error';
        } finally {
            $this->loading = false;
        }
    }

    public function deleteRatingDetail($ratingDetailId)
    {
        try {
            $ratingDetail = RatingDetail::findOrFail($ratingDetailId);
            $ratingDetail->delete();
            
            $this->message = 'Rating detail deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'An error occurred: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function resetForm()
    {
        $this->editingRatingDetail = null;
        $this->ratingDetailForm = [
            'key' => '',
            'label' => '',
            'interpretation' => '',
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

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function closeRatingDetailModal()
    {
        $this->showRatingDetailModal = false;
        $this->resetForm();
        $this->dispatch('rating-detail-modal-closed');
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.ratings.rating-details-manager', [
            'ratingDetails' => $this->ratingDetails,
        ])->layout('livewire.layout.lab-app', [
            'componentType' => 'rating-details',
            'pageTitle' => 'Rating Hub - ' . $this->ratingHeader->name . ' Details',
            'ratingHeader' => $this->ratingHeader
        ]);
    }
}