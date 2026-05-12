@extends('layouts.lab.layout.app', [ 'datePicker' => true, 'select2' => true])

@section('title2')
<title>Submission Request #{{ $request->id }} | Sample WorkFlow</title>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme">
	@include('layouts.lab.partials.lab-panel-theme-styles')

	<style>
		.sr-detail-grid {
			display: grid;
			grid-template-columns: 160px 1fr;
			gap: 0;
		}

		.sr-detail-label {
			font-size: 0.76rem;
			font-weight: 600;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.03em;
			padding: 8px 14px 8px 0;
			border-bottom: 1px solid #f1f5f9;
			display: flex;
			align-items: center;
		}

		.sr-detail-value {
			font-size: 0.87rem;
			color: #1e293b;
			padding: 8px 0;
			border-bottom: 1px solid #f1f5f9;
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 2px;
		}

		.sr-detail-grid>.sr-detail-label:last-of-type,
		.sr-detail-grid>.sr-detail-value:last-of-type {
			border-bottom: none;
		}

		.sr-analysis-chip {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			background: #f0f4ff;
			color: #3b5fc0;
			border: 1px solid #c7d7fc;
			border-radius: 20px;
			padding: 4px 12px;
			font-size: 0.8rem;
			font-weight: 500;
		}

		.sr-summary-bar {
			background: #fff;
			border: 1px solid var(--workflow-border);
			border-radius: 10px;
			padding: 14px 20px;
			margin-bottom: 1rem;
			box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		}

		.sr-stat-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 0 18px;
			border-left: 1px solid #f1f5f9;
		}

		.sr-stat-item .stat-value {
			font-size: 1.25rem;
			font-weight: 700;
			color: #1e293b;
			line-height: 1.2;
		}

		.sr-stat-item .stat-label {
			font-size: 0.7rem;
			font-weight: 600;
			color: #94a3b8;
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.sr-sub-label {
			font-size: 0.82rem;
			font-weight: 600;
			color: #475569;
			display: flex;
			align-items: center;
			gap: 6px;
			padding-bottom: 10px;
			margin-bottom: 4px;
			border-bottom: 1px solid #f1f5f9;
		}

		.sr-sub-label .mdi {
			color: var(--workflow-accent);
			font-size: 1rem;
		}

		.sr-divider-col {
			border-left: 1px solid #f1f5f9;
		}

		@media (max-width: 991px) {
			.sr-divider-col {
				border-left: none;
				border-top: 1px solid #f1f5f9;
				padding-top: 16px;
				margin-top: 16px;
			}
		}
	</style>

	<?php
	$items = [
		[
			'link' => route('dashboard-lab'),
			'name' => 'Dashboard',
			'icon' => null,
		],
		[
			'link' => route('sample-submission-requests.index'),
			'name' => 'Submission Requests',
			'icon' => null,
		],
		[
			'link' => route('sample-submission-requests.show', ['request' => $request, 'details' => 1]),
			'name' => 'Request #' . $request->id,
			'icon' => null,
		],
	];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="row" style="margin: 0 15px;">
		<div class="col-12 px-0">

			{{-- Summary Bar --}}
			<div class="sr-summary-bar d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
				<div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
					<span class="workflow-status-chip" style="--chip-accent: {{ ($request->status ?? '') === 'Submitted' ? '#3b5fc0' : '#6c757d' }}; font-size: 0.88rem;">
						{{ $request->status ?: 'Draft' }}
					</span>
					<div>
						<div class="fw-semibold" style="font-size: 0.95rem; color: #1e293b;">
							Request #{{ $request->id }}
							@if($request->case_no)
							<span class="text-muted fw-normal">&mdash; Case: {{ $request->case_no }}</span>
							@endif
						</div>
						@if($request->offence)
						<div class="text-muted" style="font-size: 0.8rem;">{{ $request->offence }}</div>
						@endif
					</div>
				</div>
				<div class="d-flex align-items-center flex-wrap" style="gap: 0;">
					<div class="sr-stat-item">
						<span class="stat-value">{{ $request->exhibits->count() }}</span>
						<span class="stat-label">Exhibits</span>
					</div>
					<div class="sr-stat-item">
						<span class="stat-value">{{ $request->suspects->count() }}</span>
						<span class="stat-label">Suspects</span>
					</div>
					<div class="sr-stat-item">
						<span class="stat-value">{{ $request->requestedAnalyses->count() }}</span>
						<span class="stat-label">Analyses</span>
					</div>
					<div style="padding-left: 18px; border-left: 1px solid #f1f5f9; margin-left: 4px;">
						<a class="btn btn-outline-secondary btn-action-sm" href="{{ route('sample-submission-requests.index') }}">
							<i class="mdi mdi-arrow-left"></i> Back
						</a>
					</div>
				</div>
			</div>

			@if($request->batch)
			<div class="workflow-board-panel mb-3">
				<div class="workflow-board-panel-body">
					<div class="row align-items-end">
						<div class="col-md-4 mb-2 mb-md-0">
							<label class="form-label mb-1">Proposed Lab Booking Date</label>
							<div class="fw-semibold">{{ $request->batch->date_expected ?: 'N/A' }}</div>
						</div>
						<div class="col-md-2 mb-2 mb-md-0">
							<label class="form-label mb-1">Decision</label>
							<div class="fw-semibold text-capitalize">{{ $request->booking_date_status ?: 'pending' }}</div>
						</div>
						<div class="col-md-6">
							<div class="d-flex flex-wrap justify-content-md-end" style="gap: 8px;">
								<form method="POST" action="{{ route('sample-submission-requests.booking-date.approve', $request) }}">
									@csrf
									<button type="submit" class="btn btn-success btn-action-sm">
										<i class="mdi mdi-check-circle-outline"></i> Approve Date
									</button>
								</form>
								<form method="POST" action="{{ route('sample-submission-requests.booking-date.reschedule', $request) }}" class="d-flex" style="gap: 8px;">
									@csrf
									<input type="date" name="date_expected" class="form-control" value="{{ old('date_expected', $request->batch->date_expected) }}" required>
									<button type="submit" class="btn btn-outline-primary btn-action-sm">
										<i class="mdi mdi-calendar-edit"></i> Move Date
									</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			@endif

			{{-- Section 1 & 2: Request Details + Seizure Address --}}
			<div class="workflow-board-panel">
				<div class="row" style="margin: 0;">
					<div class="col-12 col-lg-6" style="padding: 0;">
						<div style="padding: 14px 20px; border-bottom: 1px solid #f8fafc; background: var(--workflow-surface); border-radius: 10px 0 0 0;">
							<span class="sr-sub-label" style="border-bottom: none; padding-bottom: 0; margin-bottom: 0;">
								<i class="mdi mdi-clipboard-text-outline"></i> Request Details
							</span>
						</div>
						<div style="padding: 16px 20px;">
							<div class="sr-detail-grid">
								<div class="sr-detail-label">Customer</div>
								<div class="sr-detail-value fw-semibold">{{ $request->customer?->name ?? 'N/A' }}</div>

								<div class="sr-detail-label">Contact</div>
								<div class="sr-detail-value">
									@if($request->contact)
									<div>
										<div class="fw-semibold">{{ trim(($request->contact->first_name ?? '').' '.($request->contact->middle_name ?? '').' '.($request->contact->last_name ?? '')) }}</div>
										<div class="text-muted" style="font-size: 0.78rem;">{{ $request->contact->email ?? '' }}</div>
									</div>
									@else
									<span class="text-muted">N/A</span>
									@endif
								</div>

								<div class="sr-detail-label">Agency</div>
								<div class="sr-detail-value">{{ $request->submitting_agency ?: 'N/A' }}</div>

								<div class="sr-detail-label">Officer</div>
								<div class="sr-detail-value">
									<div>
										<div>{{ $request->submitting_officer_full_name ?: 'N/A' }}</div>
										@if($request->submitting_officer_title)
										<div class="text-muted" style="font-size: 0.78rem;">{{ $request->submitting_officer_title }}</div>
										@endif
									</div>
								</div>

								<div class="sr-detail-label">Email</div>
								<div class="sr-detail-value">{{ $request->email ?: 'N/A' }}</div>

								<div class="sr-detail-label">Batch</div>
								<div class="sr-detail-value">
									@if($request->batch)
									<a href="{{ route('view-batch-details', ['batch' => $request->batch->id, 'client' => 0, 'portal' => 0, 'status' => $request->batch->status]) }}">
										{{ $request->batch->batch_code }}
									</a>
									@else
									<span class="text-muted">Not linked</span>
									@endif
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-lg-6 sr-divider-col" style="padding: 0;">
						<div style="padding: 14px 20px; border-bottom: 1px solid #f8fafc; background: var(--workflow-surface); border-radius: 0 10px 0 0;">
							<span class="sr-sub-label" style="border-bottom: none; padding-bottom: 0; margin-bottom: 0;">
								<i class="mdi mdi-map-marker-outline"></i> Seizure Address
							</span>
						</div>
						<div style="padding: 16px 20px;">
							<div class="sr-detail-grid">
								<div class="sr-detail-label">Physical Address</div>
								<div class="sr-detail-value">{{ $request->physical_address ?: 'N/A' }}</div>

								<div class="sr-detail-label">Region / District</div>
								<div class="sr-detail-value">{{ ($request->region ?: '—') . ' / ' . ($request->district ?: '—') }}</div>

								<div class="sr-detail-label">Working Station</div>
								<div class="sr-detail-value">{{ $request->working_station ?: 'N/A' }}</div>

								<div class="sr-detail-label">Office / Mobile</div>
								<div class="sr-detail-value">{{ ($request->office_telephone_no ?: '—') . ' / ' . ($request->mobile_telephone_no ?: '—') }}</div>

								<div class="sr-detail-label">Fax</div>
								<div class="sr-detail-value">{{ $request->fax ?: 'N/A' }}</div>

								<div class="sr-detail-label">Date of Seizure</div>
								<div class="sr-detail-value">{{ optional($request->date_of_seizure)->format('Y-m-d') ?: 'N/A' }}</div>

								<div class="sr-detail-label">Seizure Region</div>
								<div class="sr-detail-value">{{ ($request->seizure_region ?: '—') . ' / ' . ($request->seizure_district ?: '—') }}</div>

								<div class="sr-detail-label">Ward</div>
								<div class="sr-detail-value">{{ $request->seizure_ward ?: 'N/A' }}</div>

								<div class="sr-detail-label">Village / Street</div>
								<div class="sr-detail-value">{{ $request->seizure_village_street ?: 'N/A' }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			{{-- Section 3: Requested Analyses --}}
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-flask-outline"></i> Requested Analyses</h6>
					<span class="text-muted" style="font-size: 0.8rem;">{{ $request->requestedAnalyses->count() }} item(s)</span>
				</div>
				<div class="workflow-board-panel-body">
					@if($request->requestedAnalyses->count() === 0)
					<div class="text-center py-3 workflow-empty-state">
						<i class="mdi mdi-flask-empty-outline" style="font-size: 1.6rem;"></i>
						<p class="mt-1 mb-0" style="font-size: 0.87rem;">No requested analyses captured.</p>
					</div>
					@else
					<div class="d-flex flex-wrap" style="gap: 8px;">
						@foreach($request->requestedAnalyses as $analysis)
						<span class="sr-analysis-chip">
							<i class="mdi mdi-test-tube" style="font-size: 0.85rem;"></i>
							{{ $analysis->analysis_label ?: $analysis->analysis_key }}
						</span>
						@endforeach
					</div>
					@endif
				</div>
			</div>

			{{-- Section 4: Suspects --}}
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-account-multiple"></i> Suspects</h6>
					<span class="text-muted" style="font-size: 0.8rem;">{{ $request->suspects->count() }} suspect(s)</span>
				</div>
				<div class="workflow-board-panel-body p-0">
					<div class="table-responsive">
						<table class="table table-hover mb-0 workflow-table">
							<thead>
								<tr>
									<th style="width: 70px;">S/N</th>
									<th style="min-width: 180px;">Full Name</th>
									<th style="width: 90px;">Sex</th>
									<th style="width: 140px;">Date of Birth</th>
									<th style="min-width: 160px;">Nationality</th>
									<th style="min-width: 180px;">ID / Passport</th>
								</tr>
							</thead>
							<tbody>
								@forelse($request->suspects as $suspect)
								<tr>
									<td class="text-muted fw-semibold">{{ $suspect->serial_number ?? '—' }}</td>
									<td class="fw-semibold">
										{{ trim(($suspect->first_name ?? '').' '.($suspect->middle_name ?? '').' '.($suspect->last_name ?? '')) ?: 'N/A' }}
									</td>
									<td>{{ $suspect->sex ?: '—' }}</td>
									<td>{{ optional($suspect->date_of_birth)->format('Y-m-d') ?: '—' }}</td>
									<td>{{ $suspect->nationality ?: '—' }}</td>
									<td>{{ $suspect->id_passport_number ?: '—' }}</td>
								</tr>
								@empty
								<tr>
									<td colspan="6">
										<div class="text-center py-4 workflow-empty-state">
											<i class="mdi mdi-account-off-outline" style="font-size: 1.8rem;"></i>
											<p class="mt-2 mb-0">No suspects captured.</p>
										</div>
									</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>

			{{-- Section 5: Exhibits --}}
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-package-variant-closed"></i> Exhibits</h6>
					<span class="text-muted" style="font-size: 0.8rem;">{{ $request->exhibits->count() }} exhibit(s)</span>
				</div>
				<div class="workflow-board-panel-body p-0">
					<div class="table-responsive">
						<table class="table table-hover mb-0 workflow-table">
							<thead>
								<tr>
									<th style="width: 80px;">S/N</th>
									<th style="width: 120px;"># Items</th>
									<th style="min-width: 320px;">Item Description</th>
									<th style="min-width: 220px;">Suspected Item</th>
								</tr>
							</thead>
							<tbody>
								@forelse($request->exhibits as $exhibit)
								<tr>
									<td class="text-muted fw-semibold">{{ $exhibit->serial_number ?? '—' }}</td>
									<td>{{ $exhibit->number_of_items ?? '—' }}</td>
									<td class="fw-semibold">{{ $exhibit->item_description ?: 'N/A' }}</td>
									<td>{{ $exhibit->suspected_item ?: '—' }}</td>
								</tr>
								@empty
								<tr>
									<td colspan="4">
										<div class="text-center py-4 workflow-empty-state">
											<i class="mdi mdi-package-variant" style="font-size: 1.8rem;"></i>
											<p class="mt-2 mb-0">No exhibits captured.</p>
										</div>
									</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>

			{{-- Section 6: Supporting Documents --}}
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-file-document-outline"></i> Supporting Documents</h6>
					<span class="text-muted" style="font-size: 0.8rem;">{{ $request->supportingDocumentInstances->count() }} document(s)</span>
				</div>
				<div class="workflow-board-panel-body p-0">
					@if($request->supportingDocumentInstances->count() === 0)
						<div class="text-center py-4 workflow-empty-state">
							<i class="mdi mdi-file-hidden" style="font-size: 1.8rem;"></i>
							<p class="mt-2 mb-0">No supporting documents for this request yet.</p>
						</div>
					@else
						<div class="table-responsive">
							<table class="table table-hover mb-0 workflow-table">
								<thead>
									<tr>
										<th style="width: 140px;">Code</th>
										<th>Title</th>
										<th style="width: 110px;">Status</th>
										<th style="width: 120px;" class="text-end">Action</th>
									</tr>
								</thead>
								<tbody>
									@foreach($request->supportingDocumentInstances as $docInstance)
										<tr>
											<td class="text-muted fw-semibold">{{ $docInstance->template?->document_code ?? '—' }}</td>
											<td class="fw-semibold">
												{{ $docInstance->template?->title ?? '—' }}
												@if($docInstance->template?->subtitle)
													<div class="text-muted" style="font-size: 0.78rem;">{{ $docInstance->template->subtitle }}</div>
												@endif
											</td>
											<td>
												<span class="workflow-status-chip" style="--chip-accent: {{ $docInstance->status === 'submitted' ? '#16a34a' : '#64748b' }};">
													{{ $docInstance->status }}
												</span>
											</td>
											<td class="text-end">
												<a class="btn btn-sm btn-outline-primary btn-action-sm" href="{{ route('sample-submission-requests.supporting-documents.edit', [$request, $docInstance]) }}">
													@if($docInstance->status === 'submitted')
														<i class="mdi mdi-eye-outline"></i> View
													@else
														<i class="mdi mdi-pencil-outline"></i> Fill
													@endif
												</a>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@endif
				</div>
			</div>

			{{-- Section 6B: Workflow Decision Forms --}}
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-file-document-multiple-outline"></i> Workflow Decision Forms</h6>
					<span class="text-muted" style="font-size: 0.8rem;">{{ $request->workflowForms->count() }} form(s)</span>
				</div>
				<div class="workflow-board-panel-body p-0">
					@if($request->workflowForms->count() === 0)
						<div class="text-center py-4 workflow-empty-state">
							<i class="mdi mdi-file-hidden" style="font-size: 1.8rem;"></i>
							<p class="mt-2 mb-0">No Laboratory Analysis Acceptance or Sample Rejection forms linked yet.</p>
						</div>
					@else
						<div class="table-responsive">
							<table class="table table-hover mb-0 workflow-table">
								<thead>
									<tr>
										<th style="min-width: 260px;">Form Type</th>
										<th style="width: 180px;">Reference</th>
										<th style="width: 180px;">Submitted</th>
										<th style="width: 140px;" class="text-end">Action</th>
									</tr>
								</thead>
								<tbody>
									@foreach($request->workflowForms as $workflowForm)
										<tr>
											<td class="fw-semibold">
												{{ $workflowForm->form_type === 'laboratory_analysis_acceptance' ? 'Laboratory Analysis Acceptance Form (GCLA/F/03)' : 'Sample Rejection Form (QARM/F/01)' }}
											</td>
											<td>{{ $workflowForm->request_reference ?: ($workflowForm->batch_code ?: '—') }}</td>
											<td>{{ optional($workflowForm->submitted_at)->format('Y-m-d H:i') ?: optional($workflowForm->created_at)->format('Y-m-d H:i') }}</td>
											<td class="text-end">
												@if($workflowForm->pdf_path)
													<a href="{{ $workflowForm->pdf_path }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary btn-action-sm">
														<i class="mdi mdi-file-pdf-box"></i> View PDF
													</a>
												@else
													<span class="text-muted small">PDF unavailable</span>
												@endif
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@endif
				</div>
			</div>

			{{-- Section 7: Submission & Reception (merged) --}}
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-swap-horizontal"></i> Submission &amp; Reception</h6>
				</div>
				<div class="workflow-board-panel-body">
					<div class="row" style="margin: 0;">
						<div class="col-12 col-lg-6" style="padding: 0 20px 0 0;">
							<div class="sr-sub-label">
								<i class="mdi mdi-account-check-outline"></i> Submitted By
							</div>
							<div class="sr-detail-grid">
								<div class="sr-detail-label">Full Name</div>
								<div class="sr-detail-value">{{ $request->submitted_by_full_name ?: 'N/A' }}</div>

								<div class="sr-detail-label">Title</div>
								<div class="sr-detail-value">{{ $request->submitted_by_title ?: 'N/A' }}</div>

								<div class="sr-detail-label">Signature</div>
								<div class="sr-detail-value">{{ $request->submitted_by_signature ?: 'N/A' }}</div>

								<div class="sr-detail-label">Date / Time</div>
								<div class="sr-detail-value">
									{{ optional($request->submitted_by_date)->format('Y-m-d') ?: 'N/A' }}
									{{ $request->submitted_by_time ? (' ' . $request->submitted_by_time) : '' }}
								</div>
							</div>
						</div>

						<div class="col-12 col-lg-6 sr-divider-col" style="padding: 0 0 0 20px;">
							<div class="sr-sub-label">
								<i class="mdi mdi-inbox-arrow-down-outline"></i> Received By
							</div>
							<div class="sr-detail-grid">
								<div class="sr-detail-label">Full Name</div>
								<div class="sr-detail-value">{{ $request->received_by_full_name ?: 'N/A' }}</div>

								<div class="sr-detail-label">Title</div>
								<div class="sr-detail-value">{{ $request->received_by_title ?: 'N/A' }}</div>

								<div class="sr-detail-label">Signature</div>
								<div class="sr-detail-value">{{ $request->received_by_signature ?: 'N/A' }}</div>

								<div class="sr-detail-label">Date / Time</div>
								<div class="sr-detail-value">
									{{ optional($request->received_by_date)->format('Y-m-d') ?: 'N/A' }}
									{{ $request->received_by_time ? (' ' . $request->received_by_time) : '' }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</main>
@endsection