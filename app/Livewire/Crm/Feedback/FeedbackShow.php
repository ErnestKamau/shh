<?php

namespace App\Livewire\Crm\Feedback;

use App\Models\CRM\CustomerFeedback;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\Attributes\On;

class FeedbackShow extends BaseCrmComponent
{
    public $feedbackId;
    public $feedback;
    public $activeTab = 'details';
    public $showTabs;

    protected $queryString = [
        'activeTab' => ['except' => 'details', 'as' => 'tab'],
    ];

    public function mount($id)
    {
        $this->initialize();
        $this->checkPermission('CRM.components.Feedbacks.View');

        $this->feedbackId = $id;
        $this->feedback = CustomerFeedback::with([
            'ratings.metric',
            'customer',
            'contact',
            'complaint.resolutions',
            'complaint.feedback',
            'correctiveAction.items.metric',
            'correctiveAction.items.responsibleUser',
            'correctiveAction.assignedUser'
        ])->findOrFail($id);

        $this->showTabs = $this->feedback->complaint && $this->feedback->complaint->exists;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $breadcrumbItems = [
            ['link' => route('customers-list'), 'name' => 'CRM', 'icon' => null],
            ['link' => route('feedback-home'), 'name' => 'Customer Feedback', 'icon' => null],
            ['link' => null, 'name' => 'Feedback Details', 'icon' => 'mdi-file-account']
        ];

        return view('livewire.crm.feedback.feedback-show', [
            'breadcrumbItems' => $breadcrumbItems
        ])->extends('layouts.crm.layout.app', ['dataTable' => false])
            ->section('content2');
    }
}