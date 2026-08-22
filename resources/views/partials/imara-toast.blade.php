{{-- Imara toast stack: purpose title + short body, variant motion presets --}}
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

	/* Variant: celebrate (bounce) */
	.imara-toast--variant-celebrate {
		animation: imara-toast-celebrate-in 0.52s cubic-bezier(0.34, 1.4, 0.64, 1);
		border-color: #bbf7d0;
	}
	.imara-toast--variant-celebrate.is-leaving {
		animation: imara-toast-celebrate-out 0.24s ease-in forwards;
	}
	.imara-toast--variant-celebrate .imara-toast__icon {
		background: linear-gradient(135deg, #dcfce7, #bbf7d0);
		color: #15803d;
	}

	/* Variant: shake (error) */
	.imara-toast--variant-shake {
		animation: imara-toast-shake-in 0.42s ease-out;
		border-color: #fecaca;
	}
	.imara-toast--variant-shake.is-leaving {
		animation: imara-toast-shake-out 0.2s ease-in forwards;
	}
	.imara-toast--variant-shake .imara-toast__icon {
		background: linear-gradient(135deg, #fee2e2, #fecaca);
		color: #b91c1c;
	}

	/* Variant: pulse (warning) */
	.imara-toast--variant-pulse {
		animation: imara-toast-pulse-in 0.36s ease-out;
		border-color: #fde68a;
	}
	.imara-toast--variant-pulse.is-leaving {
		animation: imara-toast-out 0.22s ease-in forwards;
	}
	.imara-toast--variant-pulse .imara-toast__icon {
		background: #fef3c7;
		color: #b45309;
		box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.45);
		animation: imara-toast-pulse-ring 1.4s ease-out 2;
	}
	.imara-toast--variant-pulse .imara-toast__icon i {
		animation: imara-toast-pulse-icon 0.8s ease-in-out 2;
	}

	/* Variant: glass (slide + blur) */
	.imara-toast--variant-glass {
		background: rgba(255, 255, 255, 0.82);
		backdrop-filter: blur(10px);
		border-color: rgba(186, 230, 253, 0.9);
		box-shadow: 0 16px 36px rgba(14, 165, 233, 0.16);
		animation: imara-toast-glass-in 0.34s cubic-bezier(0.22, 1, 0.36, 1);
	}
	.imara-toast--variant-glass.is-leaving {
		animation: imara-toast-glass-out 0.24s ease-in forwards;
	}
	.imara-toast--variant-glass .imara-toast__icon {
		background: linear-gradient(135deg, #e0f2fe, #dbeafe);
		color: #0369a1;
	}

	.ls-toast-gallery__grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(10.5rem, 1fr));
		gap: 0.65rem;
	}
	.ls-toast-gallery__demo {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 0.15rem;
		padding: 0.85rem 0.95rem;
		border-radius: 12px;
		border: 1px solid #e2e8f0;
		background: #fff;
		cursor: pointer;
		text-align: left;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	.ls-toast-gallery__demo:hover {
		border-color: #bfdbfe;
		box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
	}
	.ls-toast-gallery__demo i {
		font-size: 1.35rem;
	}
	.ls-toast-gallery__demo span {
		font-weight: 700;
		font-size: 0.85rem;
		color: #0f172a;
	}
	.ls-toast-gallery__demo small {
		font-size: 0.72rem;
		color: #64748b;
	}

	@keyframes imara-toast-in {
		from { opacity: 0; transform: translateY(-8px); }
		to { opacity: 1; transform: translateY(0); }
	}
	@keyframes imara-toast-out {
		from { opacity: 1; transform: translateY(0); }
		to { opacity: 0; transform: translateY(-6px); }
	}
	@keyframes imara-toast-celebrate-in {
		0% { opacity: 0; transform: translateY(12px) scale(0.92); }
		55% { opacity: 1; transform: translateY(-4px) scale(1.02); }
		100% { opacity: 1; transform: translateY(0) scale(1); }
	}
	@keyframes imara-toast-celebrate-out {
		from { opacity: 1; transform: translateY(0) scale(1); }
		to { opacity: 0; transform: translateY(-8px) scale(0.96); }
	}
	@keyframes imara-toast-shake-in {
		0% { opacity: 0; transform: translateX(16px); }
		20% { transform: translateX(-8px); }
		40% { transform: translateX(6px); }
		60% { transform: translateX(-4px); }
		80% { transform: translateX(2px); }
		100% { opacity: 1; transform: translateX(0); }
	}
	@keyframes imara-toast-shake-out {
		from { opacity: 1; transform: translateX(0); }
		to { opacity: 0; transform: translateX(12px); }
	}
	@keyframes imara-toast-pulse-in {
		from { opacity: 0; transform: scale(0.94); }
		to { opacity: 1; transform: scale(1); }
	}
	@keyframes imara-toast-pulse-ring {
		0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.45); }
		70% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
		100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
	}
	@keyframes imara-toast-pulse-icon {
		0%, 100% { transform: scale(1); }
		50% { transform: scale(1.12); }
	}
	@keyframes imara-toast-glass-in {
		from { opacity: 0; transform: translateX(24px); }
		to { opacity: 1; transform: translateX(0); }
	}
	@keyframes imara-toast-glass-out {
		from { opacity: 1; transform: translateX(0); }
		to { opacity: 0; transform: translateX(18px); }
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

	const VARIANT_ICONS = {
		celebrate: { success: 'mdi-party-popper' },
		shake: { error: 'mdi-alert-octagon-outline' },
		pulse: { warning: 'mdi-shield-alert-outline' },
		glass: { info: 'mdi-information-outline', success: 'mdi-information-outline', error: 'mdi-information-outline', warning: 'mdi-information-outline' },
	};

	const LEAVE_MS = {
		default: 220,
		celebrate: 240,
		shake: 200,
		pulse: 220,
		glass: 240,
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

	function resolveIcon(type, variant) {
		const variantIcons = VARIANT_ICONS[variant] || {};
		return variantIcons[type] || ICONS[type] || ICONS.info;
	}

	window.showImaraToast = function (options) {
		const opts = typeof options === 'string'
			? { message: options }
			: (options || {});

		const type = normalizeType(opts.type);
		const variant = String(opts.variant || '').trim();
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
		const variantClass = variant ? (' imara-toast--variant-' + variant) : '';
		toast.className = 'imara-toast imara-toast--' + type + variantClass;
		toast.setAttribute('role', 'status');
		toast.innerHTML =
			'<div class="imara-toast__icon" aria-hidden="true"><i class="mdi ' + resolveIcon(type, variant) + '"></i></div>' +
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

		const leaveMs = LEAVE_MS[variant] || LEAVE_MS.default;
		const dismiss = () => {
			if (toast.classList.contains('is-leaving')) {
				return;
			}
			toast.classList.add('is-leaving');
			window.setTimeout(() => toast.remove(), leaveMs);
		};

		toast.querySelector('.imara-toast__close').addEventListener('click', dismiss);
		root.appendChild(toast);

		if (durationMs > 0) {
			window.setTimeout(dismiss, durationMs);
		}
	};

	function bootImaraToastLivewire() {
		Livewire.on('imara-toast', (payload) => {
			const data = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
			window.showImaraToast(data);
		});
	}

	function bootImaraToastSession() {
		@if (session()->has('imara_toast'))
			window.showImaraToast(@json(session('imara_toast')));
		@endif
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bootImaraToastSession);
	} else {
		bootImaraToastSession();
	}

	document.addEventListener('livewire:init', bootImaraToastLivewire);
})();
</script>
@endonce
