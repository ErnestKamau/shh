{{--
	ls-field-rich-text-cell — table-cell rich text (expand panel, no legacy modal).
	Props: $label, $value (HTML), $preview (plain text override), $rowLabel, $hint,
	       $wireModel, $editorId, $compact
--}}
@php
	$html = (string) ($value ?? '<p>Seafood composite — <strong>lot B-441</strong>, keep refrigerated.</p>');
	$previewPlain = $preview ?? trim(strip_tags($html));
	$previewPlain = $previewPlain !== '' ? $previewPlain : 'Add description…';
	$editorId = $editorId ?? ('ls-rich-cell-'.uniqid());
	$isLive = ! empty($wireModel);
	$rowLabel = $rowLabel ?? 'Row 1';
	$isEmpty = $previewPlain === 'Add description…';
@endphp
<div
	class="ls-rich-text ls-rich-text--cell {{ ! empty($compact) ? 'ls-rich-text--compact' : '' }}"
	x-data="{
		open: false,
		panelStyle: '',
		editorId: @js($editorId),
		wireKey: @js($wireModel ?? ''),
		previewText: @js($previewPlain),
		previewDisplay() {
			const text = (this.previewText || '').trim();
			return text !== '' && text !== 'Add description…' ? text : 'Add description…';
		},
		previewTruncated() {
			const text = this.previewDisplay();
			return text.length > 42 ? text.slice(0, 42) + '…' : text;
		},
		isPreviewEmpty() {
			const text = (this.previewText || '').trim();
			return text === '' || text === 'Add description…';
		},
		updatePreviewFromHtml(html) {
			const tmp = document.createElement('div');
			tmp.innerHTML = html || '';
			const plain = (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
			this.previewText = plain !== '' ? plain : 'Add description…';
		},
		syncEditorToLivewire(live) {
			if (! this.wireKey || typeof tinymce === 'undefined') {
				return;
			}
			const editor = tinymce.get(this.editorId);
			if (! editor) {
				return;
			}
			const html = editor.getContent();
			this.updatePreviewFromHtml(html);
			if (this.$wire) {
				this.$wire.set(this.wireKey, html, !!live);
			}
		},
		toggleOpen() {
			this.open = ! this.open;
			if (this.open) {
				this.$nextTick(() => requestAnimationFrame(() => this.updatePosition()));
			}
		},
		closePanel() {
			this.syncEditorToLivewire(true);
			this.open = false;
		},
		updatePosition() {
			const trigger = this.$refs.trigger;
			if (! trigger) {
				return;
			}
			const rect = trigger.getBoundingClientRect();
			const gap = 6;
			const edge = 8;
			const minWidth = Math.min(352, window.innerWidth - edge * 2);
			const width = Math.max(rect.width, minWidth);
			const left = Math.min(
				Math.max(edge, rect.left),
				Math.max(edge, window.innerWidth - width - edge)
			);
			const spaceBelow = window.innerHeight - rect.bottom - gap - edge;
			const spaceAbove = rect.top - gap - edge;
			const dropUp = spaceBelow < 180 && spaceAbove > spaceBelow;
			const placement = dropUp
				? `bottom:${Math.max(edge, window.innerHeight - rect.top + gap)}px;top:auto`
				: `top:${rect.bottom + gap}px;bottom:auto`;
			this.panelStyle = [
				'position:fixed',
				placement,
				`left:${left}px`,
				`width:${width}px`,
				'z-index:2050',
			].join(';');
		},
		closeIfOutside(event) {
			if (! this.open) {
				return;
			}
			const target = event.target;
			if (this.$refs.trigger?.contains(target) || this.$refs.panel?.contains(target)) {
				return;
			}
			this.closePanel();
		},
		init() {
			this._reposition = () => { if (this.open) this.updatePosition(); };
			this._outside = (event) => this.closeIfOutside(event);
			window.addEventListener('resize', this._reposition);
			document.addEventListener('scroll', this._reposition, true);
			document.addEventListener('mousedown', this._outside, true);
		},
		destroy() {
			window.removeEventListener('resize', this._reposition);
			document.removeEventListener('scroll', this._reposition, true);
			document.removeEventListener('mousedown', this._outside, true);
		},
	}"
	@ls-rich-preview-update.window="if ($event.detail.editorId === editorId) updatePreviewFromHtml($event.detail.html)"
	@keydown.escape.window="closePanel()"
>
	<button
		type="button"
		class="ls-rich-text__trigger {{ $isEmpty ? 'is-empty' : '' }}"
		x-ref="trigger"
		:class="{ 'is-open': open, 'is-empty': isPreviewEmpty() }"
		@click.stop="toggleOpen()"
		:aria-expanded="open"
		:title="previewDisplay()"
	>
		<span class="ls-rich-text__trigger-icon" aria-hidden="true"><i class="mdi mdi-text-box-outline"></i></span>
		<span class="ls-rich-text__trigger-text" x-text="previewTruncated()"></span>
		<span class="ls-rich-text__trigger-action" aria-hidden="true"><i class="mdi mdi-pencil-outline"></i></span>
	</button>

	<template x-teleport="body">
		<div
			class="ls-rich-text__panel ls-rich-text__panel--floating"
			x-ref="panel"
			x-show="open"
			x-cloak
			:style="panelStyle"
			@click.stop
			role="dialog"
			aria-modal="true"
			aria-label="{{ ! empty($label) ? $label : 'Sample description' }}"
		>
			<div class="ls-rich-text__panel-head">
				<span class="ls-rich-text__panel-title">
					@if(! empty($label))
						{{ $label }}
					@else
						Sample description
					@endif
					<small>{{ $rowLabel }}</small>
				</span>
				<button type="button" class="ls-rich-text__panel-close" @click="closePanel()" aria-label="Close">
					<i class="mdi mdi-close"></i>
				</button>
			</div>
			<div class="ls-rich-text__shell" @if($isLive) wire:ignore @endif>
				@if($isLive)
					<div
						x-data="lsRichTextInline({
							editorId: @js($editorId),
							wireKey: @js($wireModel),
						})"
						x-init="mount()"
						x-on:trf-destroy-editors.window="destroy()"
					>
						<textarea id="{{ $editorId }}" class="ls-rich-text__textarea">{!! $html !!}</textarea>
					</div>
				@else
					<div class="ls-rich-text__toolbar" role="toolbar" aria-label="Formatting">
						<button type="button" class="ls-rich-text__tool is-active" disabled><i class="mdi mdi-format-bold"></i></button>
						<button type="button" class="ls-rich-text__tool" disabled><i class="mdi mdi-format-italic"></i></button>
						<button type="button" class="ls-rich-text__tool" disabled><i class="mdi mdi-format-underline"></i></button>
						<span class="ls-rich-text__tool-sep"></span>
						<button type="button" class="ls-rich-text__tool" disabled><i class="mdi mdi-format-list-bulleted"></i></button>
						<button type="button" class="ls-rich-text__tool" disabled><i class="mdi mdi-format-list-numbered"></i></button>
					</div>
					<div class="ls-rich-text__body ls-rich-text__body--cell" contenteditable="true">{!! $html !!}</div>
				@endif
			</div>
			@if($hint ?? null)
				<p class="ls-rich-text__panel-hint">{{ $hint }}</p>
			@endif
			<div class="ls-rich-text__panel-foot">
				<button type="button" class="ls-rich-text__panel-btn ls-rich-text__panel-btn--ghost" @click="closePanel()">Done</button>
			</div>
		</div>
	</template>
</div>
