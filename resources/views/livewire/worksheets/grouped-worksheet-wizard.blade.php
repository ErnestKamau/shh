<div class="grouped-worksheet-wizard">
	@include('livewire.grouped-worksheets.partials.pipeline-capture-styles')

	@if($message)
		<div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} mb-3">{{ $message }}</div>
	@endif

	@if($isRunComplete)
		<div class="alert alert-success">
			<i class="mdi mdi-check-circle"></i>
			<strong>{{ $holder->name }}</strong> pipeline is complete for this batch.
		</div>
	@else
		{{-- ── Horizontal pipeline stage step bar ─────────────────────── --}}
		<div class="gw-pipeline-stepbar mb-4">
			<div class="gw-pipeline-stepbar-header mb-2">
				<strong class="small">{{ $holder->name }}</strong>
				<span class="text-muted small ms-1">— Pipeline stages</span>
			</div>
			<div class="gw-pipeline-steps">
				@foreach($items as $index => $item)
					@php
						$isVirtualStage = $item->getItemTypeEnum()->value === 'results_capture';
						if ($isVirtualStage) {
							$status = match (true) {
								$isRunComplete => 'completed',
								$run->current_item_index === $index => 'in_progress',
								$run->current_item_index > $index => 'completed',
								default => 'pending',
							};
						} else {
							$runItem = $runItems->firstWhere('grouped_worksheet_item_id', $item->id);
							$status = $runItem?->status?->value ?? 'pending';
						}
					@endphp
					<button type="button"
						class="gw-stage-pill {{ $run->current_item_index === $index ? 'gw-stage-pill--active' : '' }} {{ $status === 'completed' ? 'gw-stage-pill--completed' : '' }} {{ $status === 'skipped' ? 'gw-stage-pill--skipped' : '' }}"
						wire:click="goToStage({{ $index }})">
						<span class="gw-stage-pill-num">
							@if($status === 'completed')
								<i class="mdi mdi-check"></i>
							@elseif($status === 'skipped')
								<i class="mdi mdi-skip-next"></i>
							@elseif($status === 'in_progress')
								<i class="mdi mdi-progress-clock"></i>
							@else
								{{ $index + 1 }}
							@endif
						</span>
						<span class="gw-stage-pill-label">{{ $item->label }}</span>
					</button>
					@if(!$loop->last)
						<span class="gw-stage-connector"></span>
					@endif
				@endforeach
			</div>
		</div>

		{{-- ── Full-width capture area ─────────────────────────────────── --}}
		@if($currentItem && $capturePreview)
			@if($capturePreview['display_mode'] === 'empty')
				<div class="alert alert-warning">
					<i class="mdi mdi-alert-outline"></i> Linked worksheet not found for this stage.
				</div>
			@else
				<div class="gw-capture-preview">
					<div class="gw-capture-preview-banner">
						<i class="mdi mdi-clipboard-edit-outline"></i>
						<span>Capture data for this stage — steps match the grouped worksheet configuration.</span>
					</div>

					<div class="gw-capture-main-header">
						<div>
							<h5 class="mb-1">{{ $capturePreview['pipeline_label'] }}</h5>
							<div class="small text-muted">
								{{ $capturePreview['item_type_label'] }} — {{ $capturePreview['reference_name'] }}
								@if(!$capturePreview['is_required'])
									<span class="badge bg-light text-dark ms-1">Optional</span>
								@endif
							</div>
						</div>
						<div class="d-flex gap-2 flex-shrink-0">
							@if(!$currentItem->is_required && !$isVirtualResultsCapture)
								<button type="button" class="btn btn-outline-secondary btn-sm" wire:click="skipStage">Skip</button>
							@endif
							<button type="button" class="btn btn-success btn-sm" wire:click="completeStage">
								Complete stage <i class="mdi mdi-arrow-right"></i>
							</button>
						</div>
					</div>

					@if(!empty($capturePreview['context']))
						<div class="gw-capture-context">
							@foreach($capturePreview['context'] as $key => $value)
								@if($value)
									<span class="gw-capture-context-chip">
										<span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
										{{ $value }}
									</span>
								@endif
							@endforeach
						</div>
					@endif

					<div class="gw-capture-body gw-capture-body--live">
						@switch($currentItem->getItemTypeEnum()->value)
							@case('procedure')
								<livewire:worksheets.procedure-worksheet-manager
									lazy
									:batchId="$batch->id"
									:initialWorksheetId="$procedureWorksheetId"
									:groupedCaptureLayout="true"
									:key="'grouped-procedure-'.$procedureWorksheetId.'-'.$run->current_item_index"
								/>
								@break
							@case('formula')
								@if($formula)
									<div class="p-3">
										<livewire:worksheets.formula-worksheet
											:batch="$batch"
											:formula="$formula"
											:groupedWorksheetHolderId="$holder->id"
											:key="'grouped-formula-'.$formula->id"
										/>
									</div>
								@else
									<div class="p-3"><div class="alert alert-warning mb-0">Formula not found.</div></div>
								@endif
								@break
							@case('stage_header')
								<div wire:ignore wire:key="grouped-ms-{{ $currentItem->id }}" class="p-2">
									@include('worksheets.partials.method-sequences-jquery', [
										'batch' => $batch,
										'stageHeaders' => $stageHeaders,
										'stageHeadersPayload' => $stageHeadersPayload,
									])
								</div>
								@break
							@case('hybrid_worksheet')
								@if($hybridWorksheet)
									<div class="p-3">
										<livewire:worksheets.hybrid-worksheet-runner
											:batch="$batch"
											:hybridWorksheet="$hybridWorksheet"
											:key="'grouped-hybrid-'.$hybridWorksheet->id"
										/>
									</div>
								@endif
								@break
							@case('log_entry_worksheet')
								@if($logEntryWorksheetId)
									<div class="p-3">
										<livewire:worksheets.log-entry-worksheet-manager
											:batch="$batch"
											:worksheet-id="$logEntryWorksheetId"
											:key="'grouped-log-entry-'.$logEntryWorksheetId.'-'.$run->current_item_index"
										/>
									</div>
								@else
									<div class="p-3"><div class="alert alert-warning mb-0">Log entry worksheet not found.</div></div>
								@endif
								@break
							@case('results_capture')
								<livewire:worksheets.grouped-results-capture
									:batch="$batch"
									:holder="$holder"
									:key="'grouped-results-capture-'.$holder->id.'-'.$run->current_item_index"
								/>
								@break
						@endswitch
					</div>
				</div>
			@endif
		@endif
	@endif

	<style>
		/* ── Pipeline horizontal step bar ───────────────────────────── */
		.gw-pipeline-stepbar {
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 0.75rem;
			padding: 1rem 1.25rem;
			box-shadow: 0 1px 4px rgba(15,23,42,0.05);
		}

		.gw-pipeline-stepbar-header {
			line-height: 1.2;
		}

		.gw-pipeline-steps {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 0;
			margin-top: 0.5rem;
		}

		.gw-stage-pill {
			display: inline-flex;
			align-items: center;
			gap: 0.4rem;
			padding: 0.4rem 0.85rem;
			border-radius: 20px;
			border: 2px solid #dee2e6;
			background: #fff;
			color: #6c757d;
			font-size: 0.8rem;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.2s ease;
			white-space: nowrap;
		}

		.gw-stage-pill:hover {
			border-color: #3b82f6;
			color: #3b82f6;
		}

		.gw-stage-pill--active {
			border-color: #3b82f6;
			background: #3b82f6;
			color: #fff;
		}

		.gw-stage-pill--active:hover {
			border-color: #2563eb;
			background: #2563eb;
			color: #fff;
		}

		.gw-stage-pill--completed {
			border-color: #28a745;
			background: #d4edda;
			color: #155724;
		}

		.gw-stage-pill--skipped {
			border-color: #adb5bd;
			background: #f8f9fa;
			color: #6c757d;
			opacity: 0.75;
		}

		.gw-stage-pill-num {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 1.3rem;
			height: 1.3rem;
			border-radius: 50%;
			background: rgba(0,0,0,0.08);
			font-size: 0.7rem;
			font-weight: 700;
			flex-shrink: 0;
		}

		.gw-stage-pill--active .gw-stage-pill-num {
			background: rgba(255,255,255,0.25);
		}

		.gw-stage-pill--completed .gw-stage-pill-num {
			background: #28a745;
			color: #fff;
		}

		.gw-stage-connector {
			flex-shrink: 0;
			width: 20px;
			height: 2px;
			background: #dee2e6;
			display: inline-block;
		}
	</style>
</div>
