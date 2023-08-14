@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>{{ $status }} | Sample WorkFlow</title>

<style>
	.form-part-toggler {
		margin: 0px 0px 5px 0px !important;
		padding: 6px 6px 6px 6px;
		border-bottom: 1px solid rgba(0, 0, 0, 0.09);
		cursor: pointer;
	}

	.form-part-toggler:hover {
		background-color: rgba(0, 0, 0, 0.08);
	}

	#sample-detail-rows .form-group {
		display: none;
	}

	#sample-detail-rows tr.selected-row {
		background-color: rgb(253, 220, 220);
	}

	#sample-detail-rows .text {
		display: unset;
	}

	#sample-detail-rows tr.editable .form-group {
		display: unset;
	}

	#sample-detail-rows tr.editable .text {
		display: none;
	}

	#sample-detail-rows tr {
		cursor: pointer;
	}

	.hidden {
		display: none;
	}

	.overdue-bg-color {
		background-color: rgba(240, 185, 83, 0.972) !important;
	}

	.upfront-bg-color {
		background-color: skyblue !important;
	}

	.ammend-bg-color {
		background-color: #fef764 !important;
	}
	.btn-white{
		background-color: white !important;
	}
</style>
@endsection
@section('content2')
<main>
	<?php
	$items = array(
		array(
			'link' => route('dashboard-lab'),
			'name' => 'Dashboard',
			'icon' => null
		),
		array(
			'link' => route('sample-workflow', ['status' => 'All Samples']),
			'name' => 'Sample Workflow',
			'icon' => null
		),
		array(
			'link' => route('sample-workflow', ['status' => $status]),
			'name' => $status,
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<span class="float-left"><i class="mdi mdi-file-document-edit"></i> Sample Workflow</span>
		<small> <i class="mdi mdi-circle-medium"></i> {{ $status }}</small>
		<div class="btn-group float-right">
			<button type="button" class="btn btn-sm btn-white dropdown-toggle" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;"  type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				Actions
			</button>
			<div class="dropdown-menu dropdown-menu-right">
				@if(isset($status) && in_array($status, array("Samples En-Route","Samples Request Review","Samples Reception","Samples In Lab")))
				<li>
					<span class="btn btn-sm dropdown-item initiate-interlab" data-toggle="modal" data-target="#inter-lab-add" data-action="bulk"><i class="mdi mdi-swap-horizontal-bold mr-2 text-warning" data-toggle="tooltip" title="Initiate inter Lab"></i> Intiate Inter Lab Transfer(s)</span>

				</li>
				@endif
				@if ($status=="Samples Reception")
			

				<li>
					<span class="btn btn-sm dropdown-item" data-toggle="modal" disabled data-target="#delete-batch">
						<i class="mdi mdi-delete-empty mr-2"></i> Cancel Batch
					</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" data-toggle="modal" disabled data-target="#move-to-lab">
						<i class="mdi mdi-swap-vertical mr-2"></i> Move to Lab
					</span>
				</li>
				
				<li>
					<span class="btn btn-sm dropdown-item" data-target="#print-labels-modal" data-toggle="modal"><i class="mdi mdi-printer mr-2"></i> Labels</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#dispatch-to-labs-modal" data-toggle="modal"><i class="mdi mdi-file-send mr-2"></i> Request Review</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#dispatch-to-labs-modal-approve" data-toggle="modal"><i class="mdi mdi-check-decagram mr-2"></i> Generate Invoice</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#approve-begin-process" data-toggle="modal"><i class="mdi mdi-checkbox-marked-circle-outline mr-2"></i> Approve For Analysis</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#dispatch-to-labs-modal-payment-reminder" data-toggle="modal" title="Dispatch Labeled"><i class="mdi mdi-bell-ring mr-2"></i> Payment Reminder</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#generarate_customer_focus" data-toggle="modal" title="Generate Customer Focus"><i class="mdi mdi-file-document-outline mr-2"></i> Generate Customer Focus</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" data-target="#clone-batches" data-toggle="modal"><i class="mdi mdi-content-duplicate mr-2"></i> Clone Batch(es)</span>
				</li>
				@endif

				@if ($status=="Reports for Collection")
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#send-email-reports-modal" data-toggle="modal" title="Email Report(s)"><i class="mdi mdi-email mr-2"></i> Email Report(s)</span>

				</li>
				@endif
				@if($status == "Samples Request Review")
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#dispatch-to-labs-modal-approve" data-toggle="modal"><i class="mdi mdi-check-decagram mr-2"></i> Generate Invoice</span>
				</li>
				<li>
					<span class="btn btn-sm dropdown-item" disabled data-target="#dispatch-to-labs-modal-review" data-toggle="modal" title="Approve Request"><i class="mdi mdi-clipboard-arrow-right mr-2"></i> Approve Request</span>
				</li>
				<li>
					` <span class="btn btn-sm dropdown-item" disabled data-target="#dispatch-to-labs-modal-review-reject" data-toggle="modal" title="Request Request">
						<i class="mdi mdi-clipboard-arrow-right mr-2"></i> Reject Request
					</span>
				</li>
				<li>

					<span class="btn btn-sm dropdown-item" data-target="#print-labels-modal" data-toggle="modal"><i class="mdi mdi-printer mr-2"></i>Print Labels</span>
				</li>
				@endif
				@if($status == "Samples In Lab")
				<li>

					<span class="btn btn-sm dropdown-item" data-target="#print-labels-modal" data-toggle="modal"><i class="mdi mdi-printe mr-2r"></i>Print Labels</span>
				</li>
				@endif

			</div>
		</div>
		@if ($status=="Samples Reception")
		<a class="btn btn-sm btn-info float-right mr-2" href="{{ route('view-batch-details', ['batch'=>time()]) }}"><i class="mdi mdi-plus mr-2"></i> Add Batch</a>
		
		@endif
		



	</h4>
	<div class="table-responsive bg-light p-4">
		<table class="table table-condensed my-small-text table-bordered table-sm">
			<thead>
				<th></th>
				<th>Priority</th>
				<th>Batch Code</th>
				@if(auth()->user()->CheckViewQcSample())
				<th>Is Qc</th>
				@endif
				
				<th>Sample Codes</th>
				<th>Lab Sections</th>
				
				<th>Stage</th>
				@if($status == 'Samples In Lab')
				@else
				<th>Client</th>
				@endif
				<th>Client / LPO Ref</th>
				<th nowrap>Receipt Date</th>
				<th nowrap>Date Collected</th>
				<th nowrap>Target Date</th>
				<th nowrap>Status Days</th>
				<th>Samples</th>
				@if($status != 'Samples In Lab')
				<th>Client Unit</th>
				@endif
				<th>Lab</th>
				<th nowrap>Sample Type</th>
				
				<th>Invoice Number</th>
				<th>Routine</th>
				<th>Routine Frequency</th>
				<th></th>
			</thead>
			<tbody>
				@foreach ($batches as $item)
				<?php
				if (isset($item->get_target_date->id)) {
					$target_date = date('Y-m-d', strtotime($item->get_target_date['date']));
					$now = Carbon\Carbon::now();
					$target_date = Carbon\Carbon::parse($target_date);

					$diff = $now->diffInDays($target_date);

					if ($target_date->greaterThan($now)) {
						$diff = 0 - $diff - 1;
					}
				} else {
					$target_date = "1970-01-01";
					$diff = 0;
				}
				$sample_codes = $item->samples->pluck('sample_code')->toArray();

				$sampleStart = $sample_codes[0] ?? '';

				$sample_count = count($sample_codes);
				$sampleEnd = end($sample_codes) ?? '';
				?>
				@if($item->current_account_status == 'Account Holder(Overdue)')
				<tr class="batch-row overdue-bg-color {{ $diff > 0 ? 'text-danger' : '' }} crm-customer-{{ $item->client->id }}" data-class="{{ $item->client->id }}">
					@elseif($item->current_account_status == 'Pay Upfront')
				<tr class="batch-row upfront-bg-color {{ $diff > 0 ? 'text-danger' : '' }} crm-customer-{{ $item->client->id }}" data-class="{{ $item->client->id }}">
					@elseif($item->in_ammendment_proccess)
				<tr class="batch-row ammend-bg-color {{ $diff > 0 ? 'text-danger' : '' }} crm-customer-{{ $item->client->id }}" data-class="{{ $item->client->id }}">
					@else
				<tr class="batch-row {{ $diff > 0 ? 'text-danger' : '' }} crm-customer-{{ $item->client->id }}" data-class="{{ $item->client->id }}">
					@endif
					<td><input type="checkbox" data-batch="{{json_encode($item)}}" value="{{ $item->batch_code }}" name="table_sample_id[]"></td>
					@if($item->in_ammendment_proccess)
					<td nowrap> <div class="badge badge-danger p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">To Amend</div> </td>
					@elseif($item->prelim_report_status == 1)
					<td nowrap> <div class="badge badge-info p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">Prelim</div> </td>
					@elseif($item->prelim_report_status == 2)
					<td nowrap> <div class="badge badge-info p-2" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">Draft</div> </td>
					@else
					<td nowrap>{!! $item->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $item->priority }}</td>

					@endif
					<td>
						
						<a href="{{ route('view-batch-details', ['batch'=>$item->id]) }}">{{ $item->batch_code }}</a>
						
					</td>
					@if(auth()->user()->CheckViewQcSample())
						<td>{!! $item->is_qc_batch == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline"></i></span>' : '-' !!}</td>
					@endif
					<td style="max-width: 200px !important;word-wrap:break-word;">
						{{$sampleStart.' - '.$sampleEnd}}</td>
					<td nowrap>{{$item->getLabSectionsNames()}}</td>
					<td style="min-width: 200px !important;">{{$item->status}}</td>
					@if($status == 'Samples In Lab')
					@else

					<td nowrap>{{ $item->client->name }}</td>
					@endif
					<td nowrap>{{ $item->reference_number ?? 'n/a' }}</td>
					<td nowrap>{{ date('Y-m-d', strtotime($item->receipt_date)) }}</td>
					<td nowrap>{{ date('Y-m-d', strtotime($item->date_collected)) }}</td>
					<td nowrap>{{ date('Y-m-d', strtotime($target_date)) }}</td>
					<td nowrap>{{ number_format($diff, 0) }} Day(s)</td>
					<td>{{ $sample_count }}</td>
					@if($status == 'Samples In Lab')
					<td nowrap>{{ $item->crm_unit_name }}</td>
					@endif
					<td nowrap>{{ implode(", ", $item->labs(true)) }}</td>
					<td nowrap>{{ $item->sample_type->name ?? '' }}</td>
					
					<?php $invoice = getInvoiceById($item->invoice_id) ?>
					@if(isset($invoice->id))
					<td nowrap><a href="/invoice/sample/{{$item->id}}">{{$invoice->invoice_number}}</a></td>

					@else
					<td>N/a</td>
					@endif

					<td>{{ $item->is_routine == 1 ? 'Yes' : 'No' }}</td>
					<td>{{ $item->is_routine == 1 ? number_format($item->routine_frequency,0).' days' : 'n/a' }}</td>
					<td><a href="{{ route('view-batch-details', ['batch'=>$item->id]) }}" data-target="#add-new-samples" class="btn btn-primary btn-sm edit-sample-details" data-header='{{ json_encode($item) }}'><i class="mdi mdi-lead-pencil"></i></a></td>
				</tr>
				@endforeach
			</tbody>
		</table>
		<div class="btn overdue-bg-color btn-sm"></div> Account Holder(Overdue) <br>
		<div class="btn upfront-bg-color btn-sm"></div> Account Pay Upfront <br>
		<div class="btn ammend-bg-color btn-sm"></div> Ammended Batch
	</div>
</main>
@endsection

@section('script2')
@if(isset($status) && in_array($status, array("Samples En-Route","Samples Request Review","Samples Reception","Samples In Lab")))
<div class="modal fade" id="inter-lab-add" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('create_sample_inter_lab_log')}}" method="post">
				@csrf  
				<div class="modal-body">
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px"></i>
						<span class="p-2">
							Initiate Interlab for all samples in the following batches below by providing the information below
						</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">To Lab</label>
						<select name="to_lab_section_id" id="" class="form-control">
							@foreach($labsections as $lab)
							<option value="{{$lab->id}}">{{$lab->code}} - {{$lab->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Quantity</label>
						<input type="text" name="quantity" value="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Expected Date</label>
						<input type="date" name="expected_date" value="" id="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Prelim Date</label>
						<input type="date" name="prelim_date" id="" class="form-control">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Remark</label>
						<textarea name="remarks" id="" cols="30" rows="5" class="form-control"></textarea>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Notify</label>
						<select name="notify_user" id="" class="form-control notify_user">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Notify</label>
						<select name="also_notify[]" multiple id="" class="form-control also_notify">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->name}}</option>
							@endforeach
						</select>
					</div>
					<input type="hidden" name="batch_level" value="1">
					<div class="form-group">
						<label class="control-label">Batches</label>
						<div class="selected-batches-interlab"></div>
					</div>
				</div>
				<div class="modal-footer">
					<Button type="submit" class="btn btn-outline-primary btn-sm submit-button"><i class="mdi mdi-swap-horizontal-bold"></i> Initiate</Button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Cancel</span>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
@if ($status=="Reports for Collection")
<div id="send-email-reports-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" id="print-labels-form" method="POST" action="{{ route('send-out-email-reports') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-printer"></i> Email Report(s) To Client </h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Client Contacts</label>
					<select class="form-control" name="contacts[]" required multiple placeholder="Select Contact..."></select>
				</div>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches"></div>
				</div>
				<div class="form-group">
					<label class="control-label">Email Body</label>
					<textarea name="email_body" class="form-control" placeholder="Email Body"></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info btn-sm send-report-to-client-btn" data-dismiss="modal"><i class="mdi mdi-send"></i> Email Reports</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@endif
@if ($status=="Samples Reception" || $status == "Samples Request Review")
<div id="dispatch-to-labs-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('change-batch-workflow') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Send Labeled Samples for Sample Request Review </h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="status" value="Samples Request Review" />
				<div id="not-paid-parent"></div>
				<div class="form-group">
					<div class="alert alert-callout alert-primary">
						<i class="fas fa-info-circle"></i> Are you sure you want to send labeled samples for <b>Sample Request Review</b>?
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches-request"></div>
				</div>

				<div class="form-check">
					<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
					<label class="form-check-label">
						Send Email Notification
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
					<label class="form-check-label">
						Send Message
					</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div class="modal fade" id="move-to-lab" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('moveToLab')}}" method="post">
				@csrf
				<div class="modal-body">
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
						<span class="p-2">Confirm you want to send the following batch(es) to Samples In Lab stage</span>
					</div>
					<div class="form-group">
						<label class="control-label">Batches</label>
						<div class="selected-batches-movetolab"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-outline-primary" type="submit"><i class="mdi mdi-thumb-up"></i> Yes, Send</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="dispatch-to-labs-modal-payment-reminder" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('send_payment_notification') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Send Payment Reminder</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="status" value="Samples Request Review" />
				<div class="form-group">
					<div class="alert alert-callout alert-primary">
						<i class="fas fa-info-circle"></i> By confirming this you will send a payment reminder email to all the clients of the following batches: </b>?
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches-request"></div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="dispatch-to-labs-modal-approve" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('generate_batch_invoice') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Generate Invoice</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="status" value="Samples Request Review" />
				<div class="form-group">
					<div class="alert alert-callout alert-primary">
						<i class="fas fa-info-circle"></i> By approving this you will generate an invoice with the following Batches</b>?
					</div>
				</div>

				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches-request-approve"></div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Generate</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="approve-begin-process" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('approve_batch_begin_process') }}" enctype="multipart/form-data">
			@csrf

			<div class="modal-body">
				<input type="hidden" name="status" value="Samples Request Review" />
				<div class="form-group">
					<div class="alert alert-callout alert-primary">
						<i class="fas fa-info-circle"></i> By clicking Approve, the following batches will proceed to Laboratory without payment!
					</div>
				</div>

				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches-request-approve"></div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Approve</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="print-labels-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" target="_blank" id="print-labels-form" method="POST" action="{{ route('print-labels') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-printer"></i> Print Labels </h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Label Size</label>
					<select class="form-control" name="label_size" required>
						<option value="small-label">Small</option>
						<option value="normal-label">Normal</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-samples">
						<div class="alert alert-callout alert-danger">
							<i class="fas fa-exclamation-triangle"></i> No batch selected.
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info btn-sm print-label-btn" data-dismiss="modal"><i class="mdi mdi-printer"></i> Print</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@endif
@if($status == 'Samples Reception')
<div class="modal fade"  id="generarate_customer_focus" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form target="_blank" action="{{route('generateCustomerFocusIndex',['batch_id'=>0])}}" method="get">
				
				<div class="modal-body">
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-alert-decagram" style="font-size: 30px"></i>
						<span class="p-2">Confirm you want to genarate a batched customer focus of the following batches below <br><br>
						<b>Kindly ensure all the batches are from the same client and the doesnot have an already signed customer focus</b></span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">Total Amount</label>
						<input type="text" name="invoice_amount" class="form-control" placeholder="Invoice Amount ...">
					</div>
					<div class="form-group">
						<label for="" class="control-label">VAT</label>
						<input type="text" name="vat" class="form-control" placeholder="Vat ...">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Amount Paid</label>
						<input type="text" name="amount_paid" class="form-control" placeholder="Amount Paid...">
					</div>
					<div class="form-group">
						<label for="" class="control-label">Balance</label>
						<input type="text" name="balance" class="form-control" placeholder="Balance ...">
					</div>
					<div class="form-group">
						<label class="control-label">Batch(es)</label>
						<div class="selected-batches-review"></div>
					</div>
					<input type="hidden" name="is_clustered" value="1">
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-thumb-up"></i> Yes, Generate</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="clone-batches" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('cloneBatchInformation')}}" method="post">
				@csrf
				<div class="modal-body">
					<div class="d-flex alert alert-primary">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px"></i>
						<span class="p-2">Confirm you want to duplicate the following batches below:</span>
					</div>
					
					<div class="form-group mt-3">
						<label class="control-label">Batches</label>
						<div class="selected-batches-clone"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-sm btn-outline-primary save-clone"><i class="mdi mdi-thumb-up"></i> Yes, Clone</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="delete-batch" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{route('delete-batch')}}" enctype="multipart/form-data">
			@csrf

			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-delete text-danger"></i> Cancel Batch(es) </h4>
			</div>
			<div class="modal-body>
				<input type=" hidden" name="status" value="Samples In Lab" />
			<p class="text-center">Are you sure you want to Cancel the following Batch(es) ? </p><br>
			<hr>
			<div class="form-group">
				<label class="control-label">Batch(es)</label>
				<div class="selected-batches-review"></div>
			</div>
	</div>


	<div class="modal-footer">
		<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
		<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
	</div>
	</form>
</div>
</div>
@endif
@if($status == 'Samples In Lab')
<div id="print-labels-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" target="_blank" id="print-labels-form" method="POST" action="{{ route('print-labels') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-printer"></i> Print Labels </h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Label Size</label>
					<select class="form-control" name="label_size" required>
						<option value="small-label">Small</option>
						<option value="normal-label">Normal</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-samples">
						<div class="alert alert-callout alert-danger">
							<i class="fas fa-exclamation-triangle"></i> No batch selected.
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info btn-sm print-label-btn" data-dismiss="modal"><i class="mdi mdi-printer"></i> Print</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@endif
@if($status=="Samples Request Review")
<div id="dispatch-to-labs-modal-review-reject" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('return_batch_reception') }}" enctype="multipart/form-data">
			@csrf

			<div class="modal-body">
				<input type="hidden" name="status" value="Samples Request Review" />
				<div class="form-group">
					<div class="alert alert-callout alert-danger">
						<i class="fas fa-info-circle"></i> Confirm you want to reject approval request for the following batche(s).
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Comments</label>
					<textarea class="form-control" name="comment" placeholder="Comments..."></textarea>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
					<label class="form-check-label">
						Send Email Notification
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
					<label class="form-check-label">
						Send SMS
					</label>
				</div>
				<br>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches-request-approve"></div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger btn-sm"><i class="mdi mdi-thumb-up"></i> Reject</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="dispatch-to-labs-modal-review" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('change-batch-workflow') }}" enctype="multipart/form-data">
			@csrf

			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request</h4>
			</div>
			<div class="modal-body">


				<input type="hidden" name="status" value="Samples In Lab" />
				<input type="hidden" name="tracking_stage" value="20008" />
				<input type="hidden" name="customer_id" value=0>
				<div class="form-group">
					<label class="control-label">Select Request Type</label>
					<select class="form-control" name="request_type_id[]" placeholder="Request Type..." multiple required>
						<option></option>
						<?php $requestTypes = getRequestTypes(); ?>
						@foreach ($requestTypes[1] as $i)
						<option value="{{ $i->id }}">{{ $i->name }}</option>
						@endforeach
						<option value="Other">Other Type</option>
					</select>
				</div>
				<div class="form-group other-reason hidden">
					<label class="control-label">Specify Other Request Type</label>
					<textarea class="form-control" name="other_type" placeholder="Specify Other Request Type..."></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Select Specific Specialist</label>
					<select class="form-control" name="specialist_analyst_id" placeholder="Specific Specialist..." required>
						<option></option>
						@foreach ($analysts as $i)
						<option value="{{ $i->id }}">{{ $i->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Approval Comments</label>
					<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" name="is_priority" value="High" /> Is High Prority</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
					<label class="form-check-label">
						Send Email Notification
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
					<label class="form-check-label">
						Send Message
					</label>
				</div>
				<br>
				<div class="form-group">
					<label class="control-label">Batches</label>
					<div class="selected-batches-review"></div>
				</div>
			</div>


			<div class="modal-footer">
				<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@endif
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script type="text/javascript">
	var selectedSampleIDs = [];
	var sampleAnalysisByType = [];
	var sampleCondtions = [];
	var defaultClass = '';
	var notPaid = [];
	$('#dispatch-to-labs-modal').on('show.bs.modal', function() {
		$('#not-paid-parent').empty();
		if (notPaid.length > 0) {
			var bodyNot = `<div class="alert alert-danger p-2">
							<span class="text-center"><i class="mdi mdi-alert-decagram"></i> The following Batch(es) have not been paid fully </span>
							
							
							<div class="row mt-3" id="NotPaidBatches">
							
							</div>
						</div>`;
			$('#not-paid-parent').append(bodyNot);
			$.each(notPaid, function(j, k) {
				var batch_body = `
					<div class="col-md-6 col-sm-6 col-lg-6"><i class="mdi mdi-chevron-right"></i> ${k}</div>
				`
				$('#NotPaidBatches').append(batch_body);
			})
		}
	})

	@if($status == "Samples Reception")
	$("input[name='table_sample_id[]']").on('change', function() {
		if ($("input[name='table_sample_id[]']:checked").length > 0) {
			$('[data-target="#delete-batch"]').removeAttr('disabled').addClass('btn-danger').removeClass('btn-outline-danger');
			$('[data-target="#inter-lab-add"]').removeAttr('disabled');
			$('[data-target="#move-to-lab"]').removeAttr('disabled');
			$('[data-target="#generarate_customer_focus"]').removeAttr('disabled');
			$('[data-target="#clone-batches"]').removeAttr('disabled')

			$('[data-target="#dispatch-to-labs-modal"]').removeAttr('disabled').addClass('btn-warning').removeClass('btn-outline-warning');
			$('[data-target="#dispatch-to-labs-modal-approve"]').removeAttr('disabled').addClass('btn-success').removeClass('btn-outline-success');
			$('[data-target="#dispatch-to-labs-modal-payment-reminder"]').removeAttr('disabled', true).removeClass('btn-outline-info').addClass('btn-info');
			$('[data-target = "#approve-begin-process"]').removeAttr('disabled').addClass('btn-outline-success').removeClass('btn-default');


		} else {
			$('[data-target="#delete-batch"]').attr('disabled', true).removeClass('btn-danger').addClass('btn-outline-danger');
			$('[data-target="#inter-lab-add"]').attr('disabled',true);
			$('[data-target="#move-to-lab"]').attr('disabled');
			$('[data-target="#generarate_customer_focus"]').attr('disabled');
			$('[data-target="#clone-batches"]').attr('disabled')


			$('[data-target="#dispatch-to-labs-modal"]').attr('disabled', true).removeClass('btn-warning').addClass('btn-outline-warning');
			$('[data-target = "#approve-begin-process"]').removeAttr('disabled').addClass('btn-default').removeClass('btn-outline-success');
			$('[data-target="#dispatch-to-labs-modal-approve"]').attr('disabled', true).removeClass('btn-success').addClass('btn-outline-success');
			$('[data-target="#dispatch-to-labs-modal-payment-reminder"]').attr('disabled', true).removeClass('btn-info').addClass('btn-outline-info');
		}
		$('.selected-batches-review').empty();
		$('.selected-batches-interlab').empty();
		$('.selected-batches-clone').empty();
		$('.selected-batches-movetolab').empty();
		$('.selected-batches-request').empty();
		$('.selected-batches-request-approve').empty();

		selectedBatchesIDs = $("input[name='table_sample_id[]']:checked")
			.map(function() {
				var $value = $(this).val();
				var batch = $(this).data('batch');

				if (batch.customer_paid == 0) {
					notPaid.push(batch.batch_code);
				}
				$('.selected-batches-review').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${ $value }"  checked >${ $value }
					</span>`
				);
				$('.selected-batches-interlab').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${ $value }"  checked >${ $value }
					</span>`
				);
				$('.selected-batches-clone').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" class="batch_clone" name="batch_code[]" value="${ $value }"  checked >${ $value }
					</span>`
				);
				$('.selected-batches-movetolab').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${ $value }"  checked >${ $value }
					</span>`
				)
				$('.selected-batches-request').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${$value}" checked>${$value}
					</span>
					`

				);
				$('.selected-batches-request-approve').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${$value}" checked>${$value}
					</span>
					`
				)
				return $value;
			}).get();



	});
	@endif
	@if($status == "Samples Request Review")
	$("input[name='table_sample_id[]']").on('change', function() {
		if ($("input[name='table_sample_id[]']:checked").length > 0) {
			$('[data-target="#dispatch-to-labs-modal-review"]').removeAttr('disabled').addClass('btn-primary').removeClass('btn-outline-primary');
			$('[data-target="#dispatch-to-labs-modal-approve"]').removeAttr('disabled').addClass('btn-success').removeClass('btn-outline-success');
			$('[data-target = "#dispatch-to-labs-modal-review-reject"]').removeAttr('disabled').addClass('btn-danger').removeClass('btn-outline-danger');
			var $custID = $(this).parents('tr').data('class');

			console.log($custID);
			$("input[name='customer_id']").val($custID);
		} else {
			$('[data-target="#dispatch-to-labs-modal-review-reject"]').attr('disabled', true).removeClass('btn-danger').addClass('btn-outline-danger');
			$('[data-target="#dispatch-to-labs-modal-review"]').attr('disabled', true).removeClass('btn-primary').addClass('btn-outline-primary');
			$('[data-target="#dispatch-to-labs-modal-approve"]').attr('disabled', true).removeClass('btn-success').addClass('btn-outline-success');
		}
		$('.selected-batches-review').empty();
		$('.selected-batches-request-approve').empty();

		selectedBatchesIDs = $("input[name='table_sample_id[]']:checked")
			.map(function() {
				var $value = $(this).val();

				$('.selected-batches-review').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${ $value }"  checked >${ $value }
					</span>`
				);
				$('.selected-batches-request-approve').append(
					`<span class="p-2 mr-2">
						<input type="checkbox" name="batch_code[]" value="${$value}" checked>${$value}
					</span>
					`
				)
				return $value;
			}).get();


	});
	@endif
	@if($status == "Reports for Collection")
	$("input[name='table_sample_id[]']").on('change', function() {
		if ($("input[name='table_sample_id[]']:checked").length > 0) {
			$('[data-target="#send-email-reports-modal"]').removeAttr('disabled').addClass('btn-primary').removeClass('btn-outline-primary');
			var custID = $(this).parents('tr').data('class');

			if (defaultClass == '') {
				$.ajax({
					url: '/get-customer-contacts/receive_report/' + custID,
					dataType: 'json',
					beforeSend: function() {
						$('select[name="contacts[]"]').empty();
					},
					success: function(js) {
						$.each(js, function(j, s) {
							$('select[name="contacts[]"]').append(`<option value="${s.id}">
										${s.first_name+' '+s.middle_name+' '+s.last_name} [${s.email}]
									</option>`)
						});
					}
				})
			}

			defaultClass = defaultClass == '' ? ".crm-customer-" + custID : defaultClass;
		} else {
			$('[data-target="#send-email-reports-modal"]').attr('disabled', true).removeClass('btn-primary').addClass('btn-outline-primary');
			defaultClass = '';
		}

		if (defaultClass == '') {
			$('tr.batch-row').find("input[name='table_sample_id[]']").removeAttr('disabled');
		} else {
			$('tr.batch-row').not(defaultClass).find("input[name='table_sample_id[]']").attr('disabled', true);
		}

		$('.selected-batches').empty();
		selectedSampleIDs = $("input[name='table_sample_id[]']:checked")
			.map(function() {
				var $val = $(this).val();
				var recordBatch = $(this).data('batch');
				$('.selected-batches').append(`
						<span class="p-2 mr-2">
							<input type="checkbox" name="sample_code[]" value="${ recordBatch.id }" checked> ${ $val }
						</span>
					`);
				return $val;
			}).get();
	});
	@else
	$("input[name='table_sample_id[]']").on('change', function() {
		$('.selected-samples').empty();
		selectedSampleIDs = $("input[name='table_sample_id[]']:checked")
			.map(function() {
				var $val = $(this).val();
				$('.selected-samples').append(`
						<span class="p-2 mr-2">
							<input type="checkbox" name="sample_code[]" value="${ $val }" checked> ${ $val }
						</span>
					`);
				return $val;
			}).get();
	});
	@endif

	$('.send-report-to-client-btn').on('click', function() {
		if ($(this).parents('form').find('.selected-batches').find('input[type="checkbox"]').length == 0) {
			alert("No batches selected");
		} else {
			$(this).parents('form').submit();
		}
	});

	$('.print-label-btn').on('click', function() {
		if ($(this).parents('form').find('.selected-samples').find('input[type="checkbox"]').length == 0) {
			alert("No batches selected");
		} else {
			$(this).parents('form').submit();
		}
	});

	var detectChange = function(ts) {
		var op = $(ts).children('option:selected');
		$('#client-unit-select').html('<option value="" selected>Select Organizational Unit...</option>');
		$('#client-unit-select').trigger('change');
		$.each(op.data('units'), function(i, e) {
			$('#client-unit-select').append('<option value="' + e.name + '">' + e.name + '</option>');
		});
	};

	$('[name="is_routine"]').on('change', function() {
		if ($(this).is(':checked')) {
			$('#routine_frequency').removeClass('hidden');
			$('[name="routine_frequency"]').prop('required');
			$('[name="routine_frequency"]').attr('required');
		} else {
			$('#routine_frequency').addClass('hidden');
			$('[name="routine_frequency"]').find("option:selected").removeAttr("selected");
			$('[name="routine_frequency"]').find("option:selected").removeProp("selected");
			$('[name="routine_frequency"]').removeAttr('required');
			$('[name="routine_frequency"]').removeProp('required');
		}
	});

	$('.form-part-toggler').on('click', function() {
		var parentSibling = $(this).parents('.form-part').siblings();

		var sibformPartSibling = parentSibling.find('.form-part-toggler');
		var siblingformrowData = parentSibling.find('.form-data-row');

		$(this).find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');
		sibformPartSibling.find('i').removeClass('fa-arrow-up').addClass('fa-arrow-down');

		$(this).parents('.form-part').find('.form-data-row').addClass('hidden');
		siblingformrowData.removeClass('hidden');
	});
	$('#clone-batches').on('show.bs.modal',(e)=>{
		$('#clone-batches').find('.save-clone').on('click',(e)=>{
			var batches_id = [];
			$.each($('#clone-batches').find('.batch_clone'),(i,obj)=>{
				batches_id.push($(obj).val());
			});

			console.log(batches_id)
		})
	})


	
</script>
@endsection