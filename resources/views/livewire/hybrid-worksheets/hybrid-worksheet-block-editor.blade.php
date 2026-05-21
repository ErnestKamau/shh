<div class="container-fluid px-0 worksheet-engine-tag-select">
@include('layouts.lab.partials.worksheet-engine-tag-select-styles')
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-file-tree"></i> {{ $version->hybridWorksheet->name }} <small class="text-muted">v{{ $version->version_number }}</small></h5>
			<button type="button" wire:click="openAddModal" class="btn btn-primary btn-sm btn-action-sm">
				<i class="mdi mdi-plus"></i> Add block
			</button>
		</div>
	</div>

	@if($message)
		<div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} mb-3">{{ $message }}</div>
	@endif

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-body">
			@if($blocks->isEmpty())
				<p class="text-muted text-center py-4">Add reference or inline blocks.</p>
			@else
				@foreach($blocks as $block)
					<div class="border rounded p-3 mb-2 d-flex align-items-start gap-2" wire:key="block-{{ $block->id }}">
						<span class="text-muted fw-bold">{{ $block->sort_order }}</span>
						<div class="flex-grow-1">
							<div class="fw-semibold">{{ $block->label ?? $block->getBlockTypeEnum()->label() }}</div>
							<div class="small text-muted">
								{{ $block->getBlockTypeEnum()->label() }}
								@if($block->getBlockTypeEnum()->isReference())
									— {{ $block->referenceName() }}
								@endif
							</div>
							@if($block->getBlockTypeEnum()->isInline())
								<button type="button" class="btn btn-link btn-sm p-0" wire:click="openInlineEditor('{{ $block->id }}')">Edit inline content</button>
							@endif
						</div>
						<div class="btn-group btn-group-sm">
							<button type="button" class="btn btn-outline-secondary" wire:click="moveUp('{{ $block->id }}')"><i class="mdi mdi-chevron-up"></i></button>
							<button type="button" class="btn btn-outline-secondary" wire:click="moveDown('{{ $block->id }}')"><i class="mdi mdi-chevron-down"></i></button>
							<button type="button" class="btn btn-outline-primary" wire:click="openEditModal('{{ $block->id }}')"><i class="mdi mdi-pencil"></i></button>
							<button type="button" class="btn btn-outline-danger" wire:click="deleteBlock('{{ $block->id }}')" onclick="return confirm('Remove block?')"><i class="mdi mdi-delete"></i></button>
						</div>
					</div>
				@endforeach
			@endif
		</div>
	</div>

	@if($showInlineEditor && $inlineBlockId)
		@php $inlineBlock = $blocks->firstWhere('id', $inlineBlockId); @endphp
		@if($inlineBlock)
			<div class="workflow-board-panel mt-3">
				<div class="workflow-board-panel-header">
					<h5>Inline editor: {{ $inlineBlock->label ?? $inlineBlock->getBlockTypeEnum()->label() }}</h5>
					<button type="button" class="btn btn-sm btn-secondary" wire:click="closeInlineEditor">Close</button>
				</div>
				<div class="workflow-board-panel-body">
					@switch($inlineBlock->getBlockTypeEnum()->value)
						@case('formula_inline')
							<button type="button" class="btn btn-sm btn-outline-primary mb-2" wire:click="addInlineFormulaStep('{{ $inlineBlockId }}')">Add formula step</button>
							<ul class="list-group">
								@foreach($inlineBlock->formulaSteps as $step)
									<li class="list-group-item">{{ $step->step_number }}. {{ $step->label }} ({{ $step->step_type }}) — {{ $step->variable_name }}</li>
								@endforeach
							</ul>
							@break
						@case('procedure_inline')
							<button type="button" class="btn btn-sm btn-outline-primary mb-2" wire:click="addInlineProcedureStep('{{ $inlineBlockId }}')">Add procedure step</button>
							<ul class="list-group">
								@foreach($inlineBlock->procedureSteps as $step)
									<li class="list-group-item">{{ $step->order }}. {{ $step->step }}</li>
								@endforeach
							</ul>
							@break
						@case('sequence_inline')
							<button type="button" class="btn btn-sm btn-outline-primary mb-2" wire:click="addInlineSequenceStage('{{ $inlineBlockId }}')">Add sequence stage</button>
							<ul class="list-group">
								@foreach($inlineBlock->sequenceStages as $stage)
									<li class="list-group-item">{{ $stage->order }}. {{ $stage->name }}</li>
								@endforeach
							</ul>
							@break
					@endswitch
				</div>
			</div>
		@endif
	@endif

	@php $modalOpen = $showAddModal || $showEditModal; @endphp
	@if($modalOpen)
		<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4)">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $showEditModal ? 'Edit block' : 'Add block' }}</h5>
						<button type="button" class="btn-close" wire:click="$set('showAddModal', false); $set('showEditModal', false)"></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<label class="form-label">Label (optional)</label>
							<input type="text" wire:model="label" class="form-control">
						</div>
						<div class="mb-3">
							<label class="form-label">Block type *</label>
							<div class="tag-select-container" wire:click="$set('showBlockTypeDropdown', true)" wire:click.outside="$set('showBlockTypeDropdown', false)">
								<div class="tag-select-input">
									@if($this->selectedBlockTypeLabel)
										<span class="tag-badge">{{ $this->selectedBlockTypeLabel }}</span>
									@endif
									<input type="text"
										class="tag-input"
										readonly
										placeholder="{{ $this->selectedBlockTypeLabel ? '' : 'Select block type...' }}"
										style="cursor: pointer;">
								</div>
								@if($showBlockTypeDropdown)
									<div class="tag-dropdown">
										@foreach($blockTypeOptions as $value => $lbl)
											<div class="tag-dropdown-item" wire:click.stop="selectBlockType('{{ $value }}')">
												<strong>{{ $lbl }}</strong>
											</div>
										@endforeach
									</div>
								@endif
							</div>
						</div>
						@if($this->isReferenceBlockType)
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
											@foreach($this->filteredReferenceOptions as $opt)
												<div class="tag-dropdown-item" wire:click.stop="selectReference('{{ $opt['id'] }}')">
													<strong>{{ $opt['name'] }}</strong>
													@if(!empty($opt['description']))
														<br><small class="text-muted">{{ Str::limit($opt['description'], 50) }}</small>
													@endif
												</div>
											@endforeach
										</div>
									@endif
								</div>
								@error('reference_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>
						@endif
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" wire:click="$set('showAddModal', false); $set('showEditModal', false)">Cancel</button>
						<button type="button" class="btn btn-primary" wire:click="{{ $showEditModal ? 'updateBlock' : 'addBlock' }}">Save</button>
					</div>
				</div>
			</div>
		</div>
	@endif
</div>
