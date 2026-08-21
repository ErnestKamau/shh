{{--
	ls-upload-image — dashed drop zone with idle / active / progress / done states.
	Props: $state (idle|active|progress|done)
--}}
@php
	$state = $state ?? 'idle';
@endphp
<div class="ls-upload" x-data="{ state: @js($state) }">
	<div class="ls-upload__head">
		<div>
			<h4 class="ls-upload__title">Upload image</h4>
		</div>
		<button type="button" class="ls-card-collection__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>
	<div
		class="ls-upload__drop"
		:class="{ 'is-active': state === 'active' }"
		@dragover.prevent="state = 'active'"
		@dragleave.prevent="state = state === 'done' || state === 'progress' ? state : 'idle'"
		@drop.prevent="state = 'progress'"
	>
		<i class="mdi mdi-file-image-plus-outline"></i>
		<p class="ls-upload__drop-text">Drag and drop your image here or <a href="#" @click.prevent="state = 'progress'">choose image</a></p>
		<p class="ls-upload__drop-hint">500 MB max image size.</p>
	</div>
	<template x-if="state === 'progress'">
		<div class="ls-upload__file">
			<i class="mdi mdi-file-image-outline ls-upload__file-icon" style="color:#2563eb;"></i>
			<div style="flex:1;">
				<p class="ls-upload__file-name">FileName.JPG</p>
				<p class="ls-upload__file-meta">75.1 MB · Uploading…</p>
				<div class="ls-upload__progress"><span style="width:75%;"></span></div>
			</div>
			<button type="button" class="ls-card-collection__close" @click="state = 'idle'" aria-label="Cancel"><i class="mdi mdi-close"></i></button>
		</div>
	</template>
	<template x-if="state === 'done'">
		<div class="ls-upload__file">
			<i class="mdi mdi-image-outline ls-upload__file-icon" style="color:#2563eb;"></i>
			<div style="flex:1;">
				<p class="ls-upload__file-name">File name.JPG</p>
				<p class="ls-upload__file-meta">75.1 MB · <span class="ls-upload__ok">Completed</span></p>
			</div>
			<button type="button" class="ls-card-collection__close" @click="state = 'idle'" aria-label="Remove"><i class="mdi mdi-trash-can-outline"></i></button>
		</div>
	</template>
	<div class="ls-card-collection__foot" style="justify-content:center;">
		<button type="button" class="ls-btn ls-btn--primary" @click="state = state === 'progress' ? 'done' : (state === 'done' ? 'idle' : 'progress')">
			<span x-text="state === 'done' ? 'Saved' : 'Save'"></span>
		</button>
	</div>
</div>
