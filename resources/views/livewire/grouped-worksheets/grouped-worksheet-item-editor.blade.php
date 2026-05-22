<div class="container-fluid px-0 worksheet-engine-tag-select">
@include('layouts.lab.partials.worksheet-engine-tag-select-styles')
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-folder-multiple-outline"></i> {{ $holder->name }}</h5>
			<button type="button" wire:click="openAddModal" class="btn btn-primary btn-sm btn-action-sm">
				<i class="mdi mdi-plus"></i> Add stage
			</button>
		</div>
		<div class="workflow-board-panel-body flush-top">
			<p class="text-muted mb-0" style="font-size: 0.9rem;">{{ $holder->description }}</p>
		</div>
	</div>

	@if($message)
		<div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3">
			{{ $message }}
			<button type="button" class="btn-close" wire:click="$set('message', '')"></button>
		</div>
	@endif

	<div class="workflow-board-panel gw-pipeline-panel">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-timeline-clock-outline"></i> Pipeline timeline &amp; capture preview</h5>
		</div>
		<div class="workflow-board-panel-body">
			@if($items->isEmpty())
				<p class="text-muted text-center py-4 mb-0">No stages yet. Add procedure, formula, method sequence, or hybrid worksheets in order.</p>
			@else
				<div class="row g-4">
					<div class="col-lg-4">
						<p class="small text-muted mb-3">Order analysts follow during sample capture. Click a stage to preview its worksheet.</p>
						<div class="gw-pipeline-timeline">
							@foreach($items as $item)
								@php
									$isSelected = $selectedItemId === $item->id;
									$typeIcon = match($item->getItemTypeEnum()->value) {
										'formula' => 'mdi-function',
										'procedure' => 'mdi-clipboard-text-outline',
										'stage_header' => 'mdi-chart-timeline-variant',
										'hybrid_worksheet' => 'mdi-view-dashboard-outline',
										default => 'mdi-file-document-outline',
									};
								@endphp
								<div class="gw-pipeline-timeline-item {{ $isSelected ? 'is-selected' : '' }}" wire:key="pipeline-{{ $item->id }}">
									<button
										type="button"
										class="gw-pipeline-timeline-node"
										wire:click="selectPipelineStage(@js($item->id))"
									>
										<span class="gw-pipeline-timeline-badge">{{ $item->sort_order }}</span>
									</button>
									<div class="gw-pipeline-timeline-card">
										<button
											type="button"
											class="gw-pipeline-timeline-select"
											wire:click="selectPipelineStage(@js($item->id))"
										>
											<div class="d-flex align-items-start gap-2">
												<span class="gw-pipeline-type-icon"><i class="mdi {{ $typeIcon }}"></i></span>
												<div class="text-start flex-grow-1 min-w-0">
													<div class="fw-semibold text-truncate">{{ $item->label }}</div>
													<div class="small text-muted">
														{{ $item->getItemTypeEnum()->label() }}
													</div>
													<div class="small text-muted text-truncate">{{ $item->referenceName() }}</div>
													@if(!$item->is_required)
														<span class="gw-pipeline-optional">Optional</span>
													@endif
												</div>
											</div>
										</button>
										<div class="gw-pipeline-timeline-actions btn-group btn-group-sm">
											<button type="button" class="btn btn-outline-secondary" wire:click="moveUp(@js($item->id))" title="Move up"><i class="mdi mdi-chevron-up"></i></button>
											<button type="button" class="btn btn-outline-secondary" wire:click="moveDown(@js($item->id))" title="Move down"><i class="mdi mdi-chevron-down"></i></button>
											<button type="button" class="btn btn-outline-primary" wire:click="openEditModal(@js($item->id))" title="Edit"><i class="mdi mdi-pencil"></i></button>
											<button type="button" class="btn btn-outline-danger" wire:click="deleteItem(@js($item->id))" onclick="return confirm('Remove this stage?')" title="Delete"><i class="mdi mdi-delete"></i></button>
										</div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
					<div class="col-lg-8">
						@include('livewire.grouped-worksheets.partials.capture-preview', ['capturePreview' => $capturePreview])
					</div>
				</div>
			@endif
		</div>
	</div>

	<style>
		.gw-pipeline-timeline {
			position: relative;
			padding-left: 1.75rem;
		}

		.gw-pipeline-timeline::before {
			content: '';
			position: absolute;
			left: 0.65rem;
			top: 0.5rem;
			bottom: 0.5rem;
			width: 2px;
			background: linear-gradient(180deg, #3b82f6 0%, #e2e8f0 100%);
			border-radius: 2px;
		}

		.gw-pipeline-timeline-item {
			position: relative;
			display: flex;
			gap: 0.75rem;
			margin-bottom: 1rem;
		}

		.gw-pipeline-timeline-item:last-child {
			margin-bottom: 0;
		}

		.gw-pipeline-timeline-node {
			position: absolute;
			left: -1.75rem;
			top: 0.85rem;
			width: 1.35rem;
			height: 1.35rem;
			padding: 0;
			border: 2px solid #fff;
			border-radius: 50%;
			background: #e2e8f0;
			box-shadow: 0 0 0 2px #e2e8f0;
			cursor: pointer;
			z-index: 2;
			transition: all 0.2s ease;
		}

		.gw-pipeline-timeline-item.is-selected .gw-pipeline-timeline-node {
			background: linear-gradient(135deg, #3b82f6, #2563eb);
			box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
		}

		.gw-pipeline-timeline-badge {
			display: none;
		}

		.gw-pipeline-timeline-card {
			flex: 1;
			min-width: 0;
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 0.65rem;
			overflow: hidden;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.gw-pipeline-timeline-item.is-selected .gw-pipeline-timeline-card {
			border-color: #93c5fd;
			box-shadow: 0 4px 14px rgba(59, 130, 246, 0.12);
		}

		.gw-pipeline-timeline-select {
			display: block;
			width: 100%;
			padding: 0.75rem 0.85rem;
			border: 0;
			background: transparent;
			text-align: left;
			cursor: pointer;
		}

		.gw-pipeline-timeline-select:hover {
			background: #f8fafc;
		}

		.gw-pipeline-type-icon {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 2rem;
			height: 2rem;
			border-radius: 0.5rem;
			background: #eff6ff;
			color: #2563eb;
			flex-shrink: 0;
		}

		.gw-pipeline-optional {
			display: inline-block;
			margin-top: 0.25rem;
			font-size: 0.65rem;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			color: #64748b;
			background: #f1f5f9;
			padding: 0.15rem 0.4rem;
			border-radius: 999px;
		}

		.gw-pipeline-timeline-actions {
			display: flex;
			justify-content: flex-end;
			padding: 0 0.5rem 0.5rem;
			gap: 0.15rem;
		}

		.gw-capture-preview {
			border: 1px solid #e2e8f0;
			border-radius: 0.75rem;
			overflow: hidden;
			background: #fff;
			box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
		}

		.gw-capture-preview-banner {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.55rem 1rem;
			background: linear-gradient(90deg, #eff6ff, #f8fafc);
			border-bottom: 1px solid #e2e8f0;
			font-size: 0.8rem;
			color: #475569;
		}

		.gw-capture-layout {
			min-height: 320px;
		}

		.gw-capture-sidebar {
			background: #f8fafc;
			border-right: 1px solid #e2e8f0;
		}

		.gw-capture-sidebar-head {
			padding: 0.85rem 1rem;
			border-bottom: 1px solid #e2e8f0;
		}

		.gw-capture-nav-item {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.55rem 1rem;
			border: 0;
			background: transparent;
			font-size: 0.8rem;
		}

		.gw-capture-nav-item.active {
			background: #fff;
			color: #2563eb;
			font-weight: 600;
			border-left: 3px solid #3b82f6;
		}

		.gw-capture-nav-order {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 1.25rem;
			height: 1.25rem;
			border-radius: 999px;
			background: #e2e8f0;
			font-size: 0.7rem;
			font-weight: 700;
		}

		.gw-capture-nav-item.active .gw-capture-nav-order {
			background: #3b82f6;
			color: #fff;
		}

		.gw-capture-main-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 1rem;
			padding: 1rem 1.15rem;
			border-bottom: 1px solid #e2e8f0;
			background: #fff;
		}

		.gw-capture-context {
			display: flex;
			flex-wrap: wrap;
			gap: 0.4rem;
			padding: 0.65rem 1.15rem;
			background: #f8fafc;
			border-bottom: 1px solid #e2e8f0;
		}

		.gw-capture-context-chip {
			font-size: 0.75rem;
			padding: 0.2rem 0.55rem;
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 999px;
		}

		.gw-capture-body {
			padding: 1rem 1.15rem 1.25rem;
			max-height: 480px;
			overflow-y: auto;
		}

		.gw-inner-timeline {
			position: relative;
			padding-left: 2.5rem;
		}

		.gw-inner-timeline::before {
			content: '';
			position: absolute;
			left: 0.9rem;
			top: 0.5rem;
			bottom: 0.5rem;
			width: 2px;
			background: #e2e8f0;
		}

		.gw-inner-timeline-item {
			position: relative;
			margin-bottom: 1rem;
		}

		.gw-inner-timeline-item:last-child {
			margin-bottom: 0;
		}

		.gw-inner-timeline-marker {
			position: absolute;
			left: -2.5rem;
			top: 0.65rem;
			width: 1.75rem;
			height: 1.75rem;
			border-radius: 50%;
			background: linear-gradient(135deg, #64748b, #94a3b8);
			color: #fff;
			font-size: 0.75rem;
			font-weight: 700;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 2px solid #fff;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
		}

		.gw-inner-timeline-card {
			background: #fff;
			border: 1px solid #e5e7eb;
			border-radius: 0.65rem;
			padding: 0.85rem 1rem;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
		}

		.gw-capture-badge {
			font-size: 0.68rem;
			font-weight: 600;
			padding: 0.15rem 0.45rem;
			border-radius: 999px;
			background: #f1f5f9;
			color: #475569;
			border: 1px solid #e2e8f0;
		}

		.gw-capture-field {
			width: 100%;
			border-radius: 0.45rem;
			font-size: 0.875rem;
		}

		.gw-capture-field--input {
			padding: 0.45rem 0.65rem;
			border: 1px solid #d1d5db;
			background: #f9fafb;
			color: #94a3b8;
		}

		.gw-capture-field--select {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 0.45rem 0.65rem;
			border: 1px solid #d1d5db;
			background: #f9fafb;
		}

		.gw-capture-field--panel {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.65rem 0.85rem;
			border: 1px dashed #cbd5e1;
			background: #f8fafc;
			color: #64748b;
			font-size: 0.8rem;
		}

		.gw-inner-block {
			border: 1px solid #e2e8f0;
			border-radius: 0.65rem;
			padding: 0.85rem;
			background: #fafbfc;
		}

		.gw-inner-block-head {
			display: flex;
			align-items: flex-start;
			gap: 0.65rem;
			margin-bottom: 0.75rem;
		}

		.gw-inner-block-order {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 1.65rem;
			height: 1.65rem;
			border-radius: 0.4rem;
			background: #dbeafe;
			color: #1d4ed8;
			font-weight: 700;
			font-size: 0.8rem;
			flex-shrink: 0;
		}
	</style>

	@php $modalOpen = $showAddModal || $showEditModal; @endphp
	@if($modalOpen)
		<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.4);">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $showEditModal ? 'Edit stage' : 'Add stage' }}</h5>
						<button type="button" class="close" wire:click="closeModal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<label class="form-label">Stage label *</label>
							<input type="text" wire:model="label" class="form-control @error('label') is-invalid @enderror" placeholder="e.g. Sample Recovery Worksheet">
							@error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
						</div>
						<div class="mb-3">
							<label class="form-label">Description</label>
							<textarea wire:model="description" class="form-control" rows="2"></textarea>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Worksheet type *</label>
								<div class="tag-select-container" wire:click="$set('showItemTypeDropdown', true)" wire:click.outside="$set('showItemTypeDropdown', false)">
									<div class="tag-select-input">
										@if($this->selectedItemTypeLabel)
											<span class="tag-badge">
												{{ $this->selectedItemTypeLabel }}
											</span>
										@endif
										<input type="text"
											class="tag-input"
											readonly
											placeholder="{{ $this->selectedItemTypeLabel ? '' : 'Select worksheet type...' }}"
											style="cursor: pointer;">
									</div>
									@if($showItemTypeDropdown)
										<div class="tag-dropdown">
											@foreach($itemTypeOptions as $value => $typeLabel)
												<div class="tag-dropdown-item" wire:click.stop="selectItemType('{{ $value }}')">
													<strong>{{ $typeLabel }}</strong>
												</div>
											@endforeach
										</div>
									@endif
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-check mt-4">
									<input type="checkbox" wire:model="is_required" class="form-check-input" id="itemRequired">
									<label class="form-check-label" for="itemRequired">Required stage</label>
								</div>
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Select worksheet *</label>
							<div class="tag-select-container" wire:click="$set('showReferenceDropdown', true)" wire:click.outside="$set('showReferenceDropdown', false)">
								<div class="tag-select-input">
									@if($this->selectedReference)
										<span class="tag-badge">
											{{ $this->selectedReference->name }}
											<i class="mdi mdi-close-circle" wire:click.stop="clearReference"></i>
										</span>
									@endif
									<input type="text"
										wire:model.live="referenceSearch"
										class="tag-input"
										placeholder="{{ $this->selectedReference ? '' : 'Search worksheets...' }}"
										autocomplete="off">
								</div>
								@if($showReferenceDropdown && count($this->filteredReferenceOptions) > 0)
									<div class="tag-dropdown">
										@foreach($this->filteredReferenceOptions as $option)
											<div class="tag-dropdown-item" wire:click.stop="selectReference('{{ $option['id'] }}')">
												<strong>{{ $option['name'] }}</strong>
												@if(!empty($option['description']))
													<br><small class="text-muted">{{ Str::limit($option['description'], 50) }}</small>
												@endif
											</div>
										@endforeach
									</div>
								@endif
							</div>
							@error('reference_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
						<button type="button" class="btn btn-primary" wire:click="{{ $showEditModal ? 'updateItem' : 'addItem' }}">Save stage</button>
					</div>
				</div>
			</div>
		</div>
	@endif
</div>
