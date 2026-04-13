<?php

namespace App\Livewire\Crm\Feedback;

use App\Models\CRM\EvaluationMetric;
use Livewire\Component;
use Livewire\WithPagination;

class EvaluationMetricManager extends Component
{
    use WithPagination;

    public $name;
    public $prompt_text;
    public $max_rating = 4;
    public $display_order = 0;
    public $is_active = true;
    public $rating_labels = [];
    public $editingMetricId = null;
    public $showModal = false;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|string|max:255',
        'prompt_text' => 'nullable|string',
        'max_rating' => 'required|integer|min:1|max:10',
        'display_order' => 'required|integer',
        'is_active' => 'boolean',
        'rating_labels.*' => 'required|string|max:255',
    ];

    public function render()
    {
        return view('livewire.crm.feedback.evaluation-metric-manager', [
            'metrics' => EvaluationMetric::orderBy('display_order')->paginate(10)
        ])->extends('layouts.crm.layout.app')->section('content2');
    }

    public function create()
    {
        $this->resetFields();
        $this->applyDefaultLabels();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $metric = EvaluationMetric::findOrFail($id);
        $this->editingMetricId = $id;
        $this->name = $metric->name;
        $this->prompt_text = $metric->prompt_text;
        $this->max_rating = $metric->max_rating;
        $this->display_order = $metric->display_order;
        $this->is_active = $metric->is_active;
        $this->rating_labels = $metric->rating_labels ?? [];
        
        // Ensure all slots have a value
        $this->applyDefaultLabels();
        
        $this->showModal = true;
    }

    public function updatedMaxRating($value)
    {
        // Allow empty when user is clearing to type a new number (don't force back to 1)
        if ($value === '' || $value === null) {
            $this->rating_labels = [];
            return;
        }
        $this->max_rating = max(1, min(10, (int) $value));
        $this->applyDefaultLabels();
    }

    public function applyDefaultLabels()
    {
        $defaults = [
            1 => 'Poor',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Excellent',
            5 => 'Outstanding',
            6 => 'Very Excellent',
            7 => 'Outstanding',
            8 => 'Exceptional',
            9 => 'Exemplary',
            10 => 'Perfect',
        ];

        $max = (int) $this->max_rating;
        $max = max(1, min(10, $max));
        $newLabels = [];
        for ($i = 1; $i <= $max; $i++) {
            $newLabels[$i] = ($this->rating_labels[$i] ?? null) ?: ($defaults[$i] ?? (string) $i);
        }
        $this->rating_labels = $newLabels;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'prompt_text' => $this->prompt_text,
            'max_rating' => $this->max_rating,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'rating_labels' => $this->rating_labels,
        ];

        if ($this->editingMetricId) {
            EvaluationMetric::find($this->editingMetricId)->update($data);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Metric updated successfully.']);
        } else {
            EvaluationMetric::create($data);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Metric created successfully.']);
        }

        $this->resetFields();
        $this->showModal = false;
    }

    public function toggleStatus($id)
    {
        $metric = EvaluationMetric::findOrFail($id);
        $metric->update(['is_active' => !$metric->is_active]);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Status updated.']);
    }

    public function deleteMetric($id)
    {
        try {
            $metric = EvaluationMetric::findOrFail($id);
            $metric->delete();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Metric deleted successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot delete metric. It is likely tied to existing feedback records. Consider changing its status to Inactive instead.']);
        }
    }

    public function resetFields()
    {
        $this->name = '';
        $this->prompt_text = '';
        $this->max_rating = 4;
        $this->rating_labels = [];
        $this->display_order = EvaluationMetric::max('display_order') + 1;
        $this->is_active = true;
        $this->editingMetricId = null;
    }
}
