@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Approval Requests | Inventory Management</title>
@endsection
@section('content2')
  <main>
		<?php
			$stages = getRequisitionWorkflow();
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => "Approval Requests",
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
      <i class="mdi mdi-format-list-checks"></i> Approval Requests
		</h3>
		<div class="bg-light">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead>
						<tr>
							<th>#</th>
							<th nowrap>Code</th>
							<th nowrap>Type</th>
							<th nowrap>Priority</th>
							<th nowrap>Description</th>
							<th nowrap>Due Date</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($approvals as $a)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td nowrap>
									<a href="{{ route('view-request-details', ['stage'=>$a->request_type, 'id'=>$a->model_id]) }}">
										{{ $a->request_code }}
									</a>
								</td>
								<td nowrap>{{ $a->request_type }}</td>
								<td nowrap>{{ $a->priority }}</td>
								<td nowrap>{{ $a->description ?? 'No items set' }}</td>
								<td nowrap>{{ $a->due_date }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
  </main>
@endsection