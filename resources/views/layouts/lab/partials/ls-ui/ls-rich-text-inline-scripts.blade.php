{{-- Shared Alpine + TinyMCE bootstrap for ls-field-rich-text-inline (classic forms + Livewire). --}}
<script>
(function () {
	function registerLsRichTextInline() {
		if (window.__lsRichTextInlineRegistered || !window.Alpine) {
			return;
		}

		window.__lsRichTextInlineRegistered = true;

		const factory = (config) => ({
			editorId: config.editorId,
			wireKey: config.wireKey || null,
			init() {
				this.$nextTick(() => this.mountEditor());
			},
			mount() {
				this.$nextTick(() => this.mountEditor());
			},
			mountEditor() {
				if (typeof tinymce === 'undefined') {
					const existing = document.querySelector('script[data-ls-tinymce]');
					if (existing) {
						existing.addEventListener('load', () => this.initTiny(), { once: true });
						return;
					}
					const script = document.createElement('script');
					script.src = '/tinymce/tinymce.min.js';
					script.dataset.lsTinymce = '1';
					script.onload = () => this.initTiny();
					document.head.appendChild(script);
					return;
				}
				this.initTiny();
			},
			notifyPreview(html) {
				window.dispatchEvent(new CustomEvent('ls-rich-preview-update', {
					detail: { editorId: this.editorId, html: html || '' },
					bubbles: true,
				}));
			},
			syncToWire(html, live) {
				if (this.$wire && this.wireKey) {
					this.$wire.set(this.wireKey, html, live);
				}
			},
			syncTextarea(html) {
				const el = document.getElementById(this.editorId);
				if (el) {
					el.value = html || '';
				}
			},
			initTiny() {
				if (typeof tinymce === 'undefined') {
					return;
				}
				if (tinymce.get(this.editorId)) {
					tinymce.remove('#' + this.editorId);
				}
				const self = this;
				tinymce.init({
					selector: '#' + this.editorId,
					height: 160,
					menubar: false,
					statusbar: false,
					branding: false,
					plugins: 'lists',
					toolbar: 'bold italic underline | bullist numlist',
					setup(editor) {
						const push = (live) => {
							const html = editor.getContent();
							self.syncTextarea(html);
							self.notifyPreview(html);
							self.syncToWire(html, live);
						};
						editor.on('change keyup', () => push(false));
						editor.on('blur', () => push(true));
					},
				});
			},
			destroy() {
				if (typeof tinymce !== 'undefined' && tinymce.get(this.editorId)) {
					tinymce.remove('#' + this.editorId);
				}
			},
		});

		window.Alpine.data('lsRichTextInline', factory);
	}

	if (window.Alpine) {
		registerLsRichTextInline();
	} else {
		document.addEventListener('alpine:init', registerLsRichTextInline);
	}

	document.addEventListener('submit', function (event) {
		const form = event.target;
		if (! form || ! form.querySelector || ! form.querySelector('.ls-rich-text__textarea')) {
			return;
		}
		if (typeof tinymce !== 'undefined' && typeof tinymce.triggerSave === 'function') {
			tinymce.triggerSave();
		}
	}, true);
})();
</script>
