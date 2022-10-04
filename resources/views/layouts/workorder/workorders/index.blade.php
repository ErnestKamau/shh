@extends('layouts.workorder.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Work Orders | Work Order Management</title>
<style>
	#eventsCalendar{
		max-width:100%;
	}
</style>
@endsection
@section('content2')
<main>
	<?php
	$items = array(
		array(
			'link' => route('workorder-home'),
			'name' => 'Work Order Management',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => 'Work Orders',
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h2 class="p-1 pr-4 pl-4">
		<i class="mdi mdi-briefcase-outline"></i> Work Orders
		<div class="btn-group btn-sm">
			<button class="btn btn-primary active tgls" data-div='#table-view'><i class="mdi mdi-table"></i></button>
			<button class="btn btn-primary tgls" data-div='#calendar-view'><i class="mdi mdi-calendar-month"></i></button>
		</div>
		<a class="btn btn-transparent text-info btn-sm float-right" href="{{ route('create-work-order') }}">
			<i class="fas fa-file-medical"></i> New Work Order
		</a>
	</h2>
	<br>
	<div class="row pl-4 pr-4 no-gutters">
		<div class="col-xl-3 col-sm-6 pr-1">
			<div class="card bg-primary text-white h-100 no-overflow">
				<div class="card-body bg-primary">
					<div class="rotate">
						<i class="mdi mdi-briefcase fa-4x"></i>
					</div>
					<h6 class="text-uppercase">ALL</h6>
					<h1 class="display-4">{{ number_format($orders['ALL']) }}</h1>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-sm-6 pr-1 pl-1">
			<div class="card text-white bg-warning h-100 no-overflow">
				<div class="card-body bg-warning">
					<div class="rotate">
						<i class="mdi mdi-briefcase-outline fa-4x"></i>
					</div>
					<h6 class="text-uppercase">PENDING</h6>
					<h1 class="display-4">{{ number_format($orders['ACCEPTED']) }}</h1>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-sm-6 pr-1 pl-1">
			<div class="card text-white bg-info h-100 no-overflow">
				<div class="card-body bg-info">
					<div class="rotate">
						<i class="mdi mdi-briefcase fa-4x"></i>
					</div>
					<h6 class="text-uppercase">IN PROGRESS</h6>
					<h1 class="display-4">{{ number_format($orders['ASSIGNED']) }}</h1>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-sm-6 pl-1">
			<div class="card text-white bg-success h-100 no-overflow">
				<div class="card-body">
					<div class="rotate">
						<i class="mdi mdi-briefcase-outline fa-4x"></i>
					</div>
					<h6 class="text-uppercase">COMPLETED</h6>
					<h1 class="display-4">{{ number_format($orders['COMPLETED']) }}</h1>
				</div>
			</div>
		</div>
	</div>
	<br>
	<div class="p-2 pl-4 pr-4">
		<div id="table-view" class="tab-data">
			<h4><i class="mdi mdi-view-list"></i> Work Orders</h4>
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" style="font-size: 14px" id="Workorders-tabs" role="tablist">
						@foreach ($orders as $k=>$v)
							<li class="nav-item">
								<a class="nav-link {{ $loop->iteration == 1 ? 'active' : '' }}" id="{{ $k }}-tab" data-toggle="tab" href="#{{ $k }}" role="tab" aria-controls="{{ $k }}" aria-selected="true">
									{{ $k }} <small class="badge badge-pill badge-secondary">{{ number_format($v) }}</small>
								</a>
							</li>
						@endforeach
					</ul>
				</div>
				<div class="tab-content" id="Workorders-tabs-content">
					@foreach ($orders as $k=>$v)
						<div class="tab-pane fade p-3 {{ $loop->iteration == 1 ? 'show active' : '' }}" id="{{ $k }}" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3 mt-1"><i class="mdi mdi-view-list"></i> {{ $k }}</h5>
							<div class="table-responsive">
								<table style="min-width: 100%" data-status="{{ $k }}" data-url="{{ $k=="ALL" ? route('server-side-work-order') : route('server-side-work-order', ['status'=>$k]) }}"
									class="server-side status-table table table-condensed my-small-text table-striped table-hover table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Work Order No</th>
											<th>Department</th>
											<th>Topology</th>
											<th>Service</th>
											<th>Priority</th>
											<th>Start Date</th>
											<th>Due Date</th>
											<th nowrap>Creation Date</th>
											<th>Resources</th>
											<th>Status</th>
											<th>Created By</th>
											<th class="notexport">&nbsp;</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
						@endforeach
				</div>
			</div>
		</div>
		<div id="calendar-view" class="tab-data hidden">
			<h4><i class="mdi mdi-calendar"></i> Calendar</h4>
			<div id="eventsCalendar" data-events="{{ json_encode($calendardata) }}"></div>
		</div>
	</div>
@endsection
@section('script2')
	<div id="resource-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<link rel="stylesheet" href="/fullcalendar/main.min.css">
	<script src="/fullcalendar/main.min.js"></script>
	<script>
		$(function(){
			$('.tgls').on('click', function(){
				var target = $(this).data('div');
				$(this).addClass('active').siblings().removeClass('active');
				$(target).removeClass('hidden').siblings('.tab-data').addClass('hidden');
				if(target.indexOf('calendar') > -1){
					$('#eventsCalendar').data('events')

					var calendarEl = document.getElementById('eventsCalendar');

					var calendar = new FullCalendar.Calendar(calendarEl, {
						initialView: 'dayGridMonth',
						initialDate: '2021-03-07',
						headerToolbar: {
							left: 'prev,next today',
							center: 'title',
							right: 'dayGridMonth,timeGridWeek,timeGridDay'
						},
						events: $('#eventsCalendar').data('events')
					});
					calendar.render();
				}
			});

			$('.status-table').each(function(i, tb){
				var $url = $(tb).data('url');
				var serverTable = $(tb).DataTable({
					lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
					dom: 'Blfrtip',
					buttons: [
						'copy', 'csv', 'excel', 'pdf', 'print'
					],
					columns: [
						{ data: "loop", "searchable": false },
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								return `
									<a class="btn btn-xs text-primary btn-transparent" href='/workorder/${data.id}'>${data.ticket_no}</a>
								`;
							}
						},
						{ data: "department" },
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								var site = data.site.split('>>');

								$(row).find('td:eq(3)').attr('nowrap', true);
								$(row).find('td:eq(3)').prop('nowrap', true);
								return `<small class="">${$.trim(site[1])}</small>`;
							}
						},
						{ data: "service_type" },
						{ data: "priority" },
						{ data: "start_date" },
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								var date = new Date(data.due_date);
								var now = new Date();

								var overdue = date < now;

								$(row).addClass('text-danger');

								return `${data.due_date} ${overdue ? `<span class="badge badge-pill badge-danger">OVERDUE</span>` : ''}`;
							}
						},
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								return timeConverter(data.created_at);
							}
						},
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								return `
								<div class="btn-group">
									<a class="btn btn-xs text-primary btn-transparent" data-id="${data.id}" data-type="personnel" data-target="#resource-modal" data-toggle="modal">
										<i class="mdi mdi-account-group"></i>
									</a>
									<a class="btn btn-xs text-suucess btn-transparent" data-id="${data.id}" data-type="inventory" data-target="#resource-modal" data-toggle="modal">
										<i class="mdi mdi-package-variant"></i>
									</a>
								</div>

								`;
							}
						},
						{ data: "current_status" },
						{ data: "created_by" },
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								return `
									<a class="btn btn-xs text-primary btn-transparent" href='/workorder/${data.id}'><i class="mdi mdi-eye"></i></a>
								`;
							}
						}
					],
					destroy: true,
					processing: true,
					serverSide: true,
					ajax: $url
				});
			});

			$('#resource-modal').on('show.bs.modal', function(e){
				var workorder_id = $(e.relatedTarget).data('id');
				var typ = $(e.relatedTarget).data('type');

				$(this).find('.modal-title').html(
					`<span> ${typ=="personnel" ? '<i class="mdi mdi-account-group"></i> Personnel Resources' :
					'<i class="mdi mdi-package-variant"></i> Inventory Resources' } </span>`
				);

				var $table = $(`<table class="table table-condensed my-small-text table-striped table-hover table-bordered">
					<thead>
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>${typ=="personnel" ? "Email" : "Quantity"}</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>`);

				var modalBody = $(this).find('.modal-body');

				$.ajax({
					url: "/workorder_resources/"+workorder_id,
					dataType: "json",
					beforeSend: function(){
						modalBody.html(`<div class="text-center pt-4 pb-4">
							<i class="fas fa-spin fa-spinner"></i> Loading Data...
						</div>`);
					},
					success: function(js){
						var dt = js[typ];

						if(dt==null || dt.length == 0){
							$table.find('tbody').append(`
								<tr>
									<td colspan="3" class="text-center"><i class="mdi mdi-information"></i> No ${typ} data available.</td>
								</tr>
							`);
						}

						$.each(dt, function(i, d){
							$table.find('tbody').append(`
								<tr>
									<td>${i+1}</td>
									<td>${d.name}</td>
									<td>${typ=="personnel" ? d.email : d.quantity.toFixed(2)}</td>
								</tr>
							`);
						});
						modalBody.html($table);
					}
				})
			});

			@if(in_array($wo_type, ["REQUEST", "REQUEST_REJECTION"]))
				$('.nav-link[href="#{{ $wo_type }}"]').trigger('click');
				console.log('{{ $wo_type }}');
			@endif
		});
	</script>
@endsection