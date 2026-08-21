{{-- Imara toast stack: purpose title + short body, auto-dismiss 7s --}}
@once
<style>
	#imara-toast-root {
		position: fixed;
		top: 1.25rem;
		right: 1.25rem;
		z-index: 21000;
		display: flex;
		flex-direction: column;
		gap: 0.65rem;
		max-width: min(22rem, calc(100vw - 2rem));
		pointer-events: none;
	}

	.imara-toast {
		pointer-events: auto;
		display: flex;
		gap: 0.75rem;
		align-items: flex-start;
		padding: 0.9rem 1rem;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
		animation: imara-toast-in 0.28s ease-out;
	}

	.imara-toast.is-leaving {
		animation: imara-toast-out 0.22s ease-in forwards;
	}

	.imara-toast__icon {
		width: 2.1rem;
		height: 2.1rem;
		border-radius: 999px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		font-size: 1.1rem;
	}

	.imara-toast--success .imara-toast__icon { background: #dcfce7; color: #15803d; }
	.imara-toast--error .imara-toast__icon { background: #fee2e2; color: #b91c1c; }
	.imara-toast--warning .imara-toast__icon { background: #fef3c7; color: #b45309; }
	.imara-toast--info .imara-toast__icon { background: #dbeafe; color: #1e40af; }

	.imara-toast__title {
		font-size: 0.9rem;
		font-weight: 700;
		color: #0f172a;
		margin: 0 0 0.15rem;
		line-height: 1.3;
	}

	.imara-toast__message {
		font-size: 0.8rem;
		color: #64748b;
		margin: 0;
		line-height: 1.4;
	}

	.imara-toast__close {
		margin-left: auto;
		border: 0;
		background: transparent;
		color: #94a3b8;
		padding: 0;
		line-height: 1;
		cursor: pointer;
		font-size: 1.1rem;
	}

	@keyframes imara-toast-in {
		from { opacity: 0; transform: translateY(-8px); }
		to { opacity: 1; transform: translateY(0); }
	}

	@keyframes imara-toast-out {
		from { opacity: 1; transform: translateY(0); }
		to { opacity: 0; transform: translateY(-6px); }
	}
</style>

<div id="imara-toast-root" aria-live="polite" aria-relevant="additions"></div>

<script>
(function () {
	if (window.showImaraToast) {
		return;
	}

	const ICONS = {
		success: 'mdi-check-circle',
		error: 'mdi-alert-circle',
		warning: 'mdi-alert',
		info: 'mdi-information',
	};

	function normalizeType(type) {
		const value = String(type || 'info').toLowerCase();
		if (value === 'danger' || value === 'failed' || value === 'fail') {
			return 'error';
		}
		if (['success', 'error', 'warning', 'info'].includes(value)) {
			return value;
		}
		return 'info';
	}

	window.showImaraToast = function (options) {
		const opts = typeof options === 'string'
			? { message: options }
			: (options || {});

		const type = normalizeType(opts.type);
		const title = String(opts.title || ({
			success: 'Done',
			error: 'Something went wrong',
			warning: 'Please check',
			info: 'Update',
		})[type]);
		const message = String(opts.message || opts.text || '');
		const durationMs = Number(opts.durationMs || opts.duration || 7000);

		const root = document.getElementById('imara-toast-root');
		if (!root) {
			return;
		}

		const toast = document.createElement('div');
		toast.className = 'imara-toast imara-toast--' + type;
		toast.setAttribute('role', 'status');
		toast.innerHTML =
			'<div class="imara-toast__icon" aria-hidden="true"><i class="mdi ' + (ICONS[type] || ICONS.info) + '"></i></div>' +
			'<div class="imara-toast__body">' +
				'<p class="imara-toast__title"></p>' +
				(message ? '<p class="imara-toast__message"></p>' : '') +
			'</div>' +
			'<button type="button" class="imara-toast__close" aria-label="Dismiss"><i class="mdi mdi-close"></i></button>';

		toast.querySelector('.imara-toast__title').textContent = title;
		const messageEl = toast.querySelector('.imara-toast__message');
		if (messageEl) {
			messageEl.textContent = message;
		}

		const dismiss = () => {
			if (toast.classList.contains('is-leaving')) {
				return;
			}
			toast.classList.add('is-leaving');
			window.setTimeout(() => toast.remove(), 220);
		};

		toast.querySelector('.imara-toast__close').addEventListener('click', dismiss);
		root.appendChild(toast);

		if (durationMs > 0) {
			window.setTimeout(dismiss, durationMs);
		}
	};

	document.addEventListener('livewire:init', () => {
		Livewire.on('imara-toast', (payload) => {
			const data = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
			window.showImaraToast(data);
		});

		Livewire.on('notify', (payload) => {
			const data = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
			if (data && (data.title || data.imara === true)) {
				window.showImaraToast(data);
			}
		});
	});
})();
</script>
@endonce
