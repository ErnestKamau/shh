{{--
	Shared analyte picker panel for quotation parameter modals.

	Amspec UAE commercial model (see QuotationPricingResolver):
	  Total price = No. of samples × Unit price  (ONCE for the sample package)
	  Tests listed here are scope (LOQ / MU / method / accreditation) — not separate billed lines.

	Required: $prefix ('quote' | 'edit')
	Optional: $introTitle, $introCaption, $footNote

	Stage model: table shell stays mounted; loading is an overlay; empty is a sibling.
	Never hide empty + table + loading all at once (avoids blank white stage).
--}}
@php
	$prefix = $prefix ?? 'quote';
	$isEdit = $prefix === 'edit';
	$emptyId = $isEdit ? 'edit-params-empty' : 'quote-params-empty';
	$emptyTitleId = $isEdit ? 'edit-params-empty-title' : 'quote-params-empty-title';
	$emptyTextId = $isEdit ? 'edit-params-empty-text' : 'quote-params-empty-text';
	$loadingId = $isEdit ? 'edit-params-loading' : 'quote-params-loading';
	$tableWrapId = $isEdit ? 'edit-params-table-wrap' : 'quote-params-table-wrap';
	$tbodyId = $isEdit ? 'edit-description' : 'analysis-analytes-holder';
	$selectAllId = $isEdit ? 'edit-analyte-all' : 'select-analyte-all';
	$accAllId = $isEdit ? 'edit-accreditted' : 'select-accredited-all';
	$subAllId = $isEdit ? 'edit-sub' : 'select-sub-all';
	$introTitle = $introTitle ?? ($isEdit ? 'Edit parameters for this line item' : 'Choose parameters for this line item');
	$introCaption = $introCaption ?? ($isEdit
		? 'Update which tests are included on this package line (qty × unit price once). Grouped by analysis type; filtered by quotation lab sections.'
		: 'Select tests for this sample package. Billing is qty × unit price once — not per test. Grouped by analysis type; only lab sections on the quotation are shown.');
	$footNote = $footNote ?? ($isEdit
		? 'Changes apply when you save this dialog, then save the line edit form.'
		: '');
@endphp
<div class="ls-quote-params-shell">
	<div class="ls-quote-params-hero">
		<div class="ls-quote-params-hero__copy">
			{{-- <p class="ls-quote-params-hero__eyebrow">Analyte console</p> --}}
			<p class="ls-quote-params-hero__title">{{ $introTitle }}</p>
			<p class="ls-quote-params-hero__caption">{{ $introCaption }}</p>
		</div>
		<div class="ls-quote-params-legend" aria-hidden="true">
			<span class="ls-quote-params-legend__chip ls-quote-params-legend__chip--acc">Accredited</span>
			<span class="ls-quote-params-legend__chip ls-quote-params-legend__chip--sub">Subcontracted</span>
		</div>
	</div>

	<div class="ls-quote-params-toolbar">
		<label class="ls-quote-params-switch" for="{{ $selectAllId }}">
			<input type="checkbox" id="{{ $selectAllId }}" class="ls-quote-params-switch__input" title="Select all">
			<span class="ls-quote-params-switch__ui" aria-hidden="true"></span>
			<span class="ls-quote-params-switch__label">Select all</span>
		</label>
		<label class="ls-quote-params-switch" for="{{ $accAllId }}">
			<input type="checkbox" id="{{ $accAllId }}" class="ls-quote-params-switch__input" title="Mark all accredited">
			<span class="ls-quote-params-switch__ui" aria-hidden="true"></span>
			<span class="ls-quote-params-switch__label">All accredited</span>
		</label>
		<label class="ls-quote-params-switch" for="{{ $subAllId }}">
			<input type="checkbox" id="{{ $subAllId }}" class="ls-quote-params-switch__input" title="Mark all subcontracted">
			<span class="ls-quote-params-switch__ui" aria-hidden="true"></span>
			<span class="ls-quote-params-switch__label">All subcontracted</span>
		</label>
	</div>

	<div class="ls-quote-params-stage">
		<div id="{{ $emptyId }}" class="ls-quote-params-empty" hidden>
			<div class="ls-quote-params-empty__art" aria-hidden="true">
				<svg viewBox="0 0 160 160" width="108" height="108" xmlns="http://www.w3.org/2000/svg">
					<circle cx="80" cy="80" r="72" fill="#fff1f2"/>
					<circle cx="80" cy="80" r="56" fill="#ffe4e6"/>
					<path d="M62 28h36v14l22 54a28 28 0 1 1-54 0l22-54V28z" fill="#fda4af" stroke="#be123c" stroke-width="4" stroke-linejoin="round"/>
					<path d="M58 96c8 14 36 14 44 0 2 18-10 30-22 30S56 114 58 96z" fill="#f43f5e"/>
					<circle cx="72" cy="108" r="5" fill="#fecdd3"/>
					<circle cx="90" cy="102" r="3.5" fill="#fecdd3"/>
					<circle cx="82" cy="116" r="2.5" fill="#fecdd3"/>
					<rect x="58" y="22" width="44" height="10" rx="4" fill="#9f1239"/>
					<path d="M118 46c8-2 14 8 8 14" fill="none" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
					<circle cx="128" cy="40" r="5" fill="#fbbf24"/>
					<path d="M36 58c-6 4-4 14 4 12" fill="none" stroke="#38bdf8" stroke-width="4" stroke-linecap="round"/>
					<circle cx="34" cy="52" r="4" fill="#0ea5e9"/>
				</svg>
			</div>
			<h4 class="ls-quote-params-empty__title" id="{{ $emptyTitleId }}">No parameters available</h4>
			<p class="ls-quote-params-empty__text" id="{{ $emptyTextId }}"></p>
		</div>

		<div id="{{ $tableWrapId }}" class="ls-quote-analyte-table is-stage-visible">
			<div
				id="{{ $loadingId }}"
				class="ls-quote-params-stage-loading"
				hidden
				role="status"
				aria-live="polite"
				aria-label="Loading parameters"
			>
				<span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
				<span class="ls-quote-params-stage-loading__label">Updating parameters…</span>
			</div>
			<table class="ls-quote-analyte-grid">
				<colgroup>
					<col class="ls-quote-analyte-col--select">
					<col class="ls-quote-analyte-col--name">
					<col class="ls-quote-analyte-col--acc">
					<col class="ls-quote-analyte-col--sub">
					<col class="ls-quote-analyte-col--loq">
					<col class="ls-quote-analyte-col--mu">
					<col class="ls-quote-analyte-col--tat">
				</colgroup>
				<thead>
					<tr>
						<th scope="col" aria-label="Select"></th>
						<th scope="col">Analyte</th>
						<th scope="col" class="text-center">Acc</th>
						<th scope="col" class="text-center">Sub</th>
						<th scope="col" class="text-center">LOQ</th>
						<th scope="col" class="text-center">MU%</th>
						<th scope="col" class="text-center" title="Turnaround time (days)">TAT</th>
					</tr>
				</thead>
				<tbody id="{{ $tbodyId }}"></tbody>
			</table>
		</div>
	</div>

	@if(filled($footNote))
		<div class="ls-quote-params-dock">
			<p class="ls-quote-params-foot">{{ $footNote }}</p>
		</div>
	@endif
</div>
