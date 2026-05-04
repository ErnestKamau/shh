<div class="row" style="margin: 0 15px;">
	<div class="col-12 px-0">
		<style>
			.sd-paragraph-flow {
				font-size: 0.95rem;
				line-height: 2.35;
				color: #1e293b;
			}

			.sd-paragraph-flow .sd-paragraph-static {
				white-space: pre-wrap;
			}

			.sd-paragraph-flow .sd-paragraph-inline-input {
				display: inline-block;
				vertical-align: baseline;
				min-width: 7rem;
				max-width: 18rem;
				padding: 2px 8px;
				height: auto;
				line-height: 1.35;
				border-radius: 4px;
				border: 1px dashed #94a3b8;
				background: #f8fafc;
			}

			.sd-paragraph-flow .sd-paragraph-inline-input:focus {
				border-style: solid;
				border-color: var(--workflow-accent, #3b5fc0);
				background: #fff;
				outline: none;
				box-shadow: 0 0 0 2px rgba(59, 95, 192, 0.15);
			}

			.sd-paragraph-flow .sd-paragraph-slot-readonly {
				border-bottom: 1px solid #64748b;
				min-width: 3.5rem;
				display: inline-block;
				vertical-align: baseline;
				padding: 0 4px 1px;
				margin: 0 2px;
				font-weight: 600;
				color: #0f172a;
			}
		</style>

		<div class="workflow-board-panel mb-3">
			<div class="workflow-board-panel-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
				<div>
					<h6 class="mb-0">
						<i class="mdi mdi-file-document-outline"></i>
						{{ $instance->template->title }}
					</h6>
					<div class="text-muted small mt-1">
						@if($instance->template->document_code)
						<span class="fw-semibold">{{ $instance->template->document_code }}</span>
						<span class="mx-1">•</span>
						@endif
						Request #{{ $submissionRequest->id }}
						<span class="mx-1">•</span>
						Version {{ $instance->template_version }}
						<span class="mx-1">•</span>
						<span class="workflow-status-chip" style="--chip-accent: {{ $instance->status === 'submitted' ? '#16a34a' : '#64748b' }};">
							{{ $instance->status }}
						</span>
					</div>
				</div>
				<a class="btn btn-outline-secondary btn-action-sm" href="{{ route('sample-submission-requests.show', ['request' => $submissionRequest, 'details' => 1]) }}">
					<i class="mdi mdi-arrow-left"></i> Back to request
				</a>
			</div>
		</div>

		@foreach($instance->template->sections->sortBy('sort_order') as $section)
		<div class="workflow-board-panel mb-3">
			<div class="workflow-board-panel-header">
				<h6 class="mb-0">{{ $section->title ?: 'Section' }}</h6>
			</div>
			<div class="workflow-board-panel-body">
				@foreach($section->elements->sortBy('sort_order') as $el)
					@if($el->element_type === 'static_text')
						<div class="mb-4">
							@if($el->label)
							<div class="fw-semibold mb-1" style="font-size: 0.9rem;">{{ $el->label }}</div>
							@endif
							<div class="text-muted" style="font-size: 0.87rem; white-space: pre-wrap;">{{ $el->default_value }}</div>
						</div>
					@elseif($el->element_type === 'paragraph_template')
						<div class="mb-4">
							@if($el->label)
							<div class="fw-semibold mb-2" style="font-size: 0.9rem;">{{ $el->label }}</div>
							@endif
							@if($el->help_text)
							<div class="text-muted small mb-2">{{ $el->help_text }}</div>
							@endif
							<div class="sd-paragraph-flow rounded border px-3 py-3" style="background: #fafbfc; border-color: #e2e8f0 !important;">
								@foreach($this->paragraphSegments($el->default_value) as $idx => $segment)
									@if($segment['type'] === 'text')
										<span class="sd-paragraph-static">{{ $segment['content'] }}</span>
									@else
										@if($readOnly)
											<span class="sd-paragraph-slot-readonly">
												{{ trim((string) ($values[$el->id][$segment['name']] ?? '')) !== '' ? ($values[$el->id][$segment['name']] ?? '') : '———' }}
											</span>
										@else
											<span class="align-middle" style="display: inline-block; margin: 0 1px;">
												<input
													type="text"
													class="form-control form-control-sm sd-paragraph-inline-input"
													wire:model.live="values.{{ $el->id }}.{{ $segment['name'] }}"
													wire:key="sd-ph-{{ $el->id }}-{{ $segment['name'] }}-{{ $idx }}"
													placeholder="———"
												/>
											</span>
										@endif
									@endif
								@endforeach
							</div>
							@php
								$phNames = \App\Support\SupportingDocumentElementValidation::paragraphPlaceholderNames($el->default_value);
							@endphp
							@foreach($phNames as $phName)
								@error('values.'.$el->id.'.'.$phName)
									<div class="text-danger small mt-1">{{ $message }}</div>
								@enderror
							@endforeach
						</div>
					@elseif(in_array($el->element_type, ['text', 'number', 'date'], true))
						<div class="mb-3">
							@if($el->label)
							<label class="form-label">{{ $el->label }} @if($el->is_required)<span class="text-danger">*</span>@endif</label>
							@endif
							@if($el->help_text)
							<div class="text-muted small mb-1">{{ $el->help_text }}</div>
							@endif
							@php
								$inputType = $el->element_type === 'text' ? 'text' : $el->element_type;
							@endphp
							@if($readOnly || $el->is_readonly)
								<div class="form-control-plaintext fw-semibold">{{ $values[$el->id] ?? '—' }}</div>
							@else
								<input
									type="{{ $inputType }}"
									class="form-control @error('values.'.$el->id) is-invalid @enderror"
									wire:model.live="values.{{ $el->id }}"
									placeholder="{{ $el->placeholder }}"
								>
								@error('values.'.$el->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
							@endif
						</div>
					@elseif($el->element_type === 'textarea')
						<div class="mb-3">
							@if($el->label)
							<label class="form-label">{{ $el->label }} @if($el->is_required)<span class="text-danger">*</span>@endif</label>
							@endif
							@if($el->help_text)
							<div class="text-muted small mb-1">{{ $el->help_text }}</div>
							@endif
							@if($readOnly || $el->is_readonly)
								<div class="form-control-plaintext fw-semibold" style="white-space: pre-wrap;">{{ $values[$el->id] ?? '—' }}</div>
							@else
								<textarea
									class="form-control @error('values.'.$el->id) is-invalid @enderror"
									wire:model.live="values.{{ $el->id }}"
									rows="4"
									placeholder="{{ $el->placeholder }}"
								></textarea>
								@error('values.'.$el->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
							@endif
						</div>
					@else
						<div class="alert alert-warning small mb-3">
							Unsupported element type <code>{{ $el->element_type }}</code> (skipped).
						</div>
					@endif
				@endforeach
			</div>
		</div>
		@endforeach

		@if(!$readOnly)
		<div class="d-flex justify-content-end flex-wrap" style="gap: 8px;">
			<button type="button" class="btn btn-outline-primary btn-action-sm" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft">
				<span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm me-1" role="status"></span>
				<i class="mdi mdi-content-save-outline"></i> Save draft
			</button>
			<button type="button" class="btn btn-primary btn-action-sm" wire:click="submitDocument" wire:loading.attr="disabled" wire:target="submitDocument">
				<span wire:loading wire:target="submitDocument" class="spinner-border spinner-border-sm me-1" role="status"></span>
				<i class="mdi mdi-send-check-outline"></i> Submit document
			</button>
		</div>
		@endif
	</div>
</div>
