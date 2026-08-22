{{--
	Shared Amspec import modal chrome (pricelist show “Import into pricelist” as base).

	Required:
	  $context — 'pricelist' | 'quotation'
	Optional Livewire bindings assumed when $driver === 'livewire' (default).
--}}
@php
	$context = $context ?? 'pricelist';
	$driver = $driver ?? 'livewire';
	$isPricelist = $context === 'pricelist';
	$format = $importFormat ?? 'excel';
	$pricingMode = $importPricingMode ?? 'per_package';
	$title = $isPricelist ? 'Import into pricelist' : 'Import quotation lines';
	$subtitle = $isPricelist
		? 'Choose Excel or PDF, then upload package prices.'
		: 'Import sample packages into this quotation from Excel or PDF.';
	$pdfTemplateUrl = route('billing.templates.amspec_quotation_preparation');
	$excelTemplateUrl = route('billing.templates.amspec_import_excel', [
		'context' => $isPricelist ? 'pricelist' : 'quotation',
	]);
	$accept = $format === 'pdf'
		? '.pdf,application/pdf'
		: '.xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	$hint = $format === 'pdf'
		? 'Amspec Quotation – preparation PDF · up to 20 MB'
		: 'Excel (.xlsx / .xls / .csv) · up to 20 MB';
	$bootstrap = $driver === 'jquery';
	$isOpen = (bool) ($isOpen ?? ($showImportModal ?? false));
@endphp
@include('layouts.lab.partials.billing.amspec-import-modal-styles')
@if($bootstrap)
{{-- Bootstrap owns show/hide animation; markup stays mounted. --}}
<div class="modal fade ls-quotation-workflow-modal ls-amspec-import-modal ls-ui-kit" id="{{ $modalId ?? 'ls-quote-import-modal' }}" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl ls-amspec-import-dialog" role="document">
		<div class="modal-content item-modal-content">
@else
{{-- Livewire keeps modal mounted to avoid morph wiping sibling page content. --}}
<div
	class="modal fade ls-amspec-import-modal ls-ui-kit {{ $isOpen ? 'show d-block' : '' }}"
	tabindex="-1"
	wire:key="ls-amspec-import-livewire-modal"
	@if($isOpen)
		style="background-color: rgba(15, 23, 42, 0.55);"
		wire:click.self="{{ $closeMethod ?? 'closeImportModal' }}"
		aria-modal="true"
		aria-hidden="false"
	@else
		style="display: none;"
		aria-hidden="true"
	@endif
>
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl ls-amspec-import-dialog">
		<div class="modal-content item-modal-content" wire:click.stop>
@endif
			<div class="modal-header item-modal-header border-0 ls-amspec-import-header">
				<div>
					<h5 class="modal-title mb-1">{{ $title }}</h5>
					<p class="mb-0 ls-amspec-import-subtitle">{{ $subtitle }}</p>
				</div>
				@if($bootstrap)
					<button type="button" class="close ls-amspec-import-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				@else
					<button type="button" class="btn-close btn-close-white ls-amspec-import-close" wire:click="{{ $closeMethod ?? 'closeImportModal' }}"></button>
				@endif
			</div>

			<div class="modal-body item-modal-body">
				<div class="ls-amspec-guide-card mb-3">
					<div class="ls-amspec-guide-card__visual" aria-hidden="true">
						<div class="ls-amspec-import-hero__orb ls-amspec-import-hero__orb--in-card">
							<div class="ls-amspec-import-hero__doc ls-amspec-import-hero__doc--back"></div>
							<div class="ls-amspec-import-hero__doc ls-amspec-import-hero__doc--mid"></div>
							<div class="ls-amspec-import-hero__doc ls-amspec-import-hero__doc--front">
								<span></span><span></span><span></span>
							</div>
							<div class="ls-amspec-import-hero__arrow">
								<i class="mdi mdi-tray-arrow-up"></i>
							</div>
						</div>
					</div>
					<div class="ls-amspec-guide-card__body">
						<div class="ls-amspec-guide-card__eyebrow">
							<i class="mdi mdi-information-outline"></i>
							Template guide
						</div>
						<strong class="ls-amspec-guide-card__title">Amspec quotation-preparation template</strong>
						<p class="ls-amspec-guide-card__text">
							Uses the Sample / Test / Method / Quantity Required / TAT / No. of Samples / Unit Price / Total layout.
							<strong>Package total = No. of samples × Unit price</strong> (once) — not × number of tests.
						</p>
						<p class="ls-amspec-guide-card__text ls-amspec-guide-card__text--muted">
							Sample type and parameter names must match LIMS. Skipped rows appear as warnings.
						</p>
						<div class="ls-amspec-guide-card__actions">
							<a href="{{ $excelTemplateUrl }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
								<i class="mdi mdi-file-excel"></i> Download Excel template
							</a>
							<a href="{{ $pdfTemplateUrl }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
								<i class="mdi mdi-file-pdf-box"></i> Download PDF template
							</a>
						</div>
					</div>
				</div>

				<div class="ls-amspec-options-row mb-3">
					<div class="item-modal-section ls-amspec-options-col">
						<label class="form-label item-modal-label d-block">File type</label>
						<div class="pricelist-mode-chips ls-amspec-option-pills" role="group" aria-label="Import format">
							@if($bootstrap)
								<button type="button" class="pricelist-mode-chip ls-amspec-fmt is-active" data-format="excel">
									<i class="mdi mdi-file-excel"></i> Excel
								</button>
								<button type="button" class="pricelist-mode-chip ls-amspec-fmt" data-format="pdf" id="ls-amspec-fmt-pdf">
									<i class="mdi mdi-file-pdf-box"></i> PDF
								</button>
							@else
								<button type="button"
										class="pricelist-mode-chip {{ $format === 'excel' ? 'is-active' : '' }}"
										wire:click="$set('importFormat', 'excel')">
									<i class="mdi mdi-file-excel"></i> Excel
								</button>
								<button type="button"
										class="pricelist-mode-chip {{ $format === 'pdf' ? 'is-active' : '' }}"
										wire:click="$set('importFormat', 'pdf')">
									<i class="mdi mdi-file-pdf-box"></i> PDF
								</button>
							@endif
						</div>
						<div class="ls-amspec-option-explain">
							<p class="ls-amspec-fmt-explain ls-amspec-fmt-explain--excel mb-0" @if($format === 'pdf') hidden @endif>
								Upload a filled spreadsheet from the Excel template.
								@if($isPricelist)
									Use columns <code>sample_type</code>, <code>parameters</code>, <code>unit_price</code>, and optional <code>tax</code>.
								@else
									Use columns <code>sample_type</code>, <code>parameters</code>, <code>quantity_required</code>, <code>quantity</code>, and <code>unit_price</code>.
								@endif
							</p>
							<p class="ls-amspec-fmt-explain ls-amspec-fmt-explain--pdf mb-0" @if($format !== 'pdf') hidden @endif>
								Upload the Amspec quotation-preparation PDF.
								@if($isPricelist)
									Package rows are imported and the PDF is saved on this pricelist.
								@else
									Sample packages and prices are read from the document layout.
								@endif
							</p>
						</div>
					</div>

					<div class="item-modal-section ls-amspec-options-col">
						<label class="form-label item-modal-label d-block">Pricing mode</label>
						<div class="pricelist-mode-chips ls-amspec-option-pills" role="group" aria-label="Import pricing mode">
							@if($bootstrap)
								<button type="button" class="pricelist-mode-chip ls-amspec-mode is-active" data-mode="per_package">
									<i class="mdi mdi-package-variant"></i> Per package
								</button>
								<button type="button" class="pricelist-mode-chip ls-amspec-mode" data-mode="per_test">
									<i class="mdi mdi-flask-outline"></i> Per test
								</button>
							@else
								<button type="button"
										class="pricelist-mode-chip {{ $pricingMode === 'per_package' ? 'is-active' : '' }}"
										wire:click="$set('importPricingMode', 'per_package')">
									<i class="mdi mdi-package-variant"></i> Per package
								</button>
								<button type="button"
										class="pricelist-mode-chip {{ $pricingMode === 'per_test' ? 'is-active' : '' }}"
										wire:click="$set('importPricingMode', 'per_test')">
									<i class="mdi mdi-flask-outline"></i> Per test
								</button>
							@endif
						</div>
						<div class="ls-amspec-option-explain">
							<p class="ls-amspec-mode-explain ls-amspec-mode-explain--package mb-0" @if($pricingMode === 'per_test') hidden @endif>
								One unit price covers the whole sample package.
								Total = No. of samples × Unit price (once), not × number of tests.
							</p>
							<p class="ls-amspec-mode-explain ls-amspec-mode-explain--test mb-0" @if($pricingMode !== 'per_test') hidden @endif>
								Each analysis is billed separately at its own unit price.
								Use this when tests are priced individually instead of as one package.
							</p>
						</div>
					</div>
				</div>

				<div class="item-modal-section">
					@include('layouts.lab.partials.ls-ui.upload.ls-upload-files', [
						'title' => 'File',
						'required' => true,
						'subtitle' => 'Excel workbook or Amspec quotation-preparation PDF.',
						'hint' => $hint,
						'accept' => $accept,
						'showUrlImport' => false,
						'showDemoFiles' => false,
						'showHeadClose' => false,
						'multiple' => false,
						'inputId' => $inputId ?? ($isPricelist ? 'ls-pricelist-import-file' : 'ls-quote-import-file'),
						'wireModel' => $bootstrap ? null : ($wireModel ?? 'importFile'),
						'errorBag' => $bootstrap ? null : ($errorBag ?? 'importFile'),
					])
					@if($bootstrap)
						<div class="small text-danger mt-1" id="ls-amspec-import-error" hidden></div>
					@else
						<div wire:loading wire:target="importFile" class="small text-muted mt-1">Uploading…</div>
					@endif
				</div>
			</div>

			<div class="modal-footer border-0 item-modal-footer">
				@if($bootstrap)
					<button type="button" class="btn btn-light item-modal-cancel-btn" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary item-modal-save-btn" id="ls-amspec-import-submit">
						<i class="mdi mdi-upload"></i> Import
					</button>
				@else
					<button type="button" class="btn btn-light item-modal-cancel-btn" wire:click="{{ $closeMethod ?? 'closeImportModal' }}">Cancel</button>
					<button type="button" class="btn btn-primary item-modal-save-btn" wire:click="{{ $submitMethod ?? 'submitImport' }}" wire:loading.attr="disabled">
						<span wire:loading.remove wire:target="{{ $submitMethod ?? 'submitImport' }}"><i class="mdi mdi-upload"></i> Import</span>
						<span wire:loading wire:target="{{ $submitMethod ?? 'submitImport' }}"><span class="spinner-border spinner-border-sm mr-1"></span> Working…</span>
					</button>
				@endif
			</div>
		</div>
	</div>
</div>

