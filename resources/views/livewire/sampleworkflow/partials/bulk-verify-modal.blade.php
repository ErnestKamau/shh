@if($showBulkVerifyModal)
	@php
		$isApproveMode = ($bulkVerifyMode ?? 'review') === 'approve';
		$reviewBatches = $this->bulkVerifyReview;
		$activeId = $this->bulkVerifyActiveBatchId;
		$completedIds = $this->bulkVerifyCompletedIds;
		$approveCompletedIds = $this->bulkApproveCompletedIds ?? [];
		$activeBatch = collect($reviewBatches)->firstWhere('id', $activeId) ?? ($reviewBatches[0] ?? null);
		$verifiedIds = collect($reviewBatches)
			->filter(fn ($job) => ($job['verified'] ?? false) || in_array($job['id'], $completedIds, true))
			->pluck('id')
			->all();
		$approvedIds = collect($reviewBatches)
			->filter(fn ($job) => ($job['approved'] ?? false) || in_array($job['id'], $approveCompletedIds, true))
			->pluck('id')
			->all();
		$pendingCount = collect($this->bulkVerifyBatchIds)
			->filter(fn ($id) => ! in_array($id, $isApproveMode ? $approvedIds : $verifiedIds, true))
			->count();
		$jobCount = count($reviewBatches);
		$showRail = $jobCount > 1;
		$activeIsPending = $activeBatch !== null && ! in_array($activeBatch['id'], $isApproveMode ? $approvedIds : $verifiedIds, true);
		$customerIds = collect($reviewBatches)
			->map(fn ($job) => trim((string) ($job['customer_id'] ?? '')))
			->filter()
			->unique()
			->values();
		$sameCustomer = $customerIds->count() <= 1;
		$allApproved = $jobCount > 0 && $pendingCount === 0 && $isApproveMode;
		$canGenerateReports = $allApproved
			&& $sameCustomer
			&& auth()->user()?->can('laboratory.components.lab-reports.view');
		$bulkReportLabSections = [];
		if ($canGenerateReports && $bulkVerifyBatchIds !== []) {
			$bulkReportLabSections = \App\SampleHeader::query()
				->whereIn('id', $bulkVerifyBatchIds)
				->get()
				->flatMap(static fn (\App\SampleHeader $batch): array => $batch->labSectionsForDisplay())
				->unique('id')
				->sortBy(static fn (array $section): string => mb_strtolower($section['name'] !== '' ? $section['name'] : $section['code']))
				->values()
				->all();
		}
		$activeMeta = $activeBatch === null
			? ''
			: collect([
				$activeBatch['batch_code'] ?? null,
				$activeBatch['customer'] ?? null,
				$activeBatch['sample_type'] ?? null,
				$activeBatch['lab_sections'] ?? null,
			])->filter(fn ($part) => filled($part) && $part !== '—')->implode(' · ');
	@endphp
	<div class="modal fade show d-block bv-verify-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="bulk-verify-title"
		style="background: rgba(15, 23, 42, 0.55); z-index: 1055;">
		<style>
			.bv-verify-modal .bv-dialog { max-width: 1120px; width: 96vw; margin: 1.25rem auto; }
			.bv-verify-modal .bv-shell {
				max-height: calc(100vh - 2.5rem);
				display: flex;
				flex-direction: column;
				overflow: hidden;
			}
			.bv-verify-modal .bv-head {
				display: flex;
				align-items: flex-start;
				justify-content: space-between;
				gap: 12px;
				padding: 14px 18px !important;
				flex-shrink: 0;
			}
			.bv-verify-modal .bv-head-copy { min-width: 0; }
			.bv-verify-modal .bv-head .modal-title {
				font-size: 1.05rem;
				font-weight: 700;
				margin: 0 0 2px;
				line-height: 1.3;
			}
			.bv-verify-modal .bv-head-sub {
				margin: 0;
				font-size: 0.8rem;
				line-height: 1.4;
				color: rgba(255, 255, 255, 0.82);
			}
			.bv-verify-modal .bv-layout {
				display: grid;
				grid-template-columns: 1fr;
				min-height: 0;
				flex: 1;
				background: #f8fafc;
			}
			.bv-verify-modal .bv-layout.has-rail {
				grid-template-columns: 232px 1fr;
			}
			.bv-verify-modal .bv-rail {
				background: #fff;
				border-right: 1px solid #e2e8f0;
				padding: 12px 10px;
				overflow-y: auto;
			}
			.bv-verify-modal .bv-rail-label {
				font-size: 0.68rem;
				font-weight: 700;
				letter-spacing: 0.08em;
				text-transform: uppercase;
				color: #64748b;
				padding: 2px 8px 10px;
			}
			.bv-verify-modal .bv-job {
				display: block;
				width: 100%;
				text-align: left;
				border: 1px solid #e2e8f0;
				background: #fff;
				color: #0f172a;
				border-radius: 8px;
				padding: 10px 10px 9px;
				margin-bottom: 6px;
			}
			.bv-verify-modal .bv-job:hover,
			.bv-verify-modal .bv-job:focus {
				background: #f8fafc;
				outline: none;
			}
			.bv-verify-modal .bv-job.is-active {
				background: #fff7f7;
				border-color: var(--color-primary, #8b1e3f);
				box-shadow: inset 3px 0 0 var(--color-primary, #8b1e3f);
			}
			.bv-verify-modal .bv-job-code {
				font-weight: 700;
				font-size: 0.82rem;
				color: #0f172a;
				display: block;
				font-variant-numeric: tabular-nums;
			}
			.bv-verify-modal .bv-job-meta {
				font-size: 0.7rem;
				color: #64748b;
				margin-top: 2px;
			}
			.bv-verify-modal .bv-chip {
				display: inline-flex;
				align-items: center;
				border-radius: 999px;
				padding: 1px 7px;
				font-size: 0.65rem;
				font-weight: 700;
				margin-top: 6px;
			}
			.bv-verify-modal .bv-chip-ok { background: #dcfce7; color: #166534; }
			.bv-verify-modal .bv-chip-warn { background: #ffedd5; color: #9a3412; }
			.bv-verify-modal .bv-chip-done { background: #e2e8f0; color: #334155; }
			.bv-verify-modal .bv-main {
				overflow-y: auto;
				padding: 16px 18px 18px;
				min-width: 0;
			}
			.bv-verify-modal .bv-sample {
				background: #fff;
				border: 1px solid #e2e8f0;
				border-radius: 10px;
				margin-bottom: 14px;
				overflow: hidden;
			}
			.bv-verify-modal .bv-sample-head {
				padding: 10px 14px;
				background: #f8fafc;
				border-bottom: 1px solid #e2e8f0;
				display: flex;
				flex-wrap: wrap;
				gap: 6px 12px;
				align-items: baseline;
			}
			.bv-verify-modal .bv-sample-code {
				font-weight: 700;
				color: #0f172a;
				font-size: 0.9rem;
				font-variant-numeric: tabular-nums;
			}
			.bv-verify-modal .bv-table {
				font-size: 0.8rem;
				margin-bottom: 0;
			}
			.bv-verify-modal .bv-table thead th {
				font-size: 0.68rem;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.04em;
				color: #64748b;
				white-space: nowrap;
				background: #fff;
				border-bottom: 1px solid #e2e8f0;
				padding: 8px 12px;
			}
			.bv-verify-modal .bv-table td {
				padding: 8px 12px;
				vertical-align: middle;
				color: #1e293b;
				border-color: #f1f5f9;
			}
			.bv-verify-modal .bv-missing td { background: #fff7ed; }
			.bv-verify-modal .bv-result-strong { font-weight: 700; font-variant-numeric: tabular-nums; }
			.bv-verify-modal .bv-tone-pass { color: #166534; }
			.bv-verify-modal .bv-tone-fail { color: #b91c1c; font-weight: 700; }
			.bv-verify-modal .bv-foot {
				flex-shrink: 0;
				justify-content: space-between;
				background: #fff;
				border-top: 1px solid #e2e8f0;
				padding: 12px 18px;
			}
			@media (max-width: 767.98px) {
				.bv-verify-modal .bv-layout.has-rail { grid-template-columns: 1fr; }
				.bv-verify-modal .bv-rail { max-height: 160px; border-right: 0; border-bottom: 1px solid #e2e8f0; }
			}
		</style>

		<div class="modal-dialog modal-xl modal-dialog-centered bv-dialog" role="document">
			<div class="modal-content shadow-lg bv-shell">
				<div class="modal-header bv-head">
					<div class="d-flex align-items-start">
						<div class="bv-head-copy">
							<h5 class="modal-title" id="bulk-verify-title">
								{{ $isApproveMode ? 'Bulk approve' : 'Review samples & results' }}
							</h5>
							<p class="bv-head-sub">
								@if($activeMeta !== '')
									{{ $activeMeta }}
								@elseif($isApproveMode)
									Review results and verification, approve jobs, then generate Test Reports for one customer.
								@else
									Confirm captured results, then complete Technical Reviewer verification.
								@endif
							</p>
						</div>
					</div>
					<button type="button" class="close" wire:click="closeBulkVerifyModal" aria-label="Close review">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="bv-layout {{ $showRail ? 'has-rail' : '' }}">
					@if($showRail)
						<nav class="bv-rail" aria-label="Selected jobs">
							<div class="bv-rail-label">{{ $jobCount }} jobs</div>
							@foreach($reviewBatches as $job)
								@php
									$isActive = (string) ($activeBatch['id'] ?? '') === (string) $job['id'];
									$isVerified = in_array($job['id'], $verifiedIds, true) || ($job['verified'] ?? false);
									$isApproved = in_array($job['id'], $approvedIds, true) || ($job['approved'] ?? false);
								@endphp
								<button type="button"
									class="bv-job {{ $isActive ? 'is-active' : '' }}"
									wire:click="setBulkVerifyActiveBatch('{{ $job['id'] }}')"
									aria-current="{{ $isActive ? 'true' : 'false' }}">
									<span class="bv-job-code">{{ $job['batch_code'] }}</span>
									<span class="bv-job-meta">{{ $job['customer'] }}</span>
									@if($isApproveMode)
										@if($isApproved)
											<span class="bv-chip bv-chip-done">Approved</span>
										@elseif($isVerified)
											<span class="bv-chip bv-chip-ok">Verified · pending approval</span>
										@else
											<span class="bv-chip bv-chip-warn">Not verified</span>
										@endif
									@elseif($isVerified)
										<span class="bv-chip bv-chip-done">Verified</span>
									@elseif(($job['missing'] ?? 0) > 0)
										<span class="bv-chip bv-chip-warn">{{ $job['missing'] }} missing</span>
									@else
										<span class="bv-chip bv-chip-ok">{{ $job['entered'] }} results in</span>
									@endif
								</button>
							@endforeach
						</nav>
					@endif

					<div class="bv-main">
						@if(session('error'))
							<div class="alert alert-danger py-2 px-3 small">{{ session('error') }}</div>
						@endif
						@if(session('success'))
							<div class="alert alert-success py-2 px-3 small">{{ session('success') }}</div>
						@endif

						@if(! $isApproveMode && $pendingCount === 0 && $jobCount > 0)
							<div class="alert alert-success py-2 px-3 small mb-3">
								All selected jobs are verified. Move them to Sample Approval when you are ready.
							</div>
						@endif
						@if($isApproveMode && $activeBatch !== null)
							<div class="d-flex flex-wrap mb-3" style="gap:8px;">
								<span class="badge border {{ ($activeBatch['verified'] ?? false) || in_array($activeBatch['id'], $verifiedIds, true) ? 'badge-success' : 'badge-warning' }}">
									Verification: {{ ($activeBatch['verified'] ?? false) || in_array($activeBatch['id'], $verifiedIds, true) ? 'Verified' : 'Not verified' }}
								</span>
								<span class="badge border {{ in_array($activeBatch['id'], $approvedIds, true) ? 'badge-success' : 'badge-warning' }}">
									Approval: {{ in_array($activeBatch['id'], $approvedIds, true) ? 'Approved' : 'Not approved' }}
								</span>
							</div>
						@endif
						@if($isApproveMode && ! $sameCustomer)
							<div class="alert alert-warning py-2 px-3 small mb-3">
								Selected jobs belong to more than one customer. You can still approve them, but bulk Test Reports require one customer.
							</div>
						@endif
						@if($isApproveMode && $bulkTestReportGenerated !== [])
							<div class="alert alert-success py-2 px-3 small mb-3">
								<strong>Test Reports generated</strong>
								<ul class="mb-0 mt-2 pl-3">
									@foreach($bulkTestReportGenerated as $report)
										<li>
											{{ $report['batch_code'] }} —
											<a href="{{ $report['online_url'] }}" target="_blank" rel="noopener">Open PDF</a>
										</li>
									@endforeach
								</ul>
							</div>
						@endif

						@if($activeBatch === null)
							<div class="alert alert-light border mb-0">Select a job to review its samples and results.</div>
						@else
							@forelse($activeBatch['samples'] as $sample)
								<section class="bv-sample">
									<div class="bv-sample-head">
										<span class="bv-sample-code">{{ $sample['code'] }}</span>
										@if(($sample['sample_type'] ?? '') !== '')
											<span class="text-muted small">{{ $sample['sample_type'] }}</span>
										@endif
										@if(($sample['notes'] ?? '') !== '')
											<span class="text-muted small">{{ $sample['notes'] }}</span>
										@endif
									</div>
									<div class="table-responsive">
										<table class="table table-sm bv-table">
											<thead>
												<tr>
													<th>Analyte</th>
													<th>Analysis type</th>
													<th>Result</th>
													<th>Unit</th>
													<th>Remark</th>
													<th>Analyst</th>
												</tr>
											</thead>
											<tbody>
												@forelse($sample['results'] as $row)
													<tr class="{{ (! $row['entered'] && ! $row['no_capture']) ? 'bv-missing' : '' }}">
														<td>{{ $row['analyte'] }}</td>
														<td>{{ $row['analysis_type'] }}</td>
														<td class="bv-result-strong">{{ $row['result'] }}</td>
														<td>{{ $row['unit'] }}</td>
														<td class="bv-tone-{{ $row['remark_tone'] }}">{{ $row['remark'] }}</td>
														<td>{{ $row['analyst'] }}</td>
													</tr>
												@empty
													<tr>
														<td colspan="6" class="text-muted small p-3">No captured results on this sample.</td>
													</tr>
												@endforelse
											</tbody>
										</table>
									</div>
								</section>
							@empty
								<div class="alert alert-light border mb-0">This job has no samples yet.</div>
							@endforelse
						@endif
					</div>
				</div>

				<div class="modal-footer bv-foot flex-column align-items-stretch" style="gap:10px;">
					@if($isApproveMode && $canGenerateReports && $bulkTestReportGenerated === [])
						<div class="border rounded p-2 w-100" style="background:#f8fafc;">
							<div class="font-weight-bold small mb-2">Generate Test Reports (same customer)</div>
							<div class="d-flex flex-wrap align-items-center mb-2" style="gap:10px;">
								@foreach(['en' => 'EN', 'ar' => 'AR', 'pt' => 'PT'] as $code => $label)
									<label class="mb-0 small" style="cursor:pointer;">
										<input type="radio" wire:model="bulkTestReportLanguage" value="{{ $code }}" class="mr-1">{{ $label }}
									</label>
								@endforeach
							</div>
							<div class="d-flex flex-wrap small" style="gap:12px;">
								<label class="mb-0"><input type="checkbox" wire:model="bulkTestReportIncludeReferenceMethod" class="mr-1"> Reference Method</label>
								<label class="mb-0"><input type="checkbox" wire:model="bulkTestReportShowSpecification" class="mr-1"> Spec limit</label>
								<label class="mb-0"><input type="checkbox" wire:model="bulkTestReportShowSpecificationStandard" class="mr-1"> Spec Standard</label>
								<label class="mb-0"><input type="checkbox" wire:model="bulkTestReportShowMuPercent" class="mr-1"> M.U%</label>
							</div>
							@if($bulkReportLabSections !== [])
								<div class="mt-2">
									<label class="small font-weight-bold mb-1 d-block" for="bulk-trr-lab-section">Lab Section</label>
									<select id="bulk-trr-lab-section" class="form-control form-control-sm" wire:model="bulkTestReportLabSectionId">
										<option value="">All lab sections (full report)</option>
										@foreach($bulkReportLabSections as $section)
											<option value="{{ $section['id'] }}">
												{{ $section['name'] !== '' ? $section['name'] : $section['code'] }}
											</option>
										@endforeach
									</select>
									<small class="text-muted d-block mt-1">
										Section-only prints keep each sample on its own page and are not saved as the official Test Report.
									</small>
								</div>
							@endif
						</div>
					@endif
					<div class="d-flex justify-content-between align-items-center w-100 flex-wrap" style="gap:8px;">
						<button type="button" class="btn btn-outline-secondary btn-sm" wire:click="closeBulkVerifyModal">
							Close
						</button>
						<div class="d-flex flex-wrap" style="gap: 8px;">
							@if($isApproveMode)
								@if($pendingCount > 1 && $activeIsPending)
									<button type="button" class="btn btn-outline-success btn-sm"
										wire:click="confirmBulkApproveActive"
										wire:loading.attr="disabled"
										wire:target="confirmBulkApproveActive,confirmBulkApprove,generateBulkTestReports">
										<span wire:loading.remove wire:target="confirmBulkApproveActive">Approve this job</span>
										<span wire:loading wire:target="confirmBulkApproveActive">Approving…</span>
									</button>
								@endif
								@if($pendingCount > 0)
									<button type="button" class="btn btn-success btn-sm"
										wire:click="confirmBulkApprove"
										wire:loading.attr="disabled"
										wire:target="confirmBulkApprove,confirmBulkApproveActive,generateBulkTestReports">
										<span wire:loading.remove wire:target="confirmBulkApprove,confirmBulkApproveActive">
											@if($pendingCount === 1) Approve job @else Approve all {{ $pendingCount }} @endif
										</span>
										<span wire:loading wire:target="confirmBulkApprove,confirmBulkApproveActive">Approving…</span>
									</button>
								@endif
								@if($canGenerateReports)
									<button type="button" class="btn btn-primary btn-sm"
										wire:click="generateBulkTestReports"
										wire:loading.attr="disabled"
										wire:target="generateBulkTestReports">
										<span wire:loading.remove wire:target="generateBulkTestReports">
											Generate Test Report{{ $jobCount === 1 ? '' : 's' }}
										</span>
										<span wire:loading wire:target="generateBulkTestReports">Generating…</span>
									</button>
								@elseif($allApproved && ! $sameCustomer)
									<button type="button" class="btn btn-outline-primary btn-sm" disabled title="Same customer required">
										Generate Test Reports
									</button>
								@endif
							@else
								@if($pendingCount > 1 && $activeIsPending)
									<button type="button" class="btn btn-outline-primary btn-sm"
										wire:click="confirmBulkVerifyActive"
										wire:loading.attr="disabled"
										wire:target="confirmBulkVerifyActive,confirmBulkVerify,confirmBulkMoveToApproval">
										<span wire:loading.remove wire:target="confirmBulkVerifyActive">Verify this job</span>
										<span wire:loading wire:target="confirmBulkVerifyActive">Verifying…</span>
									</button>
								@endif
								@if($pendingCount > 0)
									<button type="button" class="btn btn-primary btn-sm"
										wire:click="confirmBulkVerify"
										wire:loading.attr="disabled"
										wire:target="confirmBulkVerify,confirmBulkVerifyActive,confirmBulkMoveToApproval">
										<span wire:loading.remove wire:target="confirmBulkVerify,confirmBulkVerifyActive">
											@if($pendingCount === 1) Verify job @else Verify all {{ $pendingCount }} @endif
										</span>
										<span wire:loading wire:target="confirmBulkVerify,confirmBulkVerifyActive">Verifying…</span>
									</button>
								@endif
								<button type="button"
									class="btn btn-sm {{ $pendingCount === 0 && $jobCount > 0 ? 'btn-primary' : 'btn-outline-primary' }}"
									title="{{ $pendingCount === 0 ? 'Send verified jobs to Sample Approval' : 'Verify all selected jobs first' }}"
									@if($pendingCount > 0 || $jobCount === 0) disabled @endif
									wire:click="confirmBulkMoveToApproval"
									wire:loading.attr="disabled"
									wire:target="confirmBulkMoveToApproval,confirmBulkVerify,confirmBulkVerifyActive">
									<span wire:loading.remove wire:target="confirmBulkMoveToApproval">
										Move to Sample Approval
										@if($pendingCount === 0 && $jobCount > 1) ({{ $jobCount }}) @endif
									</span>
									<span wire:loading wire:target="confirmBulkMoveToApproval">Moving…</span>
								</button>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif
