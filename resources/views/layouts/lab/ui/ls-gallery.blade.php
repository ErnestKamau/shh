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
		Private preview only. Include partials from <code>layouts.lab.partials.ls-ui.*</code> when you want them — nothing here is wired into Sample Receiving or request view.
	</p>

	{{-- Tokens --}}
	<section class="ls-gallery-section">
		<h3>Tokens</h3>
		<p class="lead-muted">60/30/10: surface / slate secondary / burgundy accent + soft blue complement.</p>
		<div class="ls-swatch-row">
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;"></div><div class="ls-swatch__meta">Surface<br>#f8fafc</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#1e293b;"></div><div class="ls-swatch__meta">Secondary<br>#1e293b</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:var(--workflow-accent,#8b1e2d);"></div><div class="ls-swatch__meta">Accent<br>burgundy</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#eff6ff;"></div><div class="ls-swatch__meta">Blue soft<br>#eff6ff</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#dbeafe;"></div><div class="ls-swatch__meta">Blue border<br>#dbeafe</div></div>
			<div class="ls-swatch"><div class="ls-swatch__chip" style="background:#93c5fd;"></div><div class="ls-swatch__meta">Blue focus<br>#93c5fd</div></div>
		</div>
		<button type="button" class="btn btn-sm workflow-secondary-fill">Secondary fill button</button>
	</section>

	{{-- Fields --}}
	<section class="ls-gallery-section">
		<h3>Field columns (4)</h3>
		<p class="lead-muted">Text, affix, status indicator select, search combo.</p>
		<div class="row">
			<div class="col-md-6">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Sampling Point',
					'name' => 'demo_point',
					'value' => 'point 1',
					'hint' => 'Ordinary text input column.',
				])
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Username',
					'name' => 'demo_user_err',
					'value' => 'taken',
					'error' => 'This username is not available.',
				])
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Username',
					'name' => 'demo_user_ok',
					'value' => 'available_user',
					'success' => 'This username is available!',
				])
			</div>
			<div class="col-md-6">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
					'label' => 'Qty / Unit',
					'name' => 'demo_qty',
					'value' => '23',
					'suffixSelect' => ['g' => 'g', 'kg' => 'kg', 'ml' => 'ml'],
				])
				@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
					'label' => 'Website',
					'name' => 'demo_url',
					'prefix' => 'https://',
					'placeholder' => 'example.com',
				])
				@include('layouts.lab.partials.ls-ui.fields.ls-field-affix', [
					'label' => 'Email',
					'name' => 'demo_email',
					'placeholder' => 'you@lab.com',
					'actionLabel' => 'Subscribe',
				])
				@include('layouts.lab.partials.ls-ui.fields.ls-field-status-select', [
					'label' => 'Status',
					'hint' => 'Indicator select with colored dots.',
				])
				@include('layouts.lab.partials.ls-ui.fields.ls-field-search-combo', [
					'label' => 'Country in EU',
					'hint' => 'Combo box for large option lists.',
					'selected' => 'Croatia',
				])
			</div>
		</div>
	</section>

	{{-- Select2 --}}
	<section class="ls-gallery-section">
		<h3>Select2 shells (2)</h3>
		<p class="lead-muted">Single search + multi-tag with scoped burgundy pills.</p>
		<div class="row">
			<div class="col-md-6">
				@include('layouts.lab.partials.ls-ui.select2.ls-select2-single-search', [
					'label' => 'Search — Basic',
					'name' => 'demo_s2_single',
					'hint' => 'Magnifier + clearable single Select2.',
				])
			</div>
			<div class="col-md-6">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-select2-multi', [
					'label' => 'Tests',
					'name' => 'demo_s2_multi',
					'required' => true,
					'hint' => 'ls-field-select2-multi — pills only inside .ls-select2-multi.',
				])
			</div>
		</div>
	</section>

	{{-- Cards --}}
	<section class="ls-gallery-section">
		<h3>Cards (2)</h3>
		<p class="lead-muted">Soft baby-blue header card + hierarchy content card.</p>
		<div class="row">
			<div class="col-md-6 mb-3">
				@include('layouts.lab.partials.ls-ui.cards.ls-soft-card', [
					'title' => 'Sample 1 - bacon',
					'expanded' => true,
					'bodyHtml' => '<div class="row"><div class="col-6"><label class="ls-field__label">Sampling Point</label><input class="form-control form-control-sm" value="point 1"></div><div class="col-6"><label class="ls-field__label">Qty</label><input class="form-control form-control-sm" value="23"></div></div>',
				])
			</div>
			<div class="col-md-6 mb-3">
				@include('layouts.lab.partials.ls-ui.cards.ls-hierarchy-card')
			</div>
		</div>
	</section>

	{{-- Tables --}}
	<section class="ls-gallery-section">
		<h3>Tables (2)</h3>
		<p class="lead-muted">Status-pill table + plain density table (with skeleton demo).</p>
		<div class="row">
			<div class="col-md-7 mb-3">
				@include('layouts.lab.partials.ls-ui.tables.ls-table-status')
			</div>
			<div class="col-md-5 mb-3">
				@include('layouts.lab.partials.ls-ui.tables.ls-table-plain', ['skeleton' => true])
			</div>
		</div>
	</section>

	{{-- DnD --}}
	<section class="ls-gallery-section">
		<h3>Drag and drop (2)</h3>
		<p class="lead-muted">Flat reorder list + nested tree (gallery demo JS only).</p>
		<div class="row">
			<div class="col-md-5 mb-3">
				@include('layouts.lab.partials.ls-ui.dnd.ls-dnd-reorder-list')
			</div>
			<div class="col-md-7 mb-3">
				@include('layouts.lab.partials.ls-ui.dnd.ls-dnd-tree')
			</div>
		</div>
	</section>
</main>

@push('scripts')
<script>
(function ($) {
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
			});
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
