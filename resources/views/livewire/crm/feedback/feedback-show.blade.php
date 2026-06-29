<div>
    <main>
        @php
            $breadcrumbItems = [
                ['link' => route('customers-list'), 'name' => 'CRM', 'icon' => null],
                ['link' => route('feedback-home'), 'name' => 'Customer Feedback', 'icon' => null],
                ['link' => null, 'name' => 'Feedback Details', 'icon' => 'mdi-file-account']
            ];
        @endphp
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$breadcrumbItems"
                title="Feedback Details"
                subtitle="View detailed feedback information"
                icon="mdi-file-account"
            >
            </x-crm.page-header>

            <div class="crm-card mx-4 mb-4">
                <div class="crm-card-body">
                    @if($showTabs)
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="feedbackTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" id="details-tab" data-toggle="tab" type="button" role="tab" wire:click="setActiveTab('details')" aria-controls="details" aria-selected="{{ $activeTab === 'details' ? 'true' : 'false' }}">
                                    Feedback Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'issues' ? 'active' : '' }}" id="issues-tab" data-toggle="tab" type="button" role="tab" wire:click="setActiveTab('issues')" aria-controls="issues" aria-selected="{{ $activeTab === 'issues' ? 'true' : 'false' }}">
                                    Issue Raised
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content mt-3" id="feedbackTabsContent">
                            <div class="tab-pane fade {{ $activeTab === 'details' ? 'show active' : '' }}" id="details" role="tabpanel" aria-labelledby="details-tab">
                                @include('livewire.crm.feedback.partials.feedback-detail-content', ['feedback' => $feedback])
                            </div>
                            <div class="tab-pane fade {{ $activeTab === 'issues' ? 'show active' : '' }}" id="issues" role="tabpanel" aria-labelledby="issues-tab">
                                @include('livewire.crm.feedback.partials.feedback-issue-raised-content', ['feedback' => $feedback])
                            </div>
                        </div>
                    @else
                        @include('livewire.crm.feedback.partials.feedback-detail-content', ['feedback' => $feedback])
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>