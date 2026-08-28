{{-- Create / Edit pricelist modal — same reliability pattern as amspec-import-modal-styles (@once). --}}
@once
<style>
	.ls-pricelist-form-modal.modal {
		position: fixed !important;
		top: 0 !important;
		right: 0 !important;
		bottom: 0 !important;
		left: 0 !important;
		width: 100vw !important;
		height: 100vh !important;
		min-height: 100dvh !important;
		margin: 0 !important;
		overflow-x: hidden;
		overflow-y: auto;
		z-index: 1060 !important;
	}
	.ls-pricelist-form-modal.modal.show {
		display: block !important;
	}
	.ls-pricelist-form-modal .ls-pricelist-form-dialog {
		max-width: min(800px, calc(100vw - 2rem)) !important;
		width: calc(100% - 2rem);
		margin: 1.5rem auto !important;
		max-height: calc(100vh - 3rem);
		display: flex;
		align-items: stretch;
	}
	.ls-pricelist-form-modal .item-modal-content {
		border: 0;
		border-radius: 18px;
		overflow: hidden;
		box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
		max-height: calc(100vh - 3rem);
		width: 100%;
		display: flex;
		flex-direction: column;
	}

	/* Beat .lab-surface-theme.ls-admin-page .modal .modal-header */
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-form-modal .modal-header,
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-form-modal .modal-header.bg-light,
	.ls-pricelist-form-modal .ls-pricelist-form-header,
	.ls-pricelist-form-modal .item-modal-header.ls-pricelist-form-header {
		padding: 1.25rem 2.25rem !important;
		background: var(--color-primary, #6D0A0E) !important;
		border-bottom: 0 !important;
		color: #fff !important;
		flex: 0 0 auto;
	}
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-form-modal .modal-header .modal-title,
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-form-modal .modal-header h5,
	.lab-surface-theme.ls-admin-page .modal.ls-pricelist-form-modal .modal-header .mdi,
	.ls-pricelist-form-modal .ls-pricelist-form-header .modal-title {
		color: #fff !important;
		font-weight: 700;
	}
	.ls-pricelist-form-modal .ls-pricelist-form-subtitle {
		color: #fff !important;
		opacity: 0.92;
		font-size: 0.86rem;
	}
	.ls-pricelist-form-modal .ls-pricelist-form-close {
		color: #fff !important;
		opacity: 0.9;
		text-shadow: none;
		filter: invert(1) grayscale(100%) brightness(200%);
	}
	.ls-pricelist-form-modal .ls-pricelist-form-close:hover {
		opacity: 1;
	}

	.ls-pricelist-form-modal .item-modal-body {
		padding: 1rem 2.25rem 0.85rem !important;
		background: #f7fbfe !important;
		overflow-y: auto !important;
		flex: 1 1 auto;
		min-height: 0;
		-webkit-overflow-scrolling: touch;
	}
	.ls-pricelist-form-modal .item-modal-footer {
		padding: 0.85rem 2.25rem 1.35rem !important;
		background: #fff;
		border-top: 1px solid #dbeaf5 !important;
		flex: 0 0 auto;
	}
	.ls-pricelist-form-modal .item-modal-label {
		font-size: 0.78rem !important;
		font-weight: 700 !important;
		color: #334155 !important;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		margin-bottom: 0.55rem !important;
	}
	.ls-pricelist-form-modal .item-modal-section {
		background: #fff !important;
		border: 1px solid #d7e8f4 !important;
		border-radius: 14px;
		padding: 14px 16px 6px;
	}
	.ls-pricelist-form-modal .item-modal-cancel-btn,
	.ls-pricelist-form-modal .item-modal-save-btn {
		border-radius: 10px;
		min-height: 40px;
		font-weight: 600;
		padding: 0 16px;
	}
	.ls-pricelist-form-modal .item-modal-save-btn {
		background: var(--color-primary, #6D0A0E) !important;
		border-color: var(--color-primary, #6D0A0E) !important;
	}
	.ls-pricelist-form-modal .item-modal-save-btn:hover {
		background: var(--color-primary-hover, #8b1e2d) !important;
		border-color: var(--color-primary-hover, #8b1e2d) !important;
	}

	.ls-pricelist-form-modal .modal-input {
		min-height: 44px;
		border-radius: 10px;
		border: 1px solid #dbe3ef;
		box-shadow: none;
	}
	.ls-pricelist-form-modal .modal-input:focus {
		border-color: #93c5fd;
		box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.15);
	}

	.ls-pricelist-form-modal .flag-card {
		border: 1px dashed #cbd5e1;
		border-radius: 10px;
		padding: 10px 12px;
		background: #f8fafc;
		min-height: 44px;
	}
	.ls-pricelist-form-modal .flag-card .form-check-label {
		color: #334155;
		font-weight: 600;
	}

	.ls-pricelist-form-modal .modal-note {
		border: 1px solid #bfdbfe;
		background: #eff6ff;
		color: #1e3a8a;
		border-radius: 10px;
		padding: 10px 12px;
		font-size: 13px;
		display: flex;
		align-items: flex-start;
		gap: 8px;
	}
	.ls-pricelist-form-modal .modal-note i {
		font-size: 16px;
		margin-top: 1px;
	}

	.ls-pricelist-form-modal .tag-select-container {
		position: relative;
		cursor: text;
	}
	.ls-pricelist-form-modal .tag-select-input {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 6px;
		padding: 6px 12px;
		background: #fff;
		border: 1px solid #dbe3ef;
		border-radius: 10px;
		transition: all 0.2s ease;
	}
	.ls-pricelist-form-modal .tag-select-input:hover {
		border-color: #93c5fd;
	}
	.ls-pricelist-form-modal .tag-select-input:focus-within {
		border-color: #93c5fd;
		box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.15);
	}
	.ls-pricelist-form-modal .tag-select-input.is-invalid {
		border-color: #dc3545;
	}
	.ls-pricelist-form-modal .tag-badge {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		padding: 4px 10px;
		background-color: #eff6ff;
		color: #1d4ed8;
		border: 1px solid #bfdbfe;
		border-radius: 16px;
		font-size: 12px;
		font-weight: 600;
		white-space: nowrap;
	}
	.ls-pricelist-form-modal .tag-badge i {
		cursor: pointer;
		font-size: 14px;
	}
	.ls-pricelist-form-modal .tag-input {
		flex: 1;
		min-width: 120px;
		border: none;
		outline: none;
		padding: 4px;
		font-size: 14px;
	}
	.ls-pricelist-form-modal .tag-dropdown {
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;
		background: #fff;
		border: 1px solid #93c5fd;
		border-top: none;
		border-radius: 0 0 10px 10px;
		max-height: 240px;
		overflow-y: auto;
		z-index: 1050;
		box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
		margin-top: -1px;
	}
	.ls-pricelist-form-modal .tag-dropdown-item {
		padding: 9px 12px;
		cursor: pointer;
		transition: background-color 0.15s ease;
		border-bottom: 1px solid #f1f5f9;
		font-size: 13px;
	}
	.ls-pricelist-form-modal .tag-dropdown-item:hover {
		background-color: #eff6ff;
	}
	.ls-pricelist-form-modal .tag-dropdown-item:last-child {
		border-bottom: none;
	}
	.ls-pricelist-form-modal .tag-dropdown-item--add {
		width: 100%;
		text-align: left;
		border: 0;
		background: #f8fafc;
		color: #1d4ed8;
		font-weight: 600;
	}
	.ls-pricelist-form-modal .tag-dropdown-item--add:hover {
		background: #eff6ff;
	}

	@media (max-width: 576px) {
		.ls-pricelist-form-modal .ls-pricelist-form-header,
		.ls-pricelist-form-modal .item-modal-body,
		.ls-pricelist-form-modal .item-modal-footer {
			padding-left: 1.15rem !important;
			padding-right: 1.15rem !important;
		}
	}
</style>
@endonce
