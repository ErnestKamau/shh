@extends('layouts.personnel.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Audit Trails</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
          'name' => 'Personnel Management',
          'icon' => null
        ),
        array(
          'link' => route('server-side-audit_logs'),
          'name' => 'Audit Trails',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-file-search"></i> Audit
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
			<table id="audit-log-table" data-url="{{ route('server-side-audit_logs') }}"
				class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm server-side">
				<thead>
					<tr>
						<th>#</th>
						<th nowrap>User</th>
						<th nowrap>Email</th>
						<th nowrap>Event</th>
						<th nowrap>Entity</th>
						<th nowrap>Entity ID</th>
						<th nowrap>IP Address</th>
						<th nowrap>URL</th>
						<th nowrap>Date</th>
						<th nowrap>Changes</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
    </div>
  </main>
@endsection
@section('script2')
<div id="show-changes-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-alert-decagram"></i> Audit Changes</h4>
			</div>
			<div class="modal-body">
				<table class="table-condensed table table-sm table-banded table-hover table-xs table-bordered" id="audit-changes-table">
					<thead>
						<tr>
							<th>Field</th>
							<th nowrap>New</th>
							<th nowrap>Old</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>

	$(function(){

		$('#show-changes-modal').on('show.bs.modal', function(e){
			var auditID = $(e.relatedTarget).data('audit');
			$.ajax({
				url: '/server-side-audit_log/'+auditID+'/details',
				dataType: "json",
				beforeSend: function(){
					$('#audit-changes-table').find('tbody').html(`
						<tr>
							<td colspan="3">
								<div class="text-center p-3">
									<img src="/images/loading.gif" style="width: 100%" />
								</div>
							</td>
						</tr>
					`);
				},
				success: function(js){
					$('#audit-changes-table').find('tbody').empty();
					var columns = js.columns;
					var oldData = js.old;
					var newData = js.new;

					if(columns.length == 0){
						$('#audit-changes-table').find('tbody').append(`
							<tr style="color:#232323">
								<td colspan="3" class="text-center">
									<i class="mdi mdi-information"></i> No Data Available
								</td>
							</tr>
						`);
					}

					$.each(columns, function(i,c){
						$('#audit-changes-table').find('tbody').append(`
							<tr style="color:#232323">
								<th nowrap>${c}</th>
								<td nowrap>${newData[c] ? newData[c] : '-'}</td>
								<td nowrap>${oldData[c] ? newData[c] : '-'}</td>
							</tr>
						`);
					});
				}
			})
		});

		var $url = $('#audit-log-table').data('url');
		$('#audit-log-table').DataTable({
			lengthMenu: [[25, 50, 100, 500, 1000, -1], [25, 50, 100, 500, 1000, "All"]],
			dom: 'Blfrtip',
			buttons: [
				'copy', 'csv', 'excel', 'pdf', 'print'
			],
			columns: [
				{ data: "loop", "searchable": false },
				{ data: "name" },
				{ data: "email" },
				{ data: "event" },
				{ data: "entity" },
				{ data: "entity_id" },
				{ data: "ip_address" },
				{ data: "url" },
				{
					data: null,
					render: function(data, type, row){
						var dt = new Date(data.created_at);

						return dt.today()+" "+dt.timeNow();
					}
				},
				{
					data: null,
					className: "center",
					render: function ( data, type, row ) {
						$(row).find('td:eq(4)').attr('nowrap');
						$(row).find('td:eq(4)').prop('nowrap');
						return `<span class="btn btn-sm text-info btn-transparent" data-toggle="modal" data-audit="${data.id}" data-target="#show-changes-modal">
							<i class="mdi mdi-alert-decagram"></i> Changes
						</span>
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
</script>
@endsection