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

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-sort-numeric-ascending"></i> Ordered stages</h5>
		</div>
		<div class="workflow-board-panel-body">
			@if($items->isEmpty())
				<p class="text-muted text-center py-4 mb-0">No stages yet. Add procedure, formula, method sequence, or hybrid worksheets in order.</p>
			@else
				<div class="list-group">
					@foreach($items as $item)
						<div class="list-group-item d-flex align-items-start gap-3" wire:key="item-{{ $item->id }}">
							<div class="text-muted fw-bold" style="min-width: 2rem;">{{ $item->sort_order }}</div>
							<div class="flex-grow-1">
								<div class="fw-semibold">{{ $item->label }}</div>
								<div class="small text-muted">
									{{ $item->getItemTypeEnum()->label() }} — {{ $item->referenceName() }}
									@if(!$item->is_required)
										<span class="badge bg-light text-dark ms-1">Optional</span>
									@endif
								</div>
								@if($item->description)
									<div class="small text-muted mt-1">{{ $item->description }}</div>
								@endif
							</div>
							<div class="btn-group btn-group-sm">
								<button type="button" class="btn btn-outline-secondary" wire:click="moveUp('{{ $item->id }}')" title="Move up"><i class="mdi mdi-chevron-up"></i></button>
								<button type="button" class="btn btn-outline-secondary" wire:click="moveDown('{{ $item->id }}')" title="Move down"><i class="mdi mdi-chevron-down"></i></button>
								<button type="button" class="btn btn-outline-primary" wire:click="openEditModal('{{ $item->id }}')"><i class="mdi mdi-pencil"></i></button>
								<button type="button" class="btn btn-outline-danger" wire:click="deleteItem('{{ $item->id }}')" onclick="return confirm('Remove this stage?')"><i class="mdi mdi-delete"></i></button>
							</div>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</div>

	@php $modalOpen = $showAddModal || $showEditModal; @endphp
	@if($modalOpen)
		<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.4);">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $showEditModal ? 'Edit stage' : 'Add stage' }}</h5>
						<button type="button" class="btn-close" wire:click="$set('showAddModal', false); $set('showEditModal', false)"></button>
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
						<button type="button" class="btn btn-secondary" wire:click="$set('showAddModal', false); $set('showEditModal', false)">Cancel</button>
						<button type="button" class="btn btn-primary" wire:click="{{ $showEditModal ? 'updateItem' : 'addItem' }}">Save stage</button>
					</div>
				</div>
			</div>
		</div>
	@endif
</div>
