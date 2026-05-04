	<?php
	$items = array(
		array(
			'link' => route('customers-list'),
			'name' => __('crm.module_name'),
			'icon' => null
		),
		array(
			'link' => route('customers-list'),
			'name' => __('crm.customer_list'),
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => $customer->name,
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h2 class="p-4 imara-section-title">
		<i class="mdi mdi-microscope"></i> {{ $customer->name }} <small class="text-muted"> | {{ __('crm.module_name') }}</small>
	</h2>
	<div class="row no-gutters">
		<div class="col-sm-12 p-2">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="crm-tab-nav" id="Elements-tabs" role="tablist">
						<li class="crm-tab-item">
							<a class="crm-tab-link active" id="Company-Sections-tab" data-toggle="tab" href="#Company-Sections" role="tab" aria-controls="Company-Sections" aria-selected="true"><i class="mdi mdi-folder-outline"></i> {{ __('crm.company_section') }}</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Company-Units-tab" data-toggle="tab" href="#Company-Units" role="tab" aria-controls="Company-Units" aria-selected="false"><i class="mdi mdi-sitemap"></i> {{ trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : __('crm.company_units') }}</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Sample-Points-tab" data-toggle="tab" href="#Sample-Points" role="tab" aria-controls="Sample-Points" aria-selected="false"><i class="mdi mdi-map-marker"></i> {{ trim($customer->sample_point_configurable_name)!="" ? $customer->sample_point_configurable_name : __('crm.sample_points') }}</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Contacts-tab" data-toggle="tab" href="#Contacts" role="tab" aria-controls="Contacts" aria-selected="false"><i class="mdi mdi-account-box-outline"></i> {{ __('crm.contacts') }}</a>
						</li>
						@if(isset($is_qplus->id))
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Orders-tab" data-toggle="tab" href="#Orders" role="tab" aria-controls="Orders" aria-selected="false"><i class="mdi mdi-eyedropper-plus"></i> {{ __('crm.orders') }}</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Samples-tab" data-toggle="tab" href="#Samples" role="tab" aria-controls="Samples" aria-selected="false"><i class="mdi mdi-test-tube"></i> {{ __('crm.reports') }}</a>
						</li>
						@endif
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Complaints-tab" data-toggle="tab" href="#Complaints" role="tab" aria-controls="Complaints" aria-selected="false"><i class="mdi mdi-comment-alert"></i> {{ __('crm.complaints') }}</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Feedbacks-tab" data-toggle="tab" href="#Feedbacks" role="tab" aria-controls="Feedbacks" aria-selected="false"><i class="mdi mdi-file-account"></i> {{ __('crm.customer_feedback') }}</a>
						</li>
						@if(isset($is_qplus->id))
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Quotations-tab" data-toggle="tab" href="#quotations" role="tab" aria-controls="quotations" aria-selected="false"><i class="mdi mdi-file-settings"></i> {{ __('crm.quotation') }}</a>
						</li>
						@endif
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Certification-tab" data-toggle="tab" href="#Certification" role="tab" aria-controls="Certification" aria-selected="false"><i class="mdi mdi-file-certificate"></i> {{ __('crm.attachments') }}</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Configurations-tab" data-toggle="tab" href="#Configurations" role="tab" aria-controls="Configurations" aria-selected="false"><i class="mdi mdi-cog-outline"></i> Configurations</a>
						</li>
						<li class="crm-tab-item">
							<a class="crm-tab-link" id="Customer-Details-tab" data-toggle="tab" href="#Customer-Details" role="tab" aria-controls="Customer-Details" aria-selected="false"><i class="mdi mdi-information-outline"></i> {{ __('crm.details') }}</a>
						</li>
					</ul>
				</div>

				<div class="tab-content" id="Samples-tabs-content">
					<div class="tab-pane fade p-3" id="Company-Units" role="tabpanel" aria-labelledby="Company-Units-tab">
						@livewire('crm.customer.tabs.company-units-manager', ['customerId' => $customer->id], key('tab-company-units'))
					</div>

					<div class="tab-pane fade p-3" id="Sample-Points" role="tabpanel" aria-labelledby="Sample-Points-tab">
						<livewire:crm.customer.tabs.sample-points-manager :customerId="$customer->id" lazy="on-load" :key="'tab-sample-points'" />
					</div>

					<div class="tab-pane fade p-3" id="Contacts" role="tabpanel" aria-labelledby="Contacts-tab">
						<livewire:crm.customer.tabs.contacts-manager :customerId="$customer->id" lazy="on-load" :key="'tab-contacts'" />
					</div>

					@if(isset($is_qplus->id))
					{{-- QPLUS-only: Orders, Reports, Quotations --}}
					<div class="tab-pane fade p-3" id="Orders" role="tabpanel" aria-labelledby="Orders-tab">
						<livewire:crm.customer.tabs.orders-list :customerId="$customer->id" lazy="on-load" :key="'tab-orders'" />
					</div>
					<div class="tab-pane fade p-3" id="Samples" role="tabpanel" aria-labelledby="Samples-tab">
						<livewire:crm.customer.tabs.reports-list :customerId="$customer->id" lazy="on-load" :key="'tab-reports'" />
					</div>
					@endif

					{{-- Secondary: Complaints, Feedbacks, Attachments, Configurations, Custom Fields, Details --}}
					<div class="tab-pane fade p-3" id="Complaints" role="tabpanel" aria-labelledby="Complaints-tab">
						<livewire:crm.customer.tabs.complaints-manager :customerId="$customer->id" lazy="on-load" :key="'tab-complaints'" />
					</div>

					<div class="tab-pane fade p-3" id="Feedbacks" role="tabpanel" aria-labelledby="Feedbacks-tab">
						<livewire:crm.customer.tabs.feedbacks-list :customerId="$customer->id" lazy="on-load" :key="'tab-feedbacks'" />
					</div>

					@if(isset($is_qplus->id))
					<div class="tab-pane fade p-3" id="quotations" role="tabpanel" aria-labelledby="Quotations-tab">
						@livewire(\App\Livewire\Crm\Customer\Tabs\QuotationsList::class, ['customer' => $customer], key('tab-quotations'))
					</div>
					@endif

					<div class="tab-pane fade p-3" id="Certification" role="tabpanel" aria-labelledby="Certification-tab">
						@livewire(\App\Livewire\Crm\Customer\Tabs\AttachmentsManager::class, ['customer' => $customer], key('tab-attachments'))
					</div>

					{{-- Configurations tab disabled
					<div class="tab-pane fade p-3" id="Configurations" role="tabpanel" aria-labelledby="Configurations-tab">
						@livewire(\App\Livewire\Crm\Customer\Tabs\ConfigurationsManager::class, ['customer' => $customer], key('tab-configurations'))
					</div>
					--}}

					<div class="tab-pane fade p-3" id="Customer-Details" role="tabpanel" aria-labelledby="Customer-Details-tab">
						<livewire:crm.customer.tabs.details-form :customerId="$customer->id" lazy="on-load" :key="'tab-details'" />
					</div>
				</div>
			</div>
		</div>
	</div>
