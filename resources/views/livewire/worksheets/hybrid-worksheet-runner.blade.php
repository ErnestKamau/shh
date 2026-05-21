<div class="hybrid-worksheet-runner">
	@if($blocks->isEmpty())
		<div class="alert alert-info">No blocks defined on the active hybrid worksheet version.</div>
	@else
		<ul class="nav nav-pills mb-3">
			@foreach($blocks as $index => $block)
				<li class="nav-item">
					<span class="nav-link {{ $currentBlockIndex === $index ? 'active' : '' }}">
						{{ $block->sort_order }}. {{ $block->label ?? $block->getBlockTypeEnum()->label() }}
					</span>
				</li>
			@endforeach
		</ul>

		@if($currentBlock)
			@php $type = $currentBlock->getBlockTypeEnum(); @endphp
			@if($type->isReference())
				@switch($type->value)
					@case('formula_reference')
						@if($formula)
							<livewire:worksheets.formula-worksheet :batch="$batch" :formula="$formula" :key="'hybrid-formula-'.$formula->id" />
						@endif
						@break
					@case('procedure_reference')
						<livewire:worksheets.procedure-worksheet-manager
							:batchId="$batch->id"
							:initialWorksheetId="$procedureWorksheetId"
							:key="'hybrid-proc-'.$procedureWorksheetId"
						/>
						@break
					@case('stage_header_reference')
						<div wire:ignore>
							@include('worksheets.partials.method-sequences-jquery', [
								'batch' => $batch,
								'stageHeaders' => $stageHeaders,
								'stageHeadersPayload' => $stageHeadersPayload,
							])
						</div>
						@break
				@endswitch
			@else
				<div class="border rounded p-3 mb-3 bg-light">
					<h6>{{ $currentBlock->label ?? $type->label() }}</h6>
					@if($type->value === 'formula_inline')
						<ul class="mb-0">
							@foreach($currentBlock->formulaSteps as $step)
								<li>{{ $step->label }} ({{ $step->step_type }})</li>
							@endforeach
						</ul>
					@elseif($type->value === 'procedure_inline')
						<ul class="mb-0">
							@foreach($currentBlock->procedureSteps as $step)
								<li>{{ $step->step }}</li>
							@endforeach
						</ul>
					@elseif($type->value === 'sequence_inline')
						<ul class="mb-0">
							@foreach($currentBlock->sequenceStages as $stage)
								<li>{{ $stage->name }}</li>
							@endforeach
						</ul>
					@endif
					<p class="small text-muted mt-2 mb-0">Inline hybrid blocks: capture on batch uses the checklist above; extend with full inline editors as needed.</p>
				</div>
			@endif

			<div class="d-flex justify-content-between mt-3">
				<button type="button" class="btn btn-outline-secondary" wire:click="previousBlock" @if($currentBlockIndex === 0) disabled @endif>Previous block</button>
				<button type="button" class="btn btn-primary" wire:click="nextBlock">
					{{ $currentBlockIndex >= $blocks->count() - 1 ? 'Finish hybrid sheet' : 'Next block' }}
				</button>
			</div>
		@endif
	@endif
</div>
