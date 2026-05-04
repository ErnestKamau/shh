<div class="customer-show-section">

    @section('title2')
        <title>{{ $customer->name }} | {{ __('crm.client_registry') }} | {{ __('crm.module_name') }}</title>
    @endsection

    <main>
        <x-bread-crumb :items="$this->breadcrumbItems"></x-bread-crumb>

        {{-- Client Identity Card --}}
        <div class="px-4 pb-3 pt-1">
            <div class="d-flex align-items-center">
                {{-- Avatar Initial --}}
                <div class="mr-3 d-flex align-items-center justify-content-center rounded"
                    style="width:48px;height:48px;background:#6366f1;flex-shrink:0;">
                    <span class="text-white font-weight-bold"
                        style="font-size:1.3rem;line-height:1;">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                </div>
                {{-- Name Block --}}
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap">
                        <h5 class="font-weight-bold text-dark mb-0 mr-2">{{ $customer->name }}</h5>
                        @if($customer->active == '1')
                            <span class="crm-badge crm-badge-success mr-1">{{ __('crm.active_account') }}</span>
                        @else
                            <span class="crm-badge crm-badge-neutral mr-1">{{ __('crm.inactive') }}</span>
                        @endif
                        <span class="badge border text-muted" style="font-size:0.68rem;padding:3px 8px;">{{ __('crm.crm_client') }}</span>
                    </div>
                    <small class="text-muted" style="font-size:0.72rem;">
                        @if($customer->email)<i class="mdi mdi-email-outline mr-1"></i>{{ $customer->email }}@endif
                        @if($customer->code)<span class="mx-2 text-muted">·</span><i
                        class="mdi mdi-identifier mr-1"></i>{{ $customer->code }}@endif
                    </small>
                </div>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-sm-12 p-2">

                <div class="crm-card mb-4">
                    <div class="crm-content-tabs pt-2">
                        <ul class="crm-tab-nav" id="Elements-tabs" role="tablist">
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'company-sections' ? 'active' : '' }}"
                                    wire:click="switchTab('company-sections')" href="#Company-Sections" role="tab">
                                    <i class="mdi mdi-folder-outline"></i> {{ __('crm.company_section') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'company-units' ? 'active' : '' }}"
                                    wire:click="switchTab('company-units')" href="#Company-Units" role="tab">
                                    <i class="mdi mdi-sitemap"></i>
                                    {{ trim($customer->unit_configurable_name) != "" ? $customer->unit_configurable_name : __('crm.company_units') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'sample-points' ? 'active' : '' }}"
                                    wire:click="switchTab('sample-points')" href="#Sample-Points" role="tab">
                                    <i class="mdi mdi-map-marker"></i>
                                    {{ trim($customer->sample_point_configurable_name) != "" ? $customer->sample_point_configurable_name : __('crm.sample_points') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'contacts' ? 'active' : '' }}"
                                    wire:click="switchTab('contacts')" href="#Contacts" role="tab">
                                    <i class="mdi mdi-account-box-outline"></i> {{ __('crm.contacts') }}
                                </a>
                            </li>
                            @if(isset($isQplus->id))
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'orders' ? 'active' : '' }}"
                                    wire:click="switchTab('orders')" href="#Orders" role="tab">
                                    <i class="mdi mdi-eyedropper-plus"></i> {{ __('crm.orders') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'reports' ? 'active' : '' }}"
                                    wire:click="switchTab('reports')" href="#Samples" role="tab">
                                    <i class="mdi mdi-test-tube"></i> {{ __('crm.reports') }}
                                </a>
                            </li>
                            @endif
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'complaints' ? 'active' : '' }}"
                                    wire:click="switchTab('complaints')" href="#Complaints" role="tab">
                                    <i class="mdi mdi-comment-alert"></i> {{ __('crm.complaints') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'feedbacks' ? 'active' : '' }}"
                                    wire:click="switchTab('feedbacks')" href="#Feedbacks" role="tab">
                                    <i class="mdi mdi-file-account"></i> {{ __('crm.customer_feedback') }}
                                </a>
                            </li>
                            @if(isset($isQplus->id))
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'quotations' ? 'active' : '' }}"
                                    wire:click="switchTab('quotations')" href="#quotations" role="tab">
                                    <i class="mdi mdi-file-settings"></i> {{ __('crm.quotation') }}
                                </a>
                            </li>
                            @endif
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'attachments' ? 'active' : '' }}"
                                    wire:click="switchTab('attachments')" href="#Certification" role="tab">
                                    <i class="mdi mdi-file-certificate"></i> {{ __('crm.attachments') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab == 'details' ? 'active' : '' }}"
                                    wire:click="switchTab('details')" href="#Customer-Details" role="tab">
                                    <i class="mdi mdi-information-outline"></i> {{ __('crm.details') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="Samples-tabs-content">
                        @if($activeTab == 'company-units')
                            <div class="tab-pane fade show active p-3" id="Company-Units" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerUnitsTab::class, ['customer' => $customer], key('tab-company-units'))
                            </div>
                        @elseif($activeTab == 'sample-points')
                            <div class="tab-pane fade show active p-3" id="Sample-Points" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerSamplePointsTab::class, ['customer' => $customer], key('tab-sample-points'))
                            </div>
                        @elseif($activeTab == 'contacts')
                            <div class="tab-pane fade show active p-3" id="Contacts" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerContactsTab::class, ['customer' => $customer], key('tab-contacts'))
                            </div>
                        @elseif(isset($isQplus->id) && $activeTab == 'orders')
                            <div class="tab-pane fade show active p-3" id="Orders" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerOrdersTab::class, ['customer' => $customer], key('tab-orders'))
                            </div>
                        @elseif(isset($isQplus->id) && $activeTab == 'reports')
                            <div class="tab-pane fade show active p-3" id="Samples" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerReportsTab::class, ['customer' => $customer], key('tab-reports'))
                            </div>
                        @elseif($activeTab == 'complaints')
                            <div class="tab-pane fade show active p-3" id="Complaints" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerComplaintsTab::class, ['customer' => $customer], key('tab-complaints'))
                            </div>
                        @elseif($activeTab == 'feedbacks')
                            <div class="tab-pane fade show active p-3" id="Feedbacks" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerFeedbacksTab::class, ['customer' => $customer], key('tab-feedbacks'))
                            </div>
                        @elseif(isset($isQplus->id) && $activeTab == 'quotations')
                            <div class="tab-pane fade show active p-3" id="quotations" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\QuotationsList::class, ['customer' => $customer], key('tab-quotations'))
                            </div>
                        @elseif($activeTab == 'attachments')
                            <div class="tab-pane fade show active p-3" id="Certification" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\AttachmentsManager::class, ['customer' => $customer], key('tab-attachments'))
                            </div>
                        {{-- Configurations tab disabled
                        @elseif($activeTab == 'configurations')
                            <div class="tab-pane fade show active p-3" id="Configurations" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\ConfigurationsManager::class, ['customer' => $customer], key('tab-configurations'))
                            </div>
                        --}}
                        @elseif($activeTab == 'details')
                            <div class="tab-pane fade show active p-3" id="Customer-Details" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerDetailsTab::class, ['customer' => $customer], 'details-' . $customer->id)
                            </div>
                        @else
                            {{-- Fallback when tab is Qplus-only but isQplus not set (e.g. bookmarked URL) --}}
                            <div class="tab-pane fade show active p-3" id="Customer-Details" role="tabpanel">
                                @livewire(\App\Livewire\Crm\Customer\Tabs\CustomerDetailsTab::class, ['customer' => $customer], 'details-' . $customer->id)
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>