{{--
	ls-upload-files — multi-file drop zone + progress list + import URL.
--}}
<div class="ls-upload" x-data="{ active: false }">
	<div class="ls-upload__head">
		<div>
			<h4 class="ls-upload__title"><i class="mdi mdi-cloud-upload-outline"></i> Upload files</h4>
			<p class="ls-upload__sub">Select and upload the files of your choice.</p>
		</div>
		<button type="button" class="ls-card-collection__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>
	<div
		class="ls-upload__drop"
		:class="{ 'is-active': active }"
		@dragover.prevent="active = true"
		@dragleave.prevent="active = false"
		@drop.prevent="active = false"
	>
		<i class="mdi mdi-cloud-upload-outline"></i>
		<p class="ls-upload__drop-text">Choose a file or drag &amp; drop it here.</p>
		<p class="ls-upload__drop-hint">JPEG, PNG, PDF, and MP4 formats, up to 50 MB.</p>
		<button type="button" class="ls-btn" style="margin-top:0.55rem;">Browse File</button>
	</div>
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
	<div class="ls-upload__or">OR</div>
	<label class="ls-field__label">Import from URL Link</label>
	<div class="ls-field__control">
		<span class="ls-field__affix ls-field__affix--prefix"><i class="mdi mdi-link-variant"></i></span>
		<input type="url" class="ls-field__input" placeholder="Paste file URL">
	</div>
</div>
