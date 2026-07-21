<script>
(function () {
	function syncSignatureValue(canvas, input, pad) {
		const value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
		input.value = value;

		const livewireModel = canvas.getAttribute('data-livewire-model');
		const componentEl = canvas.closest('[wire\\:id]');
		if (livewireModel && componentEl && window.Livewire) {
			const component = Livewire.find(componentEl.getAttribute('wire:id'));
			if (component) {
				component.set(livewireModel, value, false);
			}
		}

		input.dispatchEvent(new Event('input', { bubbles: true }));
	}

	window.syncTrfSignaturesBeforeSubmit = function () {
		if (typeof SignaturePad === 'undefined') {
			return;
		}

		document.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
			const pad = canvas._trfSignaturePad;
			const fieldName = canvas.getAttribute('data-field');
			if (!pad || !fieldName) {
				return;
			}

			const input = document.getElementById('field_' + fieldName);
			if (!input) {
				return;
			}

			const value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
			input.value = value;

			const livewireModel = canvas.getAttribute('data-livewire-model');
			const componentEl = canvas.closest('[wire\\:id]');
			if (livewireModel && componentEl && window.Livewire) {
				const component = Livewire.find(componentEl.getAttribute('wire:id'));
				if (component) {
					component.set(livewireModel, value);
				}
			}

			input.dispatchEvent(new Event('input', { bubbles: true }));
		});
	};

	window.initTrfSignaturePads = function (forceReinit) {
		if (typeof SignaturePad === 'undefined') {
			return;
		}

		document.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
			if (forceReinit) {
				delete canvas.dataset.signatureInitialized;
				canvas._trfSignaturePad = null;
			}

			const fieldName = canvas.getAttribute('data-field');
			const input = document.getElementById('field_' + fieldName);
			if (!input || !canvas.parentElement) {
				return;
			}

			const rect = canvas.getBoundingClientRect();
			const width = rect.width > 10 ? rect.width : (canvas.offsetWidth || canvas.parentElement.clientWidth || 480);
			const height = rect.height > 10 ? rect.height : (canvas.offsetHeight || 140);
			if (width < 10 || height < 10) {
				delete canvas.dataset.signatureInitialized;
				return;
			}

			if (canvas.dataset.signatureInitialized === '1' && canvas._trfSignaturePad) {
				return;
			}

			canvas.dataset.signatureInitialized = '1';
			const ratio = Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = width * ratio;
			canvas.height = height * ratio;
			const context = canvas.getContext('2d');
			context.setTransform(1, 0, 0, 1, 0, 0);
			context.scale(ratio, ratio);

			const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
			canvas._trfSignaturePad = pad;

			if (input.value) {
				try { pad.fromDataURL(input.value); } catch (e) {}
			}

			pad.addEventListener('endStroke', function () {
				syncSignatureValue(canvas, input, pad);
			});

			const clearBtn = canvas.parentElement.querySelector('.trf-signature-clear[data-canvas="' + canvas.id + '"]');
			if (clearBtn && !clearBtn.dataset.bound) {
				clearBtn.dataset.bound = '1';
				clearBtn.addEventListener('click', function () {
					pad.clear();
					syncSignatureValue(canvas, input, pad);
				});
			}
		});
	};

	function boot() {
		setTimeout(function () {
			window.initTrfSignaturePads(true);
		}, 250);
	}

	document.addEventListener('DOMContentLoaded', boot);
	document.addEventListener('livewire:initialized', function () {
		boot();
		Livewire.on('trf-reinit-signatures', function () {
			setTimeout(function () { window.initTrfSignaturePads(true); }, 200);
		});
		Livewire.on('walk-in-trf-step-changed', function () {
			setTimeout(function () { window.initTrfSignaturePads(true); }, 250);
		});
		Livewire.hook('morph.updated', function () {
			setTimeout(function () { window.initTrfSignaturePads(false); }, 150);
		});
	});
})();
</script>
