{{-- Delete pricelist confirm — same reliability pattern as amspec-import-modal-styles (@once). --}}
@once
<style>
	.ls-pricelist-delete-modal.modal {
		position: fixed !important;
		top: 0 !important;
		right: 0 !important;
		bottom: 0 !important;
		left: 0 !important;
		width: 100vw !important;
		height: 100vh !important;
		min-height: 100dvh !important;
		margin: 0 !important;
		padding: 0 !important;
		overflow-x: hidden;
		overflow-y: auto;
		z-index: 1065 !important;
		background-color: rgba(15, 23, 42, 0.55);
	}
	.ls-pricelist-delete-modal.modal.show {
		display: block !important;
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-dialog {
		max-width: min(480px, calc(100vw - 2rem)) !important;
		width: calc(100% - 2rem);
		margin: 1.5rem auto !important;
	}
	.ls-pricelist-delete-modal .item-modal-content {
		border: 0;
		border-radius: 18px;
		overflow: hidden;
		box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
		width: 100%;
		display: flex;
		flex-direction: column;
	}

	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-delete-modal .modal-header,
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-delete-modal .modal-header.bg-light,
	.ls-pricelist-delete-modal .ls-pricelist-delete-header,
	.ls-pricelist-delete-modal .item-modal-header.ls-pricelist-delete-header {
		padding: 1.25rem 2.25rem !important;
		background: var(--color-primary, #6D0A0E) !important;
		border-bottom: 0 !important;
		color: #fff !important;
		flex: 0 0 auto;
	}
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-delete-modal .modal-header .modal-title,
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-delete-modal .modal-header h5,
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-delete-modal .modal-header .mdi,
	.ls-pricelist-delete-modal .ls-pricelist-delete-header .modal-title {
		color: #fff !important;
		font-weight: 700;
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-subtitle {
		color: #fff !important;
		opacity: 0.92;
		font-size: 0.86rem;
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-close {
		color: #fff !important;
		opacity: 0.9;
		text-shadow: none;
		filter: invert(1) grayscale(100%) brightness(200%);
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-close:hover {
		opacity: 1;
	}

	.ls-pricelist-delete-modal .item-modal-body {
		padding: 1.15rem 2.25rem 0.85rem !important;
		background: #f7fbfe !important;
		flex: 1 1 auto;
	}
	.ls-pricelist-delete-modal .item-modal-footer {
		padding: 0.85rem 2.25rem 1.35rem !important;
		background: #fff;
		border-top: 1px solid #dbeaf5 !important;
		flex: 0 0 auto;
		display: flex;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: 0.5rem;
	}

	.ls-pricelist-delete-warning {
		display: flex;
		gap: 0.55rem;
		align-items: flex-start;
		padding: 0.8rem 0.9rem;
		border-radius: 12px;
		background: #fff7ed;
		border: 1px solid #fed7aa;
	}
	.ls-pricelist-delete-warning__icon {
		color: #c2410c;
		margin-top: 0.1rem;
		flex-shrink: 0;
	}
	.ls-pricelist-delete-warning__text {
		margin: 0;
		color: #9a3412;
		font-size: 0.85rem;
		line-height: 1.45;
	}

	.ls-pricelist-delete-modal .item-modal-cancel-btn {
		border-radius: 10px;
		min-height: 40px;
		font-weight: 600;
		padding: 0 16px;
		border: 1px solid #cbd5e1;
		background: #fff;
		color: #334155;
	}
	.ls-pricelist-delete-modal .item-modal-cancel-btn:hover {
		background: #f8fafc;
		border-color: #94a3b8;
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-confirm-btn {
		border-radius: 10px;
		min-height: 40px;
		font-weight: 600;
		padding: 0 16px;
		border: 0;
		background: #b91c1c;
		color: #fff;
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-confirm-btn:hover:not(:disabled) {
		background: #991b1b;
		color: #fff;
	}
	.ls-pricelist-delete-modal .ls-pricelist-delete-confirm-btn:disabled {
		opacity: 0.7;
		cursor: not-allowed;
	}

	@media (max-width: 576px) {
		.ls-pricelist-delete-modal .ls-pricelist-delete-header,
		.ls-pricelist-delete-modal .item-modal-body,
		.ls-pricelist-delete-modal .item-modal-footer {
			padding-left: 1.15rem !important;
			padding-right: 1.15rem !important;
		}
	}
</style>
@endonce
