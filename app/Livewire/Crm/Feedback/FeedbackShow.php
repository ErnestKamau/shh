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
        $this->authorizeFeedbackView();

        $this->feedbackId = $id;
        $feedback = CustomerFeedback::findOrFail($id);

        if (empty($feedback->code) || !preg_match('/^FB-\d{4}-\d+$/', $feedback->code)) {
            $feedback->code = CustomerFeedback::generateUniqueCode();
            $feedback->save();
        }

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

    protected function authorizeFeedbackView(): void
    {
        if (! $this->hasPermission('crm.components.feedbacks.view')) {
            $this->checkPermission('crm.feedback.view');
        }
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