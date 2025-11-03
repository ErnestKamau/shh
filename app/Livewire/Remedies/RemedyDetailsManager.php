<?php

namespace App\Livewire\Remedies;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RemedyHeader;
use App\Models\RemedyDetail;
use Illuminate\Validation\Rule;

class RemedyDetailsManager extends Component
{
    use WithPagination;

    // Remedy Header
    public $remedyHeaderId;
    public $remedyHeader;

    // Remedy Details Management
    public $editingRemedyDetail = null;
    public $showRemedyDetailModal = false;
    
    // Remedy Detail Form
    public $remedyDetailForm = [
        'antibiotic' => '',
        'sensitivity' => 'Sensitive',
        'dimension' => '',
        'comments' => '',
    ];

    // Search and Filter
    public $search = '';
    public $sensitivityFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'remedyDetailForm.antibiotic' => 'required|string|max:255',
        'remedyDetailForm.sensitivity' => 'required|in:Sensitive,Resistant,Intermediate',
        'remedyDetailForm.dimension' => 'nullable|string|max:255',
        'remedyDetailForm.comments' => 'nullable|string',
    ];

    protected $messages = [
        'remedyDetailForm.antibiotic.required' => 'Antibiotic name is required.',
        'remedyDetailForm.antibiotic.max' => 'Antibiotic name cannot exceed 255 characters.',
        'remedyDetailForm.sensitivity.required' => 'Sensitivity is required.',
        'remedyDetailForm.sensitivity.in' => 'Sensitivity must be Sensitive, Resistant, or Intermediate.',
        'remedyDetailForm.dimension.max' => 'Dimension cannot exceed 255 characters.',
    ];

    public function mount($remedyHeaderId)
    {
        $this->remedyHeaderId = $remedyHeaderId;
        $this->remedyHeader = RemedyHeader::findOrFail($remedyHeaderId);
    }

    public function getRemedyDetailsProperty()
    {
        return RemedyDetail::query()
            ->where('remedy_header_id', $this->remedyHeaderId)
            ->when($this->search, function ($query) {
                $query->where('antibiotic', 'like', '%' . $this->search . '%')
                      ->orWhere('comments', 'like', '%' . $this->search . '%');
            })
            ->when($this->sensitivityFilter, function ($query) {
                $query->where('sensitivity', $this->sensitivityFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function showCreateRemedyDetailModal()
    {
        $this->resetForm();
        $this->showRemedyDetailModal = true;
    }

    public function showEditRemedyDetailModal($remedyDetailId)
    {
        $remedyDetail = RemedyDetail::findOrFail($remedyDetailId);
        $this->remedyDetailForm = [
            'antibiotic' => $remedyDetail->antibiotic,
            'sensitivity' => $remedyDetail->sensitivity,
            'dimension' => $remedyDetail->dimension,
            'comments' => $remedyDetail->comments,
        ];
        $this->editingRemedyDetail = $remedyDetail;
        $this->showRemedyDetailModal = true;
    }

    public function saveRemedyDetail()
    {
        $this->validate();

        try {
            $data = array_merge($this->remedyDetailForm, [
                'remedy_header_id' => $this->remedyHeaderId
            ]);

            if ($this->editingRemedyDetail) {
                $this->editingRemedyDetail->update($data);
                $this->message = 'Remedy detail updated successfully!';
            } else {
                RemedyDetail::create($data);
                $this->message = 'Remedy detail created successfully!';
            }

            $this->messageType = 'success';
            $this->showRemedyDetailModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving remedy detail: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteRemedyDetail($remedyDetailId)
    {
        try {
            RemedyDetail::findOrFail($remedyDetailId)->delete();
            $this->message = 'Remedy detail deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error deleting remedy detail: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function closeRemedyDetailModal()
    {
        $this->showRemedyDetailModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->remedyDetailForm = [
            'antibiotic' => '',
            'sensitivity' => 'Sensitive',
            'dimension' => '',
            'comments' => '',
        ];
        $this->editingRemedyDetail = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->sensitivityFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.remedies.remedy-details-manager', [
            'remedyDetails' => $this->remedyDetails,
        ])->layout('layouts.lab.app');
    }
}

