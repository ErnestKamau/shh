<div class="grouped-worksheet-wizard">
	@if($message)
		<div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} mb-3">{{ $message }}</div>
	@endif

	@if($isRunComplete)
		<div class="alert alert-success">
			<i class="mdi mdi-check-circle"></i>
			<strong>{{ $holder->name }}</strong> pipeline is complete for this batch.
		</div>
	@else
		<div class="row">
			<div class="col-md-3">
				<div class="card border-0 shadow-sm">
					<div class="card-header bg-white">
						<strong>{{ $holder->name }}</strong>
						<div class="small text-muted">Stages</div>
					</div>
					<div class="list-group list-group-flush">
						@foreach($items as $index => $item)
							@php
								$runItem = $runItems->firstWhere('grouped_worksheet_item_id', $item->id);
								$status = $runItem?->status?->value ?? 'pending';
								$icon = match($status) {
									'completed' => 'mdi-check-circle text-success',
									'in_progress' => 'mdi-progress-clock text-primary',
									'skipped' => 'mdi-skip-next text-muted',
									default => 'mdi-circle-outline text-muted',
								};
							@endphp
							<button type="button"
								class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $run->current_item_index === $index ? 'active' : '' }}"
								wire:click="goToStage({{ $index }})">
								<i class="mdi {{ $icon }}"></i>
								<span class="small">{{ $item->sort_order }}. {{ $item->label }}</span>
							</button>
						@endforeach
					</div>
				</div>
			</div>
			<div class="col-md-9">
				@if($currentItem)
					<div class="card border-0 shadow-sm mb-3">
						<div class="card-header bg-white d-flex justify-content-between align-items-center">
							<div>
								<h5 class="mb-0">{{ $currentItem->label }}</h5>
								<small class="text-muted">{{ $currentItem->getItemTypeEnum()->label() }} — {{ $currentItem->referenceName() }}</small>
							</div>
							<div class="btn-group">
								@if(!$currentItem->is_required)
									<button type="button" class="btn btn-outline-secondary btn-sm" wire:click="skipStage">Skip</button>
								@endif
								<button type="button" class="btn btn-success btn-sm" wire:click="completeStage">
									Complete stage <i class="mdi mdi-arrow-right"></i>
								</button>
							</div>
						</div>
						<div class="card-body">
							@switch($currentItem->getItemTypeEnum()->value)
								@case('formula')
									@if($formula)
										<livewire:worksheets.formula-worksheet
											:batch="$batch"
											:formula="$formula"
											:key="'grouped-formula-'.$formula->id"
										/>
									@else
										<div class="alert alert-warning">Formula not found.</div>
									@endif
									@break
								@case('procedure')
									<livewire:worksheets.procedure-worksheet-manager
										:batchId="$batch->id"
										:initialWorksheetId="$procedureWorksheetId"
										:key="'grouped-procedure-'.$procedureWorksheetId"
									/>
									@break
								@case('stage_header')
									<div wire:ignore wire:key="grouped-ms-{{ $currentItem->id }}">
										@include('worksheets.partials.method-sequences-jquery', [
											'batch' => $batch,
											'stageHeaders' => $stageHeaders,
											'stageHeadersPayload' => $stageHeadersPayload,
										])
									</div>
									@break
								@case('hybrid_worksheet')
									@if($hybridWorksheet)
										<livewire:worksheets.hybrid-worksheet-runner
											:batch="$batch"
											:hybridWorksheet="$hybridWorksheet"
											:key="'grouped-hybrid-'.$hybridWorksheet->id"
										/>
									@endif
									@break
							@endswitch
						</div>
					</div>
				@endif
			</div>
		</div>
	@endif
</div>

