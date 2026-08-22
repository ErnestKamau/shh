@once
<style>
.ls-amspec-import-modal {
		overflow-x: hidden;
		overflow-y: auto;
	}
	.ls-amspec-import-modal .ls-amspec-import-dialog {
		max-width: min(820px, calc(100vw - 4.5rem)) !important;
		width: calc(100% - 4.5rem);
		margin: 1.5rem auto !important;
		max-height: calc(100vh - 3rem);
		display: flex;
		align-items: stretch;
	}
	.ls-amspec-import-modal .item-modal-content {
		border: 0;
		border-radius: 18px;
		overflow: hidden;
		box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
		max-height: calc(100vh - 3rem);
		width: 100%;
		display: flex;
		flex-direction: column;
	}
	.ls-amspec-import-modal .ls-amspec-import-header,
	.ls-amspec-import-modal .item-modal-header.ls-amspec-import-header {
		padding: 1.25rem 2.25rem !important;
		background: var(--color-primary, #6D0A0E) !important;
		border-bottom: 0 !important;
		color: #fff;
		flex: 0 0 auto;
	}
	.ls-amspec-import-modal .ls-amspec-import-header .modal-title {
		color: #fff;
		font-weight: 700;
	}
	.ls-amspec-import-modal .ls-amspec-import-subtitle {
		color: #fff !important;
		opacity: 0.92;
		font-size: 0.86rem;
	}
	.ls-amspec-import-modal .ls-amspec-import-close {
		color: #fff !important;
		opacity: 0.9;
		text-shadow: none;
	}
	.ls-amspec-import-modal .ls-amspec-import-close:hover {
		opacity: 1;
		color: #fff !important;
	}
	.ls-amspec-import-modal .item-modal-body {
		padding: 1rem 2.25rem 0.85rem !important;
		background: #f7fbfe !important;
		overflow-y: auto !important;
		flex: 1 1 auto;
		min-height: 0;
		-webkit-overflow-scrolling: touch;
	}
	.ls-amspec-import-modal .item-modal-footer {
		padding: 0.85rem 2.25rem 1.35rem !important;
		background: #fff;
		border-top: 1px solid #dbeaf5 !important;
		flex: 0 0 auto;
	}
	.ls-amspec-import-modal .item-modal-label {
		font-size: 0.78rem !important;
		font-weight: 700 !important;
		color: #334155 !important;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		margin-bottom: 0.55rem !important;
	}
	.ls-amspec-import-modal .item-modal-cancel-btn,
	.ls-amspec-import-modal .item-modal-save-btn {
		border-radius: 10px;
		min-height: 40px;
		font-weight: 600;
		padding: 0 16px;
	}
	.ls-amspec-import-modal .item-modal-section {
		background: #fff !important;
		border: 1px solid #d7e8f4 !important;
		border-radius: 14px;
	}
	.ls-amspec-import-modal .item-modal-section .ls-upload {
		max-width: none !important;
		width: 100% !important;
		border-color: #d7e8f4;
	}

	.ls-amspec-guide-card {
		display: grid;
		grid-template-columns: 7.25rem 1fr;
		gap: 1.1rem;
		align-items: center;
		background: linear-gradient(135deg, #f4f9fd 0%, #eaf4fb 48%, #f8fafc 100%);
		border: 1px solid #c9dff0;
		border-radius: 14px;
		padding: 1rem 1.15rem;
		box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
	}
	.ls-amspec-guide-card__visual {
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.ls-amspec-guide-card__eyebrow {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		font-size: 0.68rem;
		font-weight: 700;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: #3b82a8;
		margin-bottom: 0.35rem;
	}
	.ls-amspec-guide-card__title {
		display: block;
		font-size: 0.95rem;
		color: #0f172a;
		margin-bottom: 0.35rem;
	}
	.ls-amspec-guide-card__text {
		font-size: 0.78rem;
		color: #475569;
		margin: 0 0 0.4rem;
		line-height: 1.45;
	}
	.ls-amspec-guide-card__text--muted { color: #64748b; margin-bottom: 0.75rem; }
	.ls-amspec-guide-card__actions {
		display: flex;
		flex-wrap: wrap;
		gap: 0.5rem;
	}
	.ls-amspec-guide-card__actions .btn {
		border-radius: 8px;
		font-weight: 600;
		border-color: #b7d3e8;
		color: #335f7a;
		background: rgba(255, 255, 255, 0.85);
	}
	.ls-amspec-guide-card__actions .btn:hover {
		background: #e8f3fb;
		border-color: #9fc6e0;
		color: #1e4a63;
	}

	.ls-amspec-options-row {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 1.25rem;
		align-items: stretch;
	}
	.ls-amspec-options-col {
		min-width: 0;
		height: 100%;
		display: grid !important;
		grid-template-rows: auto 2.75rem minmax(3.6rem, 1fr);
		align-items: stretch;
		padding: 14px !important;
	}
	.ls-amspec-import-modal .ls-amspec-option-pills.pricelist-mode-chips {
		width: 100% !important;
		height: 2.75rem !important;
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		gap: 0.75rem !important;
		flex-wrap: nowrap !important;
		margin: 0 !important;
		padding: 0 !important;
		background: transparent !important;
		border: 0 !important;
		border-radius: 0 !important;
	}
	.ls-amspec-import-modal .ls-amspec-option-pills .pricelist-mode-chip {
		flex: 1 1 0 !important;
		min-width: 0 !important;
		width: auto !important;
		height: 2.35rem;
		justify-content: center;
		text-align: center;
		display: inline-flex;
		align-items: center;
		gap: 0.3rem;
		border: 1px solid #e2e8f0 !important;
		background: #fff !important;
		color: #475569 !important;
		border-radius: 999px !important;
		padding: 0 0.85rem !important;
		font-size: 0.78rem;
		font-weight: 600;
		cursor: pointer;
		box-shadow: none !important;
	}
	.ls-amspec-import-modal .ls-amspec-option-pills .pricelist-mode-chip.is-active {
		background: #8b1e2d !important;
		border-color: #8b1e2d !important;
		color: #fff !important;
	}
	.ls-amspec-import-modal .ls-amspec-option-pills .pricelist-mode-chip:disabled {
		opacity: 0.45;
		cursor: not-allowed;
	}
	/* Keep divider lines aligned: fixed pill row height */
	.ls-amspec-options-col > .item-modal-label {
		grid-row: 1;
	}
	.ls-amspec-options-col > .ls-amspec-option-pills {
		grid-row: 2;
	}
	.ls-amspec-options-col > .ls-amspec-option-explain {
		grid-row: 3;
		margin-top: 0.75rem;
		padding-top: 0.75rem;
		border-top: 1px solid #dceaf5;
		min-height: 0;
	}

	.ls-amspec-option-explain p {
		font-size: 0.72rem;
		line-height: 1.4;
		color: #4a6d82;
	}
	.ls-amspec-option-explain code {
		font-size: 0.68rem;
		color: #8b1e2d;
	}

	.ls-amspec-import-modal .pricelist-mode-chips {
		display: inline-flex !important;
		flex-wrap: nowrap;
		align-items: center;
		width: auto !important;
		max-width: 100%;
		justify-content: flex-start !important;
		gap: 0.65rem !important;
		padding: 0 !important;
		background: transparent !important;
		border: 0 !important;
		border-radius: 0 !important;
	}
	.ls-amspec-import-modal .pricelist-mode-chip {
		flex: 0 0 auto !important;
		width: auto !important;
		min-width: 6.75rem;
		display: inline-flex;
		align-items: center;
		gap: 0.3rem;
		border: 1px solid #e2e8f0 !important;
		background: #fff !important;
		color: #475569 !important;
		border-radius: 999px !important;
		padding: 0.4rem 0.95rem !important;
		font-size: 0.78rem;
		font-weight: 600;
		cursor: pointer;
		box-shadow: none !important;
	}
	.ls-amspec-import-modal .pricelist-mode-chip.is-active {
		background: #8b1e2d !important;
		border-color: #8b1e2d !important;
		color: #fff !important;
	}
	.ls-amspec-import-modal .pricelist-mode-chip:disabled {
		opacity: 0.45;
		cursor: not-allowed;
	}

	.ls-amspec-import-hero__orb {
		position: relative;
		width: 118px;
		height: 118px;
		border-radius: 50%;
		background: radial-gradient(circle at 35% 30%, #fff 0%, #e8f3fb 55%, #d4e8f5 100%);
		border: 1px solid #bdd8eb;
		box-shadow: 0 10px 28px rgba(59, 130, 168, 0.12);
		overflow: hidden;
	}
	.ls-amspec-import-hero__orb--in-card {
		width: 96px;
		height: 96px;
		box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
	}
	.ls-amspec-import-hero__doc {
		position: absolute;
		width: 42px;
		height: 54px;
		border-radius: 6px;
		background: #fff;
		border: 1.5px solid #cbd5e1;
		left: 50%;
		top: 50%;
	}
	.ls-amspec-import-hero__orb--in-card .ls-amspec-import-hero__doc {
		width: 34px;
		height: 44px;
	}
	.ls-amspec-import-hero__doc--back {
		transform: translate(-68%, -62%) rotate(-14deg);
		opacity: 0.55;
		animation: ls-amspec-float 3.2s ease-in-out infinite;
	}
	.ls-amspec-import-hero__doc--mid {
		transform: translate(-32%, -58%) rotate(8deg);
		opacity: 0.75;
		animation: ls-amspec-float 3.2s ease-in-out infinite 0.35s;
		background: #f8fafc;
	}
	.ls-amspec-import-hero__doc--front {
		transform: translate(-50%, -48%);
		box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
		animation: ls-amspec-float 3.2s ease-in-out infinite 0.15s;
		display: flex;
		flex-direction: column;
		gap: 4px;
		padding: 10px 8px;
	}
	.ls-amspec-import-hero__orb--in-card .ls-amspec-import-hero__doc--front {
		padding: 8px 6px;
		gap: 3px;
	}
	.ls-amspec-import-hero__doc--front span {
		display: block;
		height: 3px;
		border-radius: 2px;
		background: #e2e8f0;
	}
	.ls-amspec-import-hero__doc--front span:nth-child(1) { width: 78%; background: #8b1e2d; opacity: 0.85; }
	.ls-amspec-import-hero__doc--front span:nth-child(2) { width: 92%; }
	.ls-amspec-import-hero__doc--front span:nth-child(3) { width: 64%; }
	.ls-amspec-import-hero__arrow {
		position: absolute;
		right: 14px;
		bottom: 14px;
		width: 34px;
		height: 34px;
		border-radius: 50%;
		background: #8b1e2d;
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 1.15rem;
		box-shadow: 0 6px 14px rgba(139, 30, 45, 0.35);
		animation: ls-amspec-bob 1.8s ease-in-out infinite;
	}
	.ls-amspec-import-hero__orb--in-card .ls-amspec-import-hero__arrow {
		right: 10px;
		bottom: 10px;
		width: 28px;
		height: 28px;
		font-size: 0.95rem;
	}
	@keyframes ls-amspec-float {
		0%, 100% { translate: 0 0; }
		50% { translate: 0 -5px; }
	}
	@keyframes ls-amspec-bob {
		0%, 100% { transform: translateY(0); }
		50% { transform: translateY(-4px); }
	}

	@media (max-width: 640px) {
		.ls-amspec-guide-card {
			grid-template-columns: 1fr;
			justify-items: center;
			text-align: center;
		}
		.ls-amspec-guide-card__actions { justify-content: center; }
		.ls-amspec-options-row {
			grid-template-columns: 1fr;
			gap: 0.85rem;
		}
	}
</style>
<script>
	(function () {
		function unlockAmspecImportUi() {
			var openModal = Array.prototype.slice.call(document.querySelectorAll('.ls-amspec-import-modal.show')).find(function (el) {
				return el.style.display !== 'none' && window.getComputedStyle(el).display !== 'none';
			});
			if (openModal) {
				return;
			}
			document.body.classList.remove('modal-open');
			document.body.style.removeProperty('overflow');
			document.body.style.removeProperty('padding-right');
			document.querySelectorAll('body > .modal-backdrop').forEach(function (el) {
				el.remove();
			});
		}

		function bindLivewireUnlock() {
			if (!window.Livewire || typeof Livewire.on !== 'function') {
				return;
			}
			Livewire.on('amspec-import-closed', unlockAmspecImportUi);
		}

		document.addEventListener('livewire:init', bindLivewireUnlock);
		document.addEventListener('livewire:initialized', bindLivewireUnlock);
		document.addEventListener('hidden.bs.modal', function (event) {
			if (event.target && event.target.classList && event.target.classList.contains('ls-amspec-import-modal')) {
				unlockAmspecImportUi();
			}
		});
	})();
</script>
@endonce
