@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
<title>LS UI Kit Gallery | Lab</title>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme workflow-theme lab-surface-theme ls-ui-kit" data-ls-type="plex">
	@include('layouts.lab.partials.lab-panel-theme-styles')
	@include('layouts.lab.partials.lab-surface-theme-styles')
	@include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')

	@php
		$items = [
			['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
			['link' => route('lab.ui.ls-gallery'), 'name' => 'LS UI Kit', 'icon' => null],
			['link' => route('lab.ui.chrome-playground'), 'name' => 'Chrome Playground', 'icon' => null],
		];
	@endphp
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="batch-header-bar mb-3">
		<div class="batch-header-top">
			<div class="batch-title-group">
				<span class="batch-code-label">Lab Design System</span>
				<span class="batch-stage-pill">
					<i class="mdi mdi-palette-outline" style="font-size:0.75rem;"></i>
					LS complementary kit (opt-in)
				</span>
			</div>
		</div>
	</div>

	<p class="text-muted small mb-4">
		Private preview only. Each demo shows the Blade include path to copy. Styles for <code>.ls-*</code> load only on this page unless you also include <code>ls-ui-tokens-and-styles</code>.
		Also try the <a href="{{ route('lab.ui.chrome-playground') }}">Chrome Playground</a> (sidebar + request pill Slice 1).
	</p>

	<nav class="ls-gallery-toc" aria-label="Gallery sections">
		<a href="{{ route('lab.ui.chrome-playground') }}">Chrome Playground</a>
		<a href="#ls-sec-tokens" onclick="document.getElementById('ls-sec-tokens').open=true">Tokens</a>
		<a href="#ls-sec-field-columns-4" onclick="document.getElementById('ls-sec-field-columns-4').open=true">Field columns (4)</a>
		<a href="#ls-sec-select2-shells-2" onclick="document.getElementById('ls-sec-select2-shells-2').open=true">Select2 shells (2)</a>
		<a href="#ls-sec-search-bars-amp-searchable-select" onclick="document.getElementById('ls-sec-search-bars-amp-searchable-select').open=true">Search bars &amp; searchable select</a>
		<a href="#ls-sec-select2-multi-dropdown-search-columns-view-edit" onclick="document.getElementById('ls-sec-select2-multi-dropdown-search-columns-view-edit').open=true">Select2 multi — dropdown search, columns, view/edit</a>
		<a href="#ls-sec-cards" onclick="document.getElementById('ls-sec-cards').open=true">Cards</a>
		<a href="#ls-sec-tables" onclick="document.getElementById('ls-sec-tables').open=true">Tables</a>
		<a href="#ls-sec-upload" onclick="document.getElementById('ls-sec-upload').open=true">Upload</a>
		<a href="#ls-sec-card-stepper" onclick="document.getElementById('ls-sec-card-stepper').open=true">Card + stepper</a>
		<a href="#ls-sec-icons-amp-motion" onclick="document.getElementById('ls-sec-icons-amp-motion').open=true">Icons &amp; motion</a>
		<a href="#ls-sec-dropdown-menus" onclick="document.getElementById('ls-sec-dropdown-menus').open=true">Dropdown menus</a>
		<a href="#ls-sec-typography" onclick="document.getElementById('ls-sec-typography').open=true">Typography</a>
		<a href="#ls-sec-trf-field-grids" onclick="document.getElementById('ls-sec-trf-field-grids').open=true">TRF field grids</a>
		<a href="#ls-sec-rich-text-columns-2" onclick="document.getElementById('ls-sec-rich-text-columns-2').open=true">Rich text columns (2)</a>
		<a href="#ls-sec-trf-food-water-steppers" onclick="document.getElementById('ls-sec-trf-food-water-steppers').open=true">Food / Water TRF steppers</a>
		<a href="#ls-sec-toast-notifications" onclick="document.getElementById('ls-sec-toast-notifications').open=true">Toast notifications</a>
		<a href="#ls-sec-drag-and-drop-2" onclick="document.getElementById('ls-sec-drag-and-drop-2').open=true">Drag and drop (2)</a>
	</nav>


	{{-- Tokens --}}
	<details class="ls-gallery-section" id="ls-sec-tokens">
		<summary>
			<div>
				<h3>Tokens</h3>
				<p class="lead-muted">60/30/10: surface / slate secondary / burgundy accent + soft blue complement. (CSS vars on <code>.lab-panel-theme</code> / surface theme — not a Blade partial.)</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="ls-swatch-row">
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;"></div><div class="ls-swatch__meta">Surface<br>#f8fafc</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#1e293b;"></div><div class="ls-swatch__meta">Secondary<br>#1e293b</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:var(--workflow-accent,#8b1e2d);"></div><div class="ls-swatch__meta">Accent<br>burgundy</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#eff6ff;"></div><div class="ls-swatch__meta">Blue soft<br>#eff6ff</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#dbeafe;"></div><div class="ls-swatch__meta">Blue border<br>#dbeafe</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#93c5fd;"></div><div class="ls-swatch__meta">Blue focus<br>#93c5fd</div></div>
		</div>
		<button type="button" class="btn btn-sm workflow-secondary-fill">Secondary fill button</button>
		</div>
	</details>

	{{-- Fields --}}
	<details class="ls-gallery-section" id="ls-sec-field-columns-4">
		<summary>
			<div>
				<h3>Field columns (4)</h3>
				<p class="lead-muted">Text, affix (Qty / Unit), status select, search combo — not Select2.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-6">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-text')</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Sampling Point',
						'name' => 'demo_point',
						'value' => 'point 1',
						'hint' => 'Ordinary text input column.',
					])
				</div>
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-text') — error state</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Username',
						'name' => 'demo_user_err',
						'value' => 'taken',
						'error' => 'This username is not available.',
					])
				</div>
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-text') — success state</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
						'label' => 'Username',
						'name' => 'demo_user_ok',
						'value' => 'available_user',
						'success' => 'This username is available!',
					])
				</div>
			</div>
			<div class="col-md-6">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-affix') — Qty / Unit</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
						'label' => 'Qty / Unit',
						'name' => 'demo_qty',
						'value' => '23',
						'suffixSelect' => ['g' => 'g', 'kg' => 'kg', 'ml' => 'ml'],
					])
				</div>
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-affix') — prefix</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
						'label' => 'Website',
						'name' => 'demo_url',
						'prefix' => 'https://',
						'placeholder' => 'example.com',
					])
				</div>
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-affix') — action button</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
						'label' => 'Email',
						'name' => 'demo_email',
						'placeholder' => 'you@lab.com',
						'actionLabel' => 'Subscribe',
					])
				</div>
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-status-select')</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-status-select', [
						'label' => 'Status',
						'hint' => 'Indicator select with colored dots.',
					])
				</div>
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-search-combo')</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-combo', [
						'label' => 'Country in EU',
						'hint' => 'Native combo (Alpine) — not Select2.',
						'selected' => 'Croatia',
					])
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Select2 --}}
	<details class="ls-gallery-section" id="ls-sec-select2-shells-2">
		<summary>
			<div>
				<h3>Select2 shells (2)</h3>
				<p class="lead-muted">jQuery Select2 only — separate from the field columns above.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-6">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.select2.ls-select2-single-search')</code>
					@include('layouts.lab.partials.ls-ui.select2.ls-select2-single-search', [
						'label' => 'Search — Basic',
						'name' => 'demo_s2_single',
						'hint' => 'Magnifier + clearable single Select2.',
					])
				</div>
			</div>
			<div class="col-md-6">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-select2-multi')</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-select2-multi', [
						'label' => 'Tests',
						'name' => 'demo_s2_multi',
						'required' => true,
						'hint' => 'Burgundy pills only inside .ls-select2-multi. Alias: select2.ls-select2-multi-tag',
					])
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Pack B: Search bars + searchable select --}}
	<details class="ls-gallery-section" id="ls-sec-search-bars-amp-searchable-select">
		<summary>
			<div>
				<h3>Search bars &amp; searchable select</h3>
				<p class="lead-muted">Pill search + filter variants; compact searchable single (blue focus / green success). Width fits content.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="d-flex flex-wrap gap-3 mb-3">
			<div class="ls-gallery-demo ls-compact">
				<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-search-bar') — detached</code>
				@include('layouts.lab.partials.ls-ui.fields.ls-search-bar', ['variant' => 'detached'])
			</div>
			<div class="ls-gallery-demo ls-compact">
				<code class="ls-gallery-include">@@include('…ls-search-bar', ['variant' => 'ghost'])</code>
				@include('layouts.lab.partials.ls-ui.fields.ls-search-bar', ['variant' => 'ghost'])
			</div>
			<div class="ls-gallery-demo ls-compact">
				<code class="ls-gallery-include">@@include('…ls-search-bar', ['variant' => 'inline'])</code>
				@include('layouts.lab.partials.ls-ui.fields.ls-search-bar', ['variant' => 'inline'])
			</div>
			<div class="ls-gallery-demo ls-compact">
				<code class="ls-gallery-include">@@include('…ls-search-bar', ['variant' => 'plain'])</code>
				@include('layouts.lab.partials.ls-ui.fields.ls-search-bar', ['variant' => 'plain'])
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic')</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Select Country',
						'hint' => 'Typeahead list; selected uses success border.',
						'selected' => null,
					])
				</div>
			</div>
			<div class="col-md-5">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('…ls-field-search-basic') — preselected</code>
					@include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
						'label' => 'Search — Basic',
						'hint' => 'This is a hint text to help user.',
						'selected' => 'Candice Cano',
						'success' => true,
					])
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Pack B: Select2 multi with dropdown search / columns / view-edit --}}
	<details class="ls-gallery-section" id="ls-sec-select2-multi-dropdown-search-columns-view-edit">
		<summary>
			<div>
				<h3>Select2 multi — dropdown search, columns, view/edit</h3>
				<p class="lead-muted">No profile pics. Tags: slate or burgundy. Chip box height = tags only; search is only inside the open dropdown.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-4">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search')</code>
					@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
						'label' => 'Category (dropdown search)',
						'name' => 'demo_s2_dd',
						'hint' => 'Search bar inside open list + checkbox style.',
					])
				</div>
			</div>
			<div class="col-md-4">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns')</code>
					@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns', [
						'label' => 'CRM values (multi-column)',
						'name' => 'demo_s2_cols',
						'hint' => 'Label + count meta in each option row.',
					])
				</div>
			</div>
			<div class="col-md-4">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.select2.ls-select2-single-columns')</code>
					@include('layouts.lab.partials.ls-ui.select2.ls-select2-single-columns', [
						'label' => 'Sample type (single + category meta)',
						'name' => 'demo_s2_single_cols',
						'placeholder' => 'Search…',
						'options' => [
							['value' => '1', 'label' => 'Drinking Water', 'meta' => 'Water'],
							['value' => '2', 'label' => 'Halal', 'meta' => 'Food'],
							['value' => '3', 'label' => 'Soil', 'meta' => 'Environmental'],
						],
						'hint' => 'Name + category columns. Click away to close.',
					])
				</div>
			</div>
			<div class="col-md-4">
				<div class="ls-gallery-demo ls-compact ls-compact-wide">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.select2.ls-select2-view-edit')</code>
					@include('layouts.lab.partials.ls-ui.select2.ls-select2-view-edit', [
						'label' => 'Current Employee',
						'name' => 'demo_s2_ve',
						'mode' => 'edit',
					])
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Cards --}}
	<details class="ls-gallery-section" id="ls-sec-cards">
		<summary>
			<div>
				<h3>Cards</h3>
				<p class="lead-muted">Soft / hierarchy (Pack A) + collection settings + notification center.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.cards.ls-trf-sample-panel')</code>
					@include('layouts.lab.partials.ls-ui.cards.ls-trf-sample-panel', [
						'title' => 'Sample 1 · Water',
						'expanded' => true,
						'bodyHtml' => '<p class="mb-0 small text-muted">Burgundy title + baby-blue expanded header for TRF sample cards.</p>',
					])
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.cards.ls-soft-card')</code>
					@include('layouts.lab.partials.ls-ui.cards.ls-soft-card', [
						'title' => 'Sample 1 - bacon',
						'expanded' => true,
						'bodyHtml' => '<div class="row"><div class="col-6"><label class="ls-field__label">Sampling Point</label><input class="form-control form-control-sm" value="point 1"></div><div class="col-6"><label class="ls-field__label">Qty</label><input class="form-control form-control-sm" value="23"></div></div>',
					])
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.cards.ls-hierarchy-card')</code>
					@include('layouts.lab.partials.ls-ui.cards.ls-hierarchy-card')
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.cards.ls-card-collection')</code>
					@include('layouts.lab.partials.ls-ui.cards.ls-card-collection')
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.cards.ls-card-notifications')</code>
					@include('layouts.lab.partials.ls-ui.cards.ls-card-notifications')
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Tables --}}
	<details class="ls-gallery-section" id="ls-sec-tables">
		<summary>
			<div>
				<h3>Tables</h3>
				<p class="lead-muted">Status / plain + filterable headers (funnel icons). Dense rows, width fits content.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.tables.ls-table-status')</code>
					@include('layouts.lab.partials.ls-ui.tables.ls-table-status')
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.tables.ls-table-filterable')</code>
					@include('layouts.lab.partials.ls-ui.tables.ls-table-filterable')
				</div>
			</div>
			<div class="col-md-5 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.tables.ls-table-plain')</code>
					@include('layouts.lab.partials.ls-ui.tables.ls-table-plain', ['skeleton' => true])
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Upload --}}
	<details class="ls-gallery-section" id="ls-sec-upload">
		<summary>
			<div>
				<h3>Upload</h3>
				<p class="lead-muted">Image drop (states) + multi-file list with URL import.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.upload.ls-upload-image')</code>
					@include('layouts.lab.partials.ls-ui.upload.ls-upload-image', ['state' => 'idle'])
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.upload.ls-upload-files')</code>
					@include('layouts.lab.partials.ls-ui.upload.ls-upload-files')
				</div>
			</div>
		</div>
		</div>
	</details>

	{{-- Stepper --}}
	<details class="ls-gallery-section" id="ls-sec-card-stepper">
		<summary>
			<div>
				<h3>Card + stepper</h3>
				<p class="lead-muted">Vertical steps + form panel (theme blue / slate / accent).</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="ls-gallery-demo">
			<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.cards.ls-card-stepper')</code>
			@include('layouts.lab.partials.ls-ui.cards.ls-card-stepper')
		</div>
		</div>
	</details>

	{{-- Icons + motion --}}
	<details class="ls-gallery-section" id="ls-sec-icons-amp-motion">
		<summary>
			<div>
				<h3>Icons &amp; motion</h3>
				<p class="lead-muted">Curated from quotations, pricelist, sample receiving, worksheets, and TRF. Includes awaiting-approval symbols. Colorful tiles allowed; motion is purposeful.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="ls-gallery-demo">
			<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.icons.ls-icon-catalog')</code>
			@include('layouts.lab.partials.ls-ui.icons.ls-icon-catalog')
		</div>
		</div>
	</details>

	{{-- Dropdown menus --}}
	<details class="ls-gallery-section" id="ls-sec-dropdown-menus">
		<summary>
			<div>
				<h3>Dropdown menus</h3>
				<p class="lead-muted">Actions menu (receiving/quotation style), outline, and kebab / icon-only. Alpine open/close.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="ls-gallery-demo">
			<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.menus.ls-dropdown-menu')</code>
			<div class="ls-dropdown-demo-row">
				@include('layouts.lab.partials.ls-ui.menus.ls-dropdown-menu', [
					'label' => 'Actions',
					'variant' => 'secondary',
					'items' => [
						['icon' => 'mdi-eye-outline', 'label' => 'View quotation'],
						['icon' => 'mdi-file-clock-outline', 'label' => 'Pending review'],
						['icon' => 'mdi-file-document-alert-outline', 'label' => 'Needs approval'],
						['icon' => 'mdi-account-clock-outline', 'label' => 'Awaiting approver'],
						['icon' => 'mdi-share-circle', 'label' => 'Request approval'],
						['icon' => 'mdi-check-decagram', 'label' => 'Mark approved'],
						['icon' => 'mdi-printer', 'label' => 'Process PDF'],
						['icon' => 'mdi-delete-empty', 'label' => 'Delete', 'danger' => true],
					],
				])
				@include('layouts.lab.partials.ls-ui.menus.ls-dropdown-menu', [
					'label' => 'More',
					'variant' => 'outline',
					'items' => [
						['icon' => 'mdi-content-duplicate', 'label' => 'Duplicate'],
						['icon' => 'mdi-source-branch', 'label' => 'Create revision'],
						['icon' => 'mdi-email-send-outline', 'label' => 'Send SOA'],
					],
				])
				@include('layouts.lab.partials.ls-ui.menus.ls-dropdown-menu', [
					'label' => 'Row actions',
					'variant' => 'kebab',
					'items' => [
						['icon' => 'mdi-eye-outline', 'label' => 'View'],
						['icon' => 'mdi-pencil-outline', 'label' => 'Edit'],
						['icon' => 'mdi-file-clock-outline', 'label' => 'Awaiting approval'],
						['icon' => 'mdi-close-circle-outline', 'label' => 'Reject', 'danger' => true],
					],
				])
				@include('layouts.lab.partials.ls-ui.menus.ls-dropdown-menu', [
					'label' => 'Overflow',
					'variant' => 'icon',
					'items' => [
						['icon' => 'mdi-barcode', 'label' => 'Print labels'],
						['icon' => 'mdi-package-down', 'label' => 'Receive samples'],
						['icon' => 'mdi-clipboard-check-outline', 'label' => 'Integrity check'],
					],
				])
			</div>
		</div>
		</div>
	</details>

	{{-- Typography --}}
	<details class="ls-gallery-section" id="ls-sec-typography">
		<summary>
			<div>
				<h3>Typography</h3>
				<p class="lead-muted">IBM Plex scale for lab surfaces — display through caption + mono codes.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="ls-gallery-demo" style="max-width: 40rem;">
			<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.forms.ls-typography')</code>
			@include('layouts.lab.partials.ls-ui.forms.ls-typography')
		</div>
		</div>
	</details>

	{{-- TRF field grids --}}
	<details class="ls-gallery-section" id="ls-sec-trf-field-grids">
		<summary>
			<div>
				<h3>TRF field grids</h3>
				<p class="lead-muted">Column grids imitating request-view / TRF fill: text, qty/unit, dates, selects, <strong>checkbox/radio option chips</strong> (transport, method, apparatus), textarea.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="ls-gallery-demo">
			<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.forms.ls-trf-field-grid')</code>
			@include('layouts.lab.partials.ls-ui.forms.ls-trf-field-grid')
		</div>
		</div>
	</details>

	{{-- Rich text columns (TRF sample description) --}}
	<details class="ls-gallery-section" id="ls-sec-rich-text-columns-2" open>
		<summary>
			<div>
				<h3>Rich text columns (2)</h3>
				<p class="lead-muted">Modern replacements for TRF sample description: <strong>inline column</strong> (card / full-width) and <strong>table cell expand</strong> (no legacy Bootstrap modal). TinyMCE hooks via <code>$wireModel</code> + <code>$editorId</code>.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
			<div class="row mb-3">
				<div class="col-lg-5 mb-3">
					<div class="ls-gallery-demo">
						<code class="ls-gallery-include">Current TRF (reference)</code>
						<p class="small text-muted mb-2">Bare TinyMCE / outline modal button — flat toolbar, no ls-field chrome.</p>
						<div class="ls-rich-text-legacy">
							<div class="ls-rich-text-legacy__toolbar">B · I · U · list</div>
							<div class="ls-rich-text-legacy__body">Composite sample from line 3 — retain cold chain notes…</div>
						</div>
						<button type="button" class="btn btn-outline-secondary btn-sm mt-2" disabled>
							<i class="mdi mdi-pencil-outline"></i> Edit description
						</button>
					</div>
				</div>
				<div class="col-lg-7 mb-3">
					<div class="ls-gallery-demo">
						<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-inline')</code>
						@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-inline', [
							'label' => 'Sample description',
							'name' => 'demo_rich_inline',
							'required' => true,
							'hint' => 'Card column — ls-field label, soft toolbar, focus ring. Wire TinyMCE with $wireModel in production.',
							'value' => '<p>Composite sample from line 3 — retain <strong>cold chain</strong> notes and batch reference on label.</p>',
						])
					</div>
					<div class="ls-gallery-demo mt-3">
						@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-inline', [
							'label' => 'Sample description',
							'name' => 'demo_rich_inline_err',
							'error' => 'Description is required for this sample type.',
							'compact' => true,
						])
					</div>
				</div>
			</div>

			<div class="ls-gallery-demo">
				<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell') — in capture table</code>
				<p class="small text-muted mb-2">Table cell: truncated preview + expand panel (click row cell to edit).</p>
				<div class="ls-table-wrap ls-table-wrap--cell-expand">
					<table class="ls-table ls-table--dense mb-0" style="min-width: 36rem;">
						<thead>
							<tr>
								<th>#</th>
								<th>Sample type</th>
								<th style="min-width: 14rem;">Description</th>
								<th>Qty</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>1</td>
								<td>Seafood composite</td>
								<td style="position: relative; overflow: visible;">
									@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
										'rowLabel' => 'Sample 1',
										'value' => '<p>Seafood composite — <strong>lot B-441</strong>, keep refrigerated.</p>',
										'hint' => 'Supports bold, lists, and multi-line notes.',
									])
								</td>
								<td>3</td>
							</tr>
							<tr>
								<td>2</td>
								<td>Swab</td>
								<td style="position: relative; overflow: visible;">
									@include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
										'rowLabel' => 'Sample 2',
										'preview' => 'Add description…',
										'compact' => true,
									])
								</td>
								<td>1</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</details>

	{{-- Food / Water TRF stepper card forms (Edit request details) --}}
	<details class="ls-gallery-section" id="ls-sec-trf-food-water-steppers" open>
		<summary>
			<div>
				<h3>Food / Water TRF steppers</h3>
				<p class="lead-muted">Full Customer → Collection → Samples steppers using the <strong>exact Edit request details columns</strong>: <code>ls-field-search-basic</code>, <code>ls-select2-multi-dropdown-search</code>, <code>ls-select2-multi-columns</code>, <code>ls-field-affix</code> (Qty/Unit), option chips. Water adds Test requirement.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
			<div class="row">
				<div class="col-lg-6 mb-3">
					<div class="ls-gallery-demo">
						<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.forms.ls-trf-food-stepper-form')</code>
						@include('layouts.lab.partials.ls-ui.forms.ls-trf-food-stepper-form', ['step' => 1])
					</div>
				</div>
				<div class="col-lg-6 mb-3">
					<div class="ls-gallery-demo">
						<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.forms.ls-trf-water-stepper-form')</code>
						@include('layouts.lab.partials.ls-ui.forms.ls-trf-water-stepper-form', ['step' => 3])
					</div>
				</div>
			</div>
		</div>
	</details>

	{{-- Quotation composites --}}
	<details class="ls-gallery-section" id="ls-sec-quotation">
		<summary>
			<div>
				<h3>Quotation overview</h3>
				<p class="lead-muted">Detached search bar + filter dropdown panel (filters stay off the main row).</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
			<div class="ls-gallery-demo">
				<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.quotation.ls-quotation-search-toolbar')</code>
				@include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')
				<div class="ls-quotation-shell">
					<div class="ls-quotation-list-header__search" style="max-width:28rem;" x-data="{ filtersOpen: false }" @click.outside="filtersOpen = false">
						<div class="ls-quotation-search-toolbar__row">
							<div class="ls-search-bar ls-quotation-search-toolbar__search">
								<div class="ls-search-bar__field">
									<i class="mdi mdi-magnify" aria-hidden="true"></i>
									<input type="search" placeholder="Quote #, customer, contact…">
								</div>
								<button type="button" class="ls-search-bar__filter ls-search-bar__filter--ghost" @click="filtersOpen = !filtersOpen">
									<i class="mdi mdi-tune-variant"></i>
								</button>
							</div>
						</div>
						<div class="ls-quotation-filter-panel" x-show="filtersOpen" x-cloak>
							<div class="ls-quotation-filter-panel__head"><span>Filters</span></div>
							<p class="small text-muted mb-0">Customer, type, lab, sort, dates — compact field columns.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</details>

	{{-- DnD --}}
	{{-- Toast notifications --}}
	<details class="ls-gallery-section" id="ls-sec-toast-notifications">
		<summary>
			<div>
				<h3>Toast notifications</h3>
				<p class="text-muted small mb-0">Imara toast stack — four motion variants used on TRF fill, direct registration, and request view.</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
			<code class="ls-gallery-include">window.showImaraToast({ type, title, message, variant })</code>
			<div class="ls-gallery-demo ls-compact-wide mt-3" x-data="{ showImaraToast: window.showImaraToast }">
				@include('layouts.lab.partials.ls-ui.feedback.ls-toast-gallery')
			</div>
		</div>
	</details>

	{{-- Drag and drop --}}
	<details class="ls-gallery-section" id="ls-sec-drag-and-drop-2">
		<summary>
			<div>
				<h3>Drag and drop (2)</h3>
				<p class="lead-muted">Flat reorder list + nested tree (gallery demo JS only).</p>
			</div>
		</summary>
		<div class="ls-gallery-section__body">
		<div class="row">
			<div class="col-md-5 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.dnd.ls-dnd-reorder-list')</code>
					@include('layouts.lab.partials.ls-ui.dnd.ls-dnd-reorder-list')
				</div>
			</div>
			<div class="col-md-7 mb-3">
				<div class="ls-gallery-demo">
					<code class="ls-gallery-include">@@include('layouts.lab.partials.ls-ui.dnd.ls-dnd-tree')</code>
					@include('layouts.lab.partials.ls-ui.dnd.ls-dnd-tree')
				</div>
			</div>
		</div>
		</div>
	</details>
</main>

@push('scripts')
<script>
(function ($) {
	function clampLsSelect2Search($el) {
		var $container = $el.next('.select2-container');
		$container.find('.select2-search--inline .select2-search__field').attr('style', 'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;');
		$container.css({ maxWidth: '100%', overflow: 'hidden' });
	}

	function wireLsMultiDropdownSearch($el) {
		$el.off('select2:open.lsDdSearch select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch')
			.on('select2:open.lsDdSearch', function () {
			clampLsSelect2Search($el);
			var $dropdown = $('.select2-container--open .select2-dropdown');
			var $existing = $dropdown.find('.ls-dd-search');
			if ($existing.length) {
				$existing.find('input').val('').trigger('focus');
				return;
			}
			var $box = $('<div class="ls-dd-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input type="search" placeholder="Search…" autocomplete="off"></div>');
			$dropdown.prepend($box);
			var $input = $box.find('input');
			$input.on('input keyup', function () {
				var q = $input.val();
				var $hidden = $el.data('select2') && $el.data('select2').$selection
					? $el.data('select2').$selection.find('.select2-search__field')
					: $();
				if (!$hidden.length) {
					$hidden = $('.select2-container--open .select2-search--inline .select2-search__field');
				}
				$hidden.val(q).trigger('input').trigger('keyup');
			});
			setTimeout(function () {
				$input.trigger('focus');
			}, 0);
		}).on('select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch', function () {
			clampLsSelect2Search($el);
		});
	}

	function initLsGallerySelect2() {
		if (!$.fn.select2) {
			return;
		}
		$('.ls-select2-multi-el').each(function () {
			var $el = $(this);
			if ($el.hasClass('select2-hidden-accessible')) {
				return;
			}
			$el.select2({
				width: '100%',
				placeholder: $el.data('placeholder') || 'Select…',
				allowClear: true,
				closeOnSelect: false,
				dropdownCssClass: 'ls-select2-dropdown-search',
			});
			wireLsMultiDropdownSearch($el);
			clampLsSelect2Search($el);
		});
		$('.ls-select2-single-el').each(function () {
			var $el = $(this);
			if ($el.hasClass('select2-hidden-accessible')) {
				return;
			}
			$el.select2({
				width: '100%',
				placeholder: $el.data('placeholder') || 'Search…',
				allowClear: true,
			});
		});
		$('.ls-select2-multi-dropdown-search-el, .ls-select2-view-edit-el').each(function () {
			var $el = $(this);
			if ($el.hasClass('select2-hidden-accessible')) {
				return;
			}
			$el.select2({
				width: '100%',
				placeholder: $el.data('placeholder') || 'Select…',
				closeOnSelect: false,
				dropdownCssClass: 'ls-select2-dropdown-search',
				templateResult: function (data) {
					if (!data.id) {
						return data.text;
					}
					var selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
					var $row = $('<span class="ls-select2-meta-row"><span class="ls-select2-check">' + (selected ? '✓' : '') + '</span><span class="ls-select2-meta-row__label"></span></span>');
					$row.find('.ls-select2-meta-row__label').text(data.text);
					return $row;
				},
				escapeMarkup: function (m) { return m; },
			});
			wireLsMultiDropdownSearch($el);
			clampLsSelect2Search($el);
		});
		$('.ls-select2-multi-columns-el').each(function () {
			var $el = $(this);
			if ($el.hasClass('select2-hidden-accessible')) {
				return;
			}
			$el.select2({
				width: '100%',
				placeholder: $el.data('placeholder') || 'Select…',
				closeOnSelect: false,
				dropdownCssClass: 'ls-select2-dropdown-search',
				templateResult: function (data) {
					if (!data.id) {
						return data.text;
					}
					var meta = $(data.element).data('meta') || '';
					var selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
					var $row = $(
						'<span class="ls-select2-meta-row ls-select2-meta-row--spread">' +
							'<span class="ls-select2-meta-row__label"></span>' +
							'<span class="ls-select2-meta-row__meta"></span>' +
							(selected ? '<i class="mdi mdi-check" style="color:#2563eb;"></i>' : '') +
						'</span>'
					);
					$row.find('.ls-select2-meta-row__label').text(data.text);
					$row.find('.ls-select2-meta-row__meta').html(meta + ' <i class="mdi mdi-account-group-outline"></i>');
					return $row;
				},
				escapeMarkup: function (m) { return m; },
			});
			wireLsMultiDropdownSearch($el);
			clampLsSelect2Search($el);
		});
	}

	function initLsDndReorder() {
		document.querySelectorAll('[data-ls-dnd-reorder]').forEach(function (list) {
			var dragEl = null;
			list.querySelectorAll('.ls-dnd-item').forEach(function (item) {
				item.addEventListener('dragstart', function () {
					dragEl = item;
					item.classList.add('is-dragging');
				});
				item.addEventListener('dragend', function () {
					item.classList.remove('is-dragging');
					list.querySelectorAll('.ls-dnd-item').forEach(function (el) {
						el.classList.remove('is-ghost');
					});
					dragEl = null;
					var labels = Array.prototype.map.call(list.querySelectorAll('.ls-dnd-item__label'), function (n) {
						return n.textContent.trim();
					});
					var hidden = list.parentElement.querySelector('.ls-dnd-order-input');
					if (hidden) {
						hidden.value = labels.join(', ');
					}
				});
				item.addEventListener('dragover', function (e) {
					e.preventDefault();
					if (!dragEl || dragEl === item) {
						return;
					}
					item.classList.add('is-ghost');
					var rect = item.getBoundingClientRect();
					var before = (e.clientY - rect.top) < rect.height / 2;
					list.insertBefore(dragEl, before ? item : item.nextSibling);
				});
				item.addEventListener('dragleave', function () {
					item.classList.remove('is-ghost');
				});
			});
		});
	}

	function initLsDndTree() {
		document.querySelectorAll('[data-ls-dnd-tree] .ls-dnd-tree-node').forEach(function (node) {
			node.addEventListener('dragstart', function () {
				node.classList.add('is-dragging');
			});
			node.addEventListener('dragend', function () {
				node.classList.remove('is-dragging');
				document.querySelectorAll('.ls-dnd-tree-node.is-drop-target').forEach(function (n) {
					n.classList.remove('is-drop-target');
				});
			});
			node.addEventListener('dragover', function (e) {
				e.preventDefault();
				node.classList.add('is-drop-target');
			});
			node.addEventListener('dragleave', function () {
				node.classList.remove('is-drop-target');
			});
		});
	}

	$(function () {
		initLsGallerySelect2();
		initLsDndReorder();
		initLsDndTree();
	});
})(jQuery);
</script>
@endpush
@endsection
