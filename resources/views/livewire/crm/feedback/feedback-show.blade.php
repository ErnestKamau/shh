@push('styles')
<style>
    .feedback-show-section {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    .feedback-show-section .card-premium {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        transition: all 0.2s ease-in-out;
    }
    
    .feedback-show-section .card-premium:hover {
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    }
    
    .feedback-show-section .section-title-premium {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 8px;
    }
    
    .feedback-show-section .section-title-premium i {
        font-size: 0.9rem;
        margin-right: 6px;
    }
    
    .feedback-show-section .meta-item {
        margin-bottom: 16px;
    }
    
    .feedback-show-section .meta-item:last-child {
        margin-bottom: 0;
    }
    
    .feedback-show-section .meta-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    
    .feedback-show-section .meta-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .feedback-show-section .meta-value a {
        color: #3b82f6;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    
    .feedback-show-section .meta-value a:hover {
        color: #2563eb;
        text-decoration: underline;
    }
    
    .feedback-show-section .meta-value-mono {
        font-family: monospace;
        font-size: 0.95rem;
        letter-spacing: -0.01em;
    }
    
    .feedback-show-section .nav-tabs-premium {
        border-bottom: 2px solid #f1f5f9;
        margin-bottom: 24px;
        gap: 8px;
        padding-left: 0;
        list-style: none;
        display: flex;
    }
    
    .feedback-show-section .nav-tabs-premium .nav-item {
        margin-bottom: -2px;
    }
    
    .feedback-show-section .nav-tabs-premium .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 12px 20px;
        position: relative;
        background: transparent;
        transition: all 0.2s ease;
        border-radius: 6px 6px 0 0;
    }
    
    .feedback-show-section .nav-tabs-premium .nav-link:hover {
        color: #1e293b;
        background-color: #f8fafc;
        text-decoration: none;
    }
    
    .feedback-show-section .nav-tabs-premium .nav-link.active {
        color: #3b82f6;
        background-color: transparent;
    }
    
    .feedback-show-section .nav-tabs-premium .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #3b82f6;
        border-radius: 99px;
    }
    
    .feedback-show-section .badge-premium-submitted {
        background-color: #f0fdf4;
        color: #15803d;
        font-weight: 700;
        border: 1px solid #bbf7d0;
        font-size: 0.72rem;
        padding: 4px 10px;
        border-radius: 99px;
        display: inline-block;
    }
    
    .feedback-show-section .badge-premium-pending {
        background-color: #fffbeb;
        color: #b45309;
        font-weight: 700;
        border: 1px solid #fef3c7;
        font-size: 0.72rem;
        padding: 4px 10px;
        border-radius: 99px;
        display: inline-block;
    }
    
    .feedback-show-section .tracker-line {
        position: relative;
        padding-left: 28px;
        margin-bottom: 24px;
    }
    
    .feedback-show-section .tracker-line::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 16px;
        bottom: -32px;
        width: 2px;
        background-color: #e2e8f0;
    }
    
    .feedback-show-section .tracker-line:last-child {
        margin-bottom: 0;
    }
    
    .feedback-show-section .tracker-line:last-child::before {
        display: none;
    }
    
    .feedback-show-section .tracker-dot {
        position: absolute;
        left: 4px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 99px;
        background-color: #cbd5e1;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 4px #f1f5f9;
        z-index: 2;
    }
    
    .feedback-show-section .tracker-dot.active {
        background-color: #10b981;
        box-shadow: 0 0 0 4px #d1fae5;
    }
    
    .feedback-show-section .tracker-dot.pending {
        background-color: #f59e0b;
        box-shadow: 0 0 0 4px #fef3c7;
    }
</style>
@endpush

<div class="feedback-show-section">
    <main>
        @php
            $breadcrumbItems = [
                ['link' => route('customers-list'), 'name' => 'CRM', 'icon' => null],
                ['link' => route('feedback-home'), 'name' => 'Customer Feedback', 'icon' => null],
                ['link' => null, 'name' => 'Feedback Details', 'icon' => 'mdi-file-account']
            ];
            
            $status = $feedback->status;
            $isSubmitted = ($status == \App\Models\CRM\CustomerFeedback::STATUS_SUBMITTED);
            
            $safeFormat = function($date) {
                if (empty($date)) {
                    return '—';
                }
                try {
                    if (is_object($date) && method_exists($date, 'format')) {
                        return $date->format('d M Y, H:i');
                    }
                    return \Carbon\Carbon::parse($date)->format('d M Y, H:i');
                } catch (\Exception $e) {
                    return '—';
                }
            };
        @endphp
        
        <div class="container-fluid">
            <!-- Header section -->
            <x-crm.page-header
                :breadcrumbItems="$breadcrumbItems"
                title="Feedback Details"
                subtitle="View detailed, structured customer feedback responses"
                icon="mdi-file-account"
            >
                <x-slot:actions>
                    <a href="{{ route('feedback-home') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px; padding: 6px 14px; font-weight: 500;">
                        <i class="mdi mdi-arrow-left mr-1"></i> Back to List
                    </a>
                </x-slot:actions>
            </x-crm.page-header>
            
            <div class="row px-4">
                <!-- Left Sidebar: Customer details / campaign metadata -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-premium shadow-sm">
                        <div class="card-body p-4">
                            <!-- Feedback Identity -->
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                <div>
                                    <span class="meta-label">Feedback Code</span>
                                    <h4 class="meta-value meta-value-mono mb-0 text-primary font-weight-bold">
                                        {{ $feedback->code ?? '—' }}
                                    </h4>
                                </div>
                                <div>
                                    @if($isSubmitted)
                                        <span class="badge-premium-submitted"><i class="mdi mdi-check-circle-outline mr-1"></i>Submitted</span>
                                    @else
                                        <span class="badge-premium-pending"><i class="mdi mdi-clock-outline mr-1"></i>Pending</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Customer Metadata Section -->
                            <div class="mb-4">
                                <span class="section-title-premium"><i class="mdi mdi-domain"></i> Customer / Client</span>
                                
                                <div class="meta-item">
                                    <div class="meta-label">Client Name</div>
                                    <div class="meta-value">
                                        @if($feedback->customer)
                                            <a href="{{ route('show-customer', ['id' => $feedback->customer_id]) }}" title="View customer profile">
                                                {{ $feedback->customer->name }} <i class="mdi mdi-open-in-new small ml-1"></i>
                                            </a>
                                        @else
                                            {{ $feedback->received_from ?? '—' }}
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="meta-item">
                                    <div class="meta-label">Contact Person</div>
                                    <div class="meta-value">
                                        {{ $feedback->contact->name ?? $feedback->received_from ?? '—' }}
                                    </div>
                                </div>
                                
                                @if(!empty($feedback->contact_position ?? $feedback->contact->position ?? ''))
                                    <div class="meta-item">
                                        <div class="meta-label">Position / Title</div>
                                        <div class="meta-value">
                                            {{ $feedback->contact_position ?? $feedback->contact->position }}
                                        </div>
                                    </div>
                                @endif
                                
                                @if(!empty($feedback->contact->email ?? ''))
                                    <div class="meta-item">
                                        <div class="meta-label">Email Address</div>
                                        <div class="meta-value">
                                            <a href="mailto:{{ $feedback->contact->email }}"><i class="mdi mdi-email-outline mr-1"></i>{{ $feedback->contact->email }}</a>
                                        </div>
                                    </div>
                                @endif
                                
                                @if(!empty($feedback->contact_phone ?? $feedback->contact->phone ?? ''))
                                    <div class="meta-item">
                                        <div class="meta-label">Phone Number</div>
                                        <div class="meta-value">
                                            <i class="mdi mdi-phone-outline mr-1 text-muted"></i>{{ $feedback->contact_phone ?? $feedback->contact->phone }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Tracking & Deliverability Section -->
                            <div class="pt-4 border-top mt-4">
                                <span class="section-title-premium mb-3 d-block"><i class="mdi mdi-ray-start-arrow text-primary"></i> Status &amp; Campaign Tracker</span>
                                
                                <div class="tracker-line">
                                    <div class="tracker-dot active"></div>
                                    <div class="meta-label" style="font-size: 0.65rem;">Campaign Created</div>
                                    <div class="meta-value text-dark" style="font-size: 0.8rem; font-weight: 600; line-height: 1.4;">
                                        {{ $safeFormat($feedback->created_at) }}
                                        <br>
                                        <small class="text-muted" style="font-weight: 500;">By: {{ $feedback->registered_by ?? 'System Generated' }}</small>
                                    </div>
                                </div>
                                
                                @if(!empty($feedback->last_reminded_at))
                                    <div class="tracker-line">
                                        <div class="tracker-dot active"></div>
                                        <div class="meta-label" style="font-size: 0.65rem;">Last Reminded</div>
                                        <div class="meta-value text-dark" style="font-size: 0.8rem; font-weight: 600; line-height: 1.4;">
                                            {{ $safeFormat($feedback->last_reminded_at) }}
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="tracker-line">
                                    @if($isSubmitted)
                                        <div class="tracker-dot active"></div>
                                        <div class="meta-label" style="font-size: 0.65rem;">Feedback Submitted</div>
                                        <div class="meta-value text-dark" style="font-size: 0.8rem; font-weight: 600; line-height: 1.4;">
                                            {{ $feedback->submitted_at ? $safeFormat($feedback->submitted_at) : $safeFormat($feedback->updated_at) }}
                                            <br>
                                            <small class="text-muted" style="font-weight: 500;">Type: {{ $feedback->user_type ?? 'External Client' }}</small>
                                        </div>
                                    @else
                                        <div class="tracker-dot pending"></div>
                                        <div class="meta-label" style="font-size: 0.65rem;">Awaiting Submission</div>
                                        <div class="meta-value text-muted" style="font-size: 0.8rem; font-weight: 500;">
                                            Pending Client Response
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Survey details, scorecards & results -->
                <div class="col-lg-8 mb-4">
                    <div class="card card-premium shadow-sm">
                        <div class="card-body p-4">
                            @if($showTabs)
                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs-premium" id="feedbackTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" id="details-tab" data-toggle="tab" type="button" role="tab" wire:click="setActiveTab('details')" aria-controls="details" aria-selected="{{ $activeTab === 'details' ? 'true' : 'false' }}">
                                            <i class="mdi mdi-ballot-outline mr-1"></i> Feedback Details
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $activeTab === 'issues' ? 'active' : '' }}" id="issues-tab" data-toggle="tab" type="button" role="tab" wire:click="setActiveTab('issues')" aria-controls="issues" aria-selected="{{ $activeTab === 'issues' ? 'true' : 'false' }}">
                                            <i class="mdi mdi-alert-circle-outline mr-1"></i> Issue Raised
                                        </button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="feedbackTabsContent">
                                    <div class="tab-pane fade {{ $activeTab === 'details' ? 'show active' : '' }}" id="details" role="tabpanel" aria-labelledby="details-tab" style="padding: 0 !important;">
                                        @include('livewire.crm.feedback.partials.feedback-detail-content', ['feedback' => $feedback])
                                    </div>
                                    <div class="tab-pane fade {{ $activeTab === 'issues' ? 'show active' : '' }}" id="issues" role="tabpanel" aria-labelledby="issues-tab" style="padding: 0 !important;">
                                        @include('livewire.crm.feedback.partials.feedback-issue-raised-content', ['feedback' => $feedback])
                                    </div>
                                </div>
                            @else
                                @include('livewire.crm.feedback.partials.feedback-detail-content', ['feedback' => $feedback])
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>