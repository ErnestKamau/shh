@extends('layouts.lab.layout.app', [ 'datePicker' => true, 'select2' => true])

@section('title2')
<title>Create Submission Request | Sample WorkFlow</title>
@endsection

@section('content2')
<main class="container-fluid lab-panel-theme">
	@include('layouts.lab.partials.lab-panel-theme-styles')

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
			'link' => route('sample-submission-requests.create'),
			'name' => 'Add Submission Request',
			'icon' => null,
		],
	];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="row">
		<div class="col-12 col-xl-8">
			<div class="workflow-board-panel">
				<div class="workflow-board-panel-header">
					<h6><i class="mdi mdi-plus-circle-outline"></i> Add Submission Request</h6>
				</div>
				<div class="workflow-board-panel-body">
					<form method="POST" action="{{ route('sample-submission-requests.store') }}">
						@csrf

						<div class="row">
							<div class="col-md-6">
								<div class="form-group mb-3">
									<label class="form-label">Customer <span class="text-danger">*</span></label>
									<select name="crm_customer_id" class="form-control">
										<option value="">Select customer...</option>
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
									<select name="crm_contact_id" class="form-control">
										<option value="">Select contact...</option>
										@foreach($contacts as $contact)
											<option value="{{ $contact->id }}" {{ (string) old('crm_contact_id') === (string) $contact->id ? 'selected' : '' }}>
												{{ trim(($contact->first_name ?? '').' '.($contact->middle_name ?? '').' '.($contact->last_name ?? '')) }} ({{ $contact->email }})
											</option>
										@endforeach
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
									<label class="form-label">Submitting Officer</label>
									<input type="text" name="submitting_officer_full_name" class="form-control" value="{{ old('submitting_officer_full_name') }}">
									@error('submitting_officer_full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group mb-3">
									<label class="form-label">Email</label>
									<input type="email" name="email" class="form-control" value="{{ old('email') }}">
									@error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
							</div>
							<div class="col-md-6">
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
									<label class="form-label">Case No</label>
									<input type="text" name="case_no" class="form-control" value="{{ old('case_no') }}">
									@error('case_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group mb-3">
									<label class="form-label">Offence</label>
									<input type="text" name="offence" class="form-control" value="{{ old('offence') }}">
									@error('offence') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
							</div>
						</div>

						<div class="d-flex justify-content-end" style="gap: 8px;">
							<a class="btn btn-outline-secondary btn-action-sm" href="{{ route('sample-submission-requests.index') }}">
								<i class="mdi mdi-arrow-left"></i> Back
							</a>
							<button type="submit" class="btn btn-primary btn-action-sm">
								<i class="mdi mdi-content-save"></i> Save Request
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection

