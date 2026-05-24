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
		<div class="row g-4">
			<div class="col-lg-4">
				<p class="small text-muted mb-3">Pipeline stages for this batch. Select a stage to capture worksheet data.</p>
				<div class="card border-0 shadow-sm grouped-wizard-pipeline-nav">
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

			<div class="col-lg-8">
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

							<div class="row g-0 gw-capture-layout">
								<div class="col-md-3 gw-capture-sidebar">
									<div class="gw-capture-sidebar-head">
										<strong class="d-block text-truncate">{{ $capturePreview['pipeline_label'] }}</strong>
										<span class="small text-muted">Worksheet steps</span>
									</div>
									<div class="list-group list-group-flush gw-capture-nav">
										@foreach($capturePreview['sidebar_stages'] as $nav)
											<div class="list-group-item gw-capture-nav-item">
												<span class="gw-capture-nav-order">{{ $nav['order'] }}</span>
												<span class="small text-truncate">{{ $nav['title'] }}</span>
											</div>
										@endforeach
										@if(count($capturePreview['sidebar_stages']) === 0)
											<div class="list-group-item text-muted small">No steps configured</div>
										@endif
									</div>
								</div>

								<div class="col-md-9">
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
											@if(!$currentItem->is_required)
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
														@if(!empty($capturePreview['steps']))
															<div class="gw-inner-timeline mb-4">
																@foreach($capturePreview['steps'] as $step)
																	@include('livewire.grouped-worksheets.partials.capture-preview-step', ['step' => $step])
																@endforeach
															</div>
														@endif
														<livewire:worksheets.formula-worksheet
															:batch="$batch"
															:formula="$formula"
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
										@endswitch
									</div>
								</div>
							</div>
						</div>
					@endif
				@endif
			</div>
		</div>
	@endif
</div>
