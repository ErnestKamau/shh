@extends('layouts.lab.layout.app', [ 'datePicker' => true, 'select2' => true])

@section('title2')
<title>Submission Requests | Sample WorkFlow</title>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme workflow-theme">
	@include('layouts.lab.partials.lab-panel-theme-styles')

	<?php
	$items = [
		[
			'link' => route('dashboard-lab'),
			'name' => 'Dashboard',
			'icon' => null,
		],
		[
			'link' => route('sample-workflow', ['status' => 'All Samples']),
			'name' => 'Sample Workflow',
			'icon' => null,
		],
		[
			'link' => route('sample-submission-requests.index'),
			'name' => 'Submission Requests',
			'icon' => null,
		],
	];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="row mb-3">
		<div class="col-12">
			<div class="batch-header-bar submission-requests-hero">
				<div class="batch-header-top">
					<div class="batch-title-group">
						<span class="batch-code-label">Sample Workflow</span>
						<span class="batch-stage-pill">
							<i class="mdi mdi-sitemap" style="font-size:0.75rem;"></i>
							Samples Receiving
						</span>
					</div>
					<div class="d-flex align-items-center flex-wrap batch-header-actions" style="gap: 6px;">
					@livewire('sampleworkflow.portal-access-requests')
					<button type="button" class="btn btn-outline-secondary btn-action-sm">
						<i class="mdi mdi-clock-outline"></i> TAT Today Batches
					</button>
					<button type="button" class="btn btn-primary btn-action-sm">
						<i class="mdi mdi-format-list-bulleted"></i> Sample Submissions
					</button>
					<div class="dropdown">
						<button class="btn btn-outline-primary btn-action-sm dropdown-toggle" type="button" data-toggle="dropdown">
							Actions
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							@php
								$reviewableRequest = $requests->getCollection()->first(function ($request) {
									return ($request->currentQuotation?->status ?? '') === 'Quotation Under Review';
								});
							@endphp
							@if($reviewableRequest && $reviewableRequest->currentQuotation)
								<a class="dropdown-item" href="{{ route('add-qoute-details-view', ['id' => $reviewableRequest->currentQuotation->id, 'stage' => $reviewableRequest->currentQuotation->status]) }}">
									<i class="mdi mdi-file-document-edit-outline mr-2"></i> Review Quotation
								</a>
							@endif
							<a class="dropdown-item" href="#" data-toggle="modal" data-target="#add-submission-request-modal">
								<i class="mdi mdi-plus-circle-outline mr-2"></i> New Submission Request
							</a>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>

	<div class="stat-cards-row">
		<div class="stat-card">
			<div class="stat-card-label">Customers Requested Submission</div>
			<div class="stat-card-content">
				<div class="stat-card-value">{{ $totals['requested'] ?? 0 }}</div>
				<div class="stat-card-icon">
					<i class="mdi mdi-account-question"></i>
				</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-label">Portal Samples Submitted</div>
			<div class="stat-card-content">
				<div class="stat-card-value">{{ $totals['portal'] ?? 0 }}</div>
				<div class="stat-card-icon">
					<i class="mdi mdi-web"></i>
				</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-label">Sent to Request Review</div>
			<div class="stat-card-content">
				<div class="stat-card-value">{{ $totals['review'] ?? 0 }}</div>
				<div class="stat-card-icon">
					<i class="mdi mdi-file-find"></i>
				</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-label">Waiting for Delivery to Lab</div>
			<div class="stat-card-content">
				<div class="stat-card-value">{{ $totals['delivery'] ?? 0 }}</div>
				<div class="stat-card-icon">
					<i class="mdi mdi-truck-delivery"></i>
				</div>
			</div>
		</div>
	</div>

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-header">
			<h6><i class="mdi mdi-table"></i> Submission Records</h6>
			<div class="d-flex align-items-center gap-3">
				<form method="GET" action="{{ route('sample-submission-requests.index') }}" class="d-flex align-items-center gap-2">
					<input type="text" name="q" class="form-control" style="width: 300px; height: 38px; border-radius: 8px;" placeholder="Search records..." value="{{ request('q') }}">
					<button class="btn btn-primary btn-action-sm" type="submit">
						<i class="mdi mdi-magnify"></i>
					</button>
					@if(request('q'))
						<a href="{{ route('sample-submission-requests.index') }}" class="btn btn-outline-secondary btn-action-sm">
							<i class="mdi mdi-close"></i>
						</a>
					@endif
				</form>
			</div>
		</div>

		<ul class="nav nav-tabs" id="submission-requests-tab" role="tablist">
			<li class="nav-item">
				<a class="nav-link{{ $tab === 'requests' ? ' active' : '' }}" href="{{ route('sample-submission-requests.index', array_merge(request()->except('page'), ['tab' => 'requests'])) }}">
					Requests <span class="ml-2 badge badge-pill {{ $tab === 'requests' ? 'badge-primary' : 'badge-light' }}">{{ $requests->total() }}</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link{{ $tab === 'received' ? ' active' : '' }}" href="{{ route('sample-submission-requests.index', array_merge(request()->except('page'), ['tab' => 'received'])) }}">
					Received
				</a>
			</li>
		</ul>

		<div class="workflow-board-panel-body p-0">
			<div class="table-responsive">
				<table class="table table-hover mb-0 workflow-table">
					<thead>
						<tr>
							<th style="width: 100px;">Req #</th>
							<th style="min-width: 150px;">Batch</th>
							<th style="min-width: 250px;">Customer</th>
							<th style="min-width: 250px;">Contact</th>
							<th style="min-width: 180px;">Case / Offence</th>
							<th style="min-width: 150px;">Status</th>
							<th style="min-width: 150px;">Submitted On</th>
							<th class="text-center">Details</th>
						</tr>
					</thead>
					<tbody>
						@forelse($requests as $req)
						<tr>
							<td class="fw-bold">
								@if($req->batch)
									<a href="{{ route('view-batch-details', ['batch' => $req->batch->id, 'client' => 0, 'portal' => 0, 'status' => 'Samples En-Route']) }}" class="text-primary">{{ $req->formatted_number }}</a>
								@else
									<a href="{{ route('sample-submission-requests.show', ['request' => $req, 'details' => 1]) }}" class="text-primary">{{ $req->formatted_number }}</a>
								@endif
							</td>
							<td>
								@if($req->batch)
								<a href="{{ route('view-batch-details', ['batch' => $req->batch->id, 'client' => 0, 'portal' => 0, 'status' => $req->batch->status]) }}" class="font-weight-bold">
									<i class="mdi mdi-barcode-scan text-muted mr-1"></i> {{ $req->batch->batch_code }}
								</a>
								@else
								<span class="text-muted italic">No Batch</span>
								@endif
							</td>
							<td>
								<div class="fw-bold text-dark">{{ $req->customer?->name ?? 'N/A' }}</div>
								<div class="text-muted small"><i class="mdi mdi-account-card-details-outline"></i> {{ $req->customer?->code ?? $req->crm_customer_id ?? 'N/A' }}</div>
							</td>
							<td>
								@if($req->contact)
								<div class="fw-bold text-dark">
									{{ trim(($req->contact->first_name ?? '').' '.($req->contact->middle_name ?? '').' '.($req->contact->last_name ?? '')) }}
								</div>
								<div class="text-muted small"><i class="mdi mdi-email-outline"></i> {{ $req->contact->email }}</div>
								@else
								<div class="text-muted small"><i class="mdi mdi-email-outline"></i> {{ $req->email ?? 'N/A' }}</div>
								@endif
							</td>
							<td>
								<div class="fw-bold text-dark">{{ $req->case_no ?: 'N/A' }}</div>
								<div class="text-muted small">{{ $req->offence ?: '' }}</div>
							</td>
							<td>
								<span class="workflow-status-chip" style="background-color: {{ ($req->status ?? '') === 'Submitted' ? '#eef2ff' : '#f1f5f9' }}; color: {{ ($req->status ?? '') === 'Submitted' ? '#3b5fc0' : '#475569' }}; border-color: {{ ($req->status ?? '') === 'Submitted' ? '#c7d7fc' : '#e2e8f0' }}">
									<i class="mdi mdi-circle mr-1" style="font-size: 8px;"></i> {{ $req->status ?: 'N/A' }}
								</span>
							</td>
							<td>
								<div class="fw-bold text-dark">{{ optional($req->submitted_by_date)->format('d M, Y') ?: 'N/A' }}</div>
								<div class="text-muted small"><i class="mdi mdi-clock-outline"></i> {{ $req->submitted_by_time ?: '' }}</div>
							</td>
							<td class="text-center">
								<div class="d-flex justify-content-center gap-2 align-items-center flex-wrap">
									<span class="badge badge-light px-2" title="Exhibits"><i class="mdi mdi-package-variant"></i> {{ (int) $req->exhibits_count }}</span>
									<span class="badge badge-light px-2" title="Analyses"><i class="mdi mdi-flask-outline"></i> {{ (int) $req->requested_analyses_count }}</span>
									<div class="dropdown">
										<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
											Actions
										</button>
										<div class="dropdown-menu dropdown-menu-right">
											@if(($req->currentQuotation?->status ?? '') === 'Quotation Under Review')
												<a class="dropdown-item" href="{{ route('add-qoute-details-view', ['id' => $req->currentQuotation->id, 'stage' => $req->currentQuotation->status]) }}">
													<i class="mdi mdi-file-document-edit-outline mr-2"></i> Review Quotation
												</a>
											@endif
											<a class="dropdown-item" href="{{ route('sample-submission-requests.show', ['request' => $req, 'details' => 1]) }}">
												<i class="mdi mdi-eye-outline mr-2"></i> View Request
											</a>
										</div>
									</div>
								</div>
							</td>
						</tr>
						@empty
						<tr>
							<td colspan="8">
								<div class="text-center py-5 workflow-empty-state">
									<i class="mdi mdi-inbox-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
									<h5 class="mt-3 font-weight-bold" style="color: #64748b;">No submission records found</h5>
									<p class="text-muted">Adjust your search or filters to see results.</p>
								</div>
							</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<div class="workflow-board-panel-body">
			{{ $requests->links() }}
		</div>
	</div>

	<!-- Create Modal -->
	<div class="modal fade" id="add-submission-request-modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document" style="max-width: 1200px;">
			<div class="modal-content" style="border-radius: 12px;">
				<div class="modal-header">
					<h5 class="modal-title"><i class="mdi mdi-plus-circle-outline"></i> Add Submission Request</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="POST" action="{{ route('sample-submission-requests.store') }}">
					@csrf
					<div class="modal-body">
						<ul class="nav nav-tabs" id="submission-request-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="tab-case-details" data-toggle="tab" href="#pane-case-details" role="tab" aria-controls="pane-case-details" aria-selected="true">
									<i class="mdi mdi-clipboard-text-outline"></i> Case Details
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="tab-exhibits-suspects" data-toggle="tab" href="#pane-exhibits-suspects" role="tab" aria-controls="pane-exhibits-suspects" aria-selected="false">
									<i class="mdi mdi-table"></i> Exhibits & Suspects
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="tab-supporting-documents" data-toggle="tab" href="#pane-supporting-documents" role="tab" aria-controls="pane-supporting-documents" aria-selected="false">
									<i class="mdi mdi-file-document-outline"></i> Supporting Documents
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="tab-submission-reception" data-toggle="tab" href="#pane-submission-reception" role="tab" aria-controls="pane-submission-reception" aria-selected="false">
									<i class="mdi mdi-account-check-outline"></i> Submission / Reception
								</a>
							</li>
						</ul>

						<div class="tab-content pt-3" id="submission-request-tab-content">
							<div class="tab-pane fade show active" id="pane-case-details" role="tabpanel" aria-labelledby="tab-case-details">
								<div class="mb-3">
									<h6 class="mb-2" style="font-weight: 700; color: #1e293b;">Case / Request Details</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Customer <span class="text-danger">*</span></label>
												<select name="crm_customer_id" id="submission-request-customer" class="form-control">
													<option value="">Select customer...</option>
													@if(($customers?->count() ?? 0) === 0)
														<option value="" disabled>No active customers found</option>
													@endif
													@foreach($customers as $customer)
													<option value="{{ $customer->id }}" {{ (string) old('crm_customer_id') === (string) $customer->id ? 'selected' : '' }}>
														{{ $customer->name }}
													</option>
													@endforeach
												</select>
												@error('crm_customer_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Contact (optional)</label>
												<select name="crm_contact_id" id="submission-request-contact" class="form-control" data-old="{{ old('crm_contact_id') }}">
													<option value="">Select customer first...</option>
												</select>
												@error('crm_contact_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Submitting Agency</label>
												<input type="text" name="submitting_agency" class="form-control" value="{{ old('submitting_agency') }}">
												@error('submitting_agency') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Submitting Officer Full Name</label>
												<input type="text" name="submitting_officer_full_name" class="form-control" value="{{ old('submitting_officer_full_name') }}">
												@error('submitting_officer_full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Submitting Officer Title</label>
												<input type="text" name="submitting_officer_title" class="form-control" value="{{ old('submitting_officer_title') }}">
												@error('submitting_officer_title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Email</label>
												<input type="email" name="email" class="form-control" value="{{ old('email') }}">
												@error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-4">
											<div class="form-group mb-3">
												<label class="form-label">Case No</label>
												<input type="text" name="case_no" class="form-control" value="{{ old('case_no') }}">
												@error('case_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-3">
												<label class="form-label">Offence</label>
												<input type="text" name="offence" class="form-control" value="{{ old('offence') }}">
												@error('offence') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-3">
												<label class="form-label">Date of Seizure</label>
												<input type="date" name="date_of_seizure" class="form-control" value="{{ old('date_of_seizure') }}">
												@error('date_of_seizure') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Physical Address</label>
												<input type="text" name="physical_address" class="form-control" value="{{ old('physical_address') }}">
												@error('physical_address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Region</label>
												<input type="text" name="region" class="form-control" value="{{ old('region') }}">
												@error('region') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">District</label>
												<input type="text" name="district" class="form-control" value="{{ old('district') }}">
												@error('district') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Working Station</label>
												<input type="text" name="working_station" class="form-control" value="{{ old('working_station') }}">
												@error('working_station') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Office Telephone No</label>
												<input type="text" name="office_telephone_no" class="form-control" value="{{ old('office_telephone_no') }}">
												@error('office_telephone_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Mobile Telephone No</label>
												<input type="text" name="mobile_telephone_no" class="form-control" value="{{ old('mobile_telephone_no') }}">
												@error('mobile_telephone_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Fax</label>
												<input type="text" name="fax" class="form-control" value="{{ old('fax') }}">
												@error('fax') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Seizure Region</label>
												<input type="text" name="seizure_region" class="form-control" value="{{ old('seizure_region') }}">
												@error('seizure_region') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Seizure District</label>
												<input type="text" name="seizure_district" class="form-control" value="{{ old('seizure_district') }}">
												@error('seizure_district') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3">
												<label class="form-label">Seizure Ward</label>
												<input type="text" name="seizure_ward" class="form-control" value="{{ old('seizure_ward') }}">
												@error('seizure_ward') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="form-group mb-0">
										<label class="form-label">Seizure Village / Street</label>
										<input type="text" name="seizure_village_street" class="form-control" value="{{ old('seizure_village_street') }}">
										@error('seizure_village_street') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="pane-exhibits-suspects" role="tabpanel" aria-labelledby="tab-exhibits-suspects">
								<div class="mb-4">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<h6 class="mb-0" style="font-weight: 700; color: #1e293b;">
											<i class="mdi mdi-account-multiple"></i> Suspects
										</h6>
										<button type="button" class="btn btn-outline-primary btn-action-sm" id="add-suspect-row">
											<i class="mdi mdi-plus"></i> Add Suspect
										</button>
									</div>
									<div class="table-responsive">
										<table class="table table-sm table-bordered mb-0">
											<thead class="thead-light">
												<tr>
													<th style="width: 70px;">S/N</th>
													<th style="min-width: 130px;">First</th>
													<th style="min-width: 130px;">Middle</th>
													<th style="min-width: 130px;">Last</th>
													<th style="width: 90px;">Sex</th>
													<th style="width: 150px;">DOB</th>
													<th style="min-width: 140px;">Nationality</th>
													<th style="min-width: 160px;">ID/Passport</th>
													<th style="width: 40px;"></th>
												</tr>
											</thead>
											<tbody id="suspects-tbody"></tbody>
										</table>
									</div>
									@error('suspects') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>

								<div class="mb-0">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<h6 class="mb-0" style="font-weight: 700; color: #1e293b;">
											<i class="mdi mdi-package-variant-closed"></i> Exhibits
										</h6>
										<button type="button" class="btn btn-outline-primary btn-action-sm" id="add-exhibit-row">
											<i class="mdi mdi-plus"></i> Add Exhibit
										</button>
									</div>
									<div class="table-responsive">
										<table class="table table-sm table-bordered mb-0">
											<thead class="thead-light">
												<tr>
													<th style="width: 80px;">S/N</th>
													<th style="width: 140px;"># Items</th>
													<th>Item Description</th>
													<th style="width: 160px;">Suspected Item</th>
													<th style="width: 40px;"></th>
												</tr>
											</thead>
											<tbody id="exhibits-tbody"></tbody>
										</table>
									</div>
									@error('exhibits') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
							</div>
							<div class="tab-pane fade" id="pane-supporting-documents" role="tabpanel" aria-labelledby="tab-supporting-documents">
								<div class="mb-0">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<h6 class="mb-0" style="font-weight: 700; color: #1e293b;">
											<i class="mdi mdi-file-document-outline"></i> Published Supporting Documents
										</h6>
										<span class="text-muted small">{{ (int) ($supportingDocumentTemplates?->count() ?? 0) }} template(s)</span>
									</div>

									@if(($supportingDocumentTemplates?->count() ?? 0) === 0)
										<div class="text-center py-4 workflow-empty-state">
											<i class="mdi mdi-file-hidden" style="font-size: 1.8rem;"></i>
											<p class="mt-2 mb-0">No published supporting document templates available.</p>
										</div>
									@else
										<div class="table-responsive">
											<table class="table table-sm table-bordered mb-0">
												<thead class="thead-light">
													<tr>
														<th style="width: 44px;"></th>
														<th style="width: 140px;">Code</th>
														<th>Title</th>
														<th style="width: 90px;">Version</th>
													</tr>
												</thead>
												<tbody>
													@foreach($supportingDocumentTemplates as $template)
														<tr>
															<td class="text-center align-middle">
																<input
																	type="checkbox"
																	name="supporting_document_template_ids[]"
																	value="{{ $template->id }}"
																	{{ in_array((string) $template->id, array_map('strval', (array) old('supporting_document_template_ids', [])), true) ? 'checked' : '' }}
																/>
															</td>
															<td class="align-middle text-muted fw-semibold">{{ $template->document_code ?? '—' }}</td>
															<td class="align-middle">
																<div class="fw-semibold">{{ $template->title }}</div>
																@if($template->subtitle)
																	<div class="text-muted small">{{ $template->subtitle }}</div>
																@endif
															</td>
															<td class="align-middle text-center">{{ $template->version ?? '—' }}</td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
										@error('supporting_document_template_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
									@endif
								</div>
							</div>
							<div class="tab-pane fade" id="pane-submission-reception" role="tabpanel" aria-labelledby="tab-submission-reception">
								<div class="mb-0">
									<h6 class="mb-2" style="font-weight: 700; color: #1e293b;">Submission / Reception</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Submitted By Full Name</label>
												<input type="text" name="submitted_by_full_name" class="form-control" value="{{ old('submitted_by_full_name') }}">
												@error('submitted_by_full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Submitted By Title</label>
												<input type="text" name="submitted_by_title" class="form-control" value="{{ old('submitted_by_title') }}">
												@error('submitted_by_title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-4">
											<div class="form-group mb-3">
												<label class="form-label">Submitted By Signature</label>
												<input type="text" name="submitted_by_signature" class="form-control" value="{{ old('submitted_by_signature') }}">
												@error('submitted_by_signature') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-3">
												<label class="form-label">Submitted By Date</label>
												<input type="date" name="submitted_by_date" class="form-control" value="{{ old('submitted_by_date') }}">
												@error('submitted_by_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-3">
												<label class="form-label">Submitted By Time</label>
												<input type="time" name="submitted_by_time" class="form-control" value="{{ old('submitted_by_time') }}">
												@error('submitted_by_time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Received By Full Name</label>
												<input type="text" name="received_by_full_name" class="form-control" value="{{ old('received_by_full_name') }}">
												@error('received_by_full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group mb-3">
												<label class="form-label">Received By Title</label>
												<input type="text" name="received_by_title" class="form-control" value="{{ old('received_by_title') }}">
												@error('received_by_title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-4">
											<div class="form-group mb-0">
												<label class="form-label">Received By Signature</label>
												<input type="text" name="received_by_signature" class="form-control" value="{{ old('received_by_signature') }}">
												@error('received_by_signature') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-0">
												<label class="form-label">Received By Date</label>
												<input type="date" name="received_by_date" class="form-control" value="{{ old('received_by_date') }}">
												@error('received_by_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-0">
												<label class="form-label">Received By Time</label>
												<input type="time" name="received_by_time" class="form-control" value="{{ old('received_by_time') }}">
												@error('received_by_time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary btn-action-sm" data-dismiss="modal">
							<i class="mdi mdi-close"></i> Cancel
						</button>
						<button type="button" class="btn btn-outline-primary btn-action-sm" id="submission-request-prev">
							<i class="mdi mdi-chevron-left"></i> Back
						</button>
						<button type="button" class="btn btn-primary btn-action-sm" id="submission-request-next">
							Next <i class="mdi mdi-chevron-right"></i>
						</button>
						<button type="submit" class="btn btn-primary btn-action-sm d-none" id="submission-request-submit">
							<i class="mdi mdi-content-save"></i> Save Request
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	@section('script2')
	@parent
	<script>
		(function initSampleSubmissionRequestModalTables() {
			function tryInit() {
				if (!window.$) {
					setTimeout(tryInit, 50);
					return;
				}

				function escapeHtml(value) {
				if (value === undefined || value === null) {
					return '';
				}
				return String(value)
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#039;');
			}

			function buildExhibitRow(index, data) {
				data = data || {};
				return [
					'<tr>',
						'<td><input type="number" min="1" name="exhibits[' + index + '][serial_number]" class="form-control form-control-sm" value="' + escapeHtml(data.serial_number || (index + 1)) + '"></td>',
						'<td><input type="number" min="0" name="exhibits[' + index + '][number_of_items]" class="form-control form-control-sm" value="' + escapeHtml(data.number_of_items || '') + '"></td>',
						'<td><input type="text" name="exhibits[' + index + '][item_description]" class="form-control form-control-sm" value="' + escapeHtml(data.item_description || '') + '"></td>',
						'<td><input type="text" name="exhibits[' + index + '][suspected_item]" class="form-control form-control-sm" value="' + escapeHtml(data.suspected_item || '') + '"></td>',
						'<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove"><i class="mdi mdi-delete"></i></button></td>',
					'</tr>'
				].join('');
			}

			function buildSuspectRow(index, data) {
				data = data || {};
				return [
					'<tr>',
						'<td><input type="number" min="1" name="suspects[' + index + '][serial_number]" class="form-control form-control-sm" value="' + escapeHtml(data.serial_number || (index + 1)) + '"></td>',
						'<td><input type="text" name="suspects[' + index + '][first_name]" class="form-control form-control-sm" value="' + escapeHtml(data.first_name || '') + '"></td>',
						'<td><input type="text" name="suspects[' + index + '][middle_name]" class="form-control form-control-sm" value="' + escapeHtml(data.middle_name || '') + '"></td>',
						'<td><input type="text" name="suspects[' + index + '][last_name]" class="form-control form-control-sm" value="' + escapeHtml(data.last_name || '') + '"></td>',
						'<td>',
							'<select name="suspects[' + index + '][sex]" class="form-control form-control-sm">',
								'<option value="" ' + (!data.sex ? 'selected' : '') + '>—</option>',
								'<option value="Male" ' + (data.sex === 'Male' ? 'selected' : '') + '>Male</option>',
								'<option value="Female" ' + (data.sex === 'Female' ? 'selected' : '') + '>Female</option>',
							'</select>',
						'</td>',
						'<td><input type="date" name="suspects[' + index + '][date_of_birth]" class="form-control form-control-sm" value="' + escapeHtml(data.date_of_birth || '') + '"></td>',
						'<td><input type="text" name="suspects[' + index + '][nationality]" class="form-control form-control-sm" value="' + escapeHtml(data.nationality || '') + '"></td>',
						'<td><input type="text" name="suspects[' + index + '][id_passport_number]" class="form-control form-control-sm" value="' + escapeHtml(data.id_passport_number || '') + '"></td>',
						'<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove"><i class="mdi mdi-delete"></i></button></td>',
					'</tr>'
				].join('');
			}

			function reindexRows($tbody, prefix) {
				$tbody.find('tr').each(function(newIndex) {
					$(this).find('input, select, textarea').each(function() {
						var name = $(this).attr('name');
						if (!name) {
							return;
						}
						var re = new RegExp('^' + prefix + '\\\\[\\\\d+\\\\]\\\\[(.+)\\\\]$');
						var match = name.match(re);
						if (match && match[1]) {
							$(this).attr('name', prefix + '[' + newIndex + '][' + match[1] + ']');
						}
					});
				});
			}

				$(document).ready(function() {
				var $modal = $('#add-submission-request-modal');
				var $exhibitsTbody = $('#exhibits-tbody');
				var $suspectsTbody = $('#suspects-tbody');
				var $customerSelect = $('#submission-request-customer');
				var $contactSelect = $('#submission-request-contact');
				var $submittingAgency = $('input[name="submitting_agency"]');
				var $submittingOfficerFullName = $('input[name="submitting_officer_full_name"]');
				var $submittingOfficerTitle = $('input[name="submitting_officer_title"]');
				var $contactEmail = $('input[name="email"]');
				var $tabs = $('#submission-request-tabs a[data-toggle="tab"]');
				var $btnPrev = $('#submission-request-prev');
				var $btnNext = $('#submission-request-next');
				var $btnSubmit = $('#submission-request-submit');
				var contactsUrlTemplate = "{{ route('sample-submission-requests.customer-contacts', ['customer' => '__CUSTOMER__']) }}";
				var loadedContactsById = {};

				function setContactLoadingState(message) {
					$contactSelect.prop('disabled', true);
					$contactSelect.html('<option value="">' + escapeHtml(message) + '</option>');
				}

				function populateContacts(contacts, selectedId) {
					loadedContactsById = {};
					var html = '<option value="">Select contact...</option>';

					(contacts || []).forEach(function(c) {
						loadedContactsById[String(c.id)] = c;
						var fullName = [c.first_name, c.middle_name, c.last_name].filter(Boolean).join(' ').trim();
						var label = fullName;
						if (c.email) {
							label = label + ' (' + c.email + ')';
						}

						var isSelected = selectedId && String(selectedId) === String(c.id);
						html += '<option value="' + escapeHtml(c.id) + '"' + (isSelected ? ' selected' : '') + '>' + escapeHtml(label) + '</option>';
					});

					$contactSelect.prop('disabled', false);
					$contactSelect.html(html);

					if ($.fn.select2 && $contactSelect.hasClass('select2-hidden-accessible')) {
						$contactSelect.trigger('change.select2');
					}
				}

				function loadCustomerContacts(customerId, selectedId) {
					if (!customerId) {
						setContactLoadingState('Select customer first...');
						return;
					}

					setContactLoadingState('Loading contacts...');
					var url = contactsUrlTemplate.replace('__CUSTOMER__', customerId);

					$.getJSON(url)
						.done(function(data) {
							populateContacts(data, selectedId);
						})
						.fail(function() {
							setContactLoadingState('Failed to load contacts');
						});
				}

				function autofillFromCustomer() {
					var customerName = $customerSelect.find('option:selected').text() || '';
					customerName = customerName.replace(/\s+\(\d+\)\s*$/, '').trim();

					if (customerName && customerName !== 'Select customer...' && customerName !== 'No active customers found') {
						$submittingAgency.val(customerName);
					}
				}

				function autofillFromContact() {
					var contactId = $contactSelect.val();
					if (!contactId) {
						return;
					}

					var c = loadedContactsById[String(contactId)];
					if (!c) {
						return;
					}

					var fullName = [c.first_name, c.middle_name, c.last_name].filter(Boolean).join(' ').trim();
					if (fullName) {
						$submittingOfficerFullName.val(fullName);
					}
					if (c.job_occupation) {
						$submittingOfficerTitle.val(c.job_occupation);
					}
					if (c.email) {
						$contactEmail.val(c.email);
					}
				}

				$customerSelect.on('change', function() {
					loadCustomerContacts($(this).val(), null);
					autofillFromCustomer();

					// reset contact + contact-dependent autofills when customer changes
					$contactSelect.val('');
					if ($.fn.select2 && $contactSelect.hasClass('select2-hidden-accessible')) {
						$contactSelect.trigger('change.select2');
					}
				});

				$contactSelect.on('change', function() {
					autofillFromContact();
				});

				function ensureSelect2InModal($select) {
					if (!$.fn.select2) {
						return;
					}

					if ($select.hasClass('select2-hidden-accessible')) {
						try {
							$select.select2('destroy');
						} catch (e) {
							// ignore - we'll re-init below
						}
					}

					$select.select2({
						width: '100%',
						dropdownParent: $modal,
						placeholder: $select.find('option:first').text() || 'Select...',
					});
				}

				function ensureInitialRows() {
					if ($exhibitsTbody.children().length === 0) {
						$exhibitsTbody.append(buildExhibitRow(0));
					}
					if ($suspectsTbody.children().length === 0) {
						$suspectsTbody.append(buildSuspectRow(0));
					}
				}

				$('#add-exhibit-row').on('click', function() {
					var index = $exhibitsTbody.children().length;
					$exhibitsTbody.append(buildExhibitRow(index));
				});

				$('#add-suspect-row').on('click', function() {
					var index = $suspectsTbody.children().length;
					$suspectsTbody.append(buildSuspectRow(index));
				});

				$modal.on('click', '.remove-row', function() {
					var $tbody = $(this).closest('tbody');
					$(this).closest('tr').remove();

					if ($tbody.is($exhibitsTbody)) {
						reindexRows($exhibitsTbody, 'exhibits');
					}
					if ($tbody.is($suspectsTbody)) {
						reindexRows($suspectsTbody, 'suspects');
					}

					ensureInitialRows();
				});

				$modal.on('shown.bs.modal', function() {
					ensureInitialRows();
					updateStepperButtons();

					ensureSelect2InModal($customerSelect);
					ensureSelect2InModal($contactSelect);

					var initialCustomerId = $customerSelect.val();
					var oldContactId = $contactSelect.data('old');
					loadCustomerContacts(initialCustomerId, oldContactId);
					autofillFromCustomer();
				});

				function getActiveTabIndex() {
					var activeIndex = 0;
					$tabs.each(function(i) {
						if ($(this).hasClass('active')) {
							activeIndex = i;
						}
					});
					return activeIndex;
				}

				function showTabByIndex(index) {
					var $target = $tabs.eq(index);
					if ($target.length) {
						$target.tab('show');
					}
				}

				function updateStepperButtons() {
					var idx = getActiveTabIndex();
					var last = $tabs.length - 1;

					$btnPrev.toggleClass('d-none', idx === 0);
					$btnNext.toggleClass('d-none', idx === last);
					$btnSubmit.toggleClass('d-none', idx !== last);
				}

				$tabs.on('shown.bs.tab', function() {
					updateStepperButtons();
				});

				$btnPrev.on('click', function() {
					var idx = getActiveTabIndex();
					showTabByIndex(Math.max(0, idx - 1));
				});

				$btnNext.on('click', function() {
					var idx = getActiveTabIndex();
					showTabByIndex(Math.min($tabs.length - 1, idx + 1));
				});
				});
			}

			tryInit();
		})();
	</script>
	@endsection

	@if($errors->any())
	@section('script2')
	@parent
	<script>
		(function() {
			if (window.$) {
				$('#add-submission-request-modal').modal('show');
			}
		})();
	</script>
	@endsection
	@endif
</main>
@endsection