{{--
	ls-upload-files — file drop zone.

	Gallery defaults keep the original demo look when no props are passed.
	Import modals pass: title, subtitle, hint, accept, inputId / wireModel,
	showUrlImport=false, showDemoFiles=false, showHeadClose=false, required=true.
--}}
@php
	$title = $title ?? 'Upload files';
	$subtitle = $subtitle ?? 'Select and upload the files of your choice.';
	$hint = $hint ?? 'JPEG, PNG, PDF, and MP4 formats, up to 50 MB.';
	$accept = $accept ?? '.pdf,.png,.jpg,.jpeg,.mp4';
	$showUrlImport = $showUrlImport ?? true;
	$showDemoFiles = $showDemoFiles ?? true;
	$showHeadClose = $showHeadClose ?? true;
	$required = $required ?? false;
	$inputId = $inputId ?? ('ls-upload-'.uniqid());
	$wireModel = $wireModel ?? null;
	$inputName = $inputName ?? 'file';
	$multiple = $multiple ?? false;
	$errorBag = $errorBag ?? null;
@endphp
<div class="ls-upload" x-data="{
	active: false,
	fileName: '',
	fileSize: '',
	pick() { this.$refs.fileInput && this.$refs.fileInput.click(); },
	onChange(e) {
		const f = e.target.files && e.target.files[0];
		if (!f) { this.fileName = ''; this.fileSize = ''; return; }
		this.fileName = f.name;
		this.fileSize = (f.size / 1024).toFixed(1) + ' KB';
	},
	onDrop(e) {
		this.active = false;
		const f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
		if (!f || !this.$refs.fileInput) return;
		const dt = new DataTransfer();
		dt.items.add(f);
		this.$refs.fileInput.files = dt.files;
		this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
		this.onChange({ target: this.$refs.fileInput });
	},
	clear() {
		this.fileName = '';
		this.fileSize = '';
		if (this.$refs.fileInput) this.$refs.fileInput.value = '';
	}
}">
	<div class="ls-upload__head">
		<div>
			<h4 class="ls-upload__title">
				<i class="mdi mdi-cloud-upload-outline"></i>
				{{ $title }}@if($required) <span class="text-danger">*</span>@endif
			</h4>
			@if($subtitle !== '')
				<p class="ls-upload__sub">{{ $subtitle }}</p>
			@endif
		</div>
		@if($showHeadClose)
			<button type="button" class="ls-card-collection__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
		@endif
	</div>

	<input
		type="file"
		class="d-none"
		id="{{ $inputId }}"
		name="{{ $inputName }}"
		accept="{{ $accept }}"
		x-ref="fileInput"
		@change="onChange($event)"
		@if($multiple) multiple @endif
		@if($required) required @endif
		@if($wireModel) wire:model="{{ $wireModel }}" @endif
	>

	<div
		class="ls-upload__drop"
		:class="{ 'is-active': active }"
		@dragover.prevent="active = true"
		@dragleave.prevent="active = false"
		@drop.prevent="onDrop($event)"
		@click="pick()"
		role="button"
		tabindex="0"
		@keydown.enter.prevent="pick()"
	>
		<i class="mdi mdi-cloud-upload-outline"></i>
		<p class="ls-upload__drop-text">Choose a file or drag &amp; drop it here.</p>
		<p class="ls-upload__drop-hint">{{ $hint }}</p>
		<button type="button" class="ls-btn" style="margin-top:0.55rem;" @click.stop="pick()">Browse File</button>
	</div>

	<template x-if="fileName">
		<div class="ls-upload__file">
			<i class="mdi mdi-file-document-outline ls-upload__file-icon" style="color:#8b1e2d;"></i>
			<div style="flex:1;">
				<p class="ls-upload__file-name" x-text="fileName"></p>
				<p class="ls-upload__file-meta"><span x-text="fileSize"></span> · <span class="ls-upload__ok"><i class="mdi mdi-check-circle"></i> Ready</span></p>
			</div>
			<button type="button" class="ls-card-collection__close" aria-label="Remove" @click.stop="clear()"><i class="mdi mdi-close"></i></button>
		</div>
	</template>

	@if($errorBag)
		@error($errorBag)
			<div class="invalid-feedback d-block mt-1">{{ $message }}</div>
		@enderror
	@endif

	@if($showDemoFiles)
		<div class="ls-upload__file">
			<i class="mdi mdi-file-pdf-box ls-upload__file-icon"></i>
			<div style="flex:1;">
				<p class="ls-upload__file-name">my-cv.pdf</p>
				<p class="ls-upload__file-meta">0 KB of 120 KB · Uploading…</p>
				<div class="ls-upload__progress"><span style="width:18%;"></span></div>
			</div>
			<button type="button" class="ls-card-collection__close" aria-label="Cancel"><i class="mdi mdi-close"></i></button>
		</div>
		<div class="ls-upload__file">
			<i class="mdi mdi-file-pdf-box ls-upload__file-icon"></i>
			<div style="flex:1;">
				<p class="ls-upload__file-name">google-certificate.pdf</p>
				<p class="ls-upload__file-meta">94 KB of 94 KB · <span class="ls-upload__ok"><i class="mdi mdi-check-circle"></i> Completed</span></p>
			</div>
			<button type="button" class="ls-card-collection__close" aria-label="Delete"><i class="mdi mdi-trash-can-outline"></i></button>
		</div>
	@endif

	@if($showUrlImport)
		<div class="ls-upload__or">OR</div>
		<label class="ls-field__label">Import from URL Link</label>
		<div class="ls-field__control">
			<span class="ls-field__affix ls-field__affix--prefix"><i class="mdi mdi-link-variant"></i></span>
			<input type="url" class="ls-field__input" placeholder="Paste file URL">
		</div>
	@endif
</div>
