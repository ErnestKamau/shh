@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Approval Requests | Inventory Management</title>
@endsection
@section('content2')
  <main>
		@php
			$stages = getRequisitionWorkflow();
      $items = [
        ['link' => route('inventory-home'), 'name' => inventoryLabel('module_name', 'Inventory Management'), 'icon' => null],
        ['link' => null, 'name' => inventoryLabel('approval_requests', 'Approval Requests'), 'icon' => null],
      ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="batch-header-bar mb-3">
      <div class="batch-header-top">
        <div class="batch-title-group">
          <span class="batch-code-label">{{ inventoryLabel('approval_requests', 'Approval Requests') }}</span>
          <span class="batch-stage-pill">
            <i class="mdi mdi-draw"></i>
            {{ count($approvals) }} {{ inventoryLabel('pending_sign_off', 'Pending Sign-Off') }}
          </span>
        </div>
      </div>
    </div>

		<div class="workflow-board-panel">
			<div class="table-responsive p-0">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
					<thead>
						<tr>
							<th>#</th>
							<th nowrap>{{ inventoryLabel('code', 'Code') }}</th>
							<th nowrap>{{ inventoryLabel('type', 'Type') }}</th>
							<th nowrap>{{ inventoryLabel('priority', 'Priority') }}</th>
							<th nowrap>{{ inventoryLabel('description', 'Description') }}</th>
							<th nowrap>{{ inventoryLabel('due_date', 'Due Date') }}</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($approvals as $a)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td nowrap>
									<a class="font-weight-bold text-primary" href="{{ route('view-request-details', ['stage'=>$a->request_type, 'id'=>$a->model_id]) }}">
										<code>{{ $a->request_code }}</code>
									</a>
								</td>
								<td nowrap><span class="badge badge-light border font-weight-bold">{{ $a->request_type }}</span></td>
								<td nowrap>
									@php
										$pri = strtolower((string)$a->priority);
										$badgeClass = $pri === 'high' ? 'badge-danger' : ($pri === 'medium' ? 'badge-warning' : 'badge-info');
									@endphp
									<span class="badge {{ $badgeClass }} badge-pill px-2">{{ ucfirst($a->priority) }}</span>
								</td>
								<td nowrap>{{ $a->description ?? 'No items set' }}</td>
								<td nowrap class="text-muted"><i class="mdi mdi-calendar-clock mr-1"></i>{{ $a->due_date }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="text-center py-5 text-muted">
									<i class="mdi mdi-check-decagram-outline d-block mb-2" style="font-size: 2.25rem; opacity: 0.4;"></i>
									<h6>{{ inventoryLabel('no_pending_approvals', 'No Pending Approval Requests') }}</h6>
									<p class="small text-muted mb-0">{{ inventoryLabel('all_caught_up', 'You are all caught up on approvals.') }}</p>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
  </main>
@endsection