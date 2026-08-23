{{-- Subcontracted queue: sample / test / period overview (no customer block) --}}
@if($showSubcontractedViewModal && is_array($subcontractedViewPayload ?? null))
	@php
		$payload = $subcontractedViewPayload;
		$samples = is_array($payload['samples'] ?? null) ? $payload['samples'] : [];
	@endphp
	<div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
		style="background: rgba(15, 23, 42, 0.45);"
		wire:click.self="closeSubcontractedViewModal">
		<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
			<div class="modal-content border-0 shadow">
				<div class="modal-header">
					<div>
						<h5 class="modal-title mb-0">Subcontracted samples &amp; tests</h5>
						<div class="small text-muted mt-1">
							{{ $payload['request_no'] ?? '—' }}
							@if(! empty($payload['batch_code']))
								· Job {{ $payload['batch_code'] }}
							@endif
							· {{ $payload['dispatch_status'] ?? 'Awaiting dispatch' }}
							@if(! empty($payload['dispatch_date']))
								· {{ $payload['dispatch_date'] }}
							@endif
						</div>
					</div>
					<button type="button" class="close" wire:click="closeSubcontractedViewModal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p class="small text-muted mb-3">
						Samples stay whole. In-house and subcontracted tests are split by assignment — not by cutting the sample into pieces.
					</p>

					@if($samples === [])
						<div class="alert alert-light border mb-0">No subcontracted tests found on this request.</div>
					@else
						@foreach($samples as $sample)
							<div class="border rounded mb-3 p-3">
								<div class="d-flex flex-wrap align-items-baseline justify-content-between mb-2" style="gap: 0.5rem;">
									<div>
										<strong>{{ $sample['sample_label'] ?? 'Sample' }}</strong>
										<span class="text-muted small ml-1">{{ $sample['sample_type'] ?? '—' }}</span>
									</div>
									<div class="small text-muted">
										Marking: {{ $sample['sample_marking'] ?? '—' }}
										· In-house tests: {{ (int) ($sample['in_house_count'] ?? 0) }}
									</div>
								</div>

								<div class="table-responsive">
									<table class="table table-sm table-hover mb-0">
										<thead>
											<tr>
												<th>Test</th>
												<th>Analysis type</th>
												<th>Period (TAT)</th>
												<th>Subcontract lab</th>
											</tr>
										</thead>
										<tbody>
											@forelse(($sample['subcontracted_tests'] ?? []) as $test)
												<tr>
													<td>{{ $test['label'] ?? '—' }}</td>
													<td>{{ $test['analysis_type'] ?? '—' }}</td>
													<td>{{ $test['period'] ?? '—' }}</td>
													<td>{{ $test['lab'] ?? 'Not assigned' }}</td>
												</tr>
											@empty
												<tr>
													<td colspan="4" class="text-muted">No subcontracted tests on this sample.</td>
												</tr>
											@endforelse
										</tbody>
									</table>
								</div>
							</div>
						@endforeach
					@endif
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" wire:click="closeSubcontractedViewModal">Close</button>
				</div>
			</div>
		</div>
	</div>
@endif
